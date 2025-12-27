<?php
// =====================================================
// صفحة إدارة المستخدمين (للمسؤولين فقط)
// =====================================================

// التحقق من الصلاحيات
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit();
}

$error = '';
$success = '';

// معالجة إضافة مستخدم جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $role = $_POST['role'] ?? '';
    $department = $_POST['department'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    if (empty($username) || empty($password) || empty($email) || empty($full_name) || empty($role)) {
        $error = $lang['fill_all_fields'];
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, email, full_name, role, department, phone)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$username, $password, $email, $full_name, $role, $department, $phone]);
            
            $success = $lang['user_added'];
            logAction($_SESSION['user_id'], 'إضافة مستخدم جديد', "اسم المستخدم: $username");
        } catch (PDOException $e) {
            $error = $lang['user_exists_error'];
        }
    }
}

// جلب قائمة المستخدمين
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY full_name");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo $_SESSION['lang'] === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['user_management']; ?> - <?php echo $lang['site_name']; ?></title>
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
                        <li><a href="index.php?page=users"><i class="fas fa-users"></i> <?php echo $lang['users']; ?></a></li>
                    </ul>
                </nav>
                <div class="navbar-user">
                    <a href="switch_lang.php?lang=<?php echo $_SESSION['lang'] === 'ar' ? 'en' : 'ar'; ?>" class="lang-toggle">
                        <i class="fas fa-globe"></i>
                        <?php echo $_SESSION['lang'] === 'ar' ? 'EN' : 'AR'; ?>
                    </a>
                    <span class="user-name"><?php echo escape($_SESSION['full_name']); ?></span>
                    <a href="index.php?page=logout" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> <?php echo $lang['logout']; ?>
                    </a>
                </div>
            </div>
        </header>
        
        <!-- المحتوى الرئيسي -->
        <main class="dashboard-content">
            <div class="container">
                <h1><?php echo $lang['user_management']; ?></h1>
                
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
                
                <!-- نموذج إضافة مستخدم جديد -->
                <section class="form-section">
                    <h2><?php echo $lang['add_new_user']; ?></h2>
                    <form method="POST" class="form-container">
                        <div class="form-row">
                            <div class="form-group form-half">
                                <label for="username"><?php echo $lang['username']; ?> <span class="required"><?php echo $lang['required_field']; ?></span></label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="form-group form-half">
                                <label for="password"><?php echo $lang['password']; ?> <span class="required"><?php echo $lang['required_field']; ?></span></label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group form-half">
                                <label for="email"><?php echo $lang['email']; ?> <span class="required"><?php echo $lang['required_field']; ?></span></label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group form-half">
                                <label for="full_name"><?php echo $lang['full_name']; ?> <span class="required"><?php echo $lang['required_field']; ?></span></label>
                                <input type="text" id="full_name" name="full_name" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group form-half">
                                <label for="role"><?php echo $lang['role']; ?> <span class="required"><?php echo $lang['required_field']; ?></span></label>
                                <select id="role" name="role" class="form-control" required>
                                    <option value=""> <?php echo $_SESSION['lang'] === 'ar' ? 'اختر دورا' : 'Choose a role'; ?> </option>
                                    <option value="admin"><?php echo $lang['admin']; ?></option>
                                    <option value="pharmacist"><?php echo $lang['pharmacist']; ?></option>
                                    <option value="purchasing"><?php echo $_SESSION['lang'] === 'ar' ? 'مسؤول مشتريات' : 'Purchasing Manager'; ?></option>
                                    <option value="doctor"><?php echo $lang['doctor']; ?></option>
                                </select>
                            </div>
                            <div class="form-group form-half">
                                <label for="department"><?php echo $lang['department']; ?></label>
                                <input type="text" id="department" name="department" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group form-full">
                                <label for="phone"><?php echo $lang['phone']; ?></label>
                                <input type="text" id="phone" name="phone" class="form-control">
                            </div>
                        </div>
                        
                        <button type="submit" name="add_user" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo $lang['add_new_user']; ?>
                        </button>
                    </form>
                </section>
                
                <!-- جدول المستخدمين -->
                <section class="table-section">
                    <h2><?php echo $lang['user_list']; ?></h2>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><?php echo $lang['username']; ?></th>
                                    <th><?php echo $lang['full_name']; ?></th>
                                    <th><?php echo $lang['email']; ?></th>
                                    <th><?php echo $lang['role']; ?></th>
                                    <th><?php echo $lang['department']; ?></th>
                                    <th><?php echo $lang['status']; ?></th>
                                    <th><?php echo $lang['actions']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo escape($user['username']); ?></td>
                                        <td><?php echo escape($user['full_name']); ?></td>
                                        <td><?php echo escape($user['email']); ?></td>
                                        <td>
                                            <span class="badge badge-role">
                                                <?php 
                                                $roles = [
                                                    'admin' => $lang['admin'],
                                                    'pharmacist' => $lang['pharmacist'],
                                                    'purchasing' => $_SESSION['lang'] === 'ar' ? 'مشتريات' : 'Purchasing',
                                                    'doctor' => $lang['doctor']
                                                ];
                                                echo $roles[$user['role']] ?? $user['role'];
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo escape($user['department'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $user['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                                <?php echo $user['is_active'] ? $lang['active'] : $lang['inactive']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-icon btn-edit" title="<?php echo $lang['edit']; ?>" onclick="openEditModal(<?php echo $user['user_id']; ?>, '<?php echo escape(json_encode($user)); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
    
    <!-- نافذة منبثقة (Modal) لتعديل المستخدم -->
    <div id="edit-user-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $lang['edit_user']; ?></h2>
                <button class="modal-close" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-user-form">
                    <input type="hidden" id="edit-user-id" name="user_id">
                    
                    <div class="form-row">
                        <div class="form-group form-half">
                            <label for="edit-full-name"><?php echo $lang['full_name']; ?> <span class="required"><?php echo $lang['required_field']; ?></span></label>
                            <input type="text" id="edit-full-name" name="full_name" class="form-control" required>
                        </div>
                        <div class="form-group form-half">
                            <label for="edit-email"><?php echo $lang['email']; ?> <span class="required"><?php echo $lang['required_field']; ?></span></label>
                            <input type="email" id="edit-email" name="email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-half">
                            <label for="edit-role"><?php echo $lang['role']; ?> <span class="required"><?php echo $lang['required_field']; ?></span></label>
                            <select id="edit-role" name="role" class="form-control" required>
                                <option value="admin"><?php echo $lang['admin']; ?></option>
                                <option value="pharmacist"><?php echo $lang['pharmacist']; ?></option>
                                <option value="purchasing"><?php echo $_SESSION['lang'] === 'ar' ? 'مسؤول مشتريات' : 'Purchasing Manager'; ?></option>
                                <option value="doctor"><?php echo $lang['doctor']; ?></option>
                            </select>
                        </div>
                        <div class="form-group form-half">
                            <label for="edit-department"><?php echo $lang['department']; ?></label>
                            <input type="text" id="edit-department" name="department" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-full">
                            <label for="edit-phone"><?php echo $lang['phone']; ?></label>
                            <input type="text" id="edit-phone" name="phone" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-full">
                            <label for="edit-password"><?php echo $lang['new_password_optional']; ?></label>
                            <input type="password" id="edit-password" name="password" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group form-full">
                            <label for="edit-is-active">
                                <input type="checkbox" id="edit-is-active" name="is_active" value="1">
                                <span><?php echo $lang['active']; ?></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()"><?php echo $lang['cancel']; ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo $lang['save_changes']; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function openEditModal(userId, userData) {
            const user = JSON.parse(userData.replace(/&quot;/g, '"'));
            
            document.getElementById('edit-user-id').value = userId;
            document.getElementById('edit-full-name').value = user.full_name;
            document.getElementById('edit-email').value = user.email;
            document.getElementById('edit-role').value = user.role;
            document.getElementById('edit-department').value = user.department || '';
            document.getElementById('edit-phone').value = user.phone || '';
            document.getElementById('edit-is-active').checked = user.is_active == 1;
            
            document.getElementById('edit-user-modal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('edit-user-modal').style.display = 'none';
        }
        
        document.getElementById('edit-user-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                user_id: document.getElementById('edit-user-id').value,
                full_name: document.getElementById('edit-full-name').value,
                email: document.getElementById('edit-email').value,
                role: document.getElementById('edit-role').value,
                department: document.getElementById('edit-department').value,
                phone: document.getElementById('edit-phone').value,
                password: document.getElementById('edit-password').value,
                is_active: document.getElementById('edit-is-active').checked ? 1 : 0
            };
            
            fetch('api/update-user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo $lang['user_updated']; ?>');
                    closeEditModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => console.error('Error:', error));
        });
        
        document.getElementById('edit-user-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
    
    <script src="js/main.js"></script>
</body>
</html>
