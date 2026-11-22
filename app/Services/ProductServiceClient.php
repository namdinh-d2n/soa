<?php

namespace App\Services;

use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductServiceClient
{
    protected string $baseUrl;

    protected int $timeout;

    protected bool $reduceStock;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('PRODUCT_SERVICE_URL', 'http://localhost:8000/api/products'), '/');
        $this->timeout = (int) env('PRODUCT_SERVICE_TIMEOUT', 5);
        $this->reduceStock = filter_var(env('PRODUCT_SERVICE_REDUCE_STOCK', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Lấy thông tin sản phẩm từ Product Service
     *
     * @throws Exception
     */
    public function getProduct(int $productId): array
    {
        $url = "{$this->baseUrl}/{$productId}";

        try {
            $response = Http::timeout($this->timeout)->get($url);

            if ($response->successful()) {
                $body = $response->json();
                // Hỗ trợ cả trường hợp trả về trực tiếp model hoặc bọc trong key 'data'
                $data = isset($body['data']) && is_array($body['data']) ? $body['data'] : $body;

                return [
                    'id' => (int) ($data['id'] ?? $productId),
                    'name' => (string) ($data['name'] ?? 'Sản phẩm #'.$productId),
                    'price' => (float) ($data['price'] ?? 0),
                    'quantity' => (int) ($data['quantity'] ?? 0),
                    'description' => $data['description'] ?? null,
                ];
            }

            if ($response->status() === 404) {
                throw new Exception("Sản phẩm ID {$productId} không tồn tại trong Dịch vụ Quản lý Sản phẩm (Product Service).");
            }

            throw new Exception("Lỗi khi truy vấn Product Service (HTTP {$response->status()}): {$response->body()}");
        } catch (Exception $e) {
            // Nếu là lỗi đã throw ở trên thì ném tiếp
            if (str_contains($e->getMessage(), 'Dịch vụ Quản lý Sản phẩm')) {
                throw $e;
            }

            Log::warning("Không kết nối được tới Product Service tại {$url}. Đang kiểm tra cơ chế fallback/local. Chi tiết: {$e->getMessage()}");

            // Local fallback: Nếu server Product Service riêng chưa bật, kiểm tra trong DB local (hỗ trợ kiểm thử)
            if (class_exists(Product::class)) {
                $localProduct = Product::find($productId);
                if ($localProduct) {
                    return [
                        'id' => (int) $localProduct->id,
                        'name' => (string) $localProduct->name,
                        'price' => (float) $localProduct->price,
                        'quantity' => (int) $localProduct->quantity,
                        'description' => $localProduct->description,
                    ];
                }
            }

            // Nếu không có local fallback và không kết nối được
            throw new Exception("Không thể kết nối đến Dịch vụ Sản phẩm (Product Service) tại [{$url}]. Vui lòng đảm bảo Product Service đang hoạt động trên cổng được cấu hình trong .env (PRODUCT_SERVICE_URL).");
        }
    }

    /**
     * Kiểm tra số lượng tồn kho của sản phẩm từ Product Service
     *
     * @return array Thông tin sản phẩm nếu hợp lệ
     *
     * @throws Exception
     */
    public function checkStock(int $productId, int $requestedQuantity): array
    {
        if ($requestedQuantity <= 0) {
            throw new Exception("Số lượng đặt hàng cho sản phẩm ID {$productId} phải lớn hơn 0.");
        }

        $product = $this->getProduct($productId);

        if ($product['quantity'] < $requestedQuantity) {
            throw new Exception("Sản phẩm '{$product['name']}' (ID: {$productId}) không đủ số lượng tồn kho! Tồn kho hiện có: {$product['quantity']}, Số lượng yêu cầu: {$requestedQuantity}.");
        }

        return $product;
    }

    /**
     * Giảm tồn kho trên Product Service (tùy chọn trong SOA)
     */
    public function deductStock(int $productId, int $quantity): bool
    {
        if (! $this->reduceStock) {
            return true;
        }

        try {
            $product = $this->getProduct($productId);
            $newQuantity = max(0, $product['quantity'] - $quantity);

            $url = "{$this->baseUrl}/{$productId}";
            $response = Http::timeout($this->timeout)->put($url, [
                'quantity' => $newQuantity,
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error("Lỗi khi cập nhật tồn kho trên Product Service: {$e->getMessage()}");

            return false;
        }
    }
}
