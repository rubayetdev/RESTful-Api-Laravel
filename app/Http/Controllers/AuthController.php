<?php

namespace App\Http\Controllers;

use App\Models\Devices;
use App\Models\User;
use App\Notifications\ResetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create a personal access token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = Auth::user(); // Retrieve the currently authenticated user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Logged in successfully',
        ]);
    }


    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete(); // Revoke all tokens

        return response()->json(['message' => 'Logged out successfully']);
    }


//    public function sendResetLinkEmail(Request $request)
//    {
//        $request->validate(['email' => 'required|email']);
//
//        $status = Password::broker()->sendResetLink(
//            $request->only('email'),
//            function ($user, $token) {
//                session(['reset_token' => $token]);
//                $user->notify(new ResetNotification($token));
//            }
//        );
//        $token = session('reset_token');
//        if ($status === Password::RESET_LINK_SENT) {
//            return response()->json([
//                'message' => 'Password reset link sent to your email address.',
//                'token' => $token
//            ], 200);
//        }
//
//
//        return response()->json([
//            'message' => $status === Password::INVALID_USER
//                ? 'No user found with this email address.'
//                : 'An error occurred. Please try again.',
//        ], $status === Password::INVALID_USER ? 404 : 400);
//    }
//
//    public function resetPassword(Request $request)
//    {
//        $request->validate([
//            'email' => 'required|email',
//            'token' => 'required|string',
//            'password' => 'required|string|min:8|confirmed',
//        ]);
//
//        $status = Password::reset(
//            $request->only('email', 'password', 'password_confirmation', 'token'),
//            function ($user, $password) {
//                $user->forceFill([
//                    'password' => Hash::make($password)
//                ])->save();
//            }
//        );
//
//        return $status === Password::PASSWORD_RESET
//            ? response()->json(['message' => __($status)], 200)
//            : response()->json(['message' => __($status)], 400);
//    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'No user found with this email address.'
            ], 404);
        }

        // Generate OTP and store it with an expiration time
        $otp = rand(100000, 999999); // 6-digit OTP
        $expiresAt = now()->addMinutes(5); // OTP expires in 5 minutes

        // Store OTP and expiration time in cache
        Cache::put('otp_' . $user->id, [
            'otp' => $otp,
            'expires_at' => $expiresAt
        ]);

        // Send OTP via email
       Mail::to($user->email)->send(new \App\Mail\ResetNotification($otp));

        return response()->json([
            'message' => 'OTP sent to your email address.','otp'=>$otp
        ], 200);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'No user found with this email address.'], 404);
        }

        $otpData = Cache::get('otp_' . $user->id);

        if ($otpData && isset($otpData['otp']) && $otpData['otp'] === (int)$request->otp) {
            // OTP is valid, proceed with password reset
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();

            // Remove OTP from cache after use
            Cache::forget('otp_' . $user->id);

            return response()->json(['message' => 'Password has been reset.'], 200);
        } else {
            // OTP is invalid or expired
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

    }



    public function clearThrottle(Request $request)
    {
        $throttleKey = Str::lower($request->input('email')).'|'.$request->ip();
        RateLimiter::clear($throttleKey);

        return response()->json(['message' => 'Throttle cleared. You can try again.']);
    }
}
