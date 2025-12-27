<?php
// =====================================================
// صفحة إدارة الطلبات
// =====================================================

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit();
}

// معالجة الموافقة أو الرفض
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'pharmacist') {
    $request_id = $_POST['request_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $quantity_approved = $_POST['quantity_approved'] ?? 0;
    
    if ($action === 'approve' && $request_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM requests WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();
        
        if ($request) {
            $stmt = $pdo->prepare("
                UPDATE requests 
                SET status = 'approved', quantity_approved = ?, approved_by = ?, approval_date = NOW()
                WHERE request_id = ?
            ");
            $stmt->execute([$quantity_approved, $_SESSION['user_id'], $request_id]);
            
            logAction($_SESSION['user_id'], 'موافقة على طلب', "رقم الطلب: $request_id");
        }
    } elseif ($action === 'reject' && $request_id > 0) {
        $stmt = $pdo->prepare("
            UPDATE requests 
            SET status = 'rejected', approved_by = ?, approval_date = NOW()
            WHERE request_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $request_id]);
        
        logAction($_SESSION['user_id'], 'رفض طلب', "رقم الطلب: $request_id");
    }
}

// جلب الطلبات حسب الدور
$query = "
    SELECT r.*, i.name as item_name, i.quantity as current_quantity,
    u.full_name as requested_by_name, a.full_name as approved_by_name
    FROM requests r
    JOIN items i ON r.item_id = i.item_id
    JOIN users u ON r.requested_by = u.user_id
    LEFT JOIN users a ON r.approved_by = a.user_id
";

if ($_SESSION['role'] === 'doctor') {
    $query .= " WHERE r.requested_by = " . $_SESSION['user_id'];
}

$query .= " ORDER BY r.requested_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$requests = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_request']) && $_SESSION['role'] === 'doctor') {
    $item_id = $_POST['item_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;
    
    if ($item_id > 0 && $quantity > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO requests (item_id, quantity_requested, requested_by, status)
            VALUES (?, ?, ?, 'pending')
        ");
        $stmt->execute([$item_id, $quantity, $_SESSION['user_id']]);
        
        $success = $lang['request_created'];
    } else {
        $error = $lang['fill_all_fields'];
    }
}

$stmt = $pdo->prepare("SELECT * FROM items WHERE is_active = 1 AND quantity > 0");
$stmt->execute();
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo $_SESSION['lang'] === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['requests']; ?> - <?php echo $lang['site_name']; ?></title>
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
                <h1><?php echo $lang['requests_management']; ?></h1>
                
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
                
                <!-- نموذج إنشاء طلب جديد (للأطباء فقط) -->
                <?php if ($_SESSION['role'] === 'doctor'): ?>
                    <section class="form-section">
                        <h2><?php echo $lang['create_new_request']; ?></h2>
                        <form method="POST" class="form-inline">
                            <div class="form-group">
                                <label for="item_id"><?php echo $lang['item']; ?>:</label>
                                <select id="item_id" name="item_id" class="form-control" required>
                                    <option value=""><?php echo $lang['choose_item']; ?></option>
                                    <?php foreach ($items as $item): ?>
                                        <option value="<?php echo $item['item_id']; ?>">
                                            <?php echo escape($item['name']) . ' (' . $lang['available'] . ' ' . $item['quantity'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantity"><?php echo $lang['quantity']; ?>:</label>
                                <input type="number" id="quantity" name="quantity" class="form-control" min="1" required>
                            </div>
                            <button type="submit" name="create_request" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <?php echo $lang['create_request']; ?>
                            </button>
                        </form>
                    </section>
                <?php endif; ?>
                
                <!-- جدول الطلبات -->
                <section class="table-section">
                    <h2><?php echo $lang['request_list']; ?></h2>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><?php echo $lang['request_number']; ?></th>
                                    <th><?php echo $lang['item']; ?></th>
                                    <th><?php echo $lang['requested_quantity']; ?></th>
                                    <th><?php echo $lang['approved_quantity']; ?></th>
                                    <th><?php echo $lang['status']; ?></th>
                                    <th><?php echo $lang['requested_by']; ?></th>
                                    <th><?php echo $lang['request_date']; ?></th>
                                    <?php if ($_SESSION['role'] === 'pharmacist'): ?>
                                        <th><?php echo $lang['actions']; ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($requests)): ?>
                                    <?php foreach ($requests as $request): ?>
                                        <tr>
                                            <td><?php echo $request['request_id']; ?></td>
                                            <td><?php echo escape($request['item_name']); ?></td>
                                            <td><?php echo $request['quantity_requested']; ?></td>
                                            <td><?php echo $request['quantity_approved'] ?: '-'; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $request['status']; ?>">
                                                    <?php 
                                                    $statuses = [
                                                        'pending' => $lang['pending'],
                                                        'approved' => $lang['approved'],
                                                        'rejected' => $lang['rejected'],
                                                        'fulfilled' => $lang['fulfilled']
                                                    ];
                                                    echo $statuses[$request['status']] ?? $request['status'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo escape($request['requested_by_name']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($request['requested_date'])); ?></td>
                                            <?php if ($_SESSION['role'] === 'pharmacist' && $request['status'] === 'pending'): ?>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                                        <input type="hidden" name="quantity_approved" value="<?php echo $request['quantity_requested']; ?>">
                                                        <button type="submit" name="action" value="approve" class="btn-icon btn-approve" title="<?php echo $lang['approved']; ?>">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                                        <button type="submit" name="action" value="reject" class="btn-icon btn-reject" title="<?php echo $lang['rejected']; ?>">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center"><?php echo $lang['no_requests']; ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
