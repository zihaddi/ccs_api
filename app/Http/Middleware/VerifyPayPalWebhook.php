<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\PayPalService;
use Symfony\Component\HttpFoundation\Response;

class VerifyPayPalWebhook
{
    protected $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->paypalService->validateWebhook($request->getContent(), $request->header())) {
            return response()->json(['message' => 'Invalid webhook signature'], 401);
        }

        return $next($request);
    }
}
