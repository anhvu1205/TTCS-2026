// 1. THÔNG BÁO

const messages = [
    "🌿 Miễn phí vận chuyển cho đơn từ 500.000₫",
    "✦ Nhập MAU10 để được giảm 10% đơn đầu tiên",
    "🍂 Bộ sưu tập hè 2026 đã ra mắt – Khám phá ngay!"
    ];

    let currentIdx = 0;
    const textElem = document.getElementById('announcement-text');

// Hàm đổi chữ mượt mà
setInterval(() => {
    if (textElem) {
        textElem.style.opacity = 0; // Hiệu ứng mờ dần
        setTimeout(() => {
        currentIdx = (currentIdx + 1) % messages.length;
    textElem.innerText = messages[currentIdx];
    textElem.style.opacity = 1; // Hiện lên lại
        }, 500);
    }
}, 3500);

    // Hàm đóng thanh thông báo
    function closeAnnouncement() {
    const bar = document.getElementById('announcement-bar');
    if (bar) bar.style.display = 'none';
}


// 2. THÊM VÀO YÊU THÍCH
$(document).ready(function () {
    $(document).off('click', '.add-to-wishlist').on('click', '.add-to-wishlist', function (e) {
        e.preventDefault();
        let btn = $(this);
        let p_id = btn.data('id');

        $.post('controll/add_like.php', { product_id: p_id }, function (res) {
            let status = res.trim();

            // TÌM TẤT CẢ CÁC NÚT TRÊN TRANG CÓ CÙNG ID SẢN PHẨM NÀY
            let allBtnsWithSameId = $(`.add-to-wishlist[data-id="${p_id}"]`);

            if (status === 'added') {
                // Cập nhật trái tim đỏ cho tất cả chỗ xuất hiện sản phẩm này
                allBtnsWithSameId.find('i').removeClass('fa-regular').addClass('fa-solid text-danger');
            } else if (status === 'removed') {
                // Trả về trái tim rỗng cho tất cả
                allBtnsWithSameId.find('i').removeClass('fa-solid text-danger').addClass('fa-regular');
            } else if (status === 'not_logged_in') {
                alert("Vui lòng đăng nhập để thực hiện chức năng này!");
            }
        });
    });
});

// 3. TĂNG GIẢM SỐ LƯỢNG TRANG CARD

function updateQty(id, delta) {
    // Lấy thẻ span hiển thị số lượng
    const qtyElement = document.getElementById('qty-' + id);
    if (!qtyElement) return; // Phòng hờ không tìm thấy thẻ

    let currentVal = parseInt(qtyElement.innerText);
    let newVal = currentVal + delta;

    if (newVal < 1) {
        if (confirm("Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?")) {
            newVal = 0; // Gửi 0 để server thực hiện unset
        } else {
            return; // Hủy thao tác nếu người dùng không muốn xóa
        }
    }

    $.ajax({
        url: 'controll/update_cart.php',
        method: 'POST',
        data: {
            cart_key: id,
            new_qty: newVal
        },
        success: function (response) {
            try {
                let data = JSON.parse(response);
                if (data.status === 'success') {
                    location.reload(); // Load lại để cập nhật tiền ship và tổng tiền
                }
            } catch (e) {
                console.error("Lỗi phản hồi từ server:", response);
            }
        }
    });
}

//4. TĂNG GIẢM SỐ LƯỢNG TRANG CHI TIẾT SẢN PHẨM
document.addEventListener('DOMContentLoaded', function () {
    const qtyInput = document.getElementById('productQty');
    const plusBtn = document.getElementById('qtyPlus');
    const minusBtn = document.getElementById('qtyMinus');

    if (qtyInput && plusBtn && minusBtn) {
        // Lấy stock từ data-attribute thay vì PHP trực tiếp
        let stock = parseInt(qtyInput.getAttribute('data-stock')) || 0;

        plusBtn.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val < stock) val++;
            qtyInput.value = val;
        });

        minusBtn.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) val--;
            qtyInput.value = val;
        });
    }
});

// Hàm đổi ảnh (để ngoài vì gọi trực tiếp từ thuộc tính onclick của HTML)
function changeMainImage(src, thumb) {
    const mainImg = document.getElementById('mainImage');
    if (mainImg) {
        mainImg.src = src;
        document.querySelectorAll('.thumb-item').forEach(i => i.classList.remove('active'));
        if (thumb) thumb.classList.add('active');
    }
}

// 5. XÓA YÊU THÍCH KHỎI TRANG CÁ NHÂN
document.addEventListener('DOMContentLoaded', function () {
    $(document).on('click', '.remove-from-wishlist', function (e) {
        e.preventDefault();
        let btn = $(this);
        let p_id = btn.data('id');
        let itemWrapper = $('#wishlist-item-' + p_id);

        $.post('controll/add_like.php', { product_id: p_id }, function (res) {
            // Chỉ thực hiện xóa giao diện nếu server báo thành công/đã xóa
            if (res.trim() === 'removed' || res.trim() === 'success') {
                itemWrapper.fadeOut(400, function () {
                    $(this).remove();
                    // Nếu xóa hết, load lại trang để hiện thông báo danh sách trống
                    if ($('.wishlist-item-wrapper').length === 0) {
                        location.reload();
                    }
                });
            }
        });
    });
});

// 6. MŨI TÊN LÊN
const backToTopBtn = document.getElementById("scrollBackToTop");

// Lắng nghe sự kiện cuộn chuột
window.onscroll = function () {
    // Nếu cuộn xuống hơn 300px thì hiện nút, ngược lại thì ẩn
    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
        backToTopBtn.style.display = "flex";
        // Thêm một chút delay để hiệu ứng fade-in mượt hơn (nếu dùng CSS transition)
        setTimeout(() => backToTopBtn.classList.add("visible"), 10);
    } else {
        backToTopBtn.classList.remove("visible");
        // Ẩn hoàn toàn sau khi hiệu ứng mờ dần kết thúc
        setTimeout(() => {
            if (!backToTopBtn.classList.contains("visible")) backToTopBtn.style.display = "none";
        }, 300);
    }
};

// Xử lý khi click vào nút
backToTopBtn.onclick = function () {
    window.scrollTo({
        top: 0,
        behavior: "smooth" // Cuộn mượt mà lên đầu trang
    });
};

// 7. Thanh tìm kiếm
$(document).ready(function () {
    // 1. Mở Search Overlay
    $('#open-search-trigger').on('click', function (e) {
        e.preventDefault();
        $('#search-overlay-fullscreen').fadeIn(300).css('display', 'flex');
        $('#full-search-input').focus();
    });

    // 2. Đóng bằng nút X
    $(document).on('click', '.close-search-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#search-overlay-fullscreen').fadeOut(300);
    });

    // 3. Đóng khi click ra vùng nền đen
    $('#search-overlay-fullscreen').on('click', function (e) {
        if (e.target.id === 'search-overlay-fullscreen') {
            $(this).fadeOut(300);
        }
    });

    // 4. Live Search: Gõ đến đâu tìm đến đó
    $('#full-search-input').on('keyup', function () {
        let query = $(this).val().trim();
        if (query.length >= 2) {
            $.post('controll/live_search.php', { query: query }, function (data) {
                if (data.trim() === "") {
                    $('#live-search-results').html('<p class="text-center text-muted py-4 small">Không tìm thấy sản phẩm...</p>').show();
                } else {
                    $('#live-search-results').html(data).show();
                }
            });
        } else {
            $('#live-search-results').hide();
        }
    });

    // 5. CHỮA LỖI ENTER: Tách riêng sự kiện keydown để bắt phím Enter
    $('#full-search-input').on('keydown', function (e) {
        if (e.which === 13) { // Phím Enter
            e.preventDefault();
            let query = $(this).val().trim();
            if (query.length > 0) {
                // Điều hướng sang trang products.php kèm từ khóa
                window.location.href = 'products.php?keyword=' + encodeURIComponent(query);
            }
        }
    });
});

// 8. KHỞI TẠO BIỂU ĐỒ DOANH THU TRÊN ADMIN
$(document).ready(function () {
    const chartElem = document.getElementById('revenueChart');

    if (chartElem) {
        // Lấy dữ liệu từ thuộc tính data- attributes đã in ở admin.php
        const labels = JSON.parse(chartElem.getAttribute('data-labels'));
        const revenueData = JSON.parse(chartElem.getAttribute('data-revenue'));

        new Chart(chartElem, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu',
                    data: revenueData,
                    backgroundColor: 'rgba(196, 98, 45, 0.4)',
                    hoverBackgroundColor: '#C4622D',
                    borderRadius: 4,
                    barThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#FAF7F2',
                        titleColor: '#8C8279',
                        bodyColor: '#C4622D',
                        bodyFont: { size: 14, weight: 'bold' },
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false,
                        borderColor: '#EDE8DF',
                        borderWidth: 1,
                        callbacks: {
                            label: function (context) {
                                return 'Doanh thu : ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + '₫';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#EDE8DF', drawBorder: false },
                        ticks: { color: '#8C8279' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#8C8279' }
                    }
                }
            }
        });
    }
});
