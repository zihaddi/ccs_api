<?php

namespace App\Repositories\Admin;

use App\Http\Traits\FileSetup;
use App\Interfaces\Admin\PartnerRepositoryInterface;
use App\Constants\Constants;
use App\Http\Resources\Admin\Partner\PartnerResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PartnerRepository extends BaseRepository implements PartnerRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    use FileSetup;
    protected $image_target_path = 'images/partner-image';

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
                $responseData = PartnerResource::collection($query)->response()->getData();
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
            $data = $obj::create([
                "name" => $request['name'],
                "short_desc" => $request['short_desc'],
                "status" => $request['status']
            ]);
            if (!empty($request['logo'])) {
                // Save new image
                $request['logo'] = $this->image_target_path . '/' . $data->id . '/' . $this->base64ToImage(
                    $request['logo'],
                    $this->image_target_path . '/' . $data->id
                );
                $data->update(['logo' => $request['logo']]);
            }
            DB::commit();

            if ($data) {
                return self::success(
                    new PartnerResource($data),
                    Constants::STORE,
                    Response::HTTP_CREATED
                );
            }
            return self::error('', Constants::FAILSTORE, Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return self::error('', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($obj, $id)
    {
        try {
            $data = $obj::with(['features'])->find($id);
            if ($data) {
                return self::success(
                    new PartnerResource($data),
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
                if (!empty($request['logo'])) {
                    // Check and delete existing image
                    $logo = $request['logo'];
                    if (is_string($logo) && strpos($logo, 'data:image') === 0) {
                        $existingImage = $obj->logo;
                        $this->deleteImage($existingImage);
                    }
                    // Save new image
                    $request['logo'] = $this->image_target_path . '/' . $id . '/' . $this->base64ToImage(
                        $request['logo'],
                        $this->image_target_path . '/' . $id
                    );
                }

                $data->update($request);
                DB::commit();
                return self::success(
                    new PartnerResource($data),
                    Constants::UPDATE
                );
            }
            return self::error('', Constants::NODATA, Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return self::error('', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($obj, $id)
    {
        try {
            DB::beginTransaction();
            $data = $obj::find($id);
            if ($data) {
                $data->delete();
                DB::commit();
                return self::success('', Constants::DESTROY);
            }
            return self::error('', Constants::NODATA, Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            return self::error('', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Restore: Restore a soft-deleted data
    public function restore($obj, $id)
    {
        try {
            $data = $obj::withTrashed()->find($id);
            if ($data) {
                $data->restore();
                return $this->success(new PartnerResource($data), Constants::RESTORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
