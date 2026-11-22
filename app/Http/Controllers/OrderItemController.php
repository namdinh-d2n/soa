<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ProductServiceClient;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderItemController extends Controller
{
    protected ProductServiceClient $productService;

    public function __construct(ProductServiceClient $productService)
    {
        $this->productService = $productService;
    }

    /**
     * GET /order_items: Lấy danh sách tất cả mặt hàng trong đơn hàng
     */
    public function index(Request $request): JsonResponse
    {
        $query = OrderItem::with('order')->latest();

        // Lọc theo order_id
        if ($request->filled('order_id')) {
            $query->where('order_id', $request->query('order_id'));
        }

        // Lọc theo product_id
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }

        $items = $query->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách mặt hàng trong đơn hàng thành công',
            'count' => $items->count(),
            'data' => $items,
        ], 200);
    }

    /**
     * GET /order_items/{id}: Lấy thông tin chi tiết một mặt hàng
     */
    public function show(string|int $id): JsonResponse
    {
        $item = OrderItem::with('order')->find($id);

        if (! $item) {
            return response()->json([
                'status' => false,
                'message' => "Không tìm thấy mặt hàng với ID {$id}",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Lấy chi tiết mặt hàng thành công',
            'data' => $item,
        ], 200);
    }

    /**
     * POST /order_items: Tạo mặt hàng mới (kiểm tra tồn kho và cập nhật tổng tiền đơn hàng)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'product_name' => 'nullable|string|max:255',
        ], [
            'order_id.required' => 'Mã đơn hàng (order_id) là bắt buộc.',
            'order_id.exists' => 'Đơn hàng tương ứng không tồn tại.',
            'product_id.required' => 'Mã sản phẩm (product_id) là bắt buộc.',
            'quantity.required' => 'Số lượng sản phẩm là bắt buộc.',
            'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $productId = (int) $request->input('product_id');
        $quantity = (int) $request->input('quantity');
        $orderId = (int) $request->input('order_id');

        // 1. Kiểm tra tồn kho từ Product Service
        try {
            $product = $this->productService->checkStock($productId, $quantity);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi kiểm tra tồn kho từ Product Service',
                'error' => $e->getMessage(),
            ], 400);
        }

        $unitPrice = $request->has('unit_price') ? (float) $request->input('unit_price') : (float) $product['price'];
        $productName = $request->input('product_name') ?: $product['name'];
        $totalPrice = round($quantity * $unitPrice, 2);

        // 2. Tạo mặt hàng và tính lại tổng tiền đơn hàng
        try {
            DB::beginTransaction();

            $orderItem = OrderItem::create([
                'order_id' => $orderId,
                'product_id' => $productId,
                'product_name' => $productName,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ]);

            // Cập nhật lại total_amount của đơn hàng
            $order = Order::find($orderId);
            if ($order) {
                $order->recalculateTotal();
            }

            // Trừ tồn kho nếu được kích hoạt
            $this->productService->deductStock($productId, $quantity);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Thêm mặt hàng vào đơn hàng thành công',
                'data' => $orderItem,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Không thể thêm mặt hàng',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /order_items/{id}: Cập nhật mặt hàng
     */
    public function update(Request $request, string|int $id): JsonResponse
    {
        $item = OrderItem::find($id);

        if (! $item) {
            return response()->json([
                'status' => false,
                'message' => "Không tìm thấy mặt hàng với ID {$id}",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|required|integer|min:1',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'product_name' => 'sometimes|required|string|max:255',
        ], [
            'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu cập nhật không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Nếu có cập nhật quantity, kiểm tra lại tồn kho
        if ($request->has('quantity')) {
            $newQuantity = (int) $request->input('quantity');
            try {
                $this->productService->checkStock($item->product_id, $newQuantity);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Lỗi kiểm tra tồn kho từ Product Service',
                    'error' => $e->getMessage(),
                ], 400);
            }
            $item->quantity = $newQuantity;
        }

        if ($request->has('unit_price')) {
            $item->unit_price = (float) $request->input('unit_price');
        }

        if ($request->has('product_name')) {
            $item->product_name = $request->input('product_name');
        }

        // Tự động cập nhật total_price
        $item->total_price = round($item->quantity * $item->unit_price, 2);
        $item->save();

        // Cập nhật lại total_amount của order
        $order = Order::find($item->order_id);
        if ($order) {
            $order->recalculateTotal();
        }

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật mặt hàng thành công',
            'data' => $item,
        ], 200);
    }

    /**
     * DELETE /order_items/{id}: Xóa một mặt hàng trong đơn hàng
     */
    public function destroy(string|int $id): JsonResponse
    {
        $item = OrderItem::find($id);

        if (! $item) {
            return response()->json([
                'status' => false,
                'message' => "Không tìm thấy mặt hàng với ID {$id}",
            ], 404);
        }

        $orderId = $item->order_id;
        $item->delete();

        // Tính lại tổng tiền của đơn hàng
        $order = Order::find($orderId);
        if ($order) {
            $order->recalculateTotal();
        }

        return response()->json([
            'status' => true,
            'message' => "Xóa mặt hàng ID {$id} khỏi đơn hàng thành công",
        ], 200);
    }
}
