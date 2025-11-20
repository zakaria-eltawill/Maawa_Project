<?php

namespace App\Http\Resources\Proposal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'property_id' => $this->property_id,
            'type' => $this->type,
            'status' => $this->status,
            'notes' => $this->notes,
            'payload' => $this->payload,
            'version' => $this->version,
            'reason' => $this->reason,
            'applied_at' => $this->applied_at?->format('Y-m-d\TH:i:s\Z'),
            'created_at' => $this->created_at?->format('Y-m-d\TH:i:s\Z'),
            'updated_at' => $this->updated_at?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
