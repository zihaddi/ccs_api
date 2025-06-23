<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\ComplianceRepositoryInterface;
use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Resources\Admin\Compliance\ComplianceResource;
use App\Http\Traits\Access;
use App\Http\Traits\FileSetup;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ComplianceRepository extends BaseRepository implements ComplianceRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    use FileSetup;
    protected $image_target_path = 'images/compliance-image';
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // Index: Retrieve all Compliances
    public function index($obj, $request)
    {
        try {
            $query = $obj::with('detail')
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
                $responseData = ComplianceResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Store: Create a new Compliance
    public function store($obj, $request)
    {
        try {
            $data = $obj::create($request);
            if ($request['details'] && count($request['details']) > 0) {
                $data->detail()->createMany($request['details']);
            }
            if (!empty($request['images'])) {
                // Save new image
                $request['images'] = $this->image_target_path . '/' . $data->id . '/' . $this->base64ToImage(
                    $request['images'],
                    $this->image_target_path . '/' . $data->id
                );
                $data->update(['images' => $request['images']]);
            }
            if ($data) {
                return $this->success(new ComplianceResource($data), Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific Compliance
    public function show($obj, $id)
    {
        try {
            $Compliance = $obj::with('detail')->find($id);
            if ($Compliance) {
                return $this->success(new ComplianceResource($Compliance), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Fully update an Compliance
    public function update($obj, $request, $id)
    {
        try {
            $obj = $obj::with('detail')->find($id);
            if ($request['details'] && count($request['details']) > 0) {
                $obj->detail()->delete();
                $obj->detail()->createMany($request['details']);
            }
            if (!empty($request['images'])) {
                // Save new image
                $request['images'] = $this->image_target_path . '/' . $id . '/' . $this->base64ToImage(
                    $request['images'],
                    $this->image_target_path . '/' . $id
                );
                $obj->update(['images' => $request['images']]);
            }
            if ($obj) {
                $updated = $obj->update($request);
                if ($updated) {
                    $data = $obj::with('detail')->find($id);
                    return $this->success(new ComplianceResource($data), Constants::UPDATE, Response::HTTP_CREATED, true);
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

    // Patch: Partially update an Compliance
    public function patch($obj, $request)
    {
        try {
            $updated = $obj->update($request->all());
            if ($updated) {
                return $this->success(new ComplianceResource($obj), Constants::PATCH, Response::HTTP_CREATED, true);
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
            $obj = $obj::with('detail')->find($id);
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
                return $this->success(new ComplianceResource($data), Constants::RESTORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
