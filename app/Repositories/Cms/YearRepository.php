<?php

namespace App\Repositories\Cms;

use App\Interfaces\Cms\YearRepositoryInterface;
use App\Constants\Constants;
use App\Http\Resources\Cms\Year\YearResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class YearRepository implements YearRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;

    public function __construct()
    {
        //
    }

    // Index: Retrieve all Years with their events
    public function index($obj, $request)
    {
        try {
            $query = $obj::with('event_details.event')
                ->orderByYear()
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
                $data = YearResource::collection($query)->response()->getData();
                return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific Year with its events
    public function show($obj, $id)
    {
        try {
            $year = $obj::with('event_details.event')->find($id);
            if ($year) {
                return $this->success(new YearResource($year), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
