<?php

namespace App\Repositories\Admin;

use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Constants\Constants;
use App\Http\Resources\Admin\Portfolio\PortfolioResource;
use App\Http\Traits\FileSetup;
use App\Interfaces\Admin\PortfolioRepositoryInterface;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PortfolioRepository extends BaseRepository implements PortfolioRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    use FileSetup;

    protected $image_target_path = 'images/portfolio-image';

    public function __construct()
    {
        //
    }

    // Index: Retrieve all portfolios
    public function index($obj, $request)
    {
        try {
            $query = $obj::with('category')
                ->orderByName()
                ->filter((array) $request);
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
                $responseData = PortfolioResource::collection($query)->response()->getData();
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

    // Store: Create a new portfolio
    public function store($obj, $request)
    {
        try {
            $portfolio = $obj::create([
                'title' => $request['title'],
                'slug' => $request['slug'],
                'cat_id' => $request['cat_id'],
                'description' => $request['description'],
                'client_name' => $request['client_name'],
                'project_url' => $request['project_url'],
                'completion_date' => $request['completion_date'],
                'technologies' => $request['technologies'],
                'status' => $request['status']
            ]);

            if (!empty($request['photo'])) {
                // Save new image
                $request['photo'] = $this->image_target_path . '/' . $portfolio->id . '/' . $this->base64ToImage(
                    $request['photo'],
                    $this->image_target_path . '/' . $portfolio->id
                );
                $portfolio->update(['photo' => $request['photo']]);
            }

            if ($portfolio) {
                return $this->success(new PortfolioResource($portfolio), Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific portfolio
    public function show($obj, $id)
    {
        try {
            $portfolio = $obj::with('category')->find($id);
            if ($portfolio) {
                return $this->success(new PortfolioResource($portfolio), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Update a portfolio
    public function update($obj, $request, $id)
    {
        try {
            $obj = $obj::with('category')->find($id);
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
                if ($updated) {
                    return $this->success(new PortfolioResource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
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

    // Destroy: Soft delete a portfolio
    public function destroy($obj, $id)
    {
        try {
            $obj = $obj::find($id);
            if ($obj) {
                $deleted = $obj->delete();
                if ($deleted) {
                    return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
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

    // Restore: Restore a soft deleted portfolio
    public function restore($obj, $id)
    {
        try {
            $restored = $obj::withTrashed()->find($id)->restore();
            if ($restored) {
                return $this->success(null, Constants::RESTORE, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::FAILRESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
