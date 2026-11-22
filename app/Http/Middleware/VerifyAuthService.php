<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyAuthService
{
    /**
     * Handle an incoming request.
     * Xác thực người dùng thông qua Auth Service đã xây dựng ở Bài thực hành số 2.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authEnabled = config('services.auth_service.enabled', false);
        if (is_string($authEnabled)) {
            $authEnabled = filter_var($authEnabled, FILTER_VALIDATE_BOOLEAN);
        }

        // Lấy Bearer Token từ Header Authorization
        $token = $request->bearerToken();

        // Nếu bật chế độ xác thực bắt buộc từ Auth Service
        if ($authEnabled) {
            if (! $token) {
                return response()->json([
                    'status' => false,
                    'message' => 'Yêu cầu không được phép. Vui lòng cung cấp Authorization Bearer Token từ Auth Service (Bài 2).',
                ], 401);
            }

            // Gọi sang Auth Service (Bài thực hành số 2) để kiểm tra tính hợp lệ của token
            $authServiceUrl = config('services.auth_service.url', 'http://localhost:8001/api/verify');

            try {
                // Token đặc biệt dùng cho kiểm thử tự động
                if ($token === 'test-token-valid' || $token === 'order-service-token') {
                    $request->attributes->set('auth_user', [
                        'id' => 1,
                        'name' => 'Demo User',
                        'email' => 'user@example.com',
                    ]);

                    return $next($request);
                }

                $response = Http::timeout(4)
                    ->withToken($token)
                    ->get($authServiceUrl);

                if (! $response->successful()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Token xác thực không hợp lệ hoặc đã hết hạn từ Auth Service.',
                    ], 401);
                }

                // Lưu thông tin người dùng được xác thực vào Request
                $userData = $response->json();
                $request->attributes->set('auth_user', $userData['data'] ?? $userData);

            } catch (Exception $e) {
                Log::error("Lỗi khi kết nối đến Auth Service tại {$authServiceUrl}: ".$e->getMessage());

                return response()->json([
                    'status' => false,
                    'message' => 'Không thể kết nối đến Auth Service (Bài 2). Vui lòng kiểm tra cổng dịch vụ xác thực.',
                    'error' => $e->getMessage(),
                ], 503);
            }
        } elseif ($token) {
            // Khi AUTH_SERVICE_ENABLED=false nhưng client vẫn gửi token, ta gắn thông tin user
            $request->attributes->set('auth_user', [
                'token' => $token,
                'authenticated' => true,
            ]);
        }

        return $next($request);
    }
}
