<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/products",
     * summary="Filter and list products",
     * description="Retrieve products based on optional filters provided in the JSON body.",
     * tags={"Products"},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\RequestBody(
     * required=false,
     * description="Optional filter criteria",
     * @OA\JsonContent(
     * @OA\Property(
     * property="category_ids",
     * type="array",
     * @OA\Items(type="integer", example=1),
     * description="Array of category IDs to filter by"
     * ),
     * @OA\Property(property="min_price", type="number", example=100, description="Minimum price"),
     * @OA\Property(property="max_price", type="number", example=1000, description="Maximum price"),
     * @OA\Property(property="search", type="string", example="Headphones", description="Search term for name or description")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Products retrieved successfully"),
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
     * )
     * )
     * )
     */
    public function products(Request $request) {
        $query = Product::query();

        // 1. Filter by Categories
        if ($request->filled('category_ids')) {
            $categoryIds = $request->input('category_ids');

            // Handle if sent as comma-separated string OR JSON array
            if (is_string($categoryIds)) {
                $categoryIds = explode(',', $categoryIds);
            }

            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('id', $categoryIds);
            });
        }

        // 2. Filter by Min Price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        // 3. Filter by Max Price
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // 4. Search (Name or Description)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->with('images')->get();

        return response()->json([
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ], 200);
    }
}