<?php

namespace App\Repositories\Cms;

use App\Constants\Constants;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Interfaces\Cms\TeamMemberRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

class TeamMemberRepository implements TeamMemberRepositoryInterface
{
    use HttpResponses;
    use Helper;

    public function __construct()
    {
        //
    }

    public function index($obj, $request)
    {
        try {
            $teamMembers = $obj::where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($teamMembers) {
                return $this->success($teamMembers, Constants::GETALL, Response::HTTP_OK, true);
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
            $teamMember = $obj::where('id', $id)
                ->where('is_active', true)
                ->first();

            if ($teamMember) {
                return $this->success($teamMember, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, 'Team member not found', Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
