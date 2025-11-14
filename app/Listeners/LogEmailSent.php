<?php

namespace App\Listeners;

use App\Mail\BookingStatusMail;
use App\Models\NotificationLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogEmailSent
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        try {
            // Access mailable from event data
            // Laravel's MessageSent event stores mailable in data array
            $mailable = null;
            
            if (isset($event->data) && is_array($event->data)) {
                // Try different possible keys
                $mailable = $event->data['mail'] ?? $event->data['mailable'] ?? null;
            }
            
            // Only log BookingStatusMail sends
            if (!($mailable instanceof BookingStatusMail)) {
                return;
            }

            // Extract recipient email from message
            $recipients = $event->message->getTo();
            $recipientEmail = $recipients ? array_key_first($recipients) : $mailable->recipient->email;

            NotificationLog::create([
                'user_id' => $mailable->recipient->id,
                'type' => 'email',
                'channel' => 'smtp',
                'status' => 'SENT',
                'recipient' => $recipientEmail,
                'subject' => $mailable->envelope()->subject,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid breaking email sending
            Log::warning('Failed to log email sent event', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
