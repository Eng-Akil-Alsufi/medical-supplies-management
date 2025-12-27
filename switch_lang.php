<?php
// =====================================================
// ملف تبديل اللغة
// =====================================================

session_start();

// الحصول على اللغة المطلوبة من URL
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'ar';

// التحقق من أن اللغة مسموحة فقط (ar أو en)
if (in_array($lang, ['ar', 'en'])) {
    $_SESSION['lang'] = $lang;
}

// الحصول على الصفحة السابقة
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php?page=dashboard';

// إعادة التوجيه إلى الصفحة السابقة
header('Location: ' . $referer);
exit();
?>
