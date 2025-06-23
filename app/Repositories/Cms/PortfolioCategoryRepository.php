<?php

namespace App\Repositories\Cms;

use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Constants\Constants;
use App\Http\Resources\Cms\PortfolioCategory\PortfolioCategoryResource;
use App\Interfaces\Cms\PortfolioCategoryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PortfolioCategoryRepository implements PortfolioCategoryRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;

    public function __construct()
    {
        //
    }

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
                $data = PortfolioCategoryResource::collection($query)->response()->getData();
                return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function show($obj, $id)
    {
        try {
            $portfolioCategory = $obj::findOrFail($id);
            if ($portfolioCategory) {
                return $this->success(new PortfolioCategoryResource($portfolioCategory), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function showBySlug($obj, $slug)
    {
        try {
            $portfolioCategory = $obj::where('slug', $slug)->first();
            if ($portfolioCategory) {
                return $this->success(new PortfolioCategoryResource($portfolioCategory), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
