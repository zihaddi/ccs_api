<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Interfaces\Cms\TutorialCategoryRepositoryInterface;
use App\Models\TutorialCategory;
use Illuminate\Http\Request;

class TutorialCategoryController extends Controller
{
    protected $client;

    public function __construct(TutorialCategoryRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(TutorialCategory $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(TutorialCategory $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function showBySlug(TutorialCategory $obj, $slug)
    {
        return $this->client->showBySlug($obj, $slug);
    }
}
