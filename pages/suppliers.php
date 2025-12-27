<?php
// =====================================================
// صفحة إدارة الموردين - متعددة اللغات
// =====================================================

// التحقق من الصلاحيات
if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin', 'purchasing'])) {
    header('Location: index.php?page=dashboard');
    exit();
}

$error = '';
$success = '';

$dir = ($_SESSION['lang'] === 'ar') ? 'rtl' : 'ltr';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    $name = $_POST['name'] ?? '';
    $contact_person = $_POST['contact_person'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $country = $_POST['country'] ?? '';
    
    if (empty($name)) {
        $error = $lang['supplier_name_required'];
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO suppliers (name, contact_person, phone, email, address, city, country)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$name, $contact_person, $phone, $email, $address, $city, $country]);
            
            $success = $lang['supplier_added_success'];
            logAction($_SESSION['user_id'], 'إضافة موردين جديد', "اسم الموردين: $name");
        } catch (PDOException $e) {
            $error = $lang['supplier_add_error'];
        }
    }
}

// معالجة تحديث موردين
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_supplier'])) {
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    
    if (empty($supplier_id) || empty($name)) {
        $error = $lang['supplier_name_required'];
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE suppliers 
                SET name = ?, contact_person = ?, phone = ?, email = ?, address = ?, city = ?, country = ?
                WHERE supplier_id = ?
            ");
            
            $stmt->execute([$name, $contact_person, $phone, $email, $address, $city, $country, $supplier_id]);
            
            $success = $lang['supplier_updated_success'];
            logAction($_SESSION['user_id'], 'تعديل موردين', "معرّف الموردين: $supplier_id، الاسم: $name");
        } catch (PDOException $e) {
            $error = $lang['supplier_update_error'];
        }
    }
}

