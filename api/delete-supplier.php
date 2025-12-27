<?php
// =====================================================
// API لحذف موردين
// =====================================================

require_once __DIR__ . '/../config.php';

// التحقق من الصلاحيات
if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin', 'purchasing'])) {
    http_response_code(403);
    exit('غير مصرح');
}

$supplier_id = $_GET['id'] ?? 0;

if ($supplier_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        $stmt->execute([$supplier_id]);
        
        logAction($_SESSION['user_id'], 'حذف موردين', "معرّف الموردين: $supplier_id");
        
        // إعادة التوجيه مع رسالة نجاح
        header('Location: index.php?page=suppliers&success=1');
        exit();
    } catch (PDOException $e) {
        http_response_code(500);
        exit('خطأ في الحذف');
    }
} else {
    http_response_code(400);
    exit('معرّف موردين غير صحيح');
}
?>
