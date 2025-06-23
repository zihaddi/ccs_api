<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DynamicHeader\DynamicHeaderStoreRequest;
use App\Http\Requests\Admin\DynamicHeader\DynamicHeaderUpdateMenuRequest;
use App\Http\Requests\Admin\DynamicHeader\DynamicHeaderUpdateRequest;
use App\Interfaces\Admin\DynamicHeaderRepositoryInterface;
use App\Models\DynamicHeader;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DynamicHeaderController extends Controller
{
    protected $treeEntityRepository;

    /**
     * TreeEntityController constructor.
     *
     * @param DynamicHeaderRepositoryInterface $treeEntityRepository
     */
    public function __construct(DynamicHeaderRepositoryInterface $treeEntityRepository)
    {
        $this->treeEntityRepository = $treeEntityRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(DynamicHeader $obj, Request $request)
    {
        return $this->treeEntityRepository->index($obj, $request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TreeEntityRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DynamicHeader $obj, DynamicHeaderStoreRequest $request)
    {
        return $this->treeEntityRepository->store($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(DynamicHeader $obj, $id)
    {
        return $this->treeEntityRepository->show($obj, $id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DynamicHeaderRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DynamicHeader $obj, DynamicHeaderUpdateRequest $request, $id)
    {
        $treeEntity = $obj::find($id);
        return $this->treeEntityRepository->update($treeEntity, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(DynamicHeader $obj, $id)
    {
        $treeEntity = $obj::find($id);
        return $this->treeEntityRepository->destroy($treeEntity, $id);
    }

    /**
     * Restore a soft-deleted resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(DynamicHeader $obj, $id)
    {
        return $this->treeEntityRepository->restore($obj, $id);
    }

    /**
     * Build a menu structure.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buildMenu(DynamicHeader $obj)
    {
        return $this->treeEntityRepository->buildmenu($obj);
    }


    public function showmenu(DynamicHeader $obj)
    {
        return $this->treeEntityRepository->showmenu($obj);
    }

    /**
     * Update menu structure.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMenu(DynamicHeader $obj, DynamicHeaderUpdateMenuRequest $request)
    {
        return $this->treeEntityRepository->updatemenu($obj, $request->all());
    }

    /**
     * Delete or deactivate a menu.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMenu(DynamicHeader $obj, Request $request)
    {
        return $this->treeEntityRepository->deleteMenu($obj, $request);
    }


    public function treemenuNew(DynamicHeader $obj)
    {
        return $this->treeEntityRepository->treemenuNew($obj);
    }
}
