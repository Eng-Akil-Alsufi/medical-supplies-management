<?php
// =====================================================
// API لتحديث بيانات المستخدم
// =====================================================

require_once '../config.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'غير مصرح']);
    exit();
}

// التحقق من أسلوب الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'طريقة الطلب غير صحيحة']);
    exit();
}

// جلب البيانات
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? null;
$full_name = $data['full_name'] ?? '';
$email = $data['email'] ?? '';
$role = $data['role'] ?? '';
$department = $data['department'] ?? '';
$phone = $data['phone'] ?? '';
$is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
$password = $data['password'] ?? '';

// التحقق من المدخلات
if (!$user_id || empty($full_name) || empty($email) || empty($role)) {
    http_response_code(400);
    echo json_encode(['error' => 'حقول مطلوبة مفقودة']);
    exit();
}

try {
    // إذا تم إدخال كلمة مرور جديدة
    if (!empty($password)) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, email = ?, role = ?, department = ?, 
                phone = ?, is_active = ?, password = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$full_name, $email, $role, $department, $phone, $is_active, $password, $user_id]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, email = ?, role = ?, department = ?, 
                phone = ?, is_active = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$full_name, $email, $role, $department, $phone, $is_active, $user_id]);
    }
    
    // تسجيل العملية
    logAction($_SESSION['user_id'], 'تعديل بيانات مستخدم', "معرف المستخدم: $user_id، الاسم: $full_name");
    
    http_response_code(200);
    echo json_encode(['success' => 'تم تحديث البيانات بنجاح'], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
