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

        if (! $user) {
            return response()->json([
                'message' => 'No account found with this email. Please create an account.',
            ], 404);
        }

        OtpCode::where('user_id', $user->id)
            ->where('type', 'reset')
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'type' => 'reset',
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
        ]);

        Mail::send([], [], function ($message) use ($user, $code) {
            $spaced = implode(' ', str_split($code));
            $name = $user->name;

            $message
                ->to($user->email, $user->name)
                ->subject('Velora Café — Password Reset Code')
                ->html("<!DOCTYPE html>
                <html lang='en'>
                <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                </head>
                <body style='margin:0;padding:0;background:#E8E0D4;font-family:Arial,Helvetica,sans-serif;'>
                  <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 16px;'>
                    <tr>
                      <td align='center'>
                        <table width='100%' style='max-width:480px;background:#F5F0E8;border-radius:16px;overflow:hidden;'>
                          <tr>
                            <td style='background:#2C1810;padding:28px 20px;text-align:center;'>
                              <p style='margin:0;font-size:13px;letter-spacing:0.35em;color:#C8A96E;font-weight:700;'>VELORA</p>
                              <p style='margin:5px 0 0;font-size:9px;letter-spacing:0.25em;color:#8a6a4a;'>C A F É</p>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:32px 28px 28px;'>
                              <p style='margin:0 0 6px;font-size:15px;color:#2C1810;'>Hello, <strong>{$name}</strong></p>
                              <p style='margin:0 0 24px;font-size:13px;color:#8a7060;line-height:1.6;'>
                                You requested a password reset for your Velora Café account.<br>
                                Use the code below to continue.
                              </p>
                              <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:24px;'>
                                <tr>
                                  <td style='background:#2C1810;border-radius:12px;padding:20px 12px;text-align:center;'>
                                    <span style='font-size:32px;font-weight:700;letter-spacing:0.4em;color:#C8A96E;'>{$spaced}</span>
                                  </td>
                                </tr>
                              </table>
                              <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:24px;'>
                                <tr>
                                  <td style='background:#EDE5D8;border-radius:8px;padding:12px 16px;text-align:center;'>
                                    <span style='font-size:12px;color:#8a7060;'>
                                      ⏱ This code expires in <strong style='color:#2C1810;'>10 minutes</strong>
                                    </span>
                                  </td>
                                </tr>
                              </table>
                              <p style='margin:0;font-size:11px;color:#b0967a;line-height:1.7;text-align:center;border-top:1px solid #D9CFC0;padding-top:20px;'>
                                If you did not request a password reset,<br>
                                you can safely ignore this email.
                              </p>
                            </td>
                          </tr>
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

        return response()->json([
            'message' => 'Reset code sent.',
            'email' => $request->email,
        ]);
    }

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
            'message' => 'OTP verified.',
            'reset_token' => base64_encode($user->id.'|'.$otp->id.'|'.$otp->code),
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
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
