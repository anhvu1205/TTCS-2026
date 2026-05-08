SIMPLE FIT - Website Bán  Thời Trang (TTCS-2026)

Dự án cho học phần hực tập cơ sở tại **Học viện Công nghệ Bưu chính Viễn thông (PTIT)**.

**Hướng dẫn cài đặt**
1. Clone repository về thư mục `htdocs` của XAMPP.
- Dự án cần tải XAMPP để chạy.
- Khi chạy XAMPP cần bật 2 Module là "Apache" và "Mysql" lên.
2. Tải database cho dự án
- Truy cập http://localhost/phpmyadmin/
- Thêm mới 1 database và đặt tên giống tên database trong dự án.
- Import file sql `database/shop_ptit.sql` vào database vừa tạo.
3. Cấu hình thông tin kết nối trong `includes/db.php`.
4. Truy cập `localhost/TTCS-2026/shop.php` để bắt đầu.
