<?php

namespace App\Repositories\Admin;

use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Admin\SocialLink\SocialLinkResource;
use App\Interfaces\Admin\SocialLinkRepositoryInterface;

class SocialLinkRepository extends BaseRepository implements SocialLinkRepositoryInterface
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

    // Index: Retrieve all social links
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
                $responseData = SocialLinkResource::collection($query)->response()->getData();
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

    // Store: Create a new social link
    public function store($obj, $request)
    {
        try {
            $socialLink = $obj::create($request);
            if ($socialLink) {
                return $this->success(new SocialLinkResource($socialLink), Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific social link
    public function show($obj, $id)
    {
        try {
            $socialLink = $obj::findOrFail($id);
            if ($socialLink) {
                return $this->success(new SocialLinkResource($socialLink), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Fully update an FAQ
    public function update($obj, $request, $id)
    {
        try {
            $obj = $obj::find($id);
            if ($obj) {
                $updated = $obj->update($request);
                if ($updated) {
                    return $this->success(new SocialLinkResource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
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

    // Patch: Partially update an FAQ
    public function patch($obj, $request)
    {
        try {
            $updated = $obj->update($request->all());
            if ($updated) {
                return $this->success(new SocialLinkResource($obj), Constants::PATCH, Response::HTTP_CREATED, true);
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
                return $this->success(new SocialLinkResource($data), Constants::RESTORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
