<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\Contact\ContactStoreRequest;
use App\Interfaces\Cms\ContactRepositoryInterface;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller 
{
    protected $client;

    protected $middleware = ['throttle:contacts'];

    public function __construct(ContactRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function store(ContactStoreRequest $request)
    {
        // Additional IP-based rate limiting
        $key = 'contacts:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) { // 5 attempts per minute
            return response()->json([
                'message' => 'Too many attempts. Please try again later.',
                'minutes' => ceil(RateLimiter::availableIn($key) / 60)
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, 60); // Key expires in 60 seconds
        
        return $this->client->store($request->validated());
    }
}
