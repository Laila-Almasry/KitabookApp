<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class UserController extends Controller
{

public function handleGoogleLogin(Request $request)
{
    $validated = $request->validate([
        'access_token' => 'required',
    ]);

    try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
          $driver = Socialite::driver('google');
      $googleUser = $driver->stateless()
          ->userFromToken($validated['access_token']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Invalid Google token'], 401);
    }

    $googleId = $googleUser->getId();
    $email = $googleUser->getEmail();

    // Try to find existing user
    $user = User::where('provider', 'google')
                ->where('provider_id', $googleId)
                ->first();

    if (!$user) {
        // Generate username from email
        $baseUsername = strstr($email, '@', true);
        $username = $baseUsername;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $suffix;
            $suffix++;
        }

        // Create new user
        $user = User::create([
            'provider' => 'google',
            'provider_id' => $googleId,
            'email' => $email,
            'username' => $username,
        ]);

        $statusMessage = 'New Google user created.';
    } else {
        $statusMessage = 'Existing Google user.';
    }

    $token = $user->createToken('google_auth_token')->plainTextToken;

    return response()->json([
        'message' => $statusMessage,
        'token' => $token,
        'user' => $user,
    ], 200);
}

public function handleTelegramLogin(Request $request)
{
    $validated = $request->validate([
        'username' => 'required',
    ]);

    $user = User::where('provider', 'telegram')
                ->where('username', $validated['username'])
                ->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $otp = rand(100000, 999999);

    // Store the OTP for verification (you'll need to add this column to your users table)
    $user->otp = $otp;
    $user->otp_expires_at = now()->addMinutes(5); // OTP valid for 5 minutes
    $user->save();

    $userTelegramId = $user->provider_id; // Assuming the column is provider_id
    $message = "Your OTP is {$otp}";

    $this->sendTelegramMessage($userTelegramId, $message);

    return response()->json(['message' => 'OTP sent to user'], 200);
}

public function verifyOtp(Request $request)
{
    $validated = $request->validate([
        'username' => 'required|string',
        'otp' => 'required|digits:6'
    ]);

    // Find the user
    $user = User::where('username', $validated['username'])
                ->where('provider', 'telegram')
                ->first();

    if (!$user) {
        return response()->json([
            'message' => 'User not found',
            'success' => false
        ], 404);
    }

    // Check if OTP exists
    if (empty($user->otp)) {
        return response()->json([
            'message' => 'No OTP requested. Please request a new OTP first.',
            'success' => false
        ], 400);
    }

    // Check if OTP is expired
    if (now()->gt($user->otp_expires_at)) {
        return response()->json([
            'message' => 'OTP has expired. Please request a new one.',
            'success' => false
        ], 400);
    }

    // Verify OTP matches
    if ($user->otp !== $validated['otp']) {
        return response()->json([
            'message' => 'Invalid OTP',
            'success' => false
        ], 400);
    }

    // OTP is valid - clear it
    $user->otp = null;
    $user->otp_expires_at = null;
    $user->save();

    // Revoke all previous tokens
    $user->tokens()->delete();

    // Create new Sanctum token
    $token = $user->createToken('telegram-otp-token', ['*'])->plainTextToken;

    // Send success message to Telegram bot
    $telegramId = $user->provider_id;
    $successMessage = "OTP verified successfully!\n\nYou're now logged in to your account.";
    $this->sendTelegramMessage($telegramId, $successMessage);

    return response()->json([
        'message' => 'OTP verified successfully',
        'success' => true,
        'user' => $user->only(['id', 'username', 'email', 'wallet_id']),
        'token' => $token,
        'token_type' => 'Bearer'
    ], 200);
}
public function handleTelegramRegister(Request $request)
{
    $validated = $request->validate([
        'username' => 'required',
        'telegram_id' => 'required',
    ]);

    $username = $validated['username'];
    $telegramId = $validated['telegram_id'];

    $user = User::where('provider_id', $telegramId)
                ->where('provider', 'telegram')
                ->first();

    $telegramMessage = '';

    if (!$user) {
        // Check if the username is already taken by someone else
        $existingUserWithUsername = User::where('username', $username)->first();

        if ($existingUserWithUsername && $existingUserWithUsername->provider_id !== $telegramId) {
            // Set old user's username to null because they probably changed it on Telegram
            $existingUserWithUsername->update(['username' => null]);
        }

        // Create new user with the Telegram username
        $user = User::create([
            'provider' => 'telegram',
            'provider_id' => $telegramId,
            'username' => $username,
        ]);
        $telegramMessage = "Welcome @$username! You’re now registered.";
    } else {
        // Existing user: check if their Telegram username changed
        if ($user->username !== $username) {
            $otherUserWithUsername = User::where('username', $username)
                                         ->where('provider_id', '!=', $telegramId)
                                         ->first();

            if (!$otherUserWithUsername) {
                // Username is available → update
                $user->update(['username' => $username]);
                $telegramMessage = "Welcome back @$username! Your username has been updated.";
            } else {
                // Username is taken by another user → can't update, stick with old username
                $telegramMessage = "Welcome back @$user->username!";
            }
        } else {
            // No change
            $telegramMessage = "Welcome back @$username!";
        }
    }
    return response()->json([
        'message' => $telegramMessage,
    ], 200);
}


    public function checkRegistration(Request $request) {
        // Validate the incoming request
        $validated = $request->validate([
            'telegram_id' => 'required',
            'username' => 'required'
        ]);

        $username = $validated['username'];
        $telegramId = $validated['telegram_id'];

        // Attempt to find the user by telegram ID
        $user = User::where('provider_id', $telegramId)
        ->where('provider', 'telegram')
        ->first();

        // Check if the user exists
        if (!$user) {
            return response()->json(['status'=>1], 200); // User not found
        } else {
            // Check if the username matches
            if ($user->username === $username) {
                return response()->json(['status'=>2], 200); // Username matches
            } else {
                return response()->json(['status'=>3], 200); // Username does not match
            }
        }
    }

private function sendTelegramMessage($telegramId, $message)
{
    $botApiUrl = 'http://127.0.0.1:3000/send-message';

    $payload = [
        'user_id' => $telegramId,
        'message' => $message,
    ];

    try {
        $client = new \GuzzleHttp\Client();
        $response = $client->post($botApiUrl, [
            'json' => $payload, // This will send the data as JSON in the request body
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        // Optional: You might want to check the response status
        if ($response->getStatusCode() !== 200) {
            Log::error("Telegram API returned status code: " . $response->getStatusCode());
        }

    } catch (\Exception $e) {
        // Log error but don't fail the flow
        Log::error("Failed to send Telegram message: " . $e->getMessage());
    }
}


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
