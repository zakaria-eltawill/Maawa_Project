<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->resource),
            'role' => $this->role,
            'fcm_tokens' => $this->fcmTokens->pluck('token')->toArray(),
        ];
    }
}
