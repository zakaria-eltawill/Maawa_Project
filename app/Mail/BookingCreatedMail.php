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

class BookingCreatedMail extends Mailable implements ShouldQueue
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
        return new Envelope(
            subject: __('mail.booking_request_subject', ['id' => $this->booking->id]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking_created',
            text: 'emails.booking_created_text',
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
        Log::error('Failed to send booking created email', [
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

