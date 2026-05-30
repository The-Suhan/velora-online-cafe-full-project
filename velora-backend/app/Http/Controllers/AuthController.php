<?php

namespace App\Http\Controllers;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // ── REGISTER ──────────────────────────────────────────────
    // POST /api/register
    // Body: { name, email, password, password_confirmation }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'is_verified' => false,
        ]);

        $this->sendOtp($user, 'register');

        return response()->json([
            'message' => 'Register succesful, check your email.',
            'email' => $user->email,
        ], 201);
    }

    // ── VERIFY OTP ────────────────────────────────────────────
    // POST /api/verify-otp
    // Body: { email, code }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $otp = OtpCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (! $otp) {
            return response()->json(['message' => 'Invalid OTP code.'], 422);
        }

        if ($otp->isExpired()) {
            return response()->json(['message' => 'OTP code has expired. Please request a new code.'], 422);
        }

        // OTP geçerli — kullanıcıyı doğrula
        $otp->update(['is_used' => true]);
        $user->update(['is_verified' => true]);

        // Bearer token oluştur
        $token = $user->generateAuthToken();

        return response()->json([
            'message' => 'Verification successful.',
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    // ── LOGIN ─────────────────────────────────────────────────
    // POST /api/login
    // Body: { email, password }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        // Hesap doğrulanmamışsa yeni OTP gönder
        if (! $user->is_verified) {
            $this->sendOtp($user, 'login');

            return response()->json([
                'message' => 'Your account is not verified. A new OTP code has been sent.',
                'email' => $user->email,
                'requires_verify' => true,
            ], 403);
        }

        $token = $user->generateAuthToken();

        return response()->json([
            'message' => 'Giriş başarılı.',
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    // ── RESEND OTP ────────────────────────────────────────────
    // POST /api/resend-otp
    // Body: { email }

    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->is_verified) {
            return response()->json(['message' => 'Account is already verified.'], 400);
        }

        // Önceki kullanılmamış OTP'leri iptal et
        OtpCode::where('user_id', $user->id)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $this->sendOtp($user, 'register');

        return response()->json([
            'message' => 'Your new OTP code has been sent.',
            'email' => $user->email,
        ]);
    }

    // ── LOGOUT ───────────────────────────────────────────────
    // POST /api/logout
    // Header: Authorization: Bearer {token}

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Log out.']);
    }

    public function deleteMe(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully.']);
    }
    // ── ME ────────────────────────────────────────────────────
    // GET /api/me
    // Header: Authorization: Bearer {token}

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_verified' => $user->is_verified,
        ]);
    }

    public function updateMe(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'name' => 'sometimes|string|max:100',
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (! Hash::check($value, $user->password)) {
                        $fail('Current password is incorrect.');
                    }
                },
            ];
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    // ── PRIVATE: OTP üret ve gönder ───────────────────────────

    private function sendOtp(User $user, string $type): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
        ]);

        Mail::send([], [], function ($message) use ($user, $code) {
            $message
                ->to($user->email, $user->name)
                ->subject('Velora Café — Your Verification Code')
                ->html("
                    <div style='font-family:sans-serif;max-width:480px;margin:auto;padding:32px;'>
                        <h2 style='color:#2C1810;'>Velora Café</h2>
                        <p>Hello  <strong>{$user->name}</strong>,</p>
                        <p>Your verification code is:</p>
                        <div style='font-size:2.5rem;font-weight:bold;letter-spacing:0.4em;
                                    color:#2C1810;background:#F5F0E8;padding:20px;
                                    text-align:center;border-radius:12px;margin:24px 0;'>
                            {$code}
                        </div>
                        <p style='color:#888;font-size:0.85rem;'>
                            This code is valid for <strong>10 minutes</strong>.<br>
                            If you did not request this action, please ignore this email.
                        </p>
                    </div>
                ");
        });
    }
}
