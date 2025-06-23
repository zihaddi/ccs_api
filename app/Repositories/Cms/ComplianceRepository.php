<?php

namespace App\Repositories\Cms;

use App\Interfaces\Cms\ComplianceRepositoryInterface;
use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Resources\Cms\Compliance\ComplianceResource;
use App\Http\Traits\Access;
use App\Http\Traits\FileSetup;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ComplianceRepository implements ComplianceRepositoryInterface
{
    use Access;
    use HttpResponses;
    use Helper;
    use FileSetup;
    protected $image_target_path = 'images/compliance-image';
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // Index: Retrieve all Compliances
    public function index($obj, $request)
    {
        try {
            $query = $obj::with('detail')
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
                $data = ComplianceResource::collection($query)->response()->getData();
                return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show: Display a specific Compliance
    public function show($obj, $id)
    {
        try {
            $Compliance = $obj::with('detail')->find($id);
            if ($Compliance) {
                return $this->success(new ComplianceResource($Compliance), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Show by slug: Display a specific Compliance
    public function showBySlug($obj, $slug)
    {
        try {
            $Compliance = $obj::with('detail')->where('slug', $slug)->first();
            if ($Compliance) {
                return $this->success(new ComplianceResource($Compliance), Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
