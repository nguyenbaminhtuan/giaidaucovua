# Project Roadmap

## Phase 1: Setup & Database Schema
- [x] Thiết kế kiến trúc và định nghĩa cấu trúc dữ liệu (`docs/system-architecture.md`).
- [x] Khởi tạo dự án Laravel 11, cài đặt Livewire 3 và Tailwind CSS.
- [x] Viết Migrations và Eloquent Models cho toàn bộ 8 thực thể.
- [x] Chạy kiểm thử Schema và quan hệ dữ liệu.

## Phase 2: Scheduling Engines
- [x] Xây dựng `RoundRobinSchedulerService` (Xoay vòng Berger + xử lý Bye).
- [x] Xây dựng `MatchGameFactory` (Tạo ván đấu, gán kiểm soát thời gian Fischer, đảo màu quân Trắng/Đen).
- [x] Xây dựng `SwissSchedulerService` (Ghép nhóm điểm, chống gặp lại, cân bằng màu).
- [x] Kiểm thử tự động thuật toán bốc thăm.

## Phase 3: Match UI & Score Management
- [x] Xây dựng form cấu hình giải (`SetupWizard` Livewire component).
- [x] Xây dựng bảng hiển thị cặp đấu theo Vòng và Bảng (`TournamentDashboard`).
- [x] Xây dựng Accordion chi tiết từng ván (`MatchRow` + Alpine.js).
- [x] Tích hợp 3 nút chọn kết quả `[ 1 - 0 ]`, `[ ½ - ½ ]`, `[ 0 - 1 ]`.
- [x] Tích hợp nút tích xanh xác nhận và cơ chế khóa kết quả.

## Phase 4: Standings & Rankings
- [x] Xây dựng `StandingsCalculatorService` tính điểm thắng 1, hòa 0.5 và hệ số phụ (Sonneborn-Berger / Buchholz).
- [x] Xây dựng giao diện bảng xếp hạng theo từng bảng (`StandingsBoard`).
- [x] Kiểm thử toàn diện luồng ghi điểm $\rightarrow$ cập nhật bảng xếp hạng tức thời.

## Future Enhancements
- [ ] Vòng đấu loại trực tiếp Knockout (Playoff bán kết / chung kết sau vòng bảng).
- [ ] Đồng hồ đếm ngược trực tiếp cho từng ván (Live Chess Clock).
- [ ] Xuất file kết quả PDF / Excel cho ban tổ chức.
