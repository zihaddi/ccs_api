<?php

namespace App\Repositories\Admin;

use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Constants\Constants;
use App\Http\Resources\Admin\PortfolioCategory\PortfolioCategoryResource;
use App\Interfaces\Admin\PortfolioCategoryRepositoryInterface;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PortfolioCategoryRepository extends BaseRepository implements PortfolioCategoryRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;

    public function __construct()
    {
        //
    }

    // Index: Retrieve all portfolio categories
    public function index($obj, $request)
    {
        try {
            $query = $obj::query()
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
                $responseData = PortfolioCategoryResource::collection($query)->response()->getData();
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

    // Store: Create a new portfolio category
    public function store($obj, $request)
    {
        try {
            $portfolioCategory = $obj::create($request);
            if ($portfolioCategory) {
                return $this->success(new PortfolioCategoryResource($portfolioCategory), Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                return $this->error(null, Constants::FAILSTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific portfolio category
    public function show($obj, $id)
    {
        try {
            $portfolioCategory = $obj::find($id);
            if ($portfolioCategory) {
                return $this->success(new PortfolioCategoryResource($portfolioCategory), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Update: Update a portfolio category
    public function update($obj, $request, $id)
    {
        try {
            $obj = $obj::find($id);
            if ($obj) {
                $updated = $obj->update($request);
                if ($updated) {
                    return $this->success(new PortfolioCategoryResource($obj), Constants::UPDATE, Response::HTTP_CREATED, true);
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

    // Destroy: Soft delete a portfolio category
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

    // Restore: Restore a soft deleted portfolio category
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
