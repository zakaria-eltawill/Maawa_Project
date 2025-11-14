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

class BookingStatusMail extends Mailable implements ShouldQueue
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
        $subject = $status === 'CONFIRMED'
            ? __('mail.booking_confirmed_subject', ['id' => $this->booking->id])
            : __('mail.booking_failed_subject', ['id' => $this->booking->id]);

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
            view: 'emails.booking_status',
            text: 'emails.booking_status_text',
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
        Log::error('Failed to send booking status email', [
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
