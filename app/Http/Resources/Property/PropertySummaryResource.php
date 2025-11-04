<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertySummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $photos = $this->photos ?? [];
        $thumbnail = !empty($photos) ? $photos[0]['url'] ?? null : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'city' => $this->city,
            'type' => $this->type,
            'price' => (float) $this->price,
            'thumbnail' => $thumbnail,
            'avg_rating' => $this->reviews()->avg('rating'),
        ];
    }
}
