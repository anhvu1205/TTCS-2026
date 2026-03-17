use clothing_shop;
-- tôi thử thêm dữ liệu bào bảng có sẵn trong database nhưng ko được nên tạo thêm bảng này á 
-- bảng này chỉ khác có mỗi order_id và maDH thoi về cách tạo mấy cái khác thì hơi khác tên 1 xíu nhé
-- đổi tên Orders->DonHang
CREATE TABLE Orders (
    order_id VARCHAR(255) PRIMARY KEY,  -- order_id là khóa chính, có thể là mã đơn hàng (ví dụ: MAU123456)
    email VARCHAR(100),
   	maKH BIGINT,					-- Email người dùng (liên kết với tài khoản)
    maNV BIGINT,
    subtotal BIGINT,                    -- Tổng tiền hàng (chưa tính phí vận chuyển)
    shipping BIGINT,                    -- Phí vận chuyển
    tongTien BIGINT,                       -- Tổng tiền (bao gồm phí vận chuyển)
    ngayTao TIMESTAMP,             -- Ngày tạo đơn hàng
    trangThai VARCHAR(50),                 -- Trạng thái đơn hàng (ví dụ: "Chờ thanh toán", "Đã thanh toán")
    diaChi VARCHAR(255),      -- Địa chỉ giao hàng
    soDienThoai VARCHAR(15),                  -- Số điện thoại người nhận
    phuongThucThanhToan VARCHAR(50),          -- Phương thức thanh toán (VNPay, COD, ...)
    FOREIGN KEY (maKH) REFERENCES KhachHang(maKH) ON DELETE CASCADE,
    FOREIGN KEY (maNV) REFERENCES NhanVien(maNV) ON DELETE SET NULL
);

CREATE TABLE OrderItems (
    order_id VARCHAR(255), --
    maSP BIGINT,--
    soLuong INT,--
    donGia BIGINT,
    thanhTien BIGINT,
    kichCo VARCHAR(50),--
    mauSac VARCHAR(50),--
     FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (maSP) REFERENCES SanPham(maSP) ON DELETE CASCADE
);