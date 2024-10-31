<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductGallery;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $description = $request->input('description');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');
        $merchant_id = $request->input('merchant_id');
        $category_id = $request->input('category_id');

        $product = Product::with(['merchant', 'category', 'galleries', 'reviews.user', 'variants']);

        if ($id) {
            $product = $product->find($id);

            if ($product) {
                return ResponseFormatter::success($product, 'Data produk berhasil diambil');
            } else {
                return ResponseFormatter::error(null, 'Data produk tidak ada', 404);
            }
        }

        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }

        if ($description) {
            $product->where('description', 'like', '%' . $description . '%');
        }

        if ($price_from) {
            $product->where('price', '>=', $price_from);
        }

        if ($price_to) {
            $product->where('price', '<=', $price_to);
        }

        if ($merchant_id) {
            $product->where('merchant_id', $merchant_id);
        }

        if ($category_id) {
            $product->where('category_id', $category_id);
        }

        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data list produk berhasil diambil'
        );
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer',
            'merchant_id' => 'required|exists:merchants,id',
            'category_id' => 'required|exists:product_categories,id',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $product = Product::create($request->all());

        return ResponseFormatter::success($product, 'Produk berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ResponseFormatter::error(null, 'Produk tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'integer',
            'merchant_id' => 'exists:merchants,id',
            'category_id' => 'exists:product_categories,id',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $product->update($request->all());

        return ResponseFormatter::success($product, 'Produk berhasil diperbarui');
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ResponseFormatter::error(null, 'Produk tidak ditemukan', 404);
        }

        $product->delete();

        return ResponseFormatter::success(null, 'Produk berhasil dihapus');
    }

    public function addGallery(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $product = Product::find($id);

        if (!$product) {
            return ResponseFormatter::error(null, 'Produk tidak ditemukan', 404);
        }

        $gallery = ProductGallery::create([
            'products_id' => $id,
            'url' => $request->url,
        ]);

        return ResponseFormatter::success($gallery, 'Galeri produk berhasil ditambahkan');
    }

    public function deleteGallery($id)
    {
        $gallery = ProductGallery::find($id);

        if (!$gallery) {
            return ResponseFormatter::error(null, 'Galeri produk tidak ditemukan', 404);
        }

        $gallery->delete();

        return ResponseFormatter::success(null, 'Galeri produk berhasil dihapus');
    }

    public function getByMerchant($merchantId)
    {
        $products = Product::where('merchant_id', $merchantId)->with(['category', 'galleries'])->paginate(10);

        if ($products->isEmpty()) {
            return ResponseFormatter::error(null, 'Tidak ada produk untuk merchant ini', 404);
        }

        return ResponseFormatter::success($products, 'Data produk merchant berhasil diambil');
    }

    public function getByCategory($categoryId)
    {
        $products = Product::where('category_id', $categoryId)->with(['merchant', 'galleries'])->paginate(10);

        if ($products->isEmpty()) {
            return ResponseFormatter::error(null, 'Tidak ada produk untuk kategori ini', 404);
        }

        return ResponseFormatter::success($products, 'Data produk kategori berhasil diambil');
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $limit = $request->input('limit', 10);

        if (!$keyword) {
            return ResponseFormatter::error(null, 'Kata kunci pencarian diperlukan', 400);
        }

        $products = Product::where('name', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->with(['merchant', 'category', 'galleries'])
            ->paginate($limit);

        if ($products->isEmpty()) {
            return ResponseFormatter::error(null, 'Produk tidak ditemukan', 404);
        }

        return ResponseFormatter::success($products, 'Produk ditemukan');
    }
    public function addVariant(Request $request, $productId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'attributes' => 'required|json',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $product = Product::find($productId);

        if (!$product) {
            return ResponseFormatter::error(null, 'Produk tidak ditemukan', 404);
        }

        $variant = ProductVariant::create([
            'product_id' => $productId,
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'attributes' => $request->attributes,
        ]);

        return ResponseFormatter::success($variant, 'Varian produk berhasil ditambahkan');
    }

    public function updateVariant(Request $request, $variantId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'price' => 'numeric',
            'stock' => 'integer',
            'attributes' => 'json',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $variant = ProductVariant::find($variantId);

        if (!$variant) {
            return ResponseFormatter::error(null, 'Varian produk tidak ditemukan', 404);
        }

        $variant->update($request->all());

        return ResponseFormatter::success($variant, 'Varian produk berhasil diperbarui');
    }

    public function deleteVariant($variantId)
    {
        $variant = ProductVariant::find($variantId);

        if (!$variant) {
            return ResponseFormatter::error(null, 'Varian produk tidak ditemukan', 404);
        }

        $variant->delete();

        return ResponseFormatter::success(null, 'Varian produk berhasil dihapus');
    }

    public function getProductVariants($productId)
    {
        $product = Product::with('variants')->find($productId);

        if (!$product) {
            return ResponseFormatter::error(null, 'Produk tidak ditemukan', 404);
        }

        return ResponseFormatter::success($product->variants, 'Varian produk berhasil diambil');
    }

    public function getVariant($variantId)
    {
        $variant = ProductVariant::find($variantId);

        if (!$variant) {
            return ResponseFormatter::error(null, 'Varian produk tidak ditemukan', 404);
        }

        return ResponseFormatter::success($variant, 'Detail varian produk berhasil diambil');
    }
}
