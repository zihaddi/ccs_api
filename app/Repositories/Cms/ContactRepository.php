<?php

namespace App\Repositories\Cms;

use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use App\Models\Contact;
use App\Jobs\SendEmailJob;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Cms\ContactRepositoryInterface;
use Illuminate\Support\Facades\View;

class ContactRepository implements ContactRepositoryInterface
{
    use HttpResponses;

    public function store($request)
    {
        try {
            $contact = Contact::create([
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'subject' => 'New Contact Form Submission',
                'message' => $request['message'],
                'phone' => $request['phone'] ?? null,
            ]);

            if ($contact) {
                // Send confirmation email to the user
                $userEmailContent = View::make('emails.contact-form-user', ['contact' => $contact])->render();
                dispatch(new SendEmailJob([
                    'email' => $contact->email,
                    'subject' => 'Thank you for contacting us',
                    'html' => $userEmailContent
                ]));

                // Send notification to admin
                $adminEmailContent = View::make('emails.contact-form-admin', ['contact' => $contact])->render();
                dispatch(new SendEmailJob([
                    'email' => 'dolardx@gmail.com',
                    'subject' => 'New Contact Form Submission',
                    'html' => $adminEmailContent
                ]));
                
                return $this->success($contact, Constants::STORE, Response::HTTP_CREATED, true);
            }
            return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
