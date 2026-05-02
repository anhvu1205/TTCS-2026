<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../includes/db.php';

if (!isset($conn) || !$conn) {
    echo json_encode(['reply_html' => 'Không kết nối được database.'], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_set_charset($conn, 'utf8mb4');

$data = json_decode(file_get_contents("php://input"), true);
$message = trim($data['message'] ?? '');
$sessionId = trim($data['session_id'] ?? 'default');
$currentUserId = $_SESSION['user']['id'] ?? null;

if ($message === '') {
    echo json_encode(['reply_html' => 'Bạn hãy nhập nội dung cần hỏi nhé.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['chat_state'][$sessionId])) {
    $_SESSION['chat_state'][$sessionId] = [
        'last_topic' => '',
        'last_keyword' => '',
        'last_style' => ''
    ];
}

function vn_money($n)
{
    return number_format((float)$n, 0, ',', '.') . '₫';
}

function normalize_text($text)
{
    return mb_strtolower(trim($text), 'UTF-8');
}

function has_kw($text, $keywords)
{
    foreach ($keywords as $kw) {
        $kw = mb_strtolower($kw, 'UTF-8');

        if (mb_strlen($kw, 'UTF-8') <= 2) {
            if (preg_match('/(^|[\s,.;!?])' . preg_quote($kw, '/') . '($|[\s,.;!?])/u', $text)) {
                return true;
            }
        } else {
            if (mb_strpos($text, $kw) !== false) {
                return true;
            }
        }
    }
    return false;
}

function parse_price($text)
{
    if (preg_match('/(\d+(?:[.,]\d+)?)\s*(k|nghìn|ngàn)/u', $text, $m)) {
        return (int)((float)str_replace(',', '.', $m[1]) * 1000);
    }

    if (preg_match('/(\d+(?:[.,]\d+)?)\s*(tr|triệu|trieu)/u', $text, $m)) {
        return (int)((float)str_replace(',', '.', $m[1]) * 1000000);
    }

    if (preg_match('/(\d{5,})/u', $text, $m)) {
        return (int)$m[1];
    }

    if (preg_match('/(\d{2,4})/u', $text, $m)) {
        return (int)$m[1] * 1000;
    }

    return null;
}

function search_products($conn, $options = [])
{
    $keyword = trim($options['keyword'] ?? '');
    $min = isset($options['min']) ? (int)$options['min'] : null;
    $max = isset($options['max']) ? (int)$options['max'] : null;
    $limit = isset($options['limit']) ? (int)$options['limit'] : 4;
    $order = $options['order'] ?? 'new';

    $sql = "SELECT maSP AS id, ten AS name, gia AS price, hinhAnh AS image
            FROM SanPham
            WHERE 1=1";

    if ($keyword !== '') {
        $safe = mysqli_real_escape_string($conn, $keyword);
        $sql .= " AND ten LIKE '%$safe%'";
    }

    if ($min !== null) {
        $sql .= " AND gia >= $min";
    }

    if ($max !== null) {
        $sql .= " AND gia <= $max";
    }

    if ($order === 'price_asc') {
        $sql .= " ORDER BY gia ASC";
    } elseif ($order === 'price_desc') {
        $sql .= " ORDER BY gia DESC";
    } else {
        $sql .= " ORDER BY maSP DESC";
    }

    $sql .= " LIMIT $limit";

    $res = mysqli_query($conn, $sql);
    $products = [];

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $products[] = $row;
        }
    }

    return $products;
}

function product_cards($products)
{
    if (!$products || count($products) === 0) {
        return "<div>Hiện mình chưa tìm thấy sản phẩm phù hợp trong shop.</div>";
    }

    $html = '<div class="chat-product-list">';

    foreach ($products as $p) {
        $id = (int)$p['id'];
        $name = htmlspecialchars($p['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $price = vn_money($p['price'] ?? 0);
        $image = htmlspecialchars($p['image'] ?? '', ENT_QUOTES, 'UTF-8');

        $html .= "
            <div class='chat-product-card'>
                <a href='detail.php?id={$id}' class='chat-product-img-wrap'>
                    <img src='{$image}' class='chat-product-img' alt='{$name}'>
                </a>

                <div class='chat-product-info'>
                    <a href='detail.php?id={$id}' class='chat-product-name'>{$name}</a>
                    <div class='chat-product-price'>{$price}</div>

                    <div class='chat-product-actions'>
                        <a href='detail.php?id={$id}' class='chat-product-btn'>Xem</a>
                        <button type='button' class='chat-add-cart' data-id='{$id}'>+ Giỏ</button>
                    </div>
                </div>
            </div>
        ";
    }

    $html .= '</div>';
    return $html;
}

function table_exists($conn, $tableName)
{
    $safeTable = mysqli_real_escape_string($conn, $tableName);
    $res = mysqli_query($conn, "SHOW TABLES LIKE '$safeTable'");
    return $res && mysqli_num_rows($res) > 0;
}

function save_chat_message($conn, $sessionId, $userId, $sender, $message)
{
    if (!table_exists($conn, 'ChatbotMessages')) return;

    $safeSession = mysqli_real_escape_string($conn, $sessionId);
    $safeSender = mysqli_real_escape_string($conn, $sender);
    $safeMessage = mysqli_real_escape_string($conn, strip_tags($message));
    $userValue = $userId ? (int)$userId : "NULL";

    @mysqli_query($conn, "
        INSERT INTO ChatbotMessages (session_id, maND, sender, message)
        VALUES ('$safeSession', $userValue, '$safeSender', '$safeMessage')
    ");
}

function save_chat_request($conn, $sessionId, $userId, $message)
{
    if (!table_exists($conn, 'ChatbotRequests')) return;

    $safeSession = mysqli_real_escape_string($conn, $sessionId);
    $safeMessage = mysqli_real_escape_string($conn, $message);
    $userValue = $userId ? (int)$userId : "NULL";

    @mysqli_query($conn, "
        INSERT INTO ChatbotRequests (session_id, maND, customer_message, status)
        VALUES ('$safeSession', $userValue, '$safeMessage', 'pending')
    ");
}


function get_chat_mode($conn, $sessionId)
{
    if (!table_exists($conn, 'ChatbotSessions')) return 'bot';

    $safeSession = mysqli_real_escape_string($conn, $sessionId);
    $res = mysqli_query($conn, "
        SELECT mode FROM ChatbotSessions
        WHERE session_id = '$safeSession'
        LIMIT 1
    ");

    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        return $row['mode'] ?? 'bot';
    }

    return 'bot';
}

// Nếu admin đã tham gia cuộc trò chuyện thì bot tạm dừng.
// Khách nhắn tiếp chỉ lưu vào DB để admin đọc, không trả lời tự động nữa.
if (get_chat_mode($conn, $sessionId) === 'admin') {
    save_chat_message($conn, $sessionId, $currentUserId, 'user', $message);

    echo json_encode([
        'session_id' => $sessionId,
        'reply_html' => '',
        'admin_mode' => true
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$text = normalize_text($message);
$reply = '';

/* FAQ */
if (has_kw($text, ['ship', 'giao hàng', 'vận chuyển', 'bao lâu', 'mấy ngày', 'phí ship', 'freeship'])) {
    $reply = "
        Shop hỗ trợ giao hàng toàn quốc 🚚<br>
        • Nội thành: khoảng <b>1 - 2 ngày</b><br>
        • Ngoại tỉnh: khoảng <b>2 - 5 ngày</b><br>
        • Đơn từ <b>500.000₫</b> có thể được miễn phí ship<br><br>
        Bạn muốn mình gợi ý vài sản phẩm dễ đủ điều kiện freeship không?<br>
        <button class='chat-choice' data-msg='Sản phẩm mới nhất'>Xem hàng mới</button>
        <button class='chat-choice' data-msg='Sản phẩm dưới 500k'>Dưới 500k</button>
    ";
} elseif (has_kw($text, ['hi', 'hello', 'xin chào', 'chào', 'alo'])) {
    $reply = "Xin chào 👋 Mình có thể tư vấn áo, quần, váy, sản phẩm theo giá hoặc hỗ trợ thông tin ship/đổi trả cho bạn.";
} elseif (has_kw($text, ['mã giảm giá', 'voucher', 'code giảm', 'discount', 'sale code', 'mã sale'])) {
    $reply = "
        Shop đang có mã giảm giá 🎁<br>
        • <b>SALE10</b>: giảm 10% cho đơn từ 300.000₫<br>
        • <b>FREESTYLE20</b>: giảm 20% cho đơn từ 800.000₫<br><br>
        Bạn có thể nhập mã ở trang giỏ hàng khi thanh toán nhé.<br>
        <button class='chat-choice' data-msg='Sản phẩm dưới 500k'>Gợi ý sản phẩm dễ áp mã</button>
    ";
} elseif (has_kw($text, ['đổi trả', 'đổi hàng', 'trả hàng', 'hoàn hàng'])) {
    $reply = "Shop hỗ trợ đổi trả nếu sản phẩm lỗi hoặc chưa phù hợp theo chính sách shop.<br>Bạn nên giữ sản phẩm còn nguyên tình trạng ban đầu và liên hệ shop sớm để được hỗ trợ nhé.";
} elseif (has_kw($text, ['thanh toán', 'cod', 'chuyển khoản', 'trả tiền'])) {
    $reply = "Shop hỗ trợ thanh toán khi nhận hàng hoặc các hình thức thanh toán có sẵn trên website.<br>Bạn muốn mình tư vấn sản phẩm trước khi đặt hàng không?";
}

/* FLOW TƯ VẤN RIÊNG */ elseif (has_kw($text, ['tư vấn áo', 'chọn áo', 'gợi ý áo'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'ao';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'áo';

    $reply = "
        Bạn muốn chọn áo theo phong cách nào?<br>
        <button class='chat-choice' data-msg='Áo đi làm'>Đi làm</button>
        <button class='chat-choice' data-msg='Áo đi chơi'>Đi chơi</button>
        <button class='chat-choice' data-msg='Áo basic'>Basic</button>
        <button class='chat-choice' data-msg='Áo giá rẻ'>Giá rẻ</button>
        <button class='chat-choice' data-msg='Áo sơ mi'>Áo sơ mi</button>
        <button class='chat-choice' data-msg='Áo thun'>Áo thun</button>
    ";
} elseif (has_kw($text, ['tư vấn quần', 'chọn quần', 'gợi ý quần'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'quan';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'quần';

    $reply = "
        Bạn muốn chọn quần theo kiểu nào?<br>
        <button class='chat-choice' data-msg='Quần đi làm'>Đi làm</button>
        <button class='chat-choice' data-msg='Quần đi chơi'>Đi chơi</button>
        <button class='chat-choice' data-msg='Quần basic'>Basic</button>
        <button class='chat-choice' data-msg='Quần giá rẻ'>Giá rẻ</button>
        <button class='chat-choice' data-msg='Quần jeans'>Quần jeans</button>
        <button class='chat-choice' data-msg='Quần short'>Quần short</button>
    ";
} elseif (has_kw($text, ['tư vấn váy', 'tư vấn đầm', 'chọn váy', 'chọn đầm', 'gợi ý váy', 'gợi ý đầm'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'vay';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'váy';

    $reply = "
        Bạn muốn chọn váy/đầm theo phong cách nào?<br>
        <button class='chat-choice' data-msg='Váy đi làm'>Đi làm</button>
        <button class='chat-choice' data-msg='Váy đi chơi'>Đi chơi</button>
        <button class='chat-choice' data-msg='Váy basic'>Basic</button>
        <button class='chat-choice' data-msg='Váy giá rẻ'>Giá rẻ</button>
        <button class='chat-choice' data-msg='Đầm'>Đầm</button>
        <button class='chat-choice' data-msg='Váy'>Váy</button>
    ";
} elseif (has_kw($text, ['tư vấn', 'gợi ý', 'không biết chọn', 'chọn giúp'])) {
    $reply = "
        Bạn muốn mình tư vấn theo hướng nào?<br>
        <button class='chat-choice' data-msg='Tư vấn áo'>Áo</button>
        <button class='chat-choice' data-msg='Tư vấn quần'>Quần</button>
        <button class='chat-choice' data-msg='Tư vấn váy'>Váy</button>
        <button class='chat-choice' data-msg='Sản phẩm dưới 500k'>Dưới 500k</button>
        <button class='chat-choice' data-msg='Có mã giảm giá không'>Mã giảm giá</button>
    ";
}

/* STYLE THEO ÁO */ elseif (has_kw($text, ['áo đi làm'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'ao';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'áo sơ mi';
    $_SESSION['chat_state'][$sessionId]['last_style'] = 'di_lam';

    $products = search_products($conn, ['keyword' => 'áo sơ mi', 'limit' => 4, 'order' => 'new']);
    $reply = "Nếu chọn áo đi làm, mình gợi ý các mẫu lịch sự, dễ phối này:" . product_cards($products);
} elseif (has_kw($text, ['áo đi chơi'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'ao';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'áo';
    $_SESSION['chat_state'][$sessionId]['last_style'] = 'di_choi';

    $products = search_products($conn, ['keyword' => 'áo', 'limit' => 4, 'order' => 'new']);
    $reply = "Nếu đi chơi, bạn có thể chọn vài mẫu áo thoải mái, dễ phối này:" . product_cards($products);
} elseif (has_kw($text, ['áo basic'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'ao';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'áo';
    $_SESSION['chat_state'][$sessionId]['last_style'] = 'basic';

    $products = search_products($conn, ['keyword' => 'áo', 'limit' => 4, 'order' => 'price_asc']);
    $reply = "Phong cách áo basic/tối giản thì mình gợi ý vài mẫu dễ mặc này:" . product_cards($products);
} elseif (has_kw($text, ['áo giá rẻ'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'ao';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'áo';

    $products = search_products($conn, ['keyword' => 'áo', 'limit' => 4, 'order' => 'price_asc']);
    $reply = "Mình lọc vài mẫu áo giá mềm cho bạn:" . product_cards($products);
}

/* STYLE THEO QUẦN */ elseif (has_kw($text, ['quần đi làm'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'quan';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'quần';
    $_SESSION['chat_state'][$sessionId]['last_style'] = 'di_lam';

    $products = search_products($conn, ['keyword' => 'quần', 'limit' => 4, 'order' => 'new']);
    $reply = "Nếu chọn quần đi làm, mình gợi ý vài mẫu lịch sự, dễ phối này:" . product_cards($products);
} elseif (has_kw($text, ['quần đi chơi'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'quan';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'quần';
    $_SESSION['chat_state'][$sessionId]['last_style'] = 'di_choi';

    $products = search_products($conn, ['keyword' => 'quần', 'limit' => 4, 'order' => 'new']);
    $reply = "Đi chơi thì bạn có thể chọn vài mẫu quần thoải mái, dễ phối này:" . product_cards($products);
} elseif (has_kw($text, ['quần basic'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'quan';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'quần';
    $_SESSION['chat_state'][$sessionId]['last_style'] = 'basic';

    $products = search_products($conn, ['keyword' => 'quần', 'limit' => 4, 'order' => 'price_asc']);
    $reply = "Phong cách quần basic/tối giản thì mình gợi ý vài mẫu dễ mặc này:" . product_cards($products);
} elseif (has_kw($text, ['quần giá rẻ'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'quan';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'quần';

    $products = search_products($conn, ['keyword' => 'quần', 'limit' => 4, 'order' => 'price_asc']);
    $reply = "Mình lọc vài mẫu quần giá mềm cho bạn:" . product_cards($products);
}

/* STYLE THEO VÁY */ elseif (has_kw($text, ['váy đi làm', 'đầm đi làm'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'vay';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'váy';
    $_SESSION['chat_state'][$sessionId]['last_style'] = 'di_lam';

    $products = search_products($conn, ['keyword' => 'váy', 'limit' => 4, 'order' => 'new']);
    if (count($products) === 0) {
        $products = search_products($conn, ['keyword' => 'đầm', 'limit' => 4, 'order' => 'new']);
    }

    $reply = "Nếu mặc đi làm, mình gợi ý vài mẫu váy/đầm lịch sự, dễ phối này:" . product_cards($products);
} elseif (has_kw($text, ['váy đi chơi', 'đầm đi chơi'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'vay';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'váy';
    $_SESSION['chat_state'][$sessionId]['last_style'] = 'di_choi';

    $products = search_products($conn, ['keyword' => 'váy', 'limit' => 4, 'order' => 'new']);
    if (count($products) === 0) {
        $products = search_products($conn, ['keyword' => 'đầm', 'limit' => 4, 'order' => 'new']);
    }

    $reply = "Đi chơi thì bạn có thể chọn vài mẫu váy/đầm nữ tính, dễ mặc này:" . product_cards($products);
} elseif (has_kw($text, ['váy basic', 'đầm basic'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'vay';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'váy';
    $_SESSION['chat_state'][$sessionId]['last_style'] = 'basic';

    $products = search_products($conn, ['keyword' => 'váy', 'limit' => 4, 'order' => 'price_asc']);
    if (count($products) === 0) {
        $products = search_products($conn, ['keyword' => 'đầm', 'limit' => 4, 'order' => 'price_asc']);
    }

    $reply = "Phong cách váy/đầm basic thì mình gợi ý vài mẫu dễ mặc này:" . product_cards($products);
} elseif (has_kw($text, ['váy giá rẻ', 'đầm giá rẻ'])) {
    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'vay';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = 'váy';

    $products = search_products($conn, ['keyword' => 'váy', 'limit' => 4, 'order' => 'price_asc']);
    if (count($products) === 0) {
        $products = search_products($conn, ['keyword' => 'đầm', 'limit' => 4, 'order' => 'price_asc']);
    }

    $reply = "Mình lọc vài mẫu váy/đầm giá mềm cho bạn:" . product_cards($products);
}

/* LỌC GIÁ */ elseif ((has_kw($text, ['dưới', 'nhỏ hơn', 'không quá']) || preg_match('/dưới\s*\d+/u', $text)) && parse_price($text)) {
    $max = parse_price($text);
    $topic = $_SESSION['chat_state'][$sessionId]['last_keyword'];

    $products = search_products($conn, [
        'keyword' => $topic,
        'max' => $max,
        'limit' => 4,
        'order' => 'price_asc'
    ]);

    $reply = "Mình lọc vài sản phẩm dưới <b>" . vn_money($max) . "</b> cho bạn:" . product_cards($products);
} elseif (preg_match('/từ.+đến/u', $text)) {
    preg_match_all('/\d+(?:[.,]\d+)?\s*(?:k|nghìn|ngàn|tr|triệu|trieu)?/u', $text, $matches);

    if (count($matches[0]) >= 2) {
        $min = parse_price($matches[0][0]);
        $max = parse_price($matches[0][1]);

        $products = search_products($conn, [
            'min' => $min,
            'max' => $max,
            'limit' => 4,
            'order' => 'price_asc'
        ]);

        $reply = "Mình tìm sản phẩm từ <b>" . vn_money($min) . "</b> đến <b>" . vn_money($max) . "</b> cho bạn:" . product_cards($products);
    }
}

/* SẢN PHẨM TRỰC TIẾP */ elseif (has_kw($text, ['áo thun', 'áo sơ mi', 'áo khoác', 'áo len', 'áo'])) {
    $kw = 'áo';

    if (mb_strpos($text, 'áo thun') !== false) $kw = 'áo thun';
    elseif (mb_strpos($text, 'sơ mi') !== false) $kw = 'áo sơ mi';
    elseif (mb_strpos($text, 'khoác') !== false) $kw = 'áo khoác';
    elseif (mb_strpos($text, 'len') !== false) $kw = 'áo len';

    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'ao';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = $kw;

    $products = search_products($conn, ['keyword' => $kw, 'limit' => 4, 'order' => 'new']);

    $reply = "Mình chọn vài mẫu <b>{$kw}</b> hợp với phong cách SimpleFit cho bạn:" . product_cards($products) . "
    <div class='chat-suggest'>Bạn muốn mình lọc tiếp theo <b>giá rẻ hơn</b>, <b>đi chơi</b> hay <b>đi làm</b>?</div>";
} elseif (has_kw($text, ['quần jeans', 'quần jean', 'quần short', 'quần', 'jeans', 'short'])) {
    $kw = 'quần';

    if (mb_strpos($text, 'jeans') !== false || mb_strpos($text, 'jean') !== false) $kw = 'quần jeans';
    elseif (mb_strpos($text, 'short') !== false) $kw = 'quần short';

    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'quan';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = $kw;

    $products = search_products($conn, ['keyword' => $kw, 'limit' => 4, 'order' => 'new']);

    $reply = "Đây là vài mẫu <b>{$kw}</b> mình gợi ý cho bạn:" . product_cards($products) . "
    <div class='chat-suggest'>Bạn thích form rộng, ôm hay mặc thường ngày?</div>";
} elseif (has_kw($text, ['váy', 'đầm'])) {
    $kw = mb_strpos($text, 'đầm') !== false ? 'đầm' : 'váy';

    $_SESSION['chat_state'][$sessionId]['last_topic'] = 'vay';
    $_SESSION['chat_state'][$sessionId]['last_keyword'] = $kw;

    $products = search_products($conn, ['keyword' => $kw, 'limit' => 4, 'order' => 'new']);

    $reply = "Mình gợi ý vài mẫu <b>{$kw}</b> cho bạn:" . product_cards($products) . "
    <div class='chat-suggest'>Bạn thích phong cách nữ tính, tối giản hay dễ mặc hằng ngày?</div>";
}

/* STYLE CHUNG */ elseif (has_kw($text, ['đi làm', 'công sở', 'văn phòng'])) {
    $topic = $_SESSION['chat_state'][$sessionId]['last_topic'];

    if ($topic === 'quan') {
        $kw = 'quần';
        $replyText = "Nếu mặc đi làm, mình gợi ý vài mẫu quần lịch sự, dễ phối này:";
    } elseif ($topic === 'vay') {
        $kw = 'váy';
        $replyText = "Nếu mặc đi làm, mình gợi ý vài mẫu váy/đầm lịch sự, dễ phối này:";
    } else {
        $kw = 'áo sơ mi';
        $replyText = "Nếu mặc đi làm, mình gợi ý các mẫu lịch sự, dễ phối này:";
    }

    $products = search_products($conn, ['keyword' => $kw, 'limit' => 4, 'order' => 'new']);
    $reply = $replyText . product_cards($products);
} elseif (has_kw($text, ['đi chơi', 'dạo phố', 'casual'])) {
    $keyword = $_SESSION['chat_state'][$sessionId]['last_keyword'] ?: 'áo';

    $products = search_products($conn, ['keyword' => $keyword, 'limit' => 4, 'order' => 'new']);
    $reply = "Đi chơi thì bạn có thể chọn vài mẫu thoải mái, dễ phối này:" . product_cards($products);
} elseif (has_kw($text, ['basic', 'đơn giản', 'tối giản'])) {
    $keyword = $_SESSION['chat_state'][$sessionId]['last_keyword'] ?: 'áo';

    $products = search_products($conn, ['keyword' => $keyword, 'limit' => 4, 'order' => 'price_asc']);
    $reply = "Phong cách basic/tối giản thì mình gợi ý vài mẫu dễ mặc này:" . product_cards($products);
} elseif (has_kw($text, ['mới nhất', 'hàng mới', 'new', 'bán chạy', 'hot'])) {
    $products = search_products($conn, ['keyword' => '', 'limit' => 4, 'order' => 'new']);
    $reply = "Đây là một vài sản phẩm mới/nổi bật bên shop:" . product_cards($products);
} elseif (has_kw($text, ['rẻ hơn', 'giá rẻ', 'mẫu rẻ', 'loại rẻ'])) {
    $kw = $_SESSION['chat_state'][$sessionId]['last_keyword'] ?? '';

    $products = search_products($conn, ['keyword' => $kw, 'limit' => 4, 'order' => 'price_asc']);
    $reply = "Mình lọc vài mẫu giá mềm hơn cho bạn:" . product_cards($products);
} elseif (has_kw($text, ['khác', 'mẫu khác', 'xem thêm'])) {
    $kw = $_SESSION['chat_state'][$sessionId]['last_keyword'] ?? '';

    $products = search_products($conn, ['keyword' => $kw, 'limit' => 4, 'order' => 'new']);
    $reply = "Bạn có thể xem thêm vài mẫu này:" . product_cards($products);
}

/* FALLBACK */ else {
    save_chat_request($conn, $sessionId, $currentUserId, $message);

    $reply = "
        Shop đã ghi nhận nội dung này, nhân viên shop sẽ kiểm tra và phản hồi bạn sớm nhất.<br><br>
        Trong lúc chờ, bạn có thể chọn nhanh một nhu cầu bên dưới:<br>
        <button class='chat-choice' data-msg='Tư vấn áo'>Áo</button>
        <button class='chat-choice' data-msg='Tư vấn quần'>Quần</button>
        <button class='chat-choice' data-msg='Tư vấn váy'>Váy</button>
        <button class='chat-choice' data-msg='Sản phẩm dưới 500k'>Dưới 500k</button>
        <button class='chat-choice' data-msg='Có mã giảm giá không'>Mã giảm giá</button>
    ";
}

$_SESSION['chat_history'][$sessionId][] = [
    'user' => $message,
    'bot' => $reply
];

save_chat_message($conn, $sessionId, $currentUserId, 'user', $message);
save_chat_message($conn, $sessionId, $currentUserId, 'bot', $reply);

echo json_encode([
    'session_id' => $sessionId,
    'reply_html' => $reply
], JSON_UNESCAPED_UNICODE);
exit;
