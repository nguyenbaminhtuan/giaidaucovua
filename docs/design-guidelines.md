# Design Guidelines

## 1. Visual Theme & Color Palette
- Phong cách: **Modern Chess Arena** - sạch sẽ, độ tương phản cao, tối ưu hiển thị trên cả máy tính và di động.
- Màu sắc chủ đạo:
  - Nền trang: Slate 50 / Slate 900 (`#f8fafc` / `#0f172a`).
  - Thẻ trận đấu: Slate 100 / Slate 800 với đường viền mảnh Slate 200 / Slate 700.
  - Quân Trắng: `#ffffff` viền xám tro, text đen.
  - Quân Đen: `#1e293b`, text trắng.
  - Nút xác nhận tích xanh: Emerald 600 (`#059669`) hover Emerald 500 (`#10b981`).
  - Điểm số & Huy chương: Vàng Gold (`#eab308`), Bạc Silver (`#94a3b8`), Đồng Bronze (`#b45309`).

## 2. Interaction Patterns
- **Accordion Ván đấu**:
  - Dòng cặp đấu có mũi tên chỉ hướng `▼` xoay 180 độ khi mở.
  - Mở ván đấu bằng transition mượt mà của Alpine.js (`x-collapse`).
- **Nút chọn kết quả 1 chạm**:
  - 3 nút bấm to, rõ ràng: `[ 1 - 0 ]`, `[ ½ - ½ ]`, `[ 0 - 1 ]`.
  - Hiệu ứng active state rõ rệt: viền sáng, đổ màu nền để người điều hành giải nhìn từ xa không bị nhầm.
- **Nút Tích Xanh Chốt Điểm**:
  - Khi chưa đủ điểm: Nút bị disable, độ mờ 40%.
  - Khi đủ điểm: Nút sáng màu xanh ngọc, có tooltip "Xác nhận chốt kết quả".
  - Khi đã chốt: Hiển thị icon khóa nhỏ bên cạnh dấu tích xanh để biểu thị cặp đấu đã hoàn tất.
