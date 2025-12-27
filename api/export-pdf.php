<?php
// =====================================================
// API لتصدير التقارير إلى PDF
// =====================================================

require_once '../config.php';

// التحقق من تسجيل الدخول والصلاحيات
if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin', 'pharmacist', 'purchasing'])) {
    http_response_code(403);
    die('غير مصرح');
}

$reportsParam = $_GET['reports'] ?? '';
$reportsList = !empty($reportsParam) ? explode(',', $reportsParam) : [];

if (empty($reportsList)) {
    http_response_code(400);
    die('لا توجد تقارير محددة');
}

// إعداد رأس PDF
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="التقارير_' . date('Y-m-d_H-i-s') . '.txt"');

// محتوى الملف
$content = "=" . str_repeat("=", 80) . "\n";
$content .= "نظام إدارة المخزون والعقاقير الطبية\n";
$content .= "تقرير شامل للعمليات\n";
$content .= "التاريخ: " . date('d/m/Y H:i:s') . "\n";
$content .= "المستخدم: " . escape($_SESSION['full_name']) . "\n";
$content .= "=" . str_repeat("=", 80) . "\n\n";

// دالة مساعدة لإنشاء جدول
function formatTableOutput($title, $headers, $data) {
    $output = "\n" . str_repeat("-", 100) . "\n";
    $output .= "تقرير: $title\n";
    $output .= str_repeat("-", 100) . "\n";
    
    // رؤوس الجدول
    foreach ($headers as $header) {
        $output .= str_pad($header, 25, ' ', STR_PAD_LEFT) . " | ";
    }
    $output .= "\n" . str_repeat("-", 100) . "\n";
    
    // البيانات
    if (!empty($data)) {
        foreach ($data as $row) {
            foreach ($row as $cell) {
                $output .= str_pad(mb_substr((string)$cell, 0, 23), 25, ' ', STR_PAD_LEFT) . " | ";
            }
            $output .= "\n";
        }
    } else {
        $output .= "لا توجد بيانات للعرض\n";
    }
    
    $output .= str_repeat("-", 100) . "\n\n";
    return $output;
}

// معالجة التقارير المحددة
foreach ($reportsList as $report_type) {
    $report_type = trim($report_type);
    
    if ($report_type === 'most-used') {
        // تقرير الأدوية الأكثر استخداماً
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
        $data = $stmt->fetchAll();
        
        $rows = [];
        foreach ($data as $row) {
            $rows[] = [
                $row['name'],
                $row['usage_count'],
                $row['total_quantity']
            ];
        }
        
        $content .= formatTableOutput(
            'الأدوية والعناصر الأكثر استخداماً (آخر 30 يوم)',
            ['اسم العنصر', 'عدد المرات', 'الكمية الإجمالية'],
            $rows
        );
        
    } elseif ($report_type === 'depletion') {
        $stmt = $pdo->prepare("
            SELECT i.name, i.quantity, i.min_quantity
            FROM items i
            WHERE i.is_active = 1
            AND i.quantity <= i.min_quantity
            ORDER BY i.quantity ASC
        ");
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        $rows = [];
        foreach ($data as $row) {
            $rows[] = [
                $row['name'],
                $row['quantity'],
                $row['min_quantity']
            ];
        }
        
        $content .= formatTableOutput(
            'العناصر قريبة من الانتهاء',
            ['اسم العنصر', 'الكمية الحالية', 'الحد الأدنى'],
            $rows
        );
        
    } elseif ($report_type === 'departments') {
        // تقرير طلبات الأقسام
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
        $data = $stmt->fetchAll();
        
        $rows = [];
        foreach ($data as $row) {
            $rows[] = [
                $row['department'] ?? 'غير محدد',
                $row['request_count'],
                $row['fulfilled_count'],
                $row['pending_count']
            ];
        }
        
        $content .= formatTableOutput(
            'تحليل طلبات الأقسام (آخر 30 يوم)',
            ['القسم', 'إجمالي الطلبات', 'المنفذة', 'المعلقة'],
            $rows
        );
        
    } elseif ($report_type === 'transactions') {
        // تقرير المعاملات
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
        $data = $stmt->fetchAll();
        
        $types = [
            'delivery' => 'تسليم',
            'receipt' => 'استقبال',
            'dispensing' => 'صرف',
            'adjustment' => 'تعديل'
        ];
        
        $rows = [];
        foreach ($data as $row) {
            $rows[] = [
                $row['trans_date'],
                $types[$row['transaction_type']] ?? $row['transaction_type'],
                $row['count'],
                $row['total_quantity']
            ];
        }
        
        $content .= formatTableOutput(
            'تقرير المعاملات (آخر 30 يوم)',
            ['التاريخ', 'نوع المعاملة', 'العدد', 'الكمية الإجمالية'],
            $rows
        );
    }
}

// الختام
$content .= "\n" . "=" . str_repeat("=", 80) . "\n";
$content .= "انتهى التقرير\n";
$content .= "=" . str_repeat("=", 80) . "\n";

// تسجيل العملية
logAction($_SESSION['user_id'], 'تصدير تقارير إلى PDF', 'التقارير المصدرة: ' . implode(', ', $reportsList));

// إرسال الملف
echo $content;
?>
