<?php

namespace App\Repositories\Cms;


use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Resources\Cms\Plan\PlanResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Interfaces\Cms\PlanRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PlanRepository implements PlanRepositoryInterface
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

    // Index: Retrieve all Plans
    public function index($obj, $request)
    {
        try {
            $query = $obj::with('features', 'prices')
                ->orderByName()
                ->filter((array)$request)
                ->paginate($request['length'] ?? $request['length'] = 10)
                ->withQueryString();
            if ($query) {
                $data = PlanResource::collection($query)->response()->getData();
                return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific Plan
    public function show($obj, $id)
    {
        try {
            $Plan = $obj::with('features', 'prices')->find($id);
            if ($Plan) {
                return $this->success(new PlanResource($Plan), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function showBySlug($obj, $slug)
    {
        try {
            $Plan = $obj::with('features', 'prices')->where('slug', $slug)->first();
            if ($Plan) {
                return $this->success(new PlanResource($Plan), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
