<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'property_id' => $this->property_id,
            'tenant_id' => $this->tenant_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ];
    }
}
