<?php

namespace App\Repositories\Cms;

use App\Constants\Constants;
use App\Http\Resources\Cms\TutorialCategory\TutorialCategoryResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Interfaces\Cms\TutorialCategoryRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

class TutorialCategoryRepository implements TutorialCategoryRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // Index: Retrieve all tutorial categories
    public function index($obj, $request)
    {
        try {
            $query = $obj::with('category')
                ->orderByName()
                ->filter((array)$request);
            $query = $query->when(
                isset($request['paginate']) && $request['paginate'] == true,
                function ($query) use ($request) {
                    return $query->paginate($request['length'] ?? $request['length'] = 15)->withQueryString();
                },
                function ($query) {
                    return $query->get();
                }
            );
            if ($query) {
                $data = TutorialCategoryResource::collection($query)->response()->getData();
                return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific tutorial category
    public function show($obj, $id)
    {
        try {
            $tutorialCategory = $obj::find($id);
            if ($tutorialCategory) {
                return $this->success(new TutorialCategoryResource($tutorialCategory), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show by slug: Display a specific tutorial category by slug
    public function showBySlug($obj, $slug)
    {
        try {
            $tutorialCategory = $obj::where('slug', $slug)->first();
           
            if ($tutorialCategory) {
                return $this->success(new TutorialCategoryResource($tutorialCategory), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
