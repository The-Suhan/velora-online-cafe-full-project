<?php

namespace App\Http\Controllers;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{


    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            OtpCode::where('user_id', $user->id)
                ->where('type', 'reset')
                ->where('is_used', false)
                ->update(['is_used' => true]);

            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            OtpCode::create([
                'user_id'    => $user->id,
                'code'       => $code,
                'type'       => 'reset',
                'expires_at' => now()->addMinutes(10),
                'is_used'    => false,
            ]);

            Mail::send([], [], function ($message) use ($user, $code) {
                $message
                    ->to($user->email, $user->name)
                    ->subject('Velora Café — Password Reset Code')
                    ->html("
                        <div style='font-family:sans-serif;max-width:480px;margin:auto;padding:32px;'>
                            <h2 style='color:#2C1810;'>Velora Café</h2>
                            <p>Hello <strong>{$user->name}</strong>,</p>
                            <p>Your password reset code:</p>
                            <div style='font-size:2.5rem;font-weight:bold;letter-spacing:0.4em;
                                        color:#2C1810;background:#F5F0E8;padding:20px;
                                        text-align:center;border-radius:12px;margin:24px 0;'>
                                {$code}
                            </div>
                            <p style='color:#888;font-size:0.85rem;'>
                                This code is valid for <strong>10 minutes</strong>.<br>
                                If you did not request this, please ignore this email.
                            </p>
                        </div>
                    ");
            });
        }

        return response()->json([
            'message' => 'If this email exists, a reset code has been sent.',
            'email'   => $request->email,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $otp = OtpCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('type', 'reset')
            ->where('is_used', false)
            ->latest()
            ->first();

        if (! $otp) {
            return response()->json(['message' => 'Invalid OTP code.'], 422);
        }

        if ($otp->isExpired()) {
            return response()->json(['message' => 'OTP code has expired.'], 422);
        }

        return response()->json([
            'message'      => 'OTP verified.',
            'reset_token'  => base64_encode($user->id . '|' . $otp->id . '|' . $otp->code),
        ]);
    }



    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'reset_token'          => 'required|string',
            'password'             => 'required|string|min:8|confirmed',
        ]);

        $decoded = base64_decode($request->reset_token);
        [$userId, $otpId, $otpCode] = explode('|', $decoded);

        $user = User::find($userId);

        if (! $user) {
            return response()->json(['message' => 'Invalid token.'], 422);
        }

        $otp = OtpCode::where('id', $otpId)
            ->where('user_id', $userId)
            ->where('code', $otpCode)
            ->where('type', 'reset')
            ->where('is_used', false)
            ->first();

        if (! $otp || $otp->isExpired()) {
            return response()->json(['message' => 'Reset token is invalid or expired.'], 422);
        }

        $otp->update(['is_used' => true]);

        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password reset successful. Please log in.',
        ]);
    }
}