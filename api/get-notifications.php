<?php
// =====================================================
// API لجلب جميع الإشعارات - مع دعم تعدد اللغات
// =====================================================

require_once '../config.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => $lang['error_message']], JSON_UNESCAPED_UNICODE);
    exit();
}

// جلب جميع الإشعارات غير المقروءة والمقروءة
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE (user_id = ? OR user_id IS NULL)
    ORDER BY created_at DESC
    LIMIT 100
");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// معالجة الإشعارات لترجمة رسائلها
$translatedNotifications = [];
foreach ($notifications as $notif) {
    $translatedNotif = $notif;
    
    // معالجة أنواع الإشعارات المختلفة
    if ($notif['notification_type'] === 'low_stock') {
        $translatedNotif['type_label'] = $lang['low_stock_alert'];
        // الرسالة تحتوي على اسم العنصر (نص ديناميكي)
        // مثال: "المخزون منخفض للعنصر: أموكسيسيلين"
    } elseif ($notif['notification_type'] === 'expiry_warning') {
        $translatedNotif['type_label'] = $lang['expiry_warning_alert'];
        // الرسالة تحتوي على اسم العنصر (نص ديناميكي)
        // مثال: "الصلاحية قريبة للعنصر: أسبيرين"
    } elseif ($notif['notification_type'] === 'request_update') {
        $translatedNotif['type_label'] = $lang['request_update_alert'];
    } else {
        $translatedNotif['type_label'] = $lang['system_alert'];
    }
    
    $translatedNotifications[] = $translatedNotif;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['notifications' => $translatedNotifications], JSON_UNESCAPED_UNICODE);
?>
