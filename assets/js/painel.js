document.addEventListener('DOMContentLoaded', function() {
    initializePainel();
});

function initializePainel() {
    // Initialize sidebar
    initializeSidebar();
    
    // Initialize modals
    initializeModals();
    
    // Initialize data tables
    initializeDataTables();
    
    // Initialize forms
    initializeForms();
    
    // Initialize real-time updates
    initializeRealTime();
}

function initializeSidebar() {
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    const currentPath = window.location.pathname;
    
    menuItems.forEach(item => {
        if (item.getAttribute('href') === currentPath) {
            item.classList.add('active');
        }
    });
}

function initializeModals() {
    // Modal triggers
    const modalTriggers = document.querySelectorAll('[data-modal]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            openModal(modalId);
        });
    });
    
    // Modal close buttons
    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
}

function initializeDataTables() {
    const tables = document.querySelectorAll('.data-table');
    tables.forEach(table => {
        // Add sorting functionality
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, this.getAttribute('data-sort'));
            });
        });
        
        // Add search functionality
        const searchInput = table.parentElement.querySelector('.table-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterTable(table, this.value);
            });
        }
    });
}

function initializeForms() {
    const forms = document.querySelectorAll('.ajax-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAjaxForm(this);
        });
    });
}

function initializeRealTime() {
    // Check for real-time updates every 30 seconds
    setInterval(checkRealTimeUpdates, 30000);
}

function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const columnIndex = getColumnIndex(table, column);
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        return aValue.localeCompare(bValue);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

function filterTable(table, searchTerm) {
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        row.style.display = matches ? '' : 'none';
    });
}

function getColumnIndex(table, columnName) {
    const headers = table.querySelectorAll('th');
    for (let i = 0; i < headers.length; i++) {
        if (headers[i].getAttribute('data-sort') === columnName) {
            return i;
        }
    }
    return 0;
}

function submitAjaxForm(form) {
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.textContent = 'Enviando...';
    
    fetch(form.action, {
        method: form.method,
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message || 'Operação realizada com sucesso!', 'success');
            
            // Close modal if form is in one
            const modal = form.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
            
            // Reload page or update content
            if (data.reload) {
                setTimeout(() => location.reload(), 1500);
            } else if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1500);
            }
        } else {
            showAlert(data.message || 'Ocorreu um erro. Tente novamente.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Ocorreu um erro. Tente novamente.', 'error');
    })
    .finally(() => {
        // Restore button state
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
}

function checkRealTimeUpdates() {
    // Check for new notifications
    fetch('/api/realtime.php?action=notifications')
        .then(response => response.json())
        .then(data => {
            if (data.notifications && data.notifications.length > 0) {
                updateNotifications(data.notifications);
            }
        })
        .catch(error => console.error('Error checking updates:', error));
}

function updateNotifications(notifications) {
    const notificationCount = document.querySelector('.notification-count');
    const notificationList = document.querySelector('.notification-list');
    
    if (notificationCount) {
        const count = notifications.length;
        notificationCount.textContent = count;
        notificationCount.style.display = count > 0 ? 'inline-block' : 'none';
    }
    
    if (notificationList) {
        notificationList.innerHTML = '';
        notifications.forEach(notification => {
            const item = document.createElement('div');
            item.className = 'notification-item';
            item.innerHTML = `
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${formatDateTime(notification.created_at)}</div>
            `;
            notificationList.appendChild(item);
        });
    }
}

// Export functions for use in other scripts
window.PainelUtils = {
    sortTable,
    filterTable,
    submitAjaxForm,
    openModal,
    closeModal,
    showAlert
};
