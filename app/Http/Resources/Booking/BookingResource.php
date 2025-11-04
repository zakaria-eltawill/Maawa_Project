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
        ];
    }
}
