<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proposal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'owner_id',
        'property_id',
        'type',
        'status',
        'notes',
        'payload',
        'version',
        'reason',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'applied_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
