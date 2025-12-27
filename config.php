<?php
// =====================================================
// ملف الإعدادات والاتصال بقاعدة البيانات
// =====================================================

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'medical_inventory_db');

// معلومات الموقع
define('SITE_NAME', 'نظام إدارة المخزون الطبي والعقاقير');
define('SITE_URL', 'http://localhost');

// إعدادات الجلسة
define('SESSION_TIMEOUT', 3600); // بالثواني (ساعة واحدة)

// إعدادات الأمان
define('EXPIRY_WARNING_DAYS', 60); // عدد أيام التحذير قبل انتهاء الصلاحية

session_start();

// تعيين اللغة الافتراضية إذا لم تكن محددة
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ar';
}

// التحقق من صحة اللغة المحددة
$valid_langs = ['ar', 'en'];
if (!in_array($_SESSION['lang'], $valid_langs)) {
    $_SESSION['lang'] = 'ar';
}

// تحميل ملف اللغة المناسب
require_once 'lang/' . $_SESSION['lang'] . '.php';

try {
    // إنشاء اتصال PDO آمن
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => FALSE,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    // رسالة خطأ في حالة فشل الاتصال
    die('خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage());
}

// دالة للتحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// دالة للتحقق من دور المستخدم
function checkRole($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    return $_SESSION['role'] === $requiredRole || $_SESSION['role'] === 'admin';
}

// دالة لتسجيل العمليات في السجل
function logAction($user_id, $action, $details = '') {
    global $pdo;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO logs (user_id, action, details, ip_address) 
            VALUES (:user_id, :action, :details, :ip_address)
        ");
        
        $stmt->execute([
            ':user_id' => $user_id > 0 ? $user_id : null,
            ':action' => $action,
            ':details' => $details,
            ':ip_address' => $ip_address
        ]);
    } catch (PDOException $e) {
        // تجاهل أخطاء تسجيل العمليات لتجنب انهيار التطبيق
        error_log('خطأ في تسجيل العملية: ' . $e->getMessage());
    }
}

// دالة للحماية من XSS
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>
