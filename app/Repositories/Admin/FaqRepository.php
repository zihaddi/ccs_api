<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\FaqRepositoryInterface;
use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Resources\Admin\Faq\FaqResource;
use App\Http\Traits\Access;
use App\Http\Traits\FileSetup;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FaqRepository extends BaseRepository implements FaqRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    use FileSetup;
    protected $image_target_path = 'images/faq';
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // Index: Retrieve all FAQs
    public function index($obj, $request)
    {
        try {
            $query = $obj::with('category')
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
                $responseData = FaqResource::collection($query)->response()->getData();
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

    // Store: Create a new FAQ
    public function store($obj, $request)
    {
        try {
            $faq = $obj::create($request);
            if (!empty($request['attachment'])) {
                if ($request['type'] == '2') {
                    // Save new image
                    $request['attachment'] = $this->image_target_path . '/' . $faq->id . '/' . $this->base64ToImage(
                        $request['attachment'],
                        $this->image_target_path . '/' . $faq->id
                    );
                    $faq->update(['attachment' => $request['attachment']]);
                } elseif ($request['type'] == '3') {
                    // Save new pdf
                    $request['attachment'] = $this->image_target_path . '/' . $faq->id . '/' . $this->base64ToPdf(
                        $request['attachment'],
                        $this->image_target_path . '/' . $faq->id
                    );
                    $faq->update(['attachment' => $request['attachment']]);
                }
            }
            if ($faq) {
                return $this->success(new FaqResource($faq), Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific FAQ
    public function show($obj, $id)
    {
        try {
            $faq = $obj::find($id)->with('category');
            if ($faq) {
                return $this->success(new FaqResource($faq), Constants::SHOW, Response::HTTP_OK, true);
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
            if (!empty($request['attachment'])) {
                // Check and delete existing image
                $attachment = $request['attachment'];
                if (is_string($attachment) && strpos($attachment, 'data:image') === 0) {
                    $existingImage = $obj->attachment;
                    $this->deleteImage($existingImage);
                }
                // Save new image
                if ($request['type'] == '2') {
                    $request['attachment'] = $this->image_target_path . '/' . $obj->id . '/' . $this->base64ToImage(
                        $request['attachment'],
                        $this->image_target_path . '/' . $obj->id
                    );
                    $obj->update(['attachment' => $request['attachment']]);
                } elseif ($request['type'] == '3') {
                    $request['attachment'] = $this->image_target_path . '/' . $obj->id . '/' . $this->base64ToPdf(
                        $request['attachment'],
                        $this->image_target_path . '/' . $obj->id
                    );
                    $obj->update(['attachment' => $request['attachment']]);
                }
            }
            if ($obj) {
                $updated = $obj->update(
                    [
                        'title' => $request['title'],
                        'description' => $request['description'] ?? '',
                        'status' => $request['status'] ?? '',
                        'cat_id' => $request['cat_id'] ?? '',
                        'embed_url' => $request['embed_url'] ?? '',
                        'type' => $request['type'] ?? '',
                    ]
                );
                if ($updated) {
                    return $this->success(new FaqResource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
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
                return $this->success(new FaqResource($obj), Constants::PATCH, Response::HTTP_CREATED, true);
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
                return $this->success(new FaqResource($data), Constants::RESTORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
