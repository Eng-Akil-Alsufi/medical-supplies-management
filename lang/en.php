<?php
// =====================================================
// English Language File
// =====================================================

$lang = [
    // General messages
    'site_name' => 'Medical Inventory Management System',
    'site_slogan' => 'Drugs and Medical Supplies',
    'site_title' => 'Medical Inventory Management System', // إضافة المفاتيح المفقودة
    
    // Main menu
    'home' => 'Home',
    'inventory' => 'Inventory',
    'requests' => 'Requests',
    'reports' => 'Reports',
    'users' => 'Users',
    'suppliers' => 'Suppliers',
    'logout' => 'Logout',
    'language' => 'Language',
    'english' => 'English',
    'arabic' => 'العربية',
    
    // Login page
    'login_title' => 'Login',
    'username_label' => 'Username',
    'username_placeholder' => 'Enter username',
    'password_label' => 'Password',
    'password_placeholder' => 'Enter password',
    'login_button' => 'Login',
    'login_error' => 'Invalid login credentials',
    'empty_fields_error' => 'Please enter username and password',
    'test_credentials' => 'Test Credentials:',
    'admin' => 'System Administrator',
    'pharmacist' => 'Pharmacist',
    'doctor' => 'Doctor',
    
    // Dashboard
    'dashboard_title' => 'Dashboard',
    'total_items' => 'Total Items',
    'low_stock' => 'Low Stock',
    'expiry_warning' => 'Expiry Warning',
    'pending_requests' => 'Pending Requests',
    'new_alerts' => 'New Alerts',
    'recent_transactions' => 'Recent Transactions',
    'view_more' => 'View More',
    'item' => 'Item',
    'transaction_type' => 'Transaction Type',
    'quantity' => 'Quantity',
    'user' => 'User',
    'date_time' => 'Date & Time',
    'all_notifications' => 'All Notifications',
    'no_notifications' => 'No notifications',
    'no_transactions' => 'No transactions',
    
    // Transaction types
    'delivery' => 'Delivery',
    'receipt' => 'Receipt',
    'dispensing' => 'Dispensing',
    'adjustment' => 'Adjustment',
    
    // Inventory management
    'inventory_management' => 'Inventory Management',
    'add_new_item' => 'Add New Item',
    'batch_number' => 'Batch Number',
    'current_quantity' => 'Current Quantity',
    'minimum_quantity' => 'Minimum Quantity',
    'daily_consumption' => 'Daily Consumption Rate',
    'days_remaining' => 'Days Remaining',
    'expiry_date' => 'Expiry Date',
    'location' => 'Location',
    'supplier' => 'Supplier',
    'actions' => 'Actions',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'confirm_delete' => 'Are you sure you want to delete this item?',
    'no_items' => 'No items found',
    
    // Requests
    'requests_management' => 'Requests Management',
    'create_new_request' => 'Create New Request',
    'request_number' => 'Request Number',
    'requested_quantity' => 'Requested Quantity',
    'approved_quantity' => 'Approved Quantity',
    'status' => 'Status',
    'request_date' => 'Request Date',
    'requested_by' => 'Requested By',
    'create_request' => 'Create Request',
    'request_list' => 'Requests List',
    'no_requests' => 'No requests found',
    'available' => 'Available:',
    'choose_item' => '-- Choose an item --',
    'request_created' => 'Request created successfully',
    'pending' => 'Pending',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
    'fulfilled' => 'Fulfilled',
    
    // Users
    'user_management' => 'User Management',
    'add_new_user' => 'Add New User',
    'username' => 'Username',
    'password' => 'Password',
    'email' => 'Email',
    'full_name' => 'Full Name',
    'role' => 'Role',
    'department' => 'Department',
    'phone' => 'Phone Number', // إضافة المفاتيح المفقودة
    'address' => 'Address', // إضافة المفاتيح المفقودة
    'status' => 'Status',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'user_added' => 'User added successfully',
    'user_exists_error' => 'Error: Username or email may already exist',
    'user_list' => 'Users List',
    'edit_user' => 'Edit User',
    'new_password_optional' => 'New Password (optional - leave empty to keep current)',
    'save_changes' => 'Save Changes',
    'cancel' => 'Cancel',
    'user_updated' => 'User updated successfully',
    'required_field' => '*',
    
    // Suppliers
    'supplier_management' => 'Supplier Management',
    'add_supplier' => 'Add Supplier',
    'supplier_name' => 'Supplier Name',
    'contact_person' => 'Contact Person',
    'supplier_phone' => 'Phone Number', // إضافة المفاتيح المفقودة
    'supplier_email' => 'Email Address', // إضافة المفاتيح المفقودة
    'supplier_address' => 'Address', // إضافة المفاتيح المفقودة
    'city' => 'City',
    'country' => 'Country',
    'supplier_added' => 'Supplier added successfully',
    'supplier_updated' => 'Supplier updated successfully',
    'supplier_name_required' => 'Please enter supplier name',
    'supplier_add_error' => 'Error adding supplier',
    'supplier_update_error' => 'Error updating supplier data',
    'add_new_supplier' => 'Add New Supplier',
    'edit_supplier_modal' => 'Edit Supplier Data',
    'suppliers_list' => 'Suppliers List',
    'no_suppliers' => 'No suppliers found',
    'confirm_delete_supplier' => 'Are you sure you want to delete this supplier?',
    'save_changes' => 'Save Changes',
    'cancel' => 'Cancel',
    
    // Reports
    'reports' => 'Reports',
    'export_pdf' => 'Export PDF',
    'most_used_items' => 'Most Used Medicines',
    'expiry_items' => 'Items Near Expiry',
    'transactions_report' => 'Transactions Report',
    'most_used_drugs_title' => 'Most Used Medicines and Items (Last 30 Days)',
    'usage_count' => 'Usage Count',
    'total_quantity_dispensed' => 'Total Quantity Dispensed',
    'depletion_items_title' => 'Items Near Depletion',
    'total_requests' => 'Total Requests',
    'fulfilled_count' => 'Fulfilled Requests',
    'pending_count' => 'Pending Requests',
    'unspecified' => 'Unspecified',
    'transactions_title' => 'Transactions Report (Last 30 Days)',
    'transaction_date' => 'Date',
    'transaction_count' => 'Count',
    'total_quantity' => 'Total Quantity',
    'export_pdf_button' => 'Export Selected Reports to PDF',
    'select_reports' => 'Export Reports to PDF',
    'report_most_used' => 'Most Used Medicines and Items',
    'report_depletion' => 'Items Near Depletion',
    'report_departments' => 'Department Requests Analysis',
    'report_transactions' => 'Transactions Report',
    'export_selected' => 'Export Selected',
    'no_data' => 'No data available',
    'department_requests_title' => 'Department Requests Analysis (Last 30 Days)', // إضافة المفاتيح المفقودة
    
    // Notifications and Alerts
    'low_stock_alert' => 'Low Stock',
    'expiry_warning_alert' => 'Expiry Warning',
    'request_update_alert' => 'Request Update',
    'system_alert' => 'System Alert',
    'stock_low_item' => 'Low stock for item: ',
    'expiry_near_item' => 'Expiry warning for item: ',
    'request_approved' => 'Request approved',
    'request_rejected' => 'Request rejected',
    'notifications' => 'notifications', // إضافة المفاتيح المفقودة
    'days_until_expiry' => 'Days Until Expiry', // تصحيح المفاتيح المفقودة
];
?>
