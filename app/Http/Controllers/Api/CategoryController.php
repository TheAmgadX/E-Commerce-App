<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/categories",
     * summary="Get all product categories",
     * tags={"Category"},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\Response(
     * response=200,
     * description="List of categories",
     * @OA\JsonContent(
     * @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/Category"))
     * )
     * )
     * )
     */
    public function categories() {
        return response()->json([
            'categories' => Category::all()
        ]);
    }
}
