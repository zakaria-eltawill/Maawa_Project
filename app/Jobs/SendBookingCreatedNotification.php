<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\FcmClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendBookingCreatedNotification implements ShouldQueue
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
        // Refresh booking to get latest data
        $this->booking->refresh();
        
        // Load relationships
        $this->booking->load(['tenant', 'property.owner']);

        // Validate required relationships exist
        if (!$this->booking->property || !$this->booking->property->owner) {
            Log::warning('Cannot send booking created notification: missing relationships', [
                'booking_id' => $this->booking->id,
                'has_property' => !is_null($this->booking->property),
                'has_owner' => !is_null($this->booking->property?->owner),
            ]);
            return;
        }

        $owner = $this->booking->property->owner;
        $propertyTitle = $this->booking->property->title;
        $city = $this->booking->property->city;
        $checkIn = $this->booking->check_in->format('Y-m-d');
        $checkOut = $this->booking->check_out->format('Y-m-d');
        $guests = $this->booking->guests;
        $total = number_format($this->booking->total, 2);

        // Prepare FCM payload
        $fcmPayload = [
            'title' => __('notifications.booking_request_title'),
            'body' => __('notifications.booking_request_body', [
                'property' => $propertyTitle,
                'city' => $city,
                'checkin' => $checkIn,
                'checkout' => $checkOut,
                'guests' => $guests,
                'total' => $total,
            ]),
            'data' => [
                'type' => 'booking',
                'booking_id' => $this->booking->id,
                'status' => 'PENDING',
            ],
        ];

        // Send push notification to owner (email not sent for booking requests)
        FcmClient::sendToUser($owner, $fcmPayload);
    }
}

