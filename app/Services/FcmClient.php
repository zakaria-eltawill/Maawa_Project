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
     * Send push notification to a specific FCM token using FCM V1 API.
     *
     * @param  User  $user
     * @param  string  $token
     * @param  array  $payload
     * @return void
     */
    protected static function sendToToken(User $user, string $token, array $payload): void
    {
        // Try V1 API first (OAuth2)
        $accessToken = FcmOAuth2Service::getAccessToken();

        if (!empty($accessToken)) {
            self::sendViaV1Api($user, $token, $payload, $accessToken);
            return;
        }

        // Fallback to Legacy API if V1 is not configured
        $serverKey = config('services.fcm.server_key');

        if (!empty($serverKey)) {
            self::sendViaLegacyApi($user, $token, $payload, $serverKey);
            return;
        }

        // No configuration available
        Log::warning('FCM not configured (neither V1 OAuth2 nor Legacy server key). Notification not sent.', [
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
            'error_message' => 'FCM not configured (neither V1 OAuth2 nor Legacy server key)',
        ]);
    }

    /**
     * Send notification via FCM V1 API.
     */
    protected static function sendViaV1Api(User $user, string $token, array $payload, string $accessToken): void
    {
        $projectId = config('services.fcm.project_id');

        if (empty($projectId)) {
            Log::error('FCM project ID not configured');
            NotificationLog::create([
                'user_id' => $user->id,
                'type' => 'push',
                'channel' => 'fcm',
                'status' => 'FAILED',
                'recipient' => $token,
                'title' => $payload['title'] ?? null,
                'body' => $payload['body'] ?? null,
                'data' => $payload['data'] ?? null,
                'error_message' => 'FCM project ID not configured',
            ]);
            return;
        }

        try {
            // FCM V1 API endpoint
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // V1 API message format
            $message = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $payload['title'] ?? '',
                        'body' => $payload['body'] ?? '',
                    ],
                ],
            ];

            // Add data payload if present (V1 API requires string values)
            if (!empty($payload['data'])) {
                $message['message']['data'] = array_map('strval', $payload['data']);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $message);

            $success = $response->successful();
            $status = $success ? 'SENT' : 'FAILED';
            $errorMessage = null;

            if (!$success) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $response->body();

                Log::warning('FCM V1 API request failed', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);
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
            Log::error('Failed to send FCM V1 notification', [
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

    /**
     * Send notification via Legacy FCM API (fallback).
     */
    protected static function sendViaLegacyApi(User $user, string $token, array $payload, string $serverKey): void
    {
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
            Log::error('Failed to send FCM Legacy notification', [
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

