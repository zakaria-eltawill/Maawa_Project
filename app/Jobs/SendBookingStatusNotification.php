<?php

namespace App\Jobs;

use App\Mail\BookingStatusMail;
use App\Models\Booking;
use App\Services\FcmClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class SendBookingStatusNotification implements ShouldQueue
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
        $this->booking->load(['tenant', 'property.owner']);

        // Validate required relationships exist
        if (!$this->booking->tenant || !$this->booking->property || !$this->booking->property->owner) {
            Log::warning('Cannot send booking status notification: missing relationships', [
                'booking_id' => $this->booking->id,
                'has_tenant' => !is_null($this->booking->tenant),
                'has_property' => !is_null($this->booking->property),
                'has_owner' => !is_null($this->booking->property?->owner),
            ]);
            return;
        }

        $status = $this->booking->status;

        // Determine notification titles based on status
        if ($status === 'CONFIRMED') {
            $title = __('notifications.booking_confirmed_title');
            $emoji = '🎉';
        } elseif ($status === 'FAILED') {
            $title = __('notifications.booking_failed_title');
            $emoji = '❌';
        } elseif ($status === 'CANCELED') {
            $title = __('notifications.booking_canceled_title', [], 'en');
            $emoji = '🚫';
        } else {
            // Only send notifications for CONFIRMED, FAILED, or CANCELED status
            return;
        }

        // Generate generic notification body for FCM (without personalization)
        $propertyTitle = $this->booking->property->title;
        $city = $this->booking->property->city;
        $checkIn = $this->booking->check_in->format('Y-m-d');
        $checkOut = $this->booking->check_out->format('Y-m-d');
        $total = number_format($this->booking->total, 2);

        if ($status === 'CONFIRMED') {
            $bodyText = __('mail.booking_confirmed_html', [
                'title' => $propertyTitle,
                'city' => $city,
                'checkin' => $checkIn,
                'checkout' => $checkOut,
                'total' => $total,
            ]);
        } elseif ($status === 'FAILED') {
            $bodyText = __('mail.booking_failed_html', [
                'title' => $propertyTitle,
                'city' => $city,
            ]);
        } elseif ($status === 'CANCELED') {
            $bodyText = __('mail.booking_canceled_html', [
                'title' => $propertyTitle,
                'city' => $city,
                'checkin' => $checkIn,
                'checkout' => $checkOut,
            ], 'en');
        } else {
            $bodyText = '';
        }

        // Prepare FCM payload
        $fcmPayload = [
            'title' => $title . ' ' . $emoji,
            'body' => strip_tags($bodyText),
            'data' => [
                'type' => 'booking',
                'booking_id' => $this->booking->id,
                'status' => $status,
            ],
        ];

        // Send push notifications to tenant and owner
        $recipients = [
            $this->booking->tenant,
            $this->booking->property->owner,
        ];

        FcmClient::sendToUsers($recipients, $fcmPayload);

        // Send emails to tenant and owner
        foreach ($recipients as $user) {
            if ($user && $user->email) {
                Mail::to($user->email)->queue(new BookingStatusMail($this->booking, $user));
            }
        }
    }
}
