<?php
// =====================================================
// صفحة التقارير - متعددة اللغات
// =====================================================

// التحقق من الصلاحيات
if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin', 'pharmacist', 'purchasing'])) {
    header('Location: index.php?page=dashboard');
    exit();
}

// 1. تقرير الأدوية الأكثر استخداماً
$stmt = $pdo->prepare("
    SELECT i.name, COUNT(t.transaction_id) as usage_count, SUM(t.quantity) as total_quantity
    FROM transactions t
    JOIN items i ON t.item_id = i.item_id
    WHERE t.transaction_type = 'dispensing'
    AND t.transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY i.item_id, i.name
    ORDER BY usage_count DESC
    LIMIT 10
");
$stmt->execute();
$mostUsedDrugs = $stmt->fetchAll();

// 2. تقرير العناصر قريبة من الانتهاء - البحث عن العناصر التي تنتهي صلاحيتها خلال 90 يوم
$stmt = $pdo->prepare("
    SELECT i.item_id, i.name, i.quantity, i.min_quantity, i.expiry_date,
    DATEDIFF(i.expiry_date, NOW()) as days_until_expiry
    FROM items i
    WHERE i.is_active = 1
    AND i.expiry_date <= DATE_ADD(NOW(), INTERVAL 90 DAY)
    AND i.expiry_date > NOW()
    ORDER BY i.expiry_date ASC
");
$stmt->execute();
$depletionItems = $stmt->fetchAll();

// 3. تقرير طلبات الأقسام
$stmt = $pdo->prepare("
    SELECT u.department, COUNT(r.request_id) as request_count,
    SUM(CASE WHEN r.status = 'fulfilled' THEN 1 ELSE 0 END) as fulfilled_count,
    SUM(CASE WHEN r.status = 'pending' THEN 1 ELSE 0 END) as pending_count
    FROM requests r
    JOIN users u ON r.requested_by = u.user_id
    WHERE r.requested_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY u.department
");
$stmt->execute();
$departmentRequests = $stmt->fetchAll();

// 4. تقرير المعاملات
$stmt = $pdo->prepare("
    SELECT DATE(transaction_date) as trans_date, 
    transaction_type,
    COUNT(*) as count,
    SUM(quantity) as total_quantity
    FROM transactions
    WHERE transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(transaction_date), transaction_type
    ORDER BY trans_date DESC
");
$stmt->execute();
$transactionReport = $stmt->fetchAll();

$dir = ($_SESSION['lang'] === 'ar') ? 'rtl' : 'ltr';
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['reports']; ?> - <?php echo $lang['site_name']; ?></title>
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
                        <li><a href="index.php?page=reports"><i class="fas fa-chart-bar"></i> <?php echo $lang['reports']; ?></a></li>
                    </ul>
                </nav>
                <div class="navbar-user">
                    <!-- إضافة زر تبديل اللغات في الشريط العلوي -->
                    <a href="switch_lang.php?lang=<?php echo $_SESSION['lang'] === 'ar' ? 'en' : 'ar'; ?>" class="lang-toggle">
                        <i class="fas fa-globe"></i>
                        <?php echo $_SESSION['lang'] === 'ar' ? 'English' : $lang['arabic']; ?>
                    </a>
                    <a href="index.php?page=logout" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> <?php echo $lang['logout']; ?>
                    </a>
                </div>
            </div>
        </header>
        
        <!-- المحتوى الرئيسي -->
        <main class="dashboard-content">
            <div class="container">
                <div class="page-header">
                    <h1><?php echo $lang['reports']; ?></h1>
                    <!-- استخدام ترجمة النصوص من ملف اللغة -->
                    <button class="btn btn-primary" onclick="openExportModal()">
                        <i class="fas fa-file-pdf"></i> <?php echo $lang['export_pdf_button']; ?>
                    </button>
                </div>
                
                <!-- تقرير الأدوية الأكثر استخداماً -->
                <section class="report-section">
                    <div class="report-header">
                        <h2><?php echo $lang['most_used_drugs_title']; ?></h2>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $lang['item']; ?></th>
                                <th><?php echo $lang['usage_count']; ?></th>
                                <th><?php echo $lang['total_quantity_dispensed']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($mostUsedDrugs)): ?>
                                <?php foreach ($mostUsedDrugs as $drug): ?>
                                    <tr>
                                        <td><?php echo escape($drug['name']); ?></td>
                                        <td><?php echo $drug['usage_count']; ?></td>
                                        <td><?php echo $drug['total_quantity']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center"><?php echo $lang['no_data']; ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
                
                <!-- تقرير العناصر قريبة من الانتهاء -->
                <section class="report-section">
                    <div class="report-header">
                        <h2><?php echo $lang['depletion_items_title']; ?></h2>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $lang['item']; ?></th>
                                <th><?php echo $lang['current_quantity']; ?></th>
                                <th><?php echo $lang['minimum_quantity']; ?></th>
                                <th><?php echo $lang['days_until_expiry']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($depletionItems)): ?>
                                <?php foreach ($depletionItems as $item): ?>
                                    <tr class="row-critical">
                                        <td><?php echo escape($item['name']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo $item['min_quantity']; ?></td>
                                        <td><?php echo $item['days_until_expiry']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center"><?php echo $lang['no_data']; ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
                
                <!-- تقرير طلبات الأقسام -->
                <section class="report-section">
                    <h2><?php echo $lang['department_requests_title']; ?></h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $lang['department']; ?></th>
                                <th><?php echo $lang['total_requests']; ?></th>
                                <th><?php echo $lang['fulfilled_count']; ?></th>
                                <th><?php echo $lang['pending_count']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($departmentRequests)): ?>
                                <?php foreach ($departmentRequests as $dept): ?>
                                    <tr>
                                        <td><?php echo escape($dept['department'] ?? $lang['unspecified']); ?></td>
                                        <td><?php echo $dept['request_count']; ?></td>
                                        <td><?php echo $dept['fulfilled_count']; ?></td>
                                        <td><?php echo $dept['pending_count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center"><?php echo $lang['no_data']; ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
                
                <!-- تقرير المعاملات -->
                <section class="report-section">
                    <div class="report-header">
                        <h2><?php echo $lang['transactions_title']; ?></h2>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $lang['transaction_date']; ?></th>
                                <th><?php echo $lang['transaction_type']; ?></th>
                                <th><?php echo $lang['transaction_count']; ?></th>
                                <th><?php echo $lang['total_quantity']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transactionReport)): ?>
                                <?php foreach ($transactionReport as $trans): ?>
                                    <tr>
                                        <td><?php echo $trans['trans_date']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $trans['transaction_type']; ?>">
                                                <?php 
                                                echo $lang[$trans['transaction_type']] ?? $trans['transaction_type'];
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo $trans['count']; ?></td>
                                        <td><?php echo $trans['total_quantity']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center"><?php echo $lang['no_data']; ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>
    
    <!-- نافذة منبثقة لاختيار التقارير للتصدير -->
    <div id="export-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $lang['select_reports']; ?></h2>
                <button class="modal-close" onclick="closeExportModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="export-form">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="reports" value="most-used" checked>
                            <span><?php echo $lang['report_most_used']; ?></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="reports" value="depletion" checked>
                            <span><?php echo $lang['report_depletion']; ?></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="reports" value="departments" checked>
                            <span><?php echo $lang['report_departments']; ?></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="reports" value="transactions" checked>
                            <span><?php echo $lang['report_transactions']; ?></span>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeExportModal()"><?php echo $lang['cancel']; ?></button>
                <button type="button" class="btn btn-primary" onclick="exportSelectedReports()"><?php echo $lang['export_selected']; ?></button>
            </div>
        </div>
    </div>
    
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            margin: 0;
        }
    </style>
    
    <script>
        // فتح نافذة التصدير
        function openExportModal() {
            document.getElementById('export-modal').style.display = 'flex';
        }
        
        // إغلاق نافذة التصدير
        function closeExportModal() {
            document.getElementById('export-modal').style.display = 'none';
        }
        
        // تصدير التقارير المحددة
        function exportSelectedReports() {
            const selectedReports = Array.from(
                document.querySelectorAll('input[name="reports"]:checked')
            ).map(cb => cb.value);
            
            if (selectedReports.length === 0) {
                alert('يرجى اختيار تقرير واحد على الأقل');
                return;
            }
            
            // إرسال طلب التصدير
            window.location.href = 'api/export-pdf.php?reports=' + selectedReports.join(',');
            closeExportModal();
        }
        
        // إغلاق Modal عند النقر خارجه
        document.getElementById('export-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeExportModal();
            }
        });
    </script>
    
    <script src="js/main.js"></script>
</body>
</html>
