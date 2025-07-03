<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\TvChannelRepositoryInterface;
use App\Constants\Constants;
use App\Http\Resources\Admin\TvChannel\TvChannelResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TvChannelRepository extends BaseRepository implements TvChannelRepositoryInterface
{
    use Access, HttpResponses, Helper;

    public function __construct()
    {
        //
    }

    public function index($obj, $request)
    {
        try {
            $query = $obj::orderByName()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $responseData = TvChannelResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function store($obj, $request)
    {
        try {
            $tvChannel = $obj::create($request);

            if ($tvChannel) {
                $responseData = new TvChannelResource($tvChannel);
                return $this->success($responseData, Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::STORE, Response::HTTP_BAD_REQUEST, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function show($obj, $id)
    {
        try {
            $tvChannel = $obj::find($id);
            if ($tvChannel) {
                $responseData = new TvChannelResource($tvChannel);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
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
            $tvChannel = $obj::where('slug', $slug)->first();
            if ($tvChannel) {
                $responseData = new TvChannelResource($tvChannel);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function update($obj, $request, $id)
    {
        try {
            $tvChannel = $obj::find($id);
            if ($tvChannel) {
                $tvChannel->update($request);
                $responseData = new TvChannelResource($tvChannel);
                return $this->success($responseData, Constants::UPDATE, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::UPDATE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function destroy($obj, $id)
    {
        try {
            $tvChannel = $obj::find($id);
            if ($tvChannel) {
                $tvChannel->delete();
                return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::DESTROY, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function restore($obj, $id)
    {
        try {
            $tvChannel = $obj::withTrashed()->find($id);
            if ($tvChannel && $tvChannel->trashed()) {
                $tvChannel->restore();
                return $this->success(null, Constants::RESTORE, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::RESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
