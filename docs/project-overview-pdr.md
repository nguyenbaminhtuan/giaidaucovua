# Project Overview & PDR (Product Development Requirements)

## 1. Product Summary
Hệ thống quản lý và tổ chức giải đấu cờ vua trực quan xây dựng trên nền tảng **PHP Laravel 11** kết hợp **Livewire 3** và **Tailwind CSS**. Ứng dụng cung cấp giải pháp toàn diện cho các câu lạc bộ, trường học và ban tổ chức giải đấu cờ vua từ khâu cấu hình thể thức, bốc thăm xếp cặp, nhập kết quả từng ván theo thời gian thực đến cập nhật bảng xếp hạng tự động.

## 2. Core Functional Requirements
1. **Thể thức thi đấu linh hoạt**:
   - **Vòng tròn chia bảng (Round-Robin with Groups)**: Tùy biến số lượng bảng (Bảng A, B, C, D...), phân chia kỳ thủ vào từng bảng. Tự động bốc thăm xoay vòng tròn theo thuật toán chuẩn Berger (nếu bảng có số lẻ kỳ thủ thì tự động xử lý suất Bye miễn đấu).
   - **Hệ Thụy Sĩ (Swiss System)**: Xếp cặp theo nhóm điểm, đảm bảo không gặp lại đối thủ cũ (rematch prevention), cân bằng màu quân Trắng/Đen và xử lý dồn nhóm (floater/bye).
2. **Cấu hình Trận đấu & Kiểm soát thời gian (Fischer Time Controls)**:
   - Tùy chọn số ván thi đấu trong mỗi cặp (ví dụ: 2 ván/cặp).
   - Thiết lập thời gian thi đấu riêng cho từng ván: Thời gian gốc (`base_minutes`) + Thời gian gia tăng mỗi nước đi (`increment_seconds`), ví dụ Ván 1: `5+2`, Ván 2: `15+2`.
   - Tự động luân phiên đổi màu quân Trắng/Đen giữa các ván.
3. **Giao diện Quản lý Trận đấu (Master-Detail Accordion UI)**:
   - Thẻ cặp đấu hiển thị tổng điểm trực quan (`Kỳ thủ A (1.5) - (0.5) Kỳ thủ B`).
   - Click vào cặp đấu để xổ Dropdown (Accordion) hiển thị chi tiết từng ván.
   - Nhập kết quả nhanh bằng 3 nút chọn kết quả: `[ 1 - 0 ]`, `[ ½ - ½ ]`, `[ 0 - 1 ]`.
   - Tổng điểm cặp đấu tự động cộng dồn từ các ván con, không cho nhập tay để tránh sai lệch.
   - Nút tích xanh xác nhận: Chỉ kích hoạt khi đã ghi nhận đủ kết quả các ván; khi nhấn sẽ chốt kết quả, khóa thao tác chỉnh sửa và cập nhật bảng xếp hạng ngay lập tức.
4. **Bảng Xếp Hạng Thời Gian Thực (Live Standings & Tie-breaks)**:
   - Hiển thị theo từng bảng thi đấu.
   - Tính điểm chuẩn cờ vua: Thắng = 1.0, Hòa = 0.5, Thua = 0.
   - Sắp xếp thứ hạng từ cao xuống thấp (Hạng 1, 2, 3...).
   - Tích hợp tiêu chí phụ (Tie-breaks): Đối đầu trực tiếp, hệ số Sonneborn-Berger (cho vòng tròn) và Buchholz (cho hệ Thụy Sĩ).

## 3. Non-Functional Requirements
- **Reactivity & Hiệu năng**: Thao tác đóng mở ván đấu tức thời qua Alpine.js; cập nhật điểm và bảng xếp hạng không cần tải lại toàn bộ trang (Livewire 3).
- **Toàn vẹn dữ liệu**: Sử dụng Database Transaction khi chốt kết quả trận đấu và tính toán lại bảng xếp hạng.
- **KISS & Modularization**: Phân tách rõ ràng giữa Service xếp lịch (`RoundRobinSchedulerService`, `SwissSchedulerService`), Service tính BXH (`StandingsCalculatorService`), và giao diện Livewire.
