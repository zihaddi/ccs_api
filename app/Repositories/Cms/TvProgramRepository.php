<?php

namespace App\Repositories\Cms;

use App\Interfaces\Cms\TvProgramRepositoryInterface;
use App\Constants\Constants;
use App\Http\Resources\Cms\TvProgram\TvProgramResource;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use Symfony\Component\HttpFoundation\Response;

class TvProgramRepository implements TvProgramRepositoryInterface
{
    use HttpResponses, Helper;

    public function __construct()
    {
        //
    }

    public function index($obj, $request)
    {
        try {
            $query = $obj::with('channel')
                ->where('status', true)
                ->whereHas('channel', function ($query) {
                    $query->where('status', true);
                })
                ->orderByBroadcastDate()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = TvProgramResource::collection($query)->response()->getData();
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
            $tvProgram = $obj::with('channel')
                ->where('status', true)
                ->whereHas('channel', function ($query) {
                    $query->where('status', true);
                })
                ->find($id);

            if ($tvProgram) {
                $responseData = new TvProgramResource($tvProgram);
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
            $tvProgram = $obj::with('channel')
                ->where('status', true)
                ->where('slug', $slug)
                ->whereHas('channel', function ($query) {
                    $query->where('status', true);
                })
                ->first();

            if ($tvProgram) {
                $responseData = new TvProgramResource($tvProgram);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getByChannel($obj, $request, $channelId)
    {
        try {
            $query = $obj::with('channel')
                ->where('status', true)
                ->where('channel_id', $channelId)
                ->whereHas('channel', function ($query) {
                    $query->where('status', true);
                })
                ->orderByBroadcastDate()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = TvProgramResource::collection($query)->response()->getData();
                return $this->success($data, 'Programs retrieved successfully', Response::HTTP_OK, true);
            } else {
                return $this->error(null, 'No programs found', Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getToday($obj, $request)
    {
        try {
            $query = $obj::with('channel')
                ->where('status', true)
                ->whereDate('broadcast_date', now()->toDateString())
                ->whereHas('channel', function ($query) {
                    $query->where('status', true);
                })
                ->orderBy('broadcast_time', 'asc')
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = TvProgramResource::collection($query)->response()->getData();
                return $this->success($data, "Today's programs retrieved successfully", Response::HTTP_OK, true);
            } else {
                return $this->error(null, "No programs found for today", Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getByType($obj, $request, $type)
    {
        try {
            $query = $obj::with('channel')
                ->where('status', true)
                ->where('type', $type)
                ->whereHas('channel', function ($query) {
                    $query->where('status', true);
                })
                ->orderByBroadcastDate()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = TvProgramResource::collection($query)->response()->getData();
                return $this->success($data, 'Programs retrieved successfully', Response::HTTP_OK, true);
            } else {
                return $this->error(null, 'No programs found', Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
