<?php

namespace App\Mail;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DemandeCloture extends Mailable
{
    use Queueable, SerializesModels;

    public $demande;
    public $pdfPath;
    public $clotureName;

    public function __construct(Demande $demande, string $pdfPath, ?string $clotureName = null)
    {
        $this->demande = $demande;
        $this->pdfPath = $pdfPath;
        $this->clotureName = $clotureName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Demande de travaux clôturée - ' . $this->demande->numero_demande,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.demande-cloture',
        );
    }

    public function attachments(): array
    {
        $fullPath = Storage::path('public/' . $this->pdfPath);
        
        if (file_exists($fullPath)) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromPath($fullPath)
                    ->as($this->demande->numero_demande . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
