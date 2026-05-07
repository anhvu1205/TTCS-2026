<?php
session_start();

session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng xuất...</title>
</head>

<body>
    <script>
        Object.keys(localStorage).forEach(function(key) {
            if (
                key.startsWith('sf_chat_session_id_') ||
                key.startsWith('sf_chat_history_')
            ) {
                localStorage.removeItem(key);
            }
        });

        window.location.href = '../shop.php';
    </script>
</body>

</html>