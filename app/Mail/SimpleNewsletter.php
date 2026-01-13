<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SimpleNewsletter extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct() {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cảm ơn bạn đã đăng ký nhận tin từ Sneaker Zone!', // Tiêu đề mail
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'client.emails.newsletter', // Trỏ đến file view chúng ta sẽ tạo
        );
    }
}