<?php
// =====================================================
// API لحذف عنصر من المخزون
// =====================================================

require_once '../config.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin', 'pharmacist', 'purchasing'])) {
    header('Location: ../index.php?page=login');
    exit();
}

$item_id = $_GET['id'] ?? 0;

if ($item_id > 0) {
    try {
        // تحديث العنصر ليكون غير نشط (حذف منطقي)
        $stmt = $pdo->prepare("UPDATE items SET is_active = 0 WHERE item_id = ?");
        $stmt->execute([$item_id]);
        
        // تسجيل العملية
        logAction($_SESSION['user_id'], 'حذف عنصر', "رقم العنصر: $item_id");
    } catch (PDOException $e) {
        // معالجة الخطأ
    }
}

// إعادة التوجيه إلى صفحة المخزون
header('Location: ../index.php?page=inventory');
exit();
?>
