<?php

namespace App\Http\Resources\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'tenant_id' => $this->tenant_id,
            'check_in' => $this->check_in->format('Y-m-d'),
            'check_out' => $this->check_out->format('Y-m-d'),
            'guests' => $this->guests,
            'total' => (float) $this->total,
            'status' => $this->status,
            'payment_due_at' => $this->payment_due_at?->format('Y-m-d\TH:i:s\Z'),
            
            // Property details for displaying title, type, price
            'property' => $this->whenLoaded('property', function () {
                return [
                    'id' => $this->property->id,
                    'title' => $this->property->title,
                    'type' => $this->property->type,
                    'city' => $this->property->city,
                    'price' => (float) $this->property->price,
                    'thumbnail' => $this->property->thumbnail,
                ];
            }),
            
            // Tenant details for displaying name and contact info
            'tenant' => $this->whenLoaded('tenant', function () {
                return [
                    'id' => $this->tenant->id,
                    'name' => $this->tenant->name,
                    'email' => $this->tenant->email,
                    'phone_number' => $this->tenant->phone_number,
                    'region' => $this->tenant->region,
                ];
            }),
        ];
    }
}
