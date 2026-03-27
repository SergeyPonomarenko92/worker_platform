<?php

namespace App\Mail;

use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DealCreatedForClientMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Deal $deal)
    {
        $this->deal->loadMissing([
            'businessProfile:id,name,slug',
            'offer:id,title',
            'client:id,name,email',
        ]);
    }

    public function envelope(): Envelope
    {
        $providerName = $this->deal->businessProfile?->name ?? 'провайдера';

        return new Envelope(
            subject: "Нова угода від {$providerName}",
        );
    }

    public function content(): Content
    {
        $providerUrl = null;
        if ($this->deal->businessProfile?->slug) {
            $providerUrl = route('providers.show', $this->deal->businessProfile->slug);
        }

        return new Content(
            markdown: 'mail.deals.created',
            with: [
                'deal' => $this->deal,
                'businessProfile' => $this->deal->businessProfile,
                'offer' => $this->deal->offer,
                'client' => $this->deal->client,
                'providerUrl' => $providerUrl,
            ],
        );
    }
}
