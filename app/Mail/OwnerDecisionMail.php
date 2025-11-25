<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OwnerDecisionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Booking $booking,
        public User $recipient
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $status = $this->booking->status;
        $subject = match ($status) {
            'ACCEPTED' => __('mail.booking_accepted_subject', ['id' => $this->booking->id]),
            'REJECTED' => __('mail.booking_rejected_subject', ['id' => $this->booking->id]),
            default => __('mail.booking_accepted_subject', ['id' => $this->booking->id]),
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.owner_decision',
            text: 'emails.owner_decision_text',
            with: [
                'booking' => $this->booking,
                'recipient' => $this->recipient,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send owner decision email', [
            'booking_id' => $this->booking->id,
            'recipient_id' => $this->recipient->id,
            'error' => $exception->getMessage(),
        ]);

        NotificationLog::create([
            'user_id' => $this->recipient->id,
            'type' => 'email',
            'channel' => 'smtp',
            'status' => 'FAILED',
            'recipient' => $this->recipient->email,
            'subject' => $this->envelope()->subject,
            'error_message' => $exception->getMessage(),
        ]);
    }
}

