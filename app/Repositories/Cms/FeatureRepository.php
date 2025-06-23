<?php

namespace App\Repositories\Cms;

use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Constants\Constants;
use App\Http\Resources\Admin\Feature\FeatureResource;
use App\Interfaces\Cms\FeatureRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FeatureRepository implements FeatureRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;

    public function index($obj, $request)
    {
        try {
            $query = $obj::query()
                ->where('status', 1)
                ->orderBy('name')
                ->filter((array)$request);

            $query = $query->when(
                isset($request['paginate']) && $request['paginate'] == true,
                function ($query) use ($request) {
                    return $query->paginate($request['length'] ?? 15)->withQueryString();
                },
                function ($query) {
                    return $query->get();
                }
            );

            if ($query) {
                $data = FeatureResource::collection($query)->response()->getData();
                return self::success($data, Constants::FETCH);
            }
            return self::error('', Constants::FETCHERROR, Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return self::error('', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($obj, $id)
    {
        try {
            $data = $obj::query()
                ->where('status', 1)
                ->find($id);

            if ($data) {
                return self::success(
                    new FeatureResource($data),
                    Constants::FETCH
                );
            }
            return self::error('', Constants::FETCHERROR, Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return self::error('', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showBySlug($obj, $slug)
    {
        try {
            $data = $obj::query()
                ->where('status', 1)
                ->where('slug', $slug)
                ->first();

            if ($data) {
                return self::success(
                    new FeatureResource($data),
                    Constants::FETCH
                );
            }
            return self::error('', Constants::FETCHERROR, Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return self::error('', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
