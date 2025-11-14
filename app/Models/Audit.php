<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class Audit extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'id',
        'action',
        'entity_type',
        'entity_id',
        'entity_name',
        'actor_id',
        'actor_name',
        'actor_email',
        'changes',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
        'metadata' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getSummaryAttribute(): string
    {
        $action = $this->action;
        $entity = $this->entity_name ?: ($this->entity_type . '#' . $this->entity_id);

        $summary = trim(implode(' ', array_filter([
            ucfirst(str_replace('_', ' ', $action)),
            $entity ? "for {$entity}" : null,
        ])));

        return $summary !== '' ? $summary : 'Audit entry';
    }

    public function getBeforeAttribute(): array
    {
        return Arr::get($this->changes, 'before', []);
    }

    public function getAfterAttribute(): array
    {
        return Arr::get($this->changes, 'after', []);
    }

    public function getDiffAttribute(): array
    {
        return Arr::get($this->changes, 'diff', []);
    }
}
