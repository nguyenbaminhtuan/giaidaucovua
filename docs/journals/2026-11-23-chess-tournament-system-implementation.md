# Technical Journal: Chess Tournament System Implementation

- Date: 2026-11-23
- Author: Antigravity (Claude Code)
- Topic: Hoàn thành hệ thống tổ chức & điều hành giải đấu cờ vua trên nền Laravel 11 + Livewire 3

## 1. Bối cảnh & Yêu cầu
Khách hàng cần xây dựng hệ thống tổ chức giải đấu cờ vua hỗ trợ:
- Thể thức: Vòng tròn chia bảng (Round-Robin) và Hệ Thụy Sĩ (Swiss).
- Tùy chỉnh số ván mỗi cặp và kiểm soát thời gian từng ván (Fischer time control: phút gốc + giây gia tăng).
- Giao diện Accordion mở rộng chi tiết ván đấu, 3 nút nhập kết quả nhanh (`1-0`, `0.5-0.5`, `0-1`), nút tích xanh xác nhận chốt điểm.
- Bảng xếp hạng tự động cập nhật theo điểm (Thắng = 1, Hòa = 0.5, Thua = 0) và hệ số phụ.

## 2. Quyết định Kỹ thuật then chốt (Key Decisions)
1. **Tách biệt Match (Trận) và Game (Ván)**:
   - Một cặp đấu (`ChessMatch`) quan hệ 1-N với từng ván (`ChessGame`).
   - Tổng điểm cặp đấu bắt buộc tự động tính từ tổng điểm các ván con (`recalculateScores()`), tuyệt đối không cho nhập tay để tránh sai lệch dữ liệu.
2. **Thuật toán Xoay vòng Berger (Polygon Rotation)**:
   - Hỗ trợ số lượng kỳ thủ bất kỳ. Nếu lẻ $N$, tự động gán suất Bye (`is_bye = true`, 1.0 điểm) để bảo đảm mọi kỳ thủ đều thi đấu đúng $N-1$ trận và nghỉ 1 trận.
3. **Thuật toán Hệ Thụy Sĩ (Swiss System)**:
   - Ghép cặp từng vòng dựa trên nhóm điểm hiện tại.
   - Cơ chế Backtracking kiểm tra đồ thị lịch sử các vòng trước để ngăn chặn tuyệt đối việc tái đấu (Rematch prevention) và cân bằng số ván cầm quân Trắng/Đen.
4. **Reactive UI với Livewire 3 + Alpine.js**:
   - Thao tác mở/đóng ván mượt mà qua Alpine.
   - Nhập điểm và bấm nút tích xanh cập nhật cơ sở dữ liệu và bảng xếp hạng theo thời gian thực mà không tải lại trang.
5. **Tuân thủ quy chuẩn Modularization**:
   - Tất cả 17 file mã nguồn đều được kiểm soát nghiêm ngặt dưới 200 dòng code.

## 3. Kết quả & Kiểm thử
- 11/11 bài test tự động (Feature & Unit) vượt qua 100% với 94 assertions.
- Bộ tài liệu kỹ thuật đầy đủ trong `docs/` (`system-architecture.md`, `project-overview-pdr.md`, `code-standards.md`, `project-roadmap.md`, `deployment-guide.md`).
