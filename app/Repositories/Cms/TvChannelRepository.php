<?php

namespace App\Repositories\Cms;

use App\Interfaces\Cms\TvChannelRepositoryInterface;
use App\Constants\Constants;
use App\Http\Resources\Cms\TvChannel\TvChannelResource;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use Symfony\Component\HttpFoundation\Response;

class TvChannelRepository implements TvChannelRepositoryInterface
{
    use HttpResponses, Helper;

    public function __construct()
    {
        //
    }

    public function index($obj, $request)
    {
        try {
            $query = $obj::where('status', true)
                ->orderByName()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = TvChannelResource::collection($query)->response()->getData();
                return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function show($obj, $id)
    {
        try {
            $tvChannel = $obj::where('status', true)->find($id);
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
            $tvChannel = $obj::where('status', true)->where('slug', $slug)->first();
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
}
