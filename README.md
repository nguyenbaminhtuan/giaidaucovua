# Hệ Thống Quản Lý Giải Đấu Cờ Vua (Chess Tournament System)

Ứng dụng web tổ chức và điều hành giải đấu cờ vua xây dựng bằng **PHP Laravel 11**, **Livewire 3**, và **Tailwind CSS**.

## Tính Năng Chính
- **Thể thức thi đấu**:
  - Vòng tròn chia bảng (Round-Robin with Groups): Tự chọn số bảng (A, B, C, D...), bốc thăm theo thuật toán chuẩn Berger, tự động xử lý suất Bye nếu bảng lẻ người.
  - Hệ Thụy Sĩ (Swiss System): Ghép cặp theo nhóm điểm, chống gặp lại đối thủ cũ, cân bằng màu quân Trắng/Đen.
- **Cấu hình ván đấu & thời gian Fischer**:
  - Chọn số ván mỗi cặp (ví dụ: 2 ván/cặp).
  - Tùy chỉnh phút gốc và giây gia tăng cho từng ván (ví dụ: Ván 1: 5p+2s, Ván 2: 15p+2s).
  - Tự động đảo màu quân giữa các ván.
- **Accordion cặp đấu & Nhập điểm 1 chạm**:
  - Nhấp vào cặp đấu để mở rộng chi tiết các ván con.
  - Nhập điểm tức thời qua 3 nút bấm: `[ 1 - 0 ]`, `[ ½ - ½ ]`, `[ 0 - 1 ]`.
  - Tự động cộng dồn tỷ số cặp đấu.
  - Nút tích xanh chốt kết quả và khóa sửa đổi.
- **Bảng xếp hạng thời gian thực**:
  - Tự động cập nhật thứ hạng các bảng (Hạng 1, 2, 3...) theo điểm số (Thắng = 1, Hòa = 0.5, Thua = 0).
  - Tích hợp tính điểm hệ số phụ (Sonneborn-Berger / Buchholz).

## Tài Liệu Kỹ Thuật
Xem chi tiết trong thư mục `docs/`:
- `docs/project-overview-pdr.md`: Đặc tả yêu cầu sản phẩm
- `docs/system-architecture.md`: Kiến trúc hệ thống và ERD
- `docs/code-standards.md`: Tiêu chuẩn viết code
- `docs/codebase-summary.md`: Cấu trúc thư mục mã nguồn
- `docs/design-guidelines.md`: Hướng dẫn giao diện và trải nghiệm
- `docs/project-roadmap.md`: Lộ trình phát triển 4 giai đoạn
- `docs/deployment-guide.md`: Hướng dẫn triển khai
