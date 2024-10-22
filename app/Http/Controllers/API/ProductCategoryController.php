<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProductCategoryController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:product_categories,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::validationError($validator->errors());
        }

        try {
            $category = ProductCategory::create($request->all());
            return ResponseFormatter::success($category, 'Product category created successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to create product category: ' . $e->getMessage(), 500);
        }
    }

    public function get($id)
    {
        try {
            $category = ProductCategory::findOrFail($id);
            return ResponseFormatter::success($category, 'Product category retrieved successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Product category not found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255|unique:product_categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::validationError($validator->errors());
        }

        try {
            $category = ProductCategory::findOrFail($id);
            $category->update($request->all());
            return ResponseFormatter::success($category, 'Product category updated successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to update product category: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        try {
            $category = ProductCategory::findOrFail($id);
            $category->delete();
            return ResponseFormatter::success(null, 'Product category deleted successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error('Failed to delete product category: ' . $e->getMessage(), 500);
        }
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $name = $request->input('name');

        $categoryQuery = ProductCategory::query();

        if ($name) {
            $categoryQuery->where('name', 'like', '%' . $name . '%');
        }

        $categories = $categoryQuery->paginate($limit);

        return ResponseFormatter::success(
            $categories,
            'Product categories list retrieved successfully'
        );
    }
}
