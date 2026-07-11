<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string|null $qrImageUrl = null; // public PNG URL — works in Gmail/Outlook
    public string|null $scanUrl    = null; // "View Booking" link — no login required

    public function __construct(public Booking $booking, public string $recipientRole)
    {
        if ($recipientRole === 'renter') {
            // Signed URL to serve QR PNG — valid 48h, no auth needed
            $this->qrImageUrl = URL::signedRoute('bookings.qr.email', ['booking' => $booking->id], now()->addHours(48));
            // Same scan page as the QR destination — renter can share/open directly
            $this->scanUrl    = URL::signedRoute('bookings.scan',     ['booking' => $booking->id], now()->addHours(48));
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Booking confirmed — {$this->booking->listing->title}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.booking-confirmed');
    }
}
