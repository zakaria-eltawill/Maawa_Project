<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Idempotency-Key');

        if (!$key) {
            return $next($request);
        }

        $userId = auth()->id();
        $method = $request->method();
        $path = $request->path();

        // Check for existing idempotency key
        $existing = IdempotencyKey::where('key', $key)
            ->where('user_id', $userId)
            ->where('method', $method)
            ->where('path', $path)
            ->where('expires_at', '>', now())
            ->first();

        if ($existing && $existing->response) {
            // Return cached response
            return response()->json(
                $existing->response,
                $existing->status_code ?? 200
            );
        }

        // Process request
        $response = $next($request);

        // Store response for idempotency (only for successful requests)
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            IdempotencyKey::updateOrCreate(
                [
                    'key' => $key,
                    'user_id' => $userId,
                    'method' => $method,
                    'path' => $path,
                ],
                [
                    'response' => json_decode($response->getContent(), true),
                    'status_code' => $response->getStatusCode(),
                    'expires_at' => now()->addHours(24),
                ]
            );
        }

        return $response;
    }
}
