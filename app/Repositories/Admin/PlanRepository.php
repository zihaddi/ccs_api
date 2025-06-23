<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\PlanRepositoryInterface;
use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Resources\Admin\Plan\PlanResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PlanRepository extends BaseRepository implements PlanRepositoryInterface
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
                $responseData = PlanResource::collection($query)->response()->getData();
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

    // Store: Create a new Plan
    public function store($obj, $request)
    {
        try {
            $Plan = $obj::create($request);

            if ($request['features']) {
                $Plan->features()->createMany($request['features']);
            }
            if ($request['prices']) {
                $Plan->prices()->createMany($request['prices']);
            }

            if ($Plan) {
                return $this->success(new PlanResource($Plan), Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
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

    // Update: Fully update an Plan
    public function update($obj, $request, $id)
    {
        try {
            $obj = $obj::find($id);
            if ($obj) {

                $updated = $obj->update($request);

                if ($request['features']) {
                    $obj->features()->delete();
                    $obj->features()->createMany($request['features']);
                }

                if ($request['prices']) {
                    $obj->prices()->delete();
                    $obj->prices()->createMany($request['prices']);
                }

                if ($updated) {
                    return $this->success(new PlanResource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
                } else {
                    return $this->error(null, Constants::FAILUPDATE, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Patch: Partially update an Plan
    public function patch($obj, $request)
    {
        try {
            $updated = $obj->update($request->all());
            if ($updated) {
                return $this->success(new PlanResource($obj), Constants::PATCH, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILPATCH, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Destroy: Soft delete a data
    public function destroy($obj, $id)
    {
        try {
            $obj = $obj::find($id);
            if ($obj) {
                $deleted = $obj->delete();
                if ($deleted) {
                    return $this->success(null, Constants::DESTROY, Response::HTTP_CREATED, true);
                } else {
                    return $this->error(null, Constants::FAILDESTROY, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Restore: Restore a soft-deleted data
    public function restore($obj, $id)
    {
        try {
            $data = $obj::withTrashed()->find($id);
            if ($data) {
                $data->restore();
                return $this->success(new PlanResource($data), Constants::RESTORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
