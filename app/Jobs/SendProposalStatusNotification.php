<?php

namespace App\Jobs;

use App\Models\Proposal;
use App\Services\FcmClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendProposalStatusNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Proposal $proposal
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh proposal to get latest status
        $this->proposal->refresh();
        
        // Load relationships
        $this->proposal->load(['owner', 'property']);

        // Validate required relationships exist
        if (!$this->proposal->owner) {
            Log::warning('Cannot send proposal status notification: missing owner', [
                'proposal_id' => $this->proposal->id,
            ]);
            return;
        }

        $owner = $this->proposal->owner;
        $status = $this->proposal->status;

        // Only send notifications for APPROVED or REJECTED status
        if (!in_array($status, ['APPROVED', 'REJECTED'])) {
            return;
        }

        // Get property title
        $propertyTitle = $this->proposal->payload['title'] ?? 
                        ($this->proposal->property?->title ?? 'Property');
        $proposalType = $this->proposal->type; // ADD, EDIT, DELETE

        if ($status === 'APPROVED') {
            $title = __('notifications.proposal_approved_title');
            $body = __('notifications.proposal_approved_body', [
                'type' => strtolower($proposalType),
                'property' => $propertyTitle,
            ]);
        } else {
            $title = __('notifications.proposal_rejected_title');
            $body = __('notifications.proposal_rejected_body', [
                'type' => strtolower($proposalType),
                'property' => $propertyTitle,
            ]);
        }

        // Prepare FCM payload
        $fcmPayload = [
            'title' => $title,
            'body' => $body,
            'data' => [
                'type' => 'proposal',
                'proposal_id' => $this->proposal->id,
                'status' => $status,
                'proposal_type' => $proposalType,
            ],
        ];

        // Send push notification to owner (email not sent for proposal status)
        FcmClient::sendToUser($owner, $fcmPayload);
    }
}

