<?php

namespace App\Http\Controllers;

use App\Http\Requests\Proposal\StoreProposalRequest;
use App\Http\Requests\Proposal\UpdateProposalRequest;
use App\Http\Resources\Proposal\ProposalResource;
use App\Models\AdminNotification;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function store(StoreProposalRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->role !== 'owner') {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only owners can create proposals',
            ], 403);
        }

        $data = [
            'owner_id' => $user->id,
            'type' => $request->type,
            'status' => 'PENDING',
        ];

        if ($request->type === 'ADD') {
            $data['payload'] = $request->payload;
        } elseif ($request->type === 'EDIT') {
            $data['property_id'] = $request->property_id;
            $data['version'] = $request->version;
            $data['payload'] = $request->payload;
        } elseif ($request->type === 'DELETE') {
            $data['property_id'] = $request->property_id;
            $data['reason'] = $request->reason;
        }

        $proposal = Proposal::create($data);

        $this->notifyAdminsOfProposal($proposal, $user->name);

        return response()->json([
            'id' => $proposal->id,
            'status' => 'PENDING',
        ], 201);
    }

    public function ownerIndex(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = Proposal::where('owner_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min($request->get('per_page', 20), 50);
        $proposals = $query->paginate($perPage);

        return response()->json([
            'data' => ProposalResource::collection($proposals->items()),
            'next_cursor' => $proposals->hasMorePages() ? $proposals->nextPageUrl() : null,
        ]);
    }

    /**
     * Get owner's proposal by ID
     * Owner can only view their own proposals
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();

        if ($user->role !== 'owner') {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only owners can view proposals',
            ], 403);
        }

        $proposal = Proposal::where('owner_id', $user->id)->findOrFail($id);

        return response()->json(new ProposalResource($proposal));
    }

    /**
     * Update owner's proposal
     * Only PENDING or REJECTED proposals can be updated
     * Status automatically becomes PENDING after update
     */
    public function update(string $id, UpdateProposalRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->role !== 'owner') {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only owners can update proposals',
            ], 403);
        }

        $proposal = Proposal::where('owner_id', $user->id)->findOrFail($id);

        // Only PENDING or REJECTED proposals can be updated
        if (!in_array($proposal->status, ['PENDING', 'REJECTED'])) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Bad Request',
                'status' => 400,
                'detail' => 'Only PENDING or REJECTED proposals can be updated',
            ], 400);
        }

        // Get validated data
        $data = $request->validated();

        // Merge with existing data for partial updates
        if ($proposal->type === 'ADD') {
            if (isset($data['payload'])) {
                $existingPayload = $proposal->payload ?? [];
                $data['payload'] = array_merge($existingPayload, $data['payload']);
            }
        } elseif ($proposal->type === 'EDIT') {
            if (isset($data['payload'])) {
                $existingPayload = $proposal->payload ?? [];
                $data['payload'] = array_merge($existingPayload, $data['payload']);
            }
            // Keep existing property_id and version if not provided
            if (!isset($data['property_id'])) {
                $data['property_id'] = $proposal->property_id;
            }
            if (!isset($data['version'])) {
                $data['version'] = $proposal->version;
            }
        } elseif ($proposal->type === 'DELETE') {
            // Keep existing property_id if not provided
            if (!isset($data['property_id'])) {
                $data['property_id'] = $proposal->property_id;
            }
            if (isset($data['reason'])) {
                $data['reason'] = $data['reason'];
            } else {
                // Keep existing reason if not provided
                $data['reason'] = $proposal->reason;
            }
        }

        // Status becomes PENDING after update
        $data['status'] = 'PENDING';

        // Update proposal
        $proposal->update($data);

        // Notify admins of updated proposal
        $this->notifyAdminsOfProposal($proposal->refresh(), $user->name);

        return response()->json([
            'id' => $proposal->id,
            'status' => 'PENDING',
            'message' => 'Proposal updated successfully. Status changed to PENDING.',
        ]);
    }

    /**
     * Delete owner's proposal
     * Only PENDING or REJECTED proposals can be deleted
     * Hard delete from database
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();

        if ($user->role !== 'owner') {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only owners can delete proposals',
            ], 403);
        }

        $proposal = Proposal::where('owner_id', $user->id)->findOrFail($id);

        // Only PENDING or REJECTED proposals can be deleted
        if (!in_array($proposal->status, ['PENDING', 'REJECTED'])) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Bad Request',
                'status' => 400,
                'detail' => 'Only PENDING or REJECTED proposals can be deleted',
            ], 400);
        }

        // Hard delete from database
        $proposal->delete();

        return response()->json([
            'message' => 'Proposal deleted successfully',
        ], 200);
    }

    protected function notifyAdminsOfProposal(Proposal $proposal, string $ownerName): void
    {
        $typeKey = strtolower($proposal->type);
        $titleKey = "admin.notification_templates.proposal_{$typeKey}_title";
        $bodyKey = "admin.notification_templates.proposal_{$typeKey}_body";

        $params = [
            'owner' => $ownerName,
        ];

        $propertyId = $proposal->property_id;
        if ($propertyId) {
            $params['property'] = $propertyId;
        }

        AdminNotification::create([
            'type' => "proposal.{$typeKey}",
            'title' => __($titleKey, $params, 'en'),
            'message' => __($bodyKey, $params, 'en'),
            'entity_type' => Proposal::class,
            'entity_id' => $proposal->id,
            'data' => [
                'title_key' => $titleKey,
                'body_key' => $bodyKey,
                'params' => $params,
                'proposal_id' => $proposal->id,
                'proposal_type' => $proposal->type,
                'owner_id' => $proposal->owner_id,
                'route' => route('admin.proposals.show', $proposal->id),
            ],
        ]);
    }
}
