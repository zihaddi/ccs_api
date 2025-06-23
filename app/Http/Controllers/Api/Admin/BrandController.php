<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Brand\UpdateBrandRequest;
use App\Interfaces\Admin\BrandRepositoryInterface;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    private BrandRepositoryInterface $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;

        // Group middleware by permission type
        $this->middleware('check.permission:view')->only(['index', 'show', 'all']);
        $this->middleware('check.permission:add')->only(['store']);
        $this->middleware('check.permission:edit')->only(['update']);
        $this->middleware('check.permission:delete')->only(['destroy', 'restore']);
    }

    /**
     * Display a listing of brands.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->brandRepository->index(Brand::class, $request);
    }

    /**
     * Store a newly created brand.
     *
     * @param StoreBrandRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreBrandRequest $request)
    {
        return $this->brandRepository->store(Brand::class, $request);
    }

    /**
     * Display the specified brand.
     *
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(mixed $id)
    {
        return $this->brandRepository->show(Brand::class, $id);
    }

    /**
     * Update the specified brand.
     *
     * @param UpdateBrandRequest $request
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateBrandRequest $request, mixed $id)
    {
        return $this->brandRepository->update(Brand::class, $request, $id);
    }

    /**
     * Remove the specified brand from storage.
     *
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(mixed $id)
    {
        return $this->brandRepository->destroy(Brand::class, $id);
    }

    /**
     * Restore the specified soft-deleted brand.
     *
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(mixed $id)
    {
        return $this->brandRepository->restore(Brand::class, $id);
    }
}
