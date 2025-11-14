<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'city',
        'type',
        'price',
        'location_lat',
        'location_lng',
        'location_url',
        'amenities',
        'photos',
        'unavailable_dates',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'photos' => 'array',
            'unavailable_dates' => 'array',
            'price' => 'decimal:2',
            'location_lat' => 'float',
            'location_lng' => 'float',
            'location_url' => 'string',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }
}
