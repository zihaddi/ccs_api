<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\MetaRepositoryInterface;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Constants\Constants;
use App\Http\Resources\Admin\Meta\MetaResource;
use App\Http\Traits\FileSetup;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class MetaRepository extends BaseRepository implements MetaRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    use FileSetup;
    protected $image_target_path = 'images/meta-image';
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // Index: Retrieve all Metas
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
                $responseData = MetaResource::collection($query)->response()->getData();
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

    // Store: Create a new meta
    public function store($obj, $request)
    {
        try {
            $meta = $obj::create($request);

            if (!empty($request['og_image'])) {
                // Save new image
                $request['og_image'] = $this->image_target_path . '/' . $meta->id . '/' . $this->base64ToImage(
                    $request['og_image'],
                    $this->image_target_path . '/' . $meta->id
                );
                $meta->update(['og_image' => $request['og_image']]);
            }

            // Handle twitter_image
            if (!empty($request['twitter_image'])) {
                // Save new image
                $request['twitter_image'] = $this->image_target_path . '/' . $meta->id . '/' . $this->base64ToImage(
                    $request['twitter_image'],
                    $this->image_target_path . '/' . $meta->id
                );
                $meta->update(['twitter_image' => $request['twitter_image']]);
            }
            if ($meta) {
                return $this->success(new MetaResource($meta), Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific meta
    public function show($obj, $id)
    {
        try {
            $meta = $obj::findOrFail($id);
            if ($meta) {
                return $this->success(new MetaResource($meta), Constants::SHOW, Response::HTTP_OK, true);
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
                if (!empty($request['og_image'])) {
                    // Check and delete existing image
                    $og_image = $request['og_image'];
                    if (is_string($og_image) && strpos($og_image, 'data:image') === 0) {
                        $existingImage = $obj->og_image;
                        $this->deleteImage($existingImage);
                    }
                    // Save new image
                    $request['og_image'] = $this->image_target_path . '/' . $obj->id . '/' . $this->base64ToImage(
                        $request['og_image'],
                        $this->image_target_path . '/' . $obj->id
                    );
                }

                // Handle twitter_image
                if (!empty($request['twitter_image'])) {
                    // Check and delete existing image
                    $twitter_image = $request['twitter_image'];
                    if (is_string($twitter_image) && strpos($twitter_image, 'data:image') === 0) {
                        $existingImage = $obj->twitter_image;
                        $this->deleteImage($existingImage);
                    }
                    // Save new image
                    $request['twitter_image'] = $this->image_target_path . '/' . $obj->id . '/' . $this->base64ToImage(
                        $request['twitter_image'],
                        $this->image_target_path . '/' . $obj->id
                    );
                }
                $updated = $obj->update($request);
                if ($updated) {
                    return $this->success(new MetaResource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
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
                return $this->success(new MetaResource($obj), Constants::PATCH, Response::HTTP_CREATED, true);
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
                return $this->success(new MetaResource($data), Constants::RESTORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
