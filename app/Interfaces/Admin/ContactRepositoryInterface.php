<?php

namespace App\Interfaces\Admin;

interface ContactRepositoryInterface
{
    public function index($obj, array $data);
    public function show($obj, $id);
    public function destroy($obj, $id);
    public function restore($obj, $id);
}
