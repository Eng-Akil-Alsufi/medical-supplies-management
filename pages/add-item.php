<?php
// =====================================================
// صفحة إضافة عنصر جديد
// =====================================================

// التحقق من الصلاحيات
if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin', 'pharmacist', 'purchasing'])) {
    header('Location: index.php?page=dashboard');
    exit();
}

$error = '';
$success = '';

// معالجة إضافة عنصر جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $batch_number = $_POST['batch_number'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $min_quantity = $_POST['min_quantity'] ?? 10;
    $department = $_POST['department'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $location = $_POST['location'] ?? '';
    $barcode = $_POST['barcode'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $supplier_id = $_POST['supplier_id'] ?? NULL;
    $category = $_POST['category'] ?? '';
    $unit_price = $_POST['unit_price'] ?? 0;
    
    // التحقق من الحقول المطلوبة
    if (empty($name) || empty($batch_number) || empty($expiry_date)) {
        $error = 'الرجاء ملء جميع الحقول المطلوبة';
    } else {
        try {
            // بدء عملية معاملة (Transaction)
            $pdo->beginTransaction();
            
            // إدراج العنصر الجديد
            $stmt = $pdo->prepare("
                INSERT INTO items (name, batch_number, quantity, min_quantity, department, expiry_date, location, barcode, notes, supplier_id, category, unit_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $name, $batch_number, $quantity, $min_quantity, $department, 
                $expiry_date, $location, $barcode, $notes, $supplier_id, $category, $unit_price
            ]);
            
            // الحصول على معرّف العنصر الجديد
            $itemId = $pdo->lastInsertId();
            
            if ($quantity > 0) {
                $stmtTrans = $pdo->prepare("
                    INSERT INTO transactions (item_id, transaction_type, quantity, user_id, notes, transaction_date)
                    VALUES (?, 'delivery', ?, ?, ?, NOW())
                ");
                $stmtTrans->execute([
                    $itemId, $quantity, $_SESSION['user_id'], "إضافة عنصر جديد: $name"
                ]);
            }
            
            // تأكيد العملية
            $pdo->commit();
            
            // تسجيل العملية
            logAction($_SESSION['user_id'], 'إضافة عنصر جديد', "اسم العنصر: $name، الكمية: $quantity");
            
            $success = 'تم إضافة العنصر بنجاح';
        } catch (PDOException $e) {
            // استرجاع التغييرات في حالة الخطأ
            $pdo->rollBack();
            $error = 'حدث خطأ أثناء إضافة العنصر';
        }
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
    <title>إضافة عنصر جديد - نظام إدارة المخزون الطبي</title>
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
                        <li><a href="index.php?page=dashboard"><i class="fas fa-home"></i> الرئيسية</a></li>
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
                    <h1>إضافة عنصر جديد</h1>
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
                
                <form method="POST" class="form-container">
                    <div class="form-row">
                        <div class="form-group form-half">
                            <label for="name">اسم العنصر <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group form-half">
                            <label for="batch_number">رقم الدفعة <span class="required">*</span></label>
                            <input type="text" id="batch_number" name="batch_number" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-half">
                            <label for="quantity">الكمية الحالية</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" value="0">
                        </div>
                        <div class="form-group form-half">
                            <label for="min_quantity">الحد الأدنى للمخزون</label>
                            <input type="number" id="min_quantity" name="min_quantity" class="form-control" value="10">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-half">
                            <label for="expiry_date">تاريخ الصلاحية <span class="required">*</span></label>
                            <input type="date" id="expiry_date" name="expiry_date" class="form-control" required>
                        </div>
                        <div class="form-group form-half">
                            <label for="department">القسم</label>
                            <input type="text" id="department" name="department" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-half">
                            <label for="location">الموقع</label>
                            <input type="text" id="location" name="location" class="form-control">
                        </div>
                        <div class="form-group form-half">
                            <label for="barcode">الباركود</label>
                            <input type="text" id="barcode" name="barcode" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-half">
                            <label for="category">الفئة</label>
                            <input type="text" id="category" name="category" class="form-control">
                        </div>
                        <div class="form-group form-half">
                            <label for="unit_price">سعر الوحدة</label>
                            <input type="number" step="0.01" id="unit_price" name="unit_price" class="form-control" value="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-full">
                            <label for="supplier_id">الموردون</label>
                            <select id="supplier_id" name="supplier_id" class="form-control">
                                <option value=""> اختر موردا </option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['supplier_id']; ?>">
                                        <?php echo escape($supplier['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-full">
                            <label for="notes">ملاحظات</label>
                            <textarea id="notes" name="notes" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ العنصر
                        </button>
                        <a href="index.php?page=inventory" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
