<?php

namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Interfaces\Cms\TeamMemberRepositoryInterface;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    protected $client;

    public function __construct(TeamMemberRepositoryInterface $client)
    {
        $this->client = $client;
    }

    public function index(TeamMember $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function show(TeamMember $obj, $id)
    {
        return $this->client->show($obj, $id);
    }
}
