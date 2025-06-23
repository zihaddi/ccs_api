<?php

namespace App\Repositories\Cms;

use App\Interfaces\Cms\MetaRepositoryInterface;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Constants\Constants;
use App\Http\Resources\Cms\Meta\MetaResource;
use App\Http\Traits\FileSetup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class MetaRepository implements MetaRepositoryInterface
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
                $data = MetaResource::collection($query)->response()->getData();
                return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
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

    //Show by slug: Display a specific meta
    public function showBySlug($obj, $slug)
    {
        try {
            $meta = $obj::where('slug', $slug)->first();
            if ($meta) {
                return $this->success(new MetaResource($meta), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
