<?php
// เริ่มต้นเซสชัน
session_start();

// ล้างข้อมูลเซสชัน
session_unset();
session_destroy();

// ลบคุกกี้ PHPSESSID ฝั่งคลไคลเอนต์
if (ini_get("session.use_cookies")) {
    setcookie(
        session_name(),
        '',
        time() - 3600,
        '/'
    );
}

// ส่งสถานะ 204 No Content (ไม่มีหน้า HTML กลับ)
http_response_code(204);

// เปลี่ยนเส้นทางกลับไปยังหน้า Login
header('Location: login.php');
exit();
?>