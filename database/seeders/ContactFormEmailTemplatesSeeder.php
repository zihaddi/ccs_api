<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class ContactFormEmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        // Admin notification template
        EmailTemplate::create([
            'name' => 'Contact Form Admin Notification',
            'slug' => 'contact-form-admin',
            'email_subject' => 'New Contact Form Submission',
            'email_body' => '
                <p>Hello Admin,</p>
                <p>A new contact form submission has been received:</p>
                <p><strong>Name:</strong> {{first_name}} {{last_name}}</p>
                <p><strong>Email:</strong> {{email}}</p>
                <p><strong>Phone:</strong> {{phone}}</p>
                <p><strong>Message:</strong></p>
                <p>{{message}}</p>
                <br>
                <p>Best regards,<br>System</p>
            ',
            'status' => true
        ]);

        // User thank you template
        EmailTemplate::create([
            'name' => 'Contact Form Thank You',
            'slug' => 'contact-form-thank-you',
            'email_subject' => 'Thank you for contacting us',
            'email_body' => '
                <p>Dear {{first_name}} {{last_name}},</p>
                <p>Thank you for contacting us. We have received your message and will get back to you as soon as possible.</p>
                <br>
                <p>Best regards,<br>Support Team</p>
            ',
            'status' => true
        ]);
    }
}
