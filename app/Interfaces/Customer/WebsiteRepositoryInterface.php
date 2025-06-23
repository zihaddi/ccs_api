<?php

namespace App\Interfaces\Customer;

interface WebsiteRepositoryInterface
{
    public function index($obj, $request);
    public function store($obj, $request);
    public function show($obj, $id);
    public function update($obj, $request, $id);
    public function patch($obj, $request);
    public function destroy($obj, $id);
    public function restore($obj, $id);
}
