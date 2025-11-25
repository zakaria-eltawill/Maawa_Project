<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmOAuth2Service
{
    /**
     * Get OAuth2 access token for FCM V1 API.
     * Tokens are cached for 50 minutes (tokens expire after 1 hour).
     */
    public static function getAccessToken(): ?string
    {
        return Cache::remember('fcm_oauth2_token', now()->addMinutes(50), function () {
            return self::generateAccessToken();
        });
    }

    /**
     * Generate a new OAuth2 access token using JWT.
     */
    protected static function generateAccessToken(): ?string
    {
        $config = config('services.fcm.service_account');

        if (empty($config['private_key']) || empty($config['client_email'])) {
            Log::error('FCM service account credentials not configured');
            return null;
        }

        // Create JWT for OAuth2
        $jwt = self::createJWT($config);

        if (!$jwt) {
            return null;
        }

        // Exchange JWT for access token
        try {
            $response = Http::asForm()->post($config['token_uri'], [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Failed to get FCM OAuth2 token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception getting FCM OAuth2 token', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create JWT for OAuth2 assertion.
     */
    protected static function createJWT(array $config): ?string
    {
        try {
            $now = Carbon::now();
            $expires = $now->copy()->addHour();

            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT',
            ];

            $payload = [
                'iss' => $config['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $config['token_uri'],
                'exp' => $expires->timestamp,
                'iat' => $now->timestamp,
            ];

            $headerEncoded = self::base64UrlEncode(json_encode($header));
            $payloadEncoded = self::base64UrlEncode(json_encode($payload));

            $signature = self::signJWT($headerEncoded . '.' . $payloadEncoded, $config['private_key']);
            if (!$signature) {
                return null;
            }

            $signatureEncoded = self::base64UrlEncode($signature);

            return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
        } catch (\Exception $e) {
            Log::error('Failed to create JWT for FCM', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sign JWT using RSA private key.
     */
    protected static function signJWT(string $data, string $privateKey): ?string
    {
        try {
            // Replace escaped newlines in private key
            $privateKey = str_replace(['\\n', "\n"], "\n", $privateKey);

            $key = openssl_pkey_get_private($privateKey);
            if ($key === false) {
                Log::error('Failed to load FCM private key', [
                    'error' => openssl_error_string(),
                ]);
                return null;
            }

            $signature = '';
            if (!openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256)) {
                openssl_free_key($key);
                Log::error('Failed to sign JWT', [
                    'error' => openssl_error_string(),
                ]);
                return null;
            }

            openssl_free_key($key);

            return $signature;
        } catch (\Exception $e) {
            Log::error('Exception signing JWT', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Base64 URL encode.
     */
    protected static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

