<?php
// =====================================================
// صفحة تعديل عنصر
// =====================================================

// التحقق من الصلاحيات
if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin', 'pharmacist', 'purchasing'])) {
    header('Location: index.php?page=dashboard');
    exit();
}

$item_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// جلب بيانات العنصر
$stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ? AND is_active = 1");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    $error = 'العنصر غير موجود';
}

// معالجة تحديث العنصر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $name = $_POST['name'] ?? $item['name'];
    $quantity = $_POST['quantity'] ?? $item['quantity'];
    $min_quantity = $_POST['min_quantity'] ?? $item['min_quantity'];
    $expiry_date = $_POST['expiry_date'] ?? $item['expiry_date'];
    $location = $_POST['location'] ?? $item['location'];
    $notes = $_POST['notes'] ?? $item['notes'];
    
    try {
        // بدء معاملة
        $pdo->beginTransaction();
        
        $oldQuantity = $item['quantity'];
        $quantityDifference = $quantity - $oldQuantity;
        
        // تحديث العنصر
        $stmt = $pdo->prepare("
            UPDATE items 
            SET name = ?, quantity = ?, min_quantity = ?, expiry_date = ?, location = ?, notes = ?
            WHERE item_id = ?
        ");
        
        $stmt->execute([$name, $quantity, $min_quantity, $expiry_date, $location, $notes, $item_id]);
        
        // تسجيل معاملة حسب نوع التغيير
        if ($quantityDifference > 0) {
            // الكمية زادت: معاملة receipt (استقبال)
            $transType = 'receipt';
            $transQuantity = $quantityDifference;
            $transNotes = "زيادة كمية: $name (من $oldQuantity إلى $quantity)";
        } elseif ($quantityDifference < 0) {
            // الكمية انخفضت: معاملة adjustment (تعديل)
            $transType = 'adjustment';
            $transQuantity = abs($quantityDifference);
            $transNotes = "تعديل كمية: $name (من $oldQuantity إلى $quantity)";
        }
        
        // إدراج المعاملة إذا كان هناك فرق
        if ($quantityDifference !== 0) {
            $stmtTrans = $pdo->prepare("
                INSERT INTO transactions (item_id, transaction_type, quantity, user_id, notes, transaction_date)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmtTrans->execute([
                $item_id, $transType, $transQuantity, $_SESSION['user_id'], $transNotes
            ]);
        }
        
        // تأكيد العملية
        $pdo->commit();
        
        $success = 'تم تحديث العنصر بنجاح';
        logAction($_SESSION['user_id'], 'تعديل عنصر', "رقم العنصر: $item_id، الفرق: $quantityDifference");
    } catch (PDOException $e) {
        // استرجاع التغييرات
        $pdo->rollBack();
        $error = 'حدث خطأ أثناء التحديث: ' . $e->getMessage();
    }
}

// جلب قائمة الموردين
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE is_active = 1");
$stmt->execute();
$suppliers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل عنصر - نظام إدارة المخزون الطبي</title>
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
                    <span><?php echo SITE_NAME; ?></span>
                </div>
                <nav class="navbar-menu">
                    <ul>
                        <li><a href="index.php?page=inventory"><i class="fas fa-boxes"></i> المخزون</a></li>
                    </ul>
                </nav>
                <div class="navbar-user">
                    <a href="index.php?page=logout" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> تسجيل خروج
                    </a>
                </div>
            </div>
        </header>
        
        <!-- المحتوى الرئيسي -->
        <main class="dashboard-content">
            <div class="container">
                <div class="page-header">
                    <a href="index.php?page=inventory" class="btn-back">
                        <i class="fas fa-arrow-right"></i> العودة
                    </a>
                    <h1>تعديل العنصر</h1>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo escape($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo escape($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($item): ?>
                    <form method="POST" class="form-container">
                        <div class="form-row">
                            <div class="form-group form-half">
                                <label for="name">اسم العنصر</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo escape($item['name']); ?>">
                            </div>
                            <div class="form-group form-half">
                                <label for="quantity">الكمية الحالية</label>
                                <input type="number" id="quantity" name="quantity" class="form-control" value="<?php echo $item['quantity']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group form-half">
                                <label for="min_quantity">الحد الأدنى</label>
                                <input type="number" id="min_quantity" name="min_quantity" class="form-control" value="<?php echo $item['min_quantity']; ?>">
                            </div>
                            <div class="form-group form-half">
                                <label for="expiry_date">تاريخ الصلاحية</label>
                                <input type="date" id="expiry_date" name="expiry_date" class="form-control" value="<?php echo $item['expiry_date']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group form-full">
                                <label for="location">الموقع</label>
                                <input type="text" id="location" name="location" class="form-control" value="<?php echo escape($item['location']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group form-full">
                                <label for="notes">ملاحظات</label>
                                <textarea id="notes" name="notes" class="form-control" rows="4"><?php echo escape($item['notes']); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ التغييرات
                            </button>
                            <a href="index.php?page=inventory" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
