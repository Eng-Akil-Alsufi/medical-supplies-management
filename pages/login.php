<?php
// =====================================================
// صفحة تسجيل الدخول
// =====================================================

// التحقق إذا كان المستخدم مسجل دخول بالفعل
if (isLoggedIn()) {
    header('Location: index.php?page=dashboard');
    exit();
}

$error = '';

// معالجة تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // التحقق من عدم ترك الحقول فارغة
    if (empty($username) || empty($password)) {
        $error = $lang['empty_fields_error'];
    } else {
        // البحث عن المستخدم
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // التحقق من بيانات المستخدم
        if ($user && $user['password'] === $password) {
            // تعيين بيانات الجلسة
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['department'] = $user['department'];
            
            // تسجيل العملية
            logAction($user['user_id'], 'تسجيل دخول', 'تسجيل دخول ناجح');
            
            // إعادة التوجيه إلى لوحة التحكم
            header('Location: index.php?page=dashboard');
            exit();
        } else {
            $error = $lang['login_error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo $_SESSION['lang'] === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['login_title']; ?> - <?php echo $lang['site_name']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <!-- إضافة زر التبديل بين اللغات في الزاوية العلوية -->
            <div class="language-switcher">
                <a href="switch_lang.php?lang=<?php echo $_SESSION['lang'] === 'ar' ? 'en' : 'ar'; ?>" class="lang-toggle">
                    <i class="fas fa-globe"></i>
                    <?php echo $_SESSION['lang'] === 'ar' ? 'English' : 'العربية'; ?>
                </a>
            </div>
            
            <div class="login-header">
                <div class="logo-icon">
                    <i class="fas fa-hospital"></i>
                </div>
                <h1><?php echo $lang['site_title']; ?></h1>
                <p><?php echo $lang['site_slogan']; ?></p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo escape($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> <?php echo $lang['username_label']; ?>
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="<?php echo $lang['username_placeholder']; ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> <?php echo $lang['password_label']; ?>
                    </label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="<?php echo $lang['password_placeholder']; ?>"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> <?php echo $lang['login_button']; ?>
                </button>
            </form>
            
            <div class="login-footer">
                <p><?php echo $lang['test_credentials']; ?></p>
                <ul class="test-credentials">
                    <li><strong><?php echo $lang['admin']; ?>:</strong> admin / admin123</li>
                    <li><strong><?php echo $lang['pharmacist']; ?>:</strong> pharmacist / pharm123</li>
                    <li><strong><?php echo $lang['doctor']; ?>:</strong> doctor / doc123</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        // دالة إظهار/إخفاء كلمة المرور
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.classList.remove('fa-eye');
                toggleBtn.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleBtn.classList.remove('fa-eye-slash');
                toggleBtn.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