// جلب قائمة الموردين
$stmt = $pdo->prepare("SELECT * FROM suppliers ORDER BY name");
$stmt->execute();
$suppliers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['suppliers']; ?> - <?php echo $lang['site_name']; ?></title>
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
                    <!-- استبدال SITE_NAME بـ $lang['site_name'] للترجمة الصحيحة -->
                    <span><?php echo $lang['site_name']; ?></span>
                </div>
                <nav class="navbar-menu">
                    <ul>
                        <li><a href="index.php?page=dashboard"><i class="fas fa-home"></i> <?php echo $lang['home']; ?></a></li>
                        <li><a href="index.php?page=suppliers"><i class="fas fa-truck"></i> <?php echo $lang['suppliers']; ?></a></li>
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
                <h1><?php echo $lang['supplier_management']; ?></h1>
                
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
                
                <!-- نموذج إضافة موردين جديد -->
                <section class="form-section">
                    <h2><?php echo $lang['add_new_supplier']; ?></h2>
                    <form method="POST" class="form-container">
                        <div class="form-row">
                            <div class="form-group form-half">
                                <label for="name"><?php echo $lang['supplier_name']; ?> <span class="required">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            <div class="form-group form-half">
                                <label for="contact_person"><?php echo $lang['contact_person']; ?></label>
                                <input type="text" id="contact_person" name="contact_person" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group form-half">
                                <label for="phone"><?php echo $lang['phone']; ?></label>
                                <input type="text" id="phone" name="phone" class="form-control">
                            </div>
                            <div class="form-group form-half">
                                <label for="email"><?php echo $lang['email']; ?></label>
                                <input type="email" id="email" name="email" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group form-half">
                                <label for="city"><?php echo $lang['city']; ?></label>
                                <input type="text" id="city" name="city" class="form-control">
                            </div>
                            <div class="form-group form-half">
                                <label for="country"><?php echo $lang['country']; ?></label>
                                <input type="text" id="country" name="country" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group form-full">
                                <label for="address"><?php echo $lang['address']; ?></label>
                                <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_supplier" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo $lang['add_supplier']; ?>
                        </button>
                    </form>
                </section>
                
                <!-- جدول الموردين -->
                <section class="table-section">
                    <h2><?php echo $lang['suppliers_list']; ?></h2>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><?php echo $lang['supplier_name']; ?></th>
                                    <th><?php echo $lang['contact_person']; ?></th>
                                    <th><?php echo $lang['phone']; ?></th>
                                    <th><?php echo $lang['email']; ?></th>
                                    <th><?php echo $lang['city']; ?></th>
                                    <th><?php echo $lang['country']; ?></th>
                                    <th><?php echo $lang['actions']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($suppliers)): ?>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <tr>
                                            <td><?php echo escape($supplier['name']); ?></td>
                                            <td><?php echo escape($supplier['contact_person'] ?? '-'); ?></td>
                                            <td><?php echo escape($supplier['phone'] ?? '-'); ?></td>
                                            <td><?php echo escape($supplier['email'] ?? '-'); ?></td>
                                            <td><?php echo escape($supplier['city'] ?? '-'); ?></td>
                                            <td><?php echo escape($supplier['country'] ?? '-'); ?></td>
                                            <td>
                                                <button class="btn-icon btn-edit" title="<?php echo $lang['edit']; ?>" onclick="openEditSupplierModal(<?php echo $supplier['supplier_id']; ?>, '<?php echo escape(json_encode($supplier)); ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-icon btn-delete" title="<?php echo $lang['delete']; ?>" onclick="deleteSupplier(<?php echo $supplier['supplier_id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center"><?php echo $lang['no_suppliers']; ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
    
    <!-- نافذة منبثقة (Modal) لتعديل الموردين -->
    <div id="edit-supplier-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $lang['edit_supplier_modal']; ?></h2>
                <button class="modal-close" onclick="closeEditSupplierModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-supplier-form" method="POST">
                    <input type="hidden" name="edit_supplier" value="1">
                    <input type="hidden" id="edit-supplier-id" name="supplier_id">
                    
                    <div class="form-row">
                        <div class="form-group form-half">
                            <label for="edit-name"><?php echo $lang['supplier_name']; ?> <span class="required">*</span></label>
                            <input type="text" id="edit-name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group form-half">
                            <label for="edit-contact"><?php echo $lang['contact_person']; ?></label>
                            <input type="text" id="edit-contact" name="contact_person" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-half">
                            <label for="edit-phone"><?php echo $lang['phone']; ?></label>
                            <input type="text" id="edit-phone" name="phone" class="form-control">
                        </div>
                        <div class="form-group form-half">
                            <label for="edit-email"><?php echo $lang['email']; ?></label>
                            <input type="email" id="edit-email" name="email" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-half">
                            <label for="edit-city"><?php echo $lang['city']; ?></label>
                            <input type="text" id="edit-city" name="city" class="form-control">
                        </div>
                        <div class="form-group form-half">
                            <label for="edit-country"><?php echo $lang['country']; ?></label>
                            <input type="text" id="edit-country" name="country" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-full">
                            <label for="edit-address"><?php echo $lang['address']; ?></label>
                            <textarea id="edit-address" name="address" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeEditSupplierModal()"><?php echo $lang['cancel']; ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo $lang['save_changes']; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // فتح نافذة تعديل الموردين
        function openEditSupplierModal(supplierId, supplierData) {
            let supplier;
            try {
                // إزالة الأحرف الخاصة وتحويل البيانات
                const cleanData = supplierData.replace(/&quot;/g, '"').replace(/&#039;/g, "'");
                supplier = JSON.parse(cleanData);
            } catch (e) {
                console.error('[v0] خطأ في معالجة بيانات الموردين:', e);
                alert('حدث خطأ في تحميل بيانات الموردين');
                return;
            }
            
            // ملء النموذج بالبيانات
            document.getElementById('edit-supplier-id').value = supplierId;
            document.getElementById('edit-name').value = supplier.name || '';
            document.getElementById('edit-contact').value = supplier.contact_person || '';
            document.getElementById('edit-phone').value = supplier.phone || '';
            document.getElementById('edit-email').value = supplier.email || '';
            document.getElementById('edit-city').value = supplier.city || '';
            document.getElementById('edit-country').value = supplier.country || '';
            document.getElementById('edit-address').value = supplier.address || '';
            
            // فتح النافذة المنبثقة
            document.getElementById('edit-supplier-modal').style.display = 'flex';
        }
        
        // إغلاق نافذة التعديل
        function closeEditSupplierModal() {
            document.getElementById('edit-supplier-modal').style.display = 'none';
        }
        
        // حذف موردين
        function deleteSupplier(supplierId) {
            if (confirm('هل تؤكد حذف هذا الموردين؟')) {
                window.location.href = `api/delete-supplier.php?id=${supplierId}`;
            }
        }
        
        // إغلاق Modal عند النقر خارجه
        document.getElementById('edit-supplier-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditSupplierModal();
            }
        });
    </script>
    
    <script src="js/main.js"></script>
</body>
</html>
