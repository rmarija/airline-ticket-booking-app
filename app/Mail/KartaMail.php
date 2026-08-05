<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class KartaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfOutput;

    /**
     * Create a new message instance.
     */
    public function __construct($pdfOutput)
    {
        $this->pdfOutput = $pdfOutput;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vaša avio karta - Uspešna rezervacija',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: '<p>Poštovani,</p><p>U prilogu Vam šaljemo elektronsku kartu (PDF) za Vaš predstojeći let.</p><p>Srećan put!</p>'
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfOutput, 'Elektronska_Karta.pdf')
                      ->withMime('application/pdf'),
        ];
    }
}