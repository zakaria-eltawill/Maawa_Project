<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RefreshRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\MeResource;
use App\Http\Resources\Auth\UserResource;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone_number' => $request->phone_number,
            'region' => $request->region,
        ]);

        $token = JWTAuth::fromUser($user);
        $refreshToken = $this->createRefreshToken($user);

        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_token' => $refreshToken->token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $userRecord = User::where('email', $credentials['email'])->first();

        if ($userRecord && !$userRecord->is_active) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => __('auth.inactive'),
            ], 403);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Unauthorized',
                'status' => 401,
                'detail' => 'Invalid credentials',
            ], 401);
        }

        $user = JWTAuth::user();

        if (!$user->is_active) {
            JWTAuth::invalidate($token);

            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => __('auth.inactive'),
            ], 403);
        }

        $refreshToken = $this->createRefreshToken($user);

        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_token' => $refreshToken->token,
        ]);
    }

    public function refresh(RefreshRequest $request): JsonResponse
    {
        $refreshToken = RefreshToken::where('token', $request->refresh_token)
            ->whereNull('revoked_at')
            ->first();

        if (!$refreshToken || $refreshToken->isExpired()) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Unauthorized',
                'status' => 401,
                'detail' => 'Invalid or expired refresh token',
            ], 401);
        }

        // Revoke old token
        $refreshToken->update(['revoked_at' => now()]);

        // Create new tokens (rotation)
        $user = $refreshToken->user;

        if (!$user->is_active) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => __('auth.inactive'),
            ], 403);
        }

        $newAccessToken = JWTAuth::fromUser($user);
        $newRefreshToken = $this->createRefreshToken($user);

        return response()->json([
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_token' => $newRefreshToken->token,
        ]);
    }

    public function logout(LogoutRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($request->input('all', false)) {
            // Revoke all refresh tokens
            RefreshToken::where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        } else {
            // Revoke current refresh token (if provided in header/cookie)
            $token = $request->input('refresh_token');
            if ($token) {
                RefreshToken::where('token', $token)
                    ->where('user_id', $user->id)
                    ->update(['revoked_at' => now()]);
            }
        }

        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(null, 204);
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();
        return response()->json(new MeResource($user));
    }

    private function createRefreshToken(User $user): RefreshToken
    {
        return RefreshToken::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(30),
        ]);
    }
}
