<?php

namespace App\Services;

use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderServiceClient
{
    protected string $baseUrl;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.order_service.url', env('ORDER_SERVICE_URL', 'http://localhost:8002/api/orders')), '/');
        $this->timeout = (int) config('services.order_service.timeout', env('ORDER_SERVICE_TIMEOUT', 5));
    }

    /**
     * Lấy danh sách tất cả các đơn hàng từ Order Service
     */
    public function getOrders(): array
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl);

            if ($response->successful()) {
                $body = $response->json();

                return $body['data'] ?? $body;
            }

            Log::warning("Order Service trả về mã {$response->status()}: {$response->body()}");
        } catch (Exception $e) {
            Log::warning("Không thể kết nối đến Order Service tại {$this->baseUrl}: {$e->getMessage()}");
        }

        // Local fallback: Nếu Order Service chạy cùng DB hoặc mock
        if (class_exists(Order::class)) {
            try {
                return Order::with('items')->get()->toArray();
            } catch (Exception) {
                // Return empty if local DB query fails
            }
        }

        return [];
    }

    /**
     * Lấy chi tiết một đơn hàng cụ thể từ Order Service
     */
    public function getOrder(int $orderId): ?array
    {
        $url = "{$this->baseUrl}/{$orderId}";

        try {
            $response = Http::timeout($this->timeout)->get($url);

            if ($response->successful()) {
                $body = $response->json();

                return $body['data'] ?? $body;
            }
        } catch (Exception $e) {
            Log::warning("Lỗi truy vấn đơn hàng ID {$orderId} từ Order Service: {$e->getMessage()}");
        }

        if (class_exists(Order::class)) {
            try {
                $order = Order::with('items')->find($orderId);

                return $order ? $order->toArray() : null;
            } catch (Exception) {
                return null;
            }
        }

        return null;
    }
}
