<?php

namespace App\Http\Controllers;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // ── REGISTER ──────────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // DB'de zaten kayıtlı ve doğrulanmış biri var mı?
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && $existingUser->is_verified) {
            return response()->json(['message' => 'This email is already registered.'], 422);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // DB'ye yazmıyoruz — Cache'e 15 dk saklıyoruz
        Cache::put("pending_register:{$request->email}", [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'code' => $code,
        ], now()->addMinutes(15));

        $this->sendOtpToEmail($request->email, $request->name, $code);

        return response()->json([
            'message' => 'Register successful, check your email.',
            'email' => $request->email,
        ], 201);
    }

    // ── VERIFY OTP ────────────────────────────────────────────
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        // DB'de kullanıcı var mı? (login sonrası verify veya re-verify)
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $otp = OtpCode::where('user_id', $user->id)
                ->where('code', $request->code)
                ->where('is_used', false)
                ->latest()
                ->first();

            if (! $otp || $otp->isExpired()) {
                return response()->json(['message' => 'Invalid or expired OTP.'], 422);
            }

            $otp->update(['is_used' => true]);
            $user->update(['is_verified' => true]);

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

        // Yeni kayıt — Cache'ten al
        $pending = Cache::get("pending_register:{$request->email}");

        if (! $pending) {
            return response()->json([
                'message' => 'Registration session expired. Please register again.',
            ], 422);
        }

        if ($pending['code'] !== $request->code) {
            return response()->json(['message' => 'Invalid OTP code.'], 422);
        }

        // ✅ Kod doğru — şimdi DB'ye yaz
        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'password' => $pending['password'],
            'role' => 'customer',
            'is_verified' => true,
        ]);

        Cache::forget("pending_register:{$request->email}");

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
            'message' => 'Login successful.',
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
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // DB'de var mı? (login verify akışı)
        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->is_verified) {
                return response()->json(['message' => 'Account is already verified.'], 400);
            }

            OtpCode::where('user_id', $user->id)
                ->where('is_used', false)
                ->update(['is_used' => true]);

            $this->sendOtp($user, 'register');

            return response()->json([
                'message' => 'New OTP sent.',
                'email' => $user->email,
            ]);
        }

        // Cache'te pending register var mı? (register akışı)
        $pending = Cache::get("pending_register:{$request->email}");

        if (! $pending) {
            return response()->json(['message' => 'User not found. Please register again.'], 404);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $pending['code'] = $code;
        Cache::put("pending_register:{$request->email}", $pending, now()->addMinutes(15));

        $this->sendOtpToEmail($pending['email'], $pending['name'], $code);

        return response()->json([
            'message' => 'New OTP sent.',
            'email' => $request->email,
        ]);
    }

    // ── LOGOUT ───────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    // ── DELETE ME ────────────────────────────────────────────
    public function deleteMe(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully.']);
    }

    // ── ME ────────────────────────────────────────────────────
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

    // ── UPDATE ME ─────────────────────────────────────────────
    public function updateMe(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = ['name' => 'sometimes|string|max:100'];

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

    // ── PRIVATE: User objesiyle OTP (login/resend için) ───────
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

        $this->sendOtpToEmail($user->email, $user->name, $code);
    }

    // ── PRIVATE: Sadece email/name ile mail gönder (register için) ──
    private function sendOtpToEmail(string $email, string $name, string $code): void
    {
        $spaced = implode(' ', str_split($code));

        Mail::send([], [], function ($message) use ($email, $name, $spaced) {
            $message
                ->to($email, $name)
                ->subject('Velora Café — Your Verification Code')
                ->html("
            <!DOCTYPE html>
            <html lang='en'>
            <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Velora Café — Verification Code</title>
            </head>
            <body style='margin:0;padding:0;background:#E8E0D4;font-family:Arial,Helvetica,sans-serif;'>
            
              <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 16px;'>
                <tr>
                  <td align='center'>
                    <table width='100%' style='max-width:480px;background:#F5F0E8;border-radius:16px;overflow:hidden;'>
            
                      <!-- Header -->
                      <tr>
                        <td style='background:#2C1810;padding:28px 20px;text-align:center;'>
                          <p style='margin:0;font-size:13px;letter-spacing:0.35em;color:#C8A96E;font-weight:700;'>VELORA</p>
                          <p style='margin:5px 0 0;font-size:9px;letter-spacing:0.25em;color:#8a6a4a;'>C A F É</p>
                        </td>
                      </tr>
            
                      <!-- Body -->
                      <tr>
                        <td style='padding:32px 28px 28px;'>
            
                          <p style='margin:0 0 6px;font-size:15px;color:#2C1810;'>Hello, <strong>{$name}</strong></p>
                          <p style='margin:0 0 24px;font-size:13px;color:#8a7060;line-height:1.6;'>
                            Your verification code for Velora Café is below.<br>
                            Enter it to complete your registration.
                          </p>
            
                          <!-- OTP Box -->
                          <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:24px;'>
                            <tr>
                              <td style='background:#2C1810;border-radius:12px;padding:20px 12px;text-align:center;'>
                                <span style='font-size:32px;font-weight:700;letter-spacing:0.4em;color:#C8A96E;'>{$spaced}</span>
                              </td>
                            </tr>
                          </table>
            
                          <!-- Validity -->
                          <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:24px;'>
                            <tr>
                              <td style='background:#EDE5D8;border-radius:8px;padding:12px 16px;text-align:center;'>
                                <span style='font-size:12px;color:#8a7060;'>
                                  ⏱ This code expires in <strong style='color:#2C1810;'>15 minutes</strong>
                                </span>
                              </td>
                            </tr>
                          </table>
            
                          <!-- Footer note -->
                          <p style='margin:0;font-size:11px;color:#b0967a;line-height:1.7;text-align:center;border-top:1px solid #D9CFC0;padding-top:20px;'>
                            If you didn't create an account with Velora Café,<br>
                            you can safely ignore this email.
                          </p>
            
                        </td>
                      </tr>
            
                      <!-- Bottom bar -->
                      <tr>
                        <td style='background:#EDE5D8;padding:14px 20px;text-align:center;'>
                          <p style='margin:0;font-size:10px;color:#b0967a;letter-spacing:0.1em;'>
                            © Velora Café — All rights reserved
                          </p>
                        </td>
                      </tr>
            
                    </table>
                  </td>
                </tr>
              </table>
            
            </body>
            </html>");
        });
    }
}
