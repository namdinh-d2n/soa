<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /products: Lấy danh sách tất cả các sản phẩm
    public function index()
    {
        $products = Product::all();

        return response()->json($products, 200);
    }

    // POST /products: Thêm một sản phẩm mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json(['message' => 'Tạo sản phẩm thành công', 'data' => $product], 201);
    }

    // GET /products/{id}: Lấy thông tin chi tiết một sản phẩm
    public function show($id)
    {
        $product = Product::find($id);
        if (! $product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        return response()->json($product, 200);
    }

    // PUT /products/{id}: Cập nhật thông tin sản phẩm
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (! $product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
        ]);

        $product->update($validated);

        return response()->json(['message' => 'Cập nhật thành công', 'data' => $product], 200);
    }

    // DELETE /products/{id}: Xóa một sản phẩm
    public function destroy($id)
    {
        $product = Product::find($id);
        if (! $product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Xóa sản phẩm thành công'], 200);
    }
}
