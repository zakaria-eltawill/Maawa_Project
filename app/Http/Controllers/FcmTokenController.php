<?php

namespace App\Http\Controllers;

use App\Http\Requests\FcmToken\StoreFcmTokenRequest;
use App\Http\Resources\FcmToken\FcmTokenResource;
use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;

class FcmTokenController extends Controller
{
    public function store(StoreFcmTokenRequest $request): JsonResponse
    {
        $user = auth()->user();

        $fcmToken = FcmToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $request->token,
            ],
            [
                'platform' => $request->platform,
            ]
        );

        return response()->json(new FcmTokenResource($fcmToken), 201);
    }

    public function destroy(string $token): JsonResponse
    {
        $user = auth()->user();

        FcmToken::where('user_id', $user->id)
            ->where('token', $token)
            ->delete();

        return response()->json(null, 204);
    }
}
