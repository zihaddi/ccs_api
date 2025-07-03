<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\TvProgramRepositoryInterface;
use App\Constants\Constants;
use App\Http\Resources\Admin\TvProgram\TvProgramResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Repositories\BaseRepository;
use Symfony\Component\HttpFoundation\Response;

class TvProgramRepository extends BaseRepository implements TvProgramRepositoryInterface
{
    use Access, HttpResponses, Helper;

    public function __construct()
    {
        //
    }

    public function index($obj, $request)
    {
        try {
            $query = $obj::with('channel')
                ->orderByBroadcastDate()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $responseData = TvProgramResource::collection($query)->response()->getData();
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
            $tvProgram = $obj::create($request);

            if ($tvProgram) {
                $responseData = new TvProgramResource($tvProgram->load('channel'));
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
            $tvProgram = $obj::with('channel')->find($id);
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
            $tvProgram = $obj::with('channel')->where('slug', $slug)->first();
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

    public function update($obj, $request, $id)
    {
        try {
            $tvProgram = $obj::find($id);
            if ($tvProgram) {
                $tvProgram->update($request);
                $responseData = new TvProgramResource($tvProgram->load('channel'));
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
            $tvProgram = $obj::find($id);
            if ($tvProgram) {
                $tvProgram->delete();
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
            $tvProgram = $obj::withTrashed()->find($id);
            if ($tvProgram && $tvProgram->trashed()) {
                $tvProgram->restore();
                return $this->success(null, Constants::RESTORE, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::RESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
