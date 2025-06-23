<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamMember\TeamMemberRequest;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use App\Interfaces\Admin\TeamMemberRepositoryInterface;

class TeamMemberController extends Controller
{
    protected $client;

    public function __construct(TeamMemberRepositoryInterface $client)
    {
        $this->client = $client;
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    public function index(TeamMember $obj, Request $request)
    {
        return $this->client->index($obj, $request->all());
    }

    public function store(TeamMember $obj, TeamMemberRequest $request)
    {
        return $this->client->store($obj, $request->validated());
    }

    public function show(TeamMember $obj, $id)
    {
        return $this->client->show($obj, $id);
    }

    public function update(TeamMember $obj, TeamMemberRequest $request, $id)
    {
        return $this->client->update($obj, $request->validated(), $id);
    }

    public function destroy(TeamMember $obj, $id)
    {
        return $this->client->destroy($obj, $id);
    }

    public function restore(TeamMember $obj, $id)
    {
        return $this->client->restore($obj, $id);
    }

    public function updateOrder(TeamMember $obj, Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:team_members,id',
            'orders.*.sort_order' => 'required|integer'
        ]);

        return $this->client->updateOrder($obj, $request->all());
    }
}
