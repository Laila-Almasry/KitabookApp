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
    $provider = $request->input('provider');
    $accessToken = $request->input('access_token');
  /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver($provider);
    $googleUser = $driver->stateless()
        ->userFromToken($accessToken);

    $user = User::updateOrCreate([
        'provider' => $provider,
        'provider_id' => $googleUser->getId(),
    ], [
        'fullname' => $googleUser->getName(),
        'email' => $googleUser->getEmail(),
        'image' => $googleUser->getAvatar(),
    ]);

    Auth::login($user);

    // return Sanctum token or user info
    return response()->json([
        'user' => $user,
        'token' => $user->createToken('authToken')->plainTextToken,
    ]);
}


public function handleTelegramLogin(Request $request)
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

    $statusMessage = '';
    $telegramMessage = '';
    $wasUsernameChanged = false;
     $isNewUser = false;

    if (!$user) {
        // Auto-resolve username collision
        $original = $username;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $original . '_' . $suffix;
            $suffix++;
            $wasUsernameChanged = true;
        }

        // Create new user
        $user = User::create([
            'provider' => 'telegram',
            'provider_id' => $telegramId,
            'username' => $username,
        ]);

         $isNewUser = true;
        $statusMessage = 'New user created.';
        $telegramMessage = $wasUsernameChanged
            ? "Welcome! Your username was changed to @$username because @$original was taken."
            : "Welcome @$username! You’re now registered.";
    } else {
        // Existing user
        if ($user->username !== $username) {
            if (!User::where('username', $username)->exists()) {
                $user->update(['username' => $username]);
                $telegramMessage = "Welcome back @$username! Your username has been updated.";
            } else {
                $telegramMessage = "Sorry, we see that your Telegram username changed, but the new one is already taken by another user — so we’ll stick with your old username @$user->username.";
            }
            $statusMessage = 'Existing user; username updated or retained.';
        } else {
            $telegramMessage = "Welcome back @$username!";
            $statusMessage = 'User exists with same username.';
        }
    }

    // Send message via bot
    $this->sendTelegramMessage($telegramId, $telegramMessage);

     $response = [
        'message' => $statusMessage,
    ];

    if (!$isNewUser) {
        $response['token'] = $user->createToken('auth_token')->plainTextToken;
    }

    return response()->json($response, 200);
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
    $botApiUrl = 'https://your-bot-backend.com/send-message';

    $payload = [
        'telegram_id' => $telegramId,
        'message' => $message,
    ];

    try {
        $client = new \GuzzleHttp\Client();
        $response = $client->post($botApiUrl, [
            'json' => $payload,
            'timeout' => 5,
        ]);
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
