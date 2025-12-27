<?php
// =====================================================
// الصفحة الرئيسية - توزيع الطلبات للصفحات المختلفة
// =====================================================

require_once 'config.php';

// الحصول على الصفحة المطلوبة من URL
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// التحقق من تسجيل الدخول
if (!isLoggedIn() && $page !== 'login') {
    header('Location: index.php?page=login');
    exit();
}

// توجيه الطلبات إلى الصفحات المختلفة
$pages = [
    'login' => 'pages/login.php',
    'dashboard' => 'pages/dashboard.php',
    'inventory' => 'pages/inventory.php',
    'add-item' => 'pages/add-item.php',
    'edit-item' => 'pages/edit-item.php',
    'requests' => 'pages/requests.php',
    'users' => 'pages/users.php',
    'reports' => 'pages/reports.php',
    'suppliers' => 'pages/suppliers.php', 
    'notifications' => 'pages/notifications.php',
    'logout' => 'pages/logout.php'
];

// التحقق من وجود الصفحة
if (isset($pages[$page])) {
    include $pages[$page];
} else {
    include 'pages/404.php';
}
?>
