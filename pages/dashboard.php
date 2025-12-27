<?php
// =====================================================
// لوحة التحكم الرئيسية
// =====================================================

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit();
}

// جلب الإحصائيات
// 1. عدد العناصر المنخفضة
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE quantity <= min_quantity AND is_active = 1");
$stmt->execute();
$lowStockCount = $stmt->fetch()['count'];

// 2. عدد العناصر قريبة من انتهاء الصلاحية
$expiryWarningDays = 90;
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count FROM items 
    WHERE expiry_date <= DATE_ADD(NOW(), INTERVAL ? DAY) 
    AND expiry_date > NOW() 
    AND is_active = 1
");
$stmt->execute([$expiryWarningDays]);
$expiryWarningCount = $stmt->fetch()['count'];

// 3. عدد الطلبات المعلقة
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM requests WHERE status = 'pending'");
$stmt->execute();
$pendingRequestsCount = $stmt->fetch()['count'];

// 4. إجمالي عدد العناصر
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE is_active = 1");
$stmt->execute();
$totalItemsCount = $stmt->fetch()['count'];

// 5. جلب آخر المعاملات - إضافة قيد الصلاحيات: متاح فقط لـ admin, pharmacist, purchasing
$recentTransactions = [];
if (in_array($_SESSION['role'], ['admin', 'pharmacist', 'purchasing'])) {
    $stmt = $pdo->prepare("
        SELECT t.*, i.name as item_name, u.full_name as user_name 
        FROM transactions t
        JOIN items i ON t.item_id = i.item_id
        JOIN users u ON t.user_id = u.user_id
        ORDER BY t.transaction_date DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recentTransactions = $stmt->fetchAll();
}

// 6. جلب التنبيهات غير المقروءة - عرض أحدث 5 إشعارات فقط
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE (user_id = ? OR user_id IS NULL)
    AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$unreadNotifications = $stmt->fetchAll();

// 7. جلب عدد الإشعارات غير المقروءة الكلي
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count FROM notifications 
    WHERE (user_id = ? OR user_id IS NULL)
    AND is_read = 0
");
$stmt->execute([$_SESSION['user_id']]);
$totalUnreadNotifications = $stmt->fetch()['count'];

// تحديد اتجاه النص بناءً على اللغة الحالية
$dir = ($_SESSION['lang'] === 'ar') ? 'rtl' : 'ltr';
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['dashboard_title']; ?> - <?php echo $lang['site_name']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- الشريط العلوي -->
        <header class="navbar">
            <div class="navbar-content">
                <div class="navbar-brand">
                    <i class="fas fa-hospital"></i>
                    <span><?php echo $lang['site_name']; ?></span>
                </div>
                <nav class="navbar-menu">
                    <ul>
                        <li><a href="index.php?page=dashboard"><i class="fas fa-home"></i> <?php echo $lang['home']; ?></a></li>
                        <li><a href="index.php?page=inventory"><i class="fas fa-boxes"></i> <?php echo $lang['inventory']; ?></a></li>
                        <li><a href="index.php?page=requests"><i class="fas fa-file-alt"></i> <?php echo $lang['requests']; ?></a></li>
                        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'pharmacist'): ?>
                            <li><a href="index.php?page=reports"><i class="fas fa-chart-bar"></i> <?php echo $lang['reports']; ?></a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a href="index.php?page=users"><i class="fas fa-users"></i> <?php echo $lang['users']; ?></a></li>
                        <?php endif; ?>
                        <!-- إضافة رابط الموردين لـ admin و purchasing فقط -->
                        <?php if (in_array($_SESSION['role'], ['admin', 'purchasing'])): ?>
                            <li><a href="index.php?page=suppliers"><i class="fas fa-truck"></i> <?php echo $lang['suppliers']; ?></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="navbar-user">
                    <!-- إضافة زر التبديل بين اللغات في الشريط العلوي مع دعم RTL/LTR -->
                    <a href="switch_lang.php?lang=<?php echo $_SESSION['lang'] === 'ar' ? 'en' : 'ar'; ?>" class="lang-toggle">
                        <i class="fas fa-globe"></i>
                        <?php echo $_SESSION['lang'] === 'ar' ? 'EN' : 'AR'; ?>
                    </a>
                    <span class="user-name"><?php echo escape($_SESSION['full_name']); ?></span>
                    <span class="user-role">(<?php echo escape($_SESSION['role']); ?>)</span>
                    <a href="index.php?page=logout" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> <?php echo $lang['logout']; ?>
                    </a>
                </div>
            </div>
        </header>
        
        <!-- المحتوى الرئيسي -->
        <main class="dashboard-content">
            <div class="container">
                <h1><?php echo $lang['dashboard_title']; ?></h1>
                
                <!-- الإحصائيات -->
                <div class="statistics-grid">
                    <div class="stat-card stat-total">
                        <div class="stat-icon">
                            <i class="fas fa-cube"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $lang['total_items']; ?></h3>
                            <p class="stat-value"><?php echo $totalItemsCount; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card stat-warning">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $lang['low_stock']; ?></h3>
                            <p class="stat-value"><?php echo $lowStockCount; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card stat-danger">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $lang['expiry_warning']; ?></h3>
                            <p class="stat-value"><?php echo $expiryWarningCount; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card stat-info">
                        <div class="stat-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $lang['pending_requests']; ?></h3>
                            <p class="stat-value"><?php echo $pendingRequestsCount; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- التنبيهات -->
                <?php if (!empty($unreadNotifications)): ?>
                    <section class="dashboard-section">
                        <div class="section-header">
                            <h2><?php echo $lang['new_alerts']; ?></h2>
                            <?php if ($totalUnreadNotifications > 5): ?>
                                <!-- استخدام ترجمة النصوص من ملف اللغة في الشارات -->
                                <span class="notification-badge"><?php echo $totalUnreadNotifications; ?> <?php echo $lang['notifications']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="notifications-list">
                            <?php foreach ($unreadNotifications as $notification): ?>
                                <div class="notification-item notification-<?php echo escape($notification['notification_type']); ?>">
                                    <div class="notification-icon">
                                        <?php 
                                        if ($notification['notification_type'] === 'low_stock') {
                                            echo '<i class="fas fa-exclamation-triangle"></i>';
                                        } elseif ($notification['notification_type'] === 'expiry_warning') {
                                            echo '<i class="fas fa-calendar-times"></i>';
                                        } else {
                                            echo '<i class="fas fa-bell"></i>';
                                        }
                                        ?>
                                    </div>
                                    <div class="notification-content">
                                        <h4><?php echo escape($notification['title']); ?></h4>
                                        <p><?php echo escape($notification['message']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- زر عرض المزيد -->
                        <?php if ($totalUnreadNotifications > 5): ?>
                            <button class="btn btn-outline" id="view-more-notifications">
                                <i class="fas fa-eye"></i> <?php echo $lang['view_more']; ?> (<?php echo $totalUnreadNotifications - 5; ?>)
                            </button>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
                
                <!-- آخر المعاملات -->
                <?php if (in_array($_SESSION['role'], ['admin', 'pharmacist', 'purchasing'])): ?>
                    <section class="dashboard-section">
                        <h2><?php echo $lang['recent_transactions']; ?></h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><?php echo $lang['item']; ?></th>
                                    <th><?php echo $lang['transaction_type']; ?></th>
                                    <th><?php echo $lang['quantity']; ?></th>
                                    <th><?php echo $lang['user']; ?></th>
                                    <th><?php echo $lang['date_time']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentTransactions)): ?>
                                    <?php foreach ($recentTransactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo escape($transaction['item_name']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $transaction['transaction_type']; ?>">
                                                    <?php 
                                                    // استخدام ترجمة أنواع المعاملات من ملف اللغة
                                                    echo $lang[$transaction['transaction_type']] ?? $transaction['transaction_type'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo escape($transaction['quantity']); ?></td>
                                            <td><?php echo escape($transaction['user_name']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($transaction['transaction_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center"><?php echo $lang['no_transactions']; ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </section>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- نافذة الإشعارات -->
    <div id="notifications-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $lang['all_notifications']; ?></h2>
                <button class="modal-close" onclick="closeNotificationsModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="notifications-container">
                <p><?php echo $_SESSION['lang'] === 'ar' ? 'جاري التحميل...' : 'Loading...'; ?></p>
            </div>
        </div>
    </div>
    
    <script>
        // فتح نافذة الإشعارات
        document.getElementById('view-more-notifications')?.addEventListener('click', function() {
            openNotificationsModal();
        });
        
        function openNotificationsModal() {
            fetch('api/get-notifications.php')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.notifications && data.notifications.length > 0) {
                        data.notifications.forEach(notif => {
                            const icon = notif.notification_type === 'low_stock' ? 'fas fa-exclamation-triangle' :
                                        notif.notification_type === 'expiry_warning' ? 'fas fa-calendar-times' : 'fas fa-bell';
                            html += `
                                <div class="notification-item notification-${notif.notification_type}">
                                    <div class="notification-icon">
                                        <i class="${icon}"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h4>${escapeHtml(notif.title)}</h4>
                                        <p>${escapeHtml(notif.message)}</p>
                                        <small>${new Date(notif.created_at).toLocaleString('<?php echo $_SESSION['lang'] === 'ar' ? 'ar-EG' : 'en-US'; ?>')}</small>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<p class="text-center"><?php echo $lang['no_notifications']; ?></p>';
                    }
                    document.getElementById('notifications-container').innerHTML = html;
                    document.getElementById('notifications-modal').style.display = 'flex';
                });
        }
        
        function closeNotificationsModal() {
            document.getElementById('notifications-modal').style.display = 'none';
        }
        
        // إغلاق Modal عند النقر خارجه
        document.getElementById('notifications-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeNotificationsModal();
            }
        });
        
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
    <script src="js/main.js"></script>
</body>
</html>
