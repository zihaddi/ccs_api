<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\FeatureRepositoryInterface;
use App\Constants\Constants;
use App\Http\Resources\Admin\Feature\FeatureResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FeatureRepository extends BaseRepository implements FeatureRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;

    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['creator', 'modifier'])
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
                $responseData = FeatureResource::collection($query)->response()->getData();
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
            $request['created_by'] = Auth::id();

            DB::beginTransaction();
            $data = $obj::create($request);
            DB::commit();

            if ($data) {
                return self::success(
                    new FeatureResource($data),
                    Constants::STORE,
                    Response::HTTP_CREATED
                );
            }
            return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
        } catch (\Exception $e) {
            DB::rollBack();
            return self::error('', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($obj, $id)
    {
        try {
            $data = $obj::find($id);
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

    public function update($obj, $request, $id)
    {
        try {
            $request['modified_by'] = Auth::id();

            DB::beginTransaction();
            $data = $obj::find($id);
            if ($data) {
                $data->update($request);
                DB::commit();
                return self::success(
                    new FeatureResource($data),
                    Constants::UPDATE
                );
            }
            return $this->error(null, Constants::NODATA, Response::HTTP_NOT_FOUND, false);
        } catch (\Exception $e) {
            DB::rollBack();
            return self::error('', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
                return $this->success(new FeatureResource($data), Constants::RESTORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
