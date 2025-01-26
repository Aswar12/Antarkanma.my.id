<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    public function list()
    {
        $categories = ProductCategory::all();

        return ResponseFormatter::success(
            $categories,
            'Data kategori produk berhasil diambil'
        );
    }

    public function get($id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ResponseFormatter::error(
                null,
                'Data kategori produk tidak ditemukan',
                404
            );
        }

        return ResponseFormatter::success(
            $category,
            'Data kategori produk berhasil diambil'
        );
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                ['errors' => $validator->errors()],
                'Validation Error',
                422
            );
        }

        $category = ProductCategory::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return ResponseFormatter::success(
            $category,
            'Kategori produk berhasil ditambahkan'
        );
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                ['errors' => $validator->errors()],
                'Validation Error',
                422
            );
        }

        $category = ProductCategory::find($id);

        if (!$category) {
            return ResponseFormatter::error(
                null,
                'Data kategori produk tidak ditemukan',
                404
            );
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return ResponseFormatter::success(
            $category,
            'Kategori produk berhasil diperbarui'
        );
    }

    public function delete($id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ResponseFormatter::error(
                null,
                'Data kategori produk tidak ditemukan',
                404
            );
        }

        $category->delete();

        return ResponseFormatter::success(
            null,
            'Kategori produk berhasil dihapus'
        );
    }
}
