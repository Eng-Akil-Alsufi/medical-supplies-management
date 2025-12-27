// =====================================================
// نظام إدارة المخزون الطبي - سكريبتات JavaScript
// =====================================================

// دالة إظهار رسالة تأكيد عند الحذف
function confirmDelete(message = 'هل تؤكد حذف هذا العنصر؟') {
    return confirm(message);
}

// دالة إظهار/إخفاء المزيد من الخيارات
function toggleMenu(element) {
    element.classList.toggle('active');
}

// دالة للمراقبة والتحقق من صحة النموذج
function validateForm(form) {
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (input.value.trim() === '') {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// دالة لإزالة الإشعارات القديمة
function removeNotification(element) {
    element.style.transition = 'all 0.3s ease';
    element.style.opacity = '0';
    element.style.marginTop = '-100px';
    
    setTimeout(() => {
        element.remove();
    }, 300);
}

// دالة لتنسيق التاريخ
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('ar-SA', options);
}

// دالة لحساب الأيام المتبقية
function calculateDaysRemaining(dateString) {
    const today = new Date();
    const expiryDate = new Date(dateString);
    const timeDiff = expiryDate - today;
    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
    
    return daysDiff;
}

// دالة للتحقق من حالة المخزون
function checkStockStatus(quantity, minQuantity) {
    if (quantity <= minQuantity) {
        return 'low-stock';
    } else if (quantity <= minQuantity * 2) {
        return 'warning-stock';
    } else {
        return 'ok-stock';
    }
}

// دالة لإدارة النوافذ المنبثقة (Modals)
// دالة فتح نافذة منبثقة
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('active');
    }
}

// دالة إغلاق نافذة منبثقة
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
}

function populateForm(formId, data) {
    const form = document.getElementById(formId);
    if (form) {
        Object.keys(data).forEach(key => {
            const field = form.elements[key];
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = data[key];
                } else {
                    field.value = data[key];
                }
            }
        });
    }
}

function sendFormData(url, formData, onSuccess, onError) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.success !== false) {
            if (onSuccess) onSuccess(data);
        } else {
            if (onError) onError(data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (onError) onError(error);
    });
}

// إضافة مستمعات الأحداث عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // مستمع لأزرار الإغلاق على الإنذارات
    const closeButtons = document.querySelectorAll('.alert .close-btn');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            removeNotification(this.parentElement);
        });
    });
    
    // مستمع للنماذج
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // يمكن إضافة التحقق من الصحة هنا
        });
    });
    
    // تفعيل تأثيرات الهوفر
    const buttons = document.querySelectorAll('.btn, .btn-icon');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
});

// دالة لتحديث الوقت الفعلي
setInterval(function() {
    const timeElement = document.querySelector('[data-current-time]');
    if (timeElement) {
        const now = new Date();
        timeElement.textContent = now.toLocaleTimeString('ar-SA');
    }
}, 1000);
