<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\FcmClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOwnerDecisionNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh booking to get latest status
        $this->booking->refresh();
        
        // Load relationships
        $this->booking->load(['tenant', 'property']);

        // Validate required relationships exist
        if (!$this->booking->tenant || !$this->booking->property) {
            Log::warning('Cannot send owner decision notification: missing relationships', [
                'booking_id' => $this->booking->id,
                'has_tenant' => !is_null($this->booking->tenant),
                'has_property' => !is_null($this->booking->property),
            ]);
            return;
        }

        $tenant = $this->booking->tenant;
        $status = $this->booking->status;
        
        // Only send notifications for ACCEPTED or REJECTED status
        if (!in_array($status, ['ACCEPTED', 'REJECTED'])) {
            return;
        }

        $propertyTitle = $this->booking->property->title;
        $city = $this->booking->property->city;
        $checkIn = $this->booking->check_in->format('Y-m-d');
        $checkOut = $this->booking->check_out->format('Y-m-d');
        $total = number_format($this->booking->total, 2);
        $paymentDueAt = $this->booking->payment_due_at?->format('Y-m-d H:i');

        if ($status === 'ACCEPTED') {
            $title = __('notifications.booking_accepted_title');
            $body = __('notifications.booking_accepted_body', [
                'property' => $propertyTitle,
                'city' => $city,
                'checkin' => $checkIn,
                'checkout' => $checkOut,
                'total' => $total,
                'payment_due' => $paymentDueAt,
            ]);
        } else {
            $title = __('notifications.booking_rejected_title');
            $body = __('notifications.booking_rejected_body', [
                'property' => $propertyTitle,
                'city' => $city,
            ]);
        }

        // Prepare FCM payload
        $fcmPayload = [
            'title' => $title,
            'body' => $body,
            'data' => [
                'type' => 'booking',
                'booking_id' => $this->booking->id,
                'status' => $status,
            ],
        ];

        // Send push notification to tenant (email not sent for owner decisions)
        FcmClient::sendToUser($tenant, $fcmPayload);
    }
}

