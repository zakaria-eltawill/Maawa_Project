<?php

namespace App\Services;

use App\Models\FcmToken;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmClient
{
    /**
     * Send push notification to multiple users.
     *
     * @param  array<User>|Collection  $users
     * @param  array  $payload
     * @return void
     */
    public static function sendToUsers(array|Collection $users, array $payload): void
    {
        $users = is_array($users) ? collect($users) : $users;

        foreach ($users as $user) {
            self::sendToUser($user, $payload);
        }
    }

    /**
     * Send push notification to a single user.
     *
     * @param  User  $user
     * @param  array  $payload
     * @return void
     */
    public static function sendToUser(User $user, array $payload): void
    {
        $tokens = FcmToken::where('user_id', $user->id)->pluck('token');

        if ($tokens->isEmpty()) {
            // Log that user has no FCM tokens
            NotificationLog::create([
                'user_id' => $user->id,
                'type' => 'push',
                'channel' => 'fcm',
                'status' => 'FAILED',
                'recipient' => $user->email,
                'title' => $payload['title'] ?? null,
                'body' => $payload['body'] ?? null,
                'data' => $payload['data'] ?? null,
                'error_message' => 'User has no FCM tokens registered',
            ]);

            return;
        }

        foreach ($tokens as $token) {
            self::sendToToken($user, $token, $payload);
        }
    }

    /**
     * Send push notification to a specific FCM token.
     *
     * @param  User  $user
     * @param  string  $token
     * @param  array  $payload
     * @return void
     */
    protected static function sendToToken(User $user, string $token, array $payload): void
    {
        $serverKey = config('services.fcm.server_key');

        if (empty($serverKey)) {
            Log::warning('FCM server key not configured. Notification not sent.', [
                'user_id' => $user->id,
                'token' => substr($token, 0, 20) . '...',
            ]);

            NotificationLog::create([
                'user_id' => $user->id,
                'type' => 'push',
                'channel' => 'fcm',
                'status' => 'FAILED',
                'recipient' => $token,
                'title' => $payload['title'] ?? null,
                'body' => $payload['body'] ?? null,
                'data' => $payload['data'] ?? null,
                'error_message' => 'FCM server key not configured',
            ]);

            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => $payload['title'] ?? '',
                    'body' => $payload['body'] ?? '',
                ],
                'data' => $payload['data'] ?? [],
            ]);

            $success = $response->successful();
            $status = $success ? 'SENT' : 'FAILED';
            $errorMessage = null;

            if (!$success) {
                $errorMessage = $response->json('error') ?? $response->body();
            }

            NotificationLog::create([
                'user_id' => $user->id,
                'type' => 'push',
                'channel' => 'fcm',
                'status' => $status,
                'recipient' => $token,
                'title' => $payload['title'] ?? null,
                'body' => $payload['body'] ?? null,
                'data' => $payload['data'] ?? null,
                'error_message' => $errorMessage,
                'sent_at' => $success ? now() : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification', [
                'user_id' => $user->id,
                'token' => substr($token, 0, 20) . '...',
                'error' => $e->getMessage(),
            ]);

            NotificationLog::create([
                'user_id' => $user->id,
                'type' => 'push',
                'channel' => 'fcm',
                'status' => 'FAILED',
                'recipient' => $token,
                'title' => $payload['title'] ?? null,
                'body' => $payload['body'] ?? null,
                'data' => $payload['data'] ?? null,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}

