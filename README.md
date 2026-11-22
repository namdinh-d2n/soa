# DỊCH VỤ BÁO CÁO & THỐNG KÊ (REPORT & ANALYTICS SERVICE)
## KIẾN TRÚC HƯỚNG DỊCH VỤ (SOA - SERVICE-ORIENTED ARCHITECTURE)

Dịch vụ Báo cáo & Thống kê được xây dựng bằng framework **Laravel 11 LTS** (tương thích 100% với PHP 8.2 của XAMPP), hoạt động hoàn toàn độc lập trên **cổng riêng** (`localhost:8003`) và **cơ sở dữ liệu riêng** (`report_service.sqlite` / `report_db`), đáp ứng đầy đủ các yêu cầu bài toán:

> **Yêu cầu đề bài:**
> - Xây dựng dịch vụ báo cáo với các chức năng thống kê:
>   1. **Số lượng hàng tồn** (Inventory Quantity)
>   2. **Hàng bán được** (Sold Quantity)
>   3. **Doanh thu** (Revenue)
>   4. **Chi phí** (Cost)
>   5. **Lợi nhuận** (Profit = Revenue - Cost)
> - **Báo cáo theo sản phẩm** (Report by Product) và **Báo cáo theo đơn hàng** (Report by Order)
> - Dịch vụ hoạt động độc lập trên một cổng và cơ sở dữ liệu riêng (tuân theo nguyên tắc của kiến trúc SOA).
> - Có giao diện **HTML trực quan** đầy đủ cho người dùng và giảng viên chấm bài.

---

## 1. Các Trang Giao Diện HTML Trực Quan (Web Dashboard)

Khi khởi chạy dịch vụ trên cổng `8003`, bạn có thể mở trực tiếp các trang HTML trên trình duyệt:

| Trang giao diện | Đường dẫn URL | Mô tả chức năng |
| :--- | :--- | :--- |
| 📊 **Tổng quan Thống kê (Dashboard)** | `http://localhost:8003/` | • 5 thẻ chỉ số kinh doanh: Hàng tồn, Hàng bán được, Doanh thu, Chi phí, Lợi nhuận, Tỷ suất lợi nhuận.<br>• Biểu đồ cột so sánh Doanh thu - Chi phí - Lợi nhuận (Chart.js).<br>• Biểu đồ tròn tỷ lệ Hàng tồn kho vs Hàng bán.<br>• Top sản phẩm bán chạy nhất & thống kê đơn hàng.<br>• Nút **Đồng bộ SOA** dữ liệu thời gian thực. |
| 📦 **Báo cáo theo Sản phẩm** | `http://localhost:8003/products-report` | • Bảng thống kê chi tiết từng sản phẩm: Tồn kho, Số lượng đã bán, Giá vốn, Giá bán, Doanh thu, Chi phí, Lợi nhuận, Tỷ suất lợi nhuận.<br>• Ô tìm kiếm theo tên và sắp xếp theo: Doanh thu, Lợi nhuận, Bán chạy, Tồn kho. |
| 🛒 **Báo cáo theo Đơn hàng** | `http://localhost:8003/orders-report` | • Bảng danh sách đơn hàng: Mã đơn, Khách hàng, Ngày đặt, Trạng thái, Doanh thu, Chi phí vốn, Lợi nhuận.<br>• Nút mở rộng xem chi tiết từng món hàng trong đơn (kèm giá vốn và lợi nhuận). |
| 🏬 **Thống kê Hàng tồn kho** | `http://localhost:8003/inventory-report` | • Tổng hàng tồn, định giá tồn kho theo giá vốn & giá bán lẻ.<br>• Cảnh báo các sản phẩm sắp hết hàng (Low Stock Alert). |

---

## 2. Hướng dẫn Khởi chạy Dịch vụ

### Bước 1: Khởi động Dịch vụ trên Cổng 8003
Mở Terminal tại thư mục `product-service` và gõ:
```powershell
php artisan serve --port=8003
```
> Mở trình duyệt tại: **`http://localhost:8003`**

### Bước 2: Nạp dữ liệu mẫu (Seeder)
```powershell
php artisan db:seed
```

### Bước 3: Chạy Kiểm thử tự động (Unit & Feature Tests)
```powershell
php artisan test
```
*(Kết quả: **25 passed, 144 assertions** bao phủ toàn bộ API và các trang giao diện HTML).*

---

## 3. Bản đồ Kiến trúc SOA & Cổng Dịch vụ (Ports)

| Dịch vụ | Cổng | Cơ sở dữ liệu | Vai trò trong hệ thống SOA |
| :--- | :--- | :--- | :--- |
| **Report & Analytics Service** | `localhost:8003` | `report_service.sqlite` | Tổng hợp, phân tích, thống kê hàng tồn, hàng bán, doanh thu, chi phí, lợi nhuận |
| **Product Service** | `localhost:8000` | `product_db` | Quản lý danh mục sản phẩm, giá bán, số lượng tồn kho |
| **Order Service** | `localhost:8002` | `order_db` | Tiếp nhận đơn hàng, xử lý thanh toán, lưu chi tiết mua hàng |
| **Auth Service** | `localhost:8001` | `auth_db` | Xác thực người dùng và cấp phát Token |

---

## 4. Danh mục RESTful API (Dành cho Postman / Frontend)

| Nghiệp vụ | Phương thức & Endpoint | Mô tả |
| :--- | :--- | :--- |
| **Thống kê Tổng quan** | `GET /api/reports/overview` | Trả về JSON tổng hợp: `total_inventory_quantity`, `total_sold_quantity`, `total_revenue`, `total_cost`, `total_profit`, `profit_margin_percent`. |
| **Báo cáo Sản phẩm** | `GET /api/reports/products` | Trả về JSON danh sách sản phẩm với các chỉ số tài chính và tồn kho. |
| **Báo cáo Đơn hàng** | `GET /api/reports/orders` | Trả về JSON đơn hàng kèm doanh thu, chi phí vốn và lợi nhuận từng đơn. |
| **Báo cáo Tồn kho** | `GET /api/reports/inventory` | Trả về JSON định giá tồn kho và danh sách cảnh báo hàng tồn thấp. |
| **Đồng bộ Dữ liệu SOA** | `POST /api/reports/sync` | Tự động gọi Product Service (8000) và Order Service (8002) để cập nhật số liệu. |
