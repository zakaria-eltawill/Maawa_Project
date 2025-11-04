<?php

namespace App\Http\Resources\FcmToken;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FcmTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'token' => $this->token,
        ];
    }
}
