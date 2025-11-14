<?php

namespace App\Http\Controllers;

use App\Http\Requests\Proposal\StoreProposalRequest;
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
