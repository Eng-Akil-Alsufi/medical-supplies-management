<?php
// =====================================================
// صفحة إدارة المخزون
// =====================================================

// التحقق من الصلاحيات
if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin', 'pharmacist', 'purchasing'])) {
    header('Location: index.php?page=dashboard');
    exit();
}

// الحصول على قائمة العناصر
$stmt = $pdo->prepare("
    SELECT i.*, s.name as supplier_name,
    COALESCE(
        ROUND(
            (SELECT SUM(quantity) FROM transactions 
             WHERE item_id = i.item_id 
             AND transaction_type = 'dispensing' 
             AND transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) / 30, 2
        ), 0
    ) as daily_consumption_rate,
    CASE 
        WHEN i.quantity > 0 
        THEN FLOOR(i.quantity / COALESCE(
            (SELECT SUM(quantity) FROM transactions 
             WHERE item_id = i.item_id 
             AND transaction_type = 'dispensing' 
             AND transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) / 30, 0.1
        ))
        ELSE 0
    END as days_until_empty
    FROM items i
    LEFT JOIN suppliers s ON i.supplier_id = s.supplier_id
    WHERE i.is_active = 1
    ORDER BY i.name
");
$stmt->execute();
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo $_SESSION['lang'] === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['inventory_management']; ?> - <?php echo $lang['site_name']; ?></title>
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
                        <?php if (in_array($_SESSION['role'], ['admin', 'purchasing'])): ?>
                            <li><a href="index.php?page=suppliers"><i class="fas fa-truck"></i> <?php echo $lang['suppliers']; ?></a></li>
                        <?php endif; ?>
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
                <div class="page-header">
                    <h1><?php echo $lang['inventory_management']; ?></h1>
                    <?php if (in_array($_SESSION['role'], ['admin', 'pharmacist'])): ?>
                        <a href="index.php?page=add-item" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo $lang['add_new_item']; ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- جدول العناصر -->
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $lang['item']; ?> - الاسم</th>
                                <th><?php echo $lang['batch_number']; ?></th>
                                <th><?php echo $lang['current_quantity']; ?></th>
                                <th><?php echo $lang['minimum_quantity']; ?></th>
                                <th><?php echo $lang['daily_consumption']; ?></th>
                                <th><?php echo $lang['days_remaining']; ?></th>
                                <th><?php echo $lang['expiry_date']; ?></th>
                                <th><?php echo $lang['location']; ?></th>
                                <th><?php echo $lang['supplier']; ?></th>
                                <th><?php echo $lang['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr class="<?php echo $item['quantity'] <= $item['min_quantity'] ? 'row-warning' : ''; ?>">
                                        <td><?php echo escape($item['name']); ?></td>
                                        <td><?php echo escape($item['batch_number']); ?></td>
                                        <td>
                                            <span class="quantity-badge <?php echo $item['quantity'] <= $item['min_quantity'] ? 'low-stock' : 'ok'; ?>">
                                                <?php echo $item['quantity']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $item['min_quantity']; ?></td>
                                        <td><?php echo $item['daily_consumption_rate']; ?></td>
                                        <td>
                                            <?php 
                                            if ($item['daily_consumption_rate'] > 0) {
                                                echo floor($item['quantity'] / $item['daily_consumption_rate']);
                                            } else {
                                                echo '∞';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $daysUntilExpiry = (strtotime($item['expiry_date']) - time()) / (60 * 60 * 24);
                                            $statusClass = $daysUntilExpiry <= 60 ? 'expiry-warning' : 'ok';
                                            ?>
                                            <span class="expiry-badge <?php echo $statusClass; ?>">
                                                <?php echo date('d/m/Y', strtotime($item['expiry_date'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo escape($item['location']); ?></td>
                                        <td><?php echo escape($item['supplier_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if (in_array($_SESSION['role'], ['admin', 'pharmacist'])): ?>
                                                    <a href="index.php?page=edit-item&id=<?php echo $item['item_id']; ?>" class="btn-icon btn-edit" title="<?php echo $lang['edit']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button onclick="deleteItem(<?php echo $item['item_id']; ?>)" class="btn-icon btn-delete" title="<?php echo $lang['delete']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center"><?php echo $lang['no_items']; ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function deleteItem(itemId) {
            if (confirm('<?php echo $lang['confirm_delete']; ?>')) {
                window.location.href = `api/delete-item.php?id=${itemId}`;
            }
        }
    </script>
    <script src="js/main.js"></script>
</body>
</html>
