<?php

namespace App\Repositories\Cms;

use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Constants\Constants;
use App\Http\Resources\Cms\Portfolio\PortfolioResource;
use App\Http\Traits\FileSetup;
use App\Interfaces\Cms\PortfolioRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PortfolioRepository implements PortfolioRepositoryInterface
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
                $data = PortfolioResource::collection($query)->response()->getData();
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
            $portfolio = $obj::with('category')->findOrFail($id);
            if ($portfolio) {
                return $this->success(new PortfolioResource($portfolio), Constants::SHOW, Response::HTTP_OK, true);
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
            $portfolio = $obj::where('slug', $slug)->with('category')->first();
            if ($portfolio) {
                return $this->success(new PortfolioResource($portfolio), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
