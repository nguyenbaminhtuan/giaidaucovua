# Code Standards & Guidelines

## 1. Core Principles
- **KISS (Keep It Simple, Stupid)**: Ưu tiên giải pháp đơn giản, dễ đọc, dễ kiểm thử. Tránh trừu tượng hóa quá mức.
- **YAGNI (You Aren't Gonna Need It)**: Chỉ viết những tính năng đã được yêu cầu. Không thêm tính năng phỏng đoán cho tương lai.
- **DRY (Don't Repeat Yourself)**: Gom các logic tính toán điểm cờ vua, xoay vòng cặp đấu vào các Service chuyên biệt.
- **Modularization**: 
  - File code không vượt quá 200 dòng.
  - Khi file lớn hơn 200 dòng, phân rã thành các Service, Trait, hoặc Sub-component nhỏ hơn.
  - Sử dụng kebab-case cho tên file JS/Blade/CSS và PascalCase cho PHP classes.

## 2. Laravel & PHP Standards
- Tuân thủ chuẩn PSR-12 và PER Coding Style.
- Kiểu dữ liệu chặt chẽ (Strict typing): Khai báo kiểu dữ liệu cho method arguments và return types.
- Controller mỏng (Skinny Controller / Thin Livewire): Logic nghiệp vụ chuyển vào Services trong `app/Services/`.
- Điểm số cờ vua: Luôn dùng kiểu `float` hoặc `decimal(4,1)` vì cờ vua có điểm lẻ `0.5`.

## 3. Livewire & Frontend Standards
- Sử dụng **Alpine.js** cho các tương tác DOM cục bộ (dropdown, accordion, toggle tabs, animation mở ván đấu).
- Sử dụng **Livewire 3** cho đồng bộ dữ liệu với máy chủ (lưu kết quả ván, bấm nút tích xanh xác nhận, cập nhật BXH).
- Sử dụng Tailwind CSS cho giao diện, tuân thủ bảng màu cờ vua trang nhã (Slate / Neutral / Amber accents).
