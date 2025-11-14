<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePropertyRequest;
use App\Models\Property;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with('owner');

        // Filters
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 50);
        $properties = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('admin.properties.index', compact('properties'));
    }

    public function show(string $id)
    {
        $property = Property::with(['owner', 'bookings', 'reviews'])->findOrFail($id);
        return view('admin.properties.show', compact('property'));
    }

    public function edit(string $id)
    {
        $property = Property::with('owner')->findOrFail($id);
        $owners = User::where('role', 'owner')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.properties.edit', compact('property', 'owners'));
    }

    public function update(UpdatePropertyRequest $request, string $id)
    {
        $property = Property::findOrFail($id);
        $data = $request->validated();

        $trackedAttributes = [
            'title',
            'description',
            'city',
            'type',
            'price',
            'location_lat',
            'location_lng',
            'location_url',
            'owner_id',
            'amenities',
            'photos',
            'version',
        ];

        $before = Arr::only($property->toArray(), $trackedAttributes);
        $before['amenities'] = $property->amenities ?? [];
        $before['photos'] = $property->photos ?? [];

        $amenities = collect(preg_split('/[\n,]+/', $data['amenities'] ?? '', -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();

        $existingPhotos = collect($property->photos ?? []);
        $removePhotos = collect($data['remove_photos'] ?? [])->filter();

        if ($removePhotos->isNotEmpty()) {
            $existingPhotos = $existingPhotos->reject(function ($photo) use ($removePhotos) {
                $url = is_array($photo) ? ($photo['url'] ?? null) : $photo;
                return $url && $removePhotos->contains($url);
            })->values();

            $removePhotos->each(function ($url) {
                $path = parse_url($url, PHP_URL_PATH);
                if ($path && Str::startsWith($path, '/storage/')) {
                    $relative = Str::after($path, '/storage/');
                    if (Storage::disk('public')->exists($relative)) {
                        Storage::disk('public')->delete($relative);
                    }
                }
            });
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if (!$file) {
                    continue;
                }
                $storedPath = $file->store('properties/' . $property->id, 'public');
                $existingPhotos->push([
                    'url' => Storage::url($storedPath),
                ]);
            }
        }

        $photos = $existingPhotos->values()->map(function ($photo, $index) {
            $url = is_array($photo) ? ($photo['url'] ?? null) : $photo;
            return [
                'url' => $url,
                'position' => $index,
            ];
        })->all();

        $property->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'city' => $data['city'],
            'type' => $data['type'],
            'price' => $data['price'],
            'location_lat' => $data['location_lat'] ?? null,
            'location_lng' => $data['location_lng'] ?? null,
            'location_url' => $data['location_url'] ?? null,
            'amenities' => $amenities,
            'photos' => $photos,
        ]);

        if (isset($data['owner_id'])) {
            $property->owner_id = $data['owner_id'];
        }

        $property->version = ($property->version ?? 0) + 1;
        $property->save();

        $property->refresh();

        $after = Arr::only($property->toArray(), $trackedAttributes);
        $after['amenities'] = $property->amenities ?? [];
        $after['photos'] = $property->photos ?? [];

        AuditLogger::record(
            'property.updated',
            [
                'entity_type' => 'property',
                'entity_id' => $property->id,
                'entity_name' => $property->title,
            ],
            $before,
            $after,
            [
                'owner_id' => $property->owner_id,
                'source' => 'admin_panel',
            ]
        );

        return redirect()
            ->route('admin.properties.show', $property->id)
            ->with('status', __('admin.property_updated'));
    }

    public function destroy(string $id)
    {
        $property = Property::with(['bookings'])->findOrFail($id);

        $snapshot = Arr::only($property->toArray(), [
            'id',
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
            'owner_id',
            'version',
        ]);
        $snapshot['amenities'] = $property->amenities ?? [];
        $snapshot['photos'] = $property->photos ?? [];

        // Check for active bookings
        $activeBookings = $property->bookings()
            ->whereIn('status', ['PENDING', 'ACCEPTED', 'CONFIRMED'])
            ->count();

        if ($activeBookings > 0) {
            return back()->with('error', __('admin.cannot_delete_property_with_bookings', ['count' => $activeBookings]));
        }

        // Delete the property
        $property->delete();

        AuditLogger::record(
            'property.deleted',
            [
                'entity_type' => 'property',
                'entity_id' => $snapshot['id'],
                'entity_name' => $snapshot['title'] ?? null,
            ],
            $snapshot,
            null,
            [
                'owner_id' => $snapshot['owner_id'] ?? null,
                'source' => 'admin_panel',
            ]
        );

        return redirect()
            ->route('admin.properties.index')
            ->with('status', __('admin.property_deleted'));
    }
}
