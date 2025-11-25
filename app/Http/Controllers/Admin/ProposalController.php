<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewProposalRequest;
use App\Http\Resources\Proposal\ProposalResource;
use App\Jobs\SendProposalStatusNotification;
use App\Models\Proposal;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Proposal::with(['owner', 'property']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $perPage = min($request->get('per_page', 20), 50);
        $proposals = $query->paginate($perPage);

        return response()->json([
            'data' => ProposalResource::collection($proposals->items()),
            'next_cursor' => $proposals->hasMorePages() ? $proposals->nextPageUrl() : null,
        ]);
    }

    public function review(string $id, ReviewProposalRequest $request): JsonResponse
    {
        $proposal = Proposal::findOrFail($id);

        if ($proposal->status !== 'PENDING') {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Conflict',
                'status' => 409,
                'detail' => 'Proposal already reviewed',
            ], 409);
        }

        $decision = $request->decision === 'APPROVED' ? 'APPROVED' : 'REJECTED';
        $appliedAt = null;

        if ($decision === 'APPROVED') {
            // Apply the proposal
            if ($proposal->type === 'ADD') {
                $created = Property::create([
                    'owner_id' => $proposal->owner_id,
                    'title' => $proposal->payload['title'],
                    'description' => $proposal->payload['description'],
                    'city' => $proposal->payload['city'],
                    'type' => $proposal->payload['type'],
                    'price' => $proposal->payload['price'],
                    'location_lat' => data_get($proposal->payload, 'location.latitude'),
                    'location_lng' => data_get($proposal->payload, 'location.longitude'),
                    'amenities' => $proposal->payload['amenities'] ?? [],
                    'photos' => $proposal->payload['photos'] ?? [],
                ]);
            } elseif ($proposal->type === 'EDIT') {
                $property = Property::findOrFail($proposal->property_id);
                $updatePayload = array_merge($proposal->payload, ['version' => $proposal->version + 1]);
                // If location provided in EDIT, map to columns
                if (data_get($proposal->payload, 'location.latitude') !== null) {
                    $updatePayload['location_lat'] = data_get($proposal->payload, 'location.latitude');
                }
                if (data_get($proposal->payload, 'location.longitude') !== null) {
                    $updatePayload['location_lng'] = data_get($proposal->payload, 'location.longitude');
                }
                unset($updatePayload['location']);
                $property->update($updatePayload);
            } elseif ($proposal->type === 'DELETE') {
                Property::findOrFail($proposal->property_id)->delete();
            }
            $appliedAt = now();
        }

        $proposal->update([
            'status' => $decision,
            'notes' => $request->notes,
            'applied_at' => $appliedAt,
        ]);

        // Send notification to owner about proposal status
        SendProposalStatusNotification::dispatch($proposal);

        return response()->json([
            'id' => $proposal->id,
            'status' => $decision,
            'applied_at' => $appliedAt?->format('Y-m-d\TH:i:s\Z'),
        ]);
    }
}
