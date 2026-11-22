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

class OrderController extends Controller
{
    protected ProductServiceClient $productService;

    public function __construct(ProductServiceClient $productService)
    {
        $this->productService = $productService;
    }

    /**
     * GET /orders: Lấy danh sách tất cả các đơn hàng
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('items')->latest();

        // Lọc theo trạng thái đơn hàng (pending, completed, cancelled)
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Lọc theo email khách hàng
        if ($request->filled('customer_email')) {
            $query->where('customer_email', $request->query('customer_email'));
        }

        // Tìm kiếm theo tên hoặc email
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách đơn hàng thành công',
            'count' => $orders->count(),
            'data' => $orders,
        ], 200);
    }

    /**
     * GET /orders/{id}: Lấy thông tin chi tiết một đơn hàng
     */
    public function show(string|int $id): JsonResponse
    {
        $order = Order::with('items')->find($id);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => "Không tìm thấy đơn hàng với ID {$id}",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Lấy thông tin đơn hàng thành công',
            'data' => $order,
        ], 200);
    }

    /**
     * POST /orders: Tạo đơn hàng mới (kiểm tra tồn kho từ Product Service)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'status' => 'nullable|string|in:pending,completed,cancelled',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|integer',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.product_name' => 'nullable|string|max:255',
        ], [
            'customer_name.required' => 'Tên khách hàng không được để trống.',
            'customer_email.required' => 'Email khách hàng không được để trống.',
            'customer_email.email' => 'Email khách hàng không đúng định dạng.',
            'status.in' => 'Trạng thái đơn hàng phải là: pending, completed hoặc cancelled.',
            'items.array' => 'Danh sách mặt hàng phải là một mảng.',
            'items.*.product_id.required_with' => 'ID sản phẩm là bắt buộc cho từng mặt hàng.',
            'items.*.quantity.min' => 'Số lượng mặt hàng phải lớn hơn hoặc bằng 1.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $itemsData = $request->input('items', []);
        $preparedItems = [];
        $totalAmount = 0.00;

        // 1. Kiểm tra tồn kho và lấy thông tin giá/tên từ Product Service
        if (! empty($itemsData)) {
            foreach ($itemsData as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];

                try {
                    // Gọi Product Service để kiểm tra tồn kho
                    $product = $this->productService->checkStock($productId, $quantity);

                    // Ưu tiên giá và tên từ Product Service nếu client không truyền
                    $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : (float) $product['price'];
                    $productName = ! empty($item['product_name']) ? (string) $item['product_name'] : (string) $product['name'];
                    $totalPrice = round($quantity * $unitPrice, 2);

                    $preparedItems[] = [
                        'product_id' => $productId,
                        'product_name' => $productName,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ];

                    $totalAmount += $totalPrice;
                } catch (Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Lỗi kiểm tra tồn kho từ Dịch vụ Sản phẩm (Product Service)',
                        'error' => $e->getMessage(),
                    ], 400);
                }
            }
        }

        // 2. Lưu đơn hàng và chi tiết đơn hàng vào Cơ sở dữ liệu trong Transaction
        try {
            DB::beginTransaction();

            $order = Order::create([
                'customer_name' => $request->input('customer_name'),
                'customer_email' => $request->input('customer_email'),
                'total_amount' => $totalAmount,
                'status' => $request->input('status', Order::STATUS_PENDING),
            ]);

            foreach ($preparedItems as $pItem) {
                $pItem['order_id'] = $order->id;
                OrderItem::create($pItem);

                // Tùy chọn trừ tồn kho trên Product Service
                $this->productService->deductStock($pItem['product_id'], $pItem['quantity']);
            }

            DB::commit();

            // Load lại relation để trả về response đầy đủ
            $order->load('items');

            return response()->json([
                'status' => true,
                'message' => 'Tạo đơn hàng mới thành công',
                'data' => $order,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Không thể tạo đơn hàng do lỗi hệ thống',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /orders/{id}: Cập nhật trạng thái đơn hàng
     */
    public function update(Request $request, string|int $id): JsonResponse
    {
        $order = Order::with('items')->find($id);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => "Không tìm thấy đơn hàng với ID {$id}",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|in:pending,completed,cancelled',
            'customer_name' => 'sometimes|required|string|max:255',
            'customer_email' => 'sometimes|required|email|max:255',
        ], [
            'status.in' => 'Trạng thái đơn hàng phải là một trong các giá trị: pending, completed, cancelled.',
            'customer_email.email' => 'Email khách hàng không đúng định dạng.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu cập nhật không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order->update($request->only(['status', 'customer_name', 'customer_email']));

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật trạng thái đơn hàng thành công',
            'data' => $order->fresh('items'),
        ], 200);
    }

    /**
     * DELETE /orders/{id}: Xóa một đơn hàng
     */
    public function destroy(string|int $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => "Không tìm thấy đơn hàng với ID {$id}",
            ], 404);
        }

        // Xóa đơn hàng (order_items sẽ được xóa tự động nhờ cascade on delete)
        $order->delete();

        return response()->json([
            'status' => true,
            'message' => "Xóa đơn hàng ID {$id} thành công",
        ], 200);
    }
}
