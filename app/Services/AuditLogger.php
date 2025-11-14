<?php

namespace App\Services;

use App\Models\Audit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class AuditLogger
{
    /**
     * Record an audit entry.
     *
     * @param  string  $action
     * @param  array  $context
     * @param  array|null  $before
     * @param  array|null  $after
     * @param  array  $metadata
     */
    public static function record(string $action, array $context, ?array $before = null, ?array $after = null, array $metadata = []): void
    {
        $actor = self::resolveActor();

        Audit::create([
            'id' => (string) Str::uuid(),
            'action' => $action,
            'entity_type' => $context['entity_type'] ?? 'unknown',
            'entity_id' => $context['entity_id'] ?? null,
            'entity_name' => $context['entity_name'] ?? null,
            'actor_id' => $actor?->getAuthIdentifier(),
            'actor_name' => $actor?->name,
            'actor_email' => $actor?->email,
            'changes' => [
                'before' => $before,
                'after' => $after,
                'diff' => self::diff($before ?? [], $after ?? []),
            ],
            'metadata' => array_filter($metadata),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    protected static function resolveActor(): ?Authenticatable
    {
        $guardNames = [null, 'web', 'api'];
        foreach ($guardNames as $guard) {
            $user = $guard ? Auth::guard($guard)->user() : Auth::user();
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    protected static function diff(array $before, array $after): array
    {
        $keys = collect(array_keys($before))
            ->merge(array_keys($after))
            ->unique()
            ->values();

        $diff = [];
        foreach ($keys as $key) {
            $old = Arr::get($before, $key);
            $new = Arr::get($after, $key);

            if ($old !== $new) {
                $diff[$key] = [
                    'before' => $old,
                    'after' => $new,
                ];
            }
        }

        return $diff;
    }
}
