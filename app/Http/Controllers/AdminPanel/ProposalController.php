<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Jobs\SendProposalStatusNotification;
use App\Models\Proposal;
use App\Models\Property;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $query = Proposal::with(['owner', 'property']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 50);
        $proposals = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('admin.proposals.index', compact('proposals'));
    }

    public function show(string $id)
    {
        $proposal = Proposal::with(['owner', 'property'])->findOrFail($id);
        return view('admin.proposals.show', compact('proposal'));
    }

    public function review(string $id, Request $request)
    {
        $request->validate([
            'decision' => 'required|in:APPROVED,REJECTED',
            'notes' => 'nullable|string|max:500',
        ]);

        $proposal = Proposal::findOrFail($id);

        if ($proposal->status !== 'PENDING') {
            return back()->with('error', __('admin.proposal_already_reviewed'));
        }

        $decision = $request->decision === 'APPROVED' ? 'APPROVED' : 'REJECTED';
        $appliedAt = null;
        $createdProperty = null;
        $updatedPropertyBefore = null;
        $updatedPropertyAfter = null;
        $deletedPropertySnapshot = null;
        $trackedPropertyAttributes = [
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
            'version',
            'owner_id',
        ];

        $beforeStatus = [
            'status' => $proposal->status,
            'notes' => $proposal->notes,
        ];

        DB::transaction(function () use ($proposal, $decision, $request, &$appliedAt, &$createdProperty, &$updatedPropertyBefore, &$updatedPropertyAfter, &$deletedPropertySnapshot, $trackedPropertyAttributes) {
            if ($decision === 'APPROVED') {
                // Apply the proposal
                if ($proposal->type === 'ADD') {
                    $createdProperty = Property::create([
                        'owner_id' => $proposal->owner_id,
                        'title' => $proposal->payload['title'],
                        'description' => $proposal->payload['description'],
                        'city' => $proposal->payload['city'],
                        'type' => $proposal->payload['type'],
                        'price' => $proposal->payload['price'],
                        'location_lat' => data_get($proposal->payload, 'location.latitude'),
                        'location_lng' => data_get($proposal->payload, 'location.longitude'),
                        'location_url' => data_get($proposal->payload, 'location.map_url') ?? data_get($proposal->payload, 'location.url'),
                        'amenities' => $proposal->payload['amenities'] ?? [],
                        'photos' => $proposal->payload['photos'] ?? [],
                        'version' => 1,
                    ]);
                } elseif ($proposal->type === 'EDIT') {
                    $property = Property::findOrFail($proposal->property_id);
                    $updatedPropertyBefore = Arr::only($property->toArray(), $trackedPropertyAttributes);
                    $updatedPropertyBefore['amenities'] = $property->amenities ?? [];
                    $updatedPropertyBefore['photos'] = $property->photos ?? [];

                    $updatePayload = array_merge($proposal->payload, ['version' => ($property->version ?? 0) + 1]);
                    // If location provided in EDIT, map to columns
                    if (data_get($proposal->payload, 'location.latitude') !== null) {
                        $updatePayload['location_lat'] = data_get($proposal->payload, 'location.latitude');
                    }
                    if (data_get($proposal->payload, 'location.longitude') !== null) {
                        $updatePayload['location_lng'] = data_get($proposal->payload, 'location.longitude');
                    }
                    $mapUrl = data_get($proposal->payload, 'location.map_url') ?? data_get($proposal->payload, 'location.url');
                    if ($mapUrl !== null) {
                        $updatePayload['location_url'] = $mapUrl;
                    }
                    unset($updatePayload['location']);
                    $property->update($updatePayload);
                    $property->refresh();
                    $updatedPropertyAfter = Arr::only($property->toArray(), $trackedPropertyAttributes);
                    $updatedPropertyAfter['amenities'] = $property->amenities ?? [];
                    $updatedPropertyAfter['photos'] = $property->photos ?? [];
                } elseif ($proposal->type === 'DELETE') {
                    $property = Property::findOrFail($proposal->property_id);
                    $deletedPropertySnapshot = Arr::only($property->toArray(), $trackedPropertyAttributes);
                    $deletedPropertySnapshot['amenities'] = $property->amenities ?? [];
                    $deletedPropertySnapshot['photos'] = $property->photos ?? [];
                    $property->delete();
                }
                $appliedAt = now();
            }

            $proposal->update([
                'status' => $decision,
                'notes' => $request->notes,
                'applied_at' => $appliedAt,
            ]);
        });

        $proposal->refresh();

        $afterStatus = [
            'status' => $proposal->status,
            'notes' => $proposal->notes,
            'applied_at' => optional($proposal->applied_at)->toDateTimeString(),
        ];

        AuditLogger::record(
            'proposal.reviewed',
            [
                'entity_type' => 'proposal',
                'entity_id' => $proposal->id,
                'entity_name' => $proposal->type . ' #' . $proposal->id,
            ],
            $beforeStatus,
            $afterStatus,
            [
                'decision' => $decision,
                'notes' => $request->notes,
                'proposal_type' => $proposal->type,
                'owner_id' => $proposal->owner_id,
                'property_id' => $proposal->property_id,
            ]
        );

        if ($createdProperty) {
            AuditLogger::record(
                'property.created_from_proposal',
                [
                    'entity_type' => 'property',
                    'entity_id' => $createdProperty->id,
                    'entity_name' => $createdProperty->title,
                ],
                null,
                Arr::only($createdProperty->toArray(), $trackedPropertyAttributes),
                [
                    'proposal_id' => $proposal->id,
                    'owner_id' => $createdProperty->owner_id,
                ]
            );
        }

        if ($updatedPropertyAfter && $updatedPropertyBefore) {
            AuditLogger::record(
                'property.updated_from_proposal',
                [
                    'entity_type' => 'property',
                    'entity_id' => $proposal->property_id,
                    'entity_name' => data_get($updatedPropertyAfter, 'title'),
                ],
                $updatedPropertyBefore,
                $updatedPropertyAfter,
                [
                    'proposal_id' => $proposal->id,
                    'owner_id' => data_get($updatedPropertyAfter, 'owner_id'),
                ]
            );
        }

        if ($deletedPropertySnapshot) {
            AuditLogger::record(
                'property.deleted_from_proposal',
                [
                    'entity_type' => 'property',
                    'entity_id' => data_get($deletedPropertySnapshot, 'id'),
                    'entity_name' => data_get($deletedPropertySnapshot, 'title'),
                ],
                $deletedPropertySnapshot,
                null,
                [
                    'proposal_id' => $proposal->id,
                    'owner_id' => data_get($deletedPropertySnapshot, 'owner_id'),
                ]
            );
        }

        // Send notification to owner about proposal status
        SendProposalStatusNotification::dispatch($proposal);

        $message = $decision === 'APPROVED' 
            ? __('admin.proposal_approved') 
            : __('admin.proposal_rejected');

        return redirect()
            ->route('admin.proposals.show', $proposal->id)
            ->with('status', $message);
    }
}
