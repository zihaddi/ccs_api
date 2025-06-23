<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\BrandRepositoryInterface;
use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Resources\Admin\Brand\BrandResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\FileSetup;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BrandRepository extends BaseRepository implements BrandRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    use FileSetup;

    protected $image_target_path = 'images/brand-logos';

    public function __construct()
    {
        //
    }

    // Index: Retrieve all brands
    public function index($obj, $request)
    {
        try {
            $query = $obj::query()
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
                $responseData = BrandResource::collection($query)->response()->getData();
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

    // Store: Create a new brand
    public function store($obj, $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();

            if (isset($request->logo)) {
                $data['logo'] = $this->base64ToImage($request->logo, $this->image_target_path);
            }

            $data['created_by'] = Auth::id();
            $brand = $obj::create($data);

            if ($brand) {
                DB::commit();
                $responseData = ['data' => new BrandResource($brand)];
                return $this->success($responseData, Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                DB::rollBack();
                return $this->error([], Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific brand
    public function show($obj, $id)
    {
        try {
            $brand = $obj::find($id);
            if ($brand) {
                $responseData = ['data' => new BrandResource($brand)];
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error([], Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Fully update a brand
    public function update($obj, $request, $id)
    {
        try {
            DB::beginTransaction();
            $brand = $obj::find($id);

            if ($brand) {
                $data = $request->all();

                if (isset($request->logo)) {
                    $this->deleteImage($brand->logo);
                    $data['logo'] = $this->base64ToImage($request->logo, $this->image_target_path);
                }

                $data['modified_by'] = Auth::id();
                $updated = $brand->update($data);

                if ($updated) {
                    DB::commit();
                    $responseData = ['data' => new BrandResource($brand)];
                    return $this->success($responseData, Constants::UPDATE, Response::HTTP_CREATED, true);
                } else {
                    DB::rollBack();
                    return $this->error([], Constants::FAILUPDATE, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error([], Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Destroy: Soft delete a brand
    public function destroy($obj, $id)
    {
        try {
            $brand = $obj::find($id);
            if ($brand) {
                $deleted = $brand->delete();
                if ($deleted) {
                    return $this->success([], Constants::DESTROY, Response::HTTP_CREATED, true);
                } else {
                    return $this->error([], Constants::FAILDESTROY, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error([], Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Restore: Restore a soft-deleted brand
    public function restore($obj, $id)
    {
        try {
            $brand = $obj::withTrashed()->find($id);
            if ($brand) {
                $restored = $brand->restore();
                if ($restored) {
                    return $this->success([], Constants::RESTORE, Response::HTTP_CREATED, true);
                } else {
                    return $this->error([], Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
                }
            } else {
                return $this->error([], Constants::NODATA, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
