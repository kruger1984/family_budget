<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CategoryRequest;
use App\Http\Requests\Api\UpdateCategoryRequest;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use App\Support\Http\ApiResponse;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $activeFamily = $request->attributes->get('active_family');

        $categories = Category::query()
            ->where(function ($query) use ($activeFamily): void {
                $query->default();
                if ($activeFamily) {
                    $query->orWhere(fn ($q) => $q->forFamily($activeFamily->id));
                }
            })
            ->with('children')
            ->get();

        return ApiResponse::success(
            data: CategoryResource::collection($categories),
        );
    }

    /**
     * @throws Exception
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $activeFamily = $request->attributes->get('active_family');

        $category = Category::query()->create([
            ...$request->validated(),
            'family_id' => $activeFamily->id,
        ]);

        return ApiResponse::success(
            data: CategoryResource::make($category->load('children')),
            message: 'Category created successfully',
            status: 201
        );
    }

    public function show(Category $category): JsonResponse
    {
        $this->authorize('view', $category);

        return ApiResponse::success(
            data: CategoryResource::make($category)
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $data = $request->validated();

        $category->update($data);

        return ApiResponse::success(
            data: CategoryResource::make($category),
            message: 'Category updated successfully'
        );
    }

    public function destroy(Category $category): Response
    {
        $this->authorize('delete', $category);

        $category->delete();

        return response()->noContent();
    }
}
