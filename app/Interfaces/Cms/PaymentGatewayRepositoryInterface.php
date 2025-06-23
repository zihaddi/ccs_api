<?php

namespace App\Interfaces\Cms;

interface PaymentGatewayRepositoryInterface
{
    public function index($obj, $request);
    public function show($obj, $request);
    public function showBySlug($obj, $slug);
}
