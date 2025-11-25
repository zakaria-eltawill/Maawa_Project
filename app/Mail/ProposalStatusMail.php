<?php

namespace App\Mail;

use App\Models\NotificationLog;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProposalStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Proposal $proposal,
        public User $recipient
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $status = $this->proposal->status;
        $subject = match ($status) {
            'APPROVED' => __('mail.proposal_approved_subject', ['id' => $this->proposal->id]),
            'REJECTED' => __('mail.proposal_rejected_subject', ['id' => $this->proposal->id]),
            default => __('mail.proposal_approved_subject', ['id' => $this->proposal->id]),
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
            view: 'emails.proposal_status',
            text: 'emails.proposal_status_text',
            with: [
                'proposal' => $this->proposal,
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
        Log::error('Failed to send proposal status email', [
            'proposal_id' => $this->proposal->id,
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

