<?php

namespace App\Repositories\Admin;

use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Constants\Constants;
use App\Http\Resources\Admin\Event\EventResource;
use App\Http\Traits\FileSetup;
use App\Interfaces\Admin\EventRepositoryInterface;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    use FileSetup;
    protected $image_target_path = 'images/event-image';

    public function __construct()
    {
        //
    }

    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['category', 'details'])
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
                $responseData = EventResource::collection($query)->response()->getData();
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
            $event = $obj::create([
                'title' => $request['title'],
                'slug' => $request['slug'],
                'category_id' => $request['category_id'],
                'description' => $request['description'],
                'status' => $request['status']
            ]);

            if (!empty($request['photo'])) {
                // Save new image
                $request['photo'] = $this->image_target_path . '/' . $event->id . '/' . $this->base64ToImage(
                    $request['photo'],
                    $this->image_target_path . '/' . $event->id
                );
                $event->update(['photo' => $request['photo']]);
            }

            if ($request['details']) {
                $event->details()->createMany($request['details']);
            }

            if ($event) {
                return $this->success(new EventResource($event), Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function show($obj, $id)
    {
        try {
            $event = $obj::with(['category', 'details'])->find($id);
            if ($event) {
                return $this->success(new EventResource($event), Constants::SHOW, Response::HTTP_OK, true);
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
            $obj = $obj::with(['category', 'details'])->find($id);
            if ($obj) {
                if (!empty($request['photo'])) {
                    // Check and delete existing image
                    $photo = $request['photo'];
                    if (is_string($photo) && strpos($photo, 'data:image') === 0) {
                        $existingImage = $obj->photo;
                        $this->deleteImage($existingImage);
                    }
                    // Save new image
                    $request['photo'] = $this->image_target_path . '/' . $obj->id . '/' . $this->base64ToImage(
                        $request['photo'],
                        $this->image_target_path . '/' . $obj->id
                    );
                }
                $updated = $obj->update($request);

                if ($request['details']) {
                    $obj->details()->delete();
                    $obj->details()->createMany($request['details']);
                }
                if ($updated) {
                    $obj = $obj::with(['category', 'details'])->find($id);
                    return $this->success(new EventResource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
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

    public function restore($obj, $id)
    {
        try {
            $data = $obj::withTrashed()->find($id);
            if ($data) {
                $data->restore();
                return $this->success(new EventResource($data), Constants::RESTORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
