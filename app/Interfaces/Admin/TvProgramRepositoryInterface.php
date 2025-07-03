<?php

namespace App\Interfaces\Admin;

interface TvProgramRepositoryInterface
{
    public function index($obj, $request);
    public function store($obj, $request);
    public function show($obj, $id);
    public function showBySlug($obj, $slug);
    public function update($obj, $request, $id);
    public function destroy($obj, $id);
    public function restore($obj, $id);
}
