/**
 * Dashboard JavaScript System
 * Fully Responsive Sidebar Drawer & Interactive UI Controls
 * E-Vaccination Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Sidebar Drawer & Backdrop Toggle
    const sidebar = document.getElementById('dashboardSidebar');
    const toggleBtn = document.querySelector('.topbar-toggle');
    const closeBtn = document.getElementById('sidebarCloseBtn');
    const backdrop = document.getElementById('sidebarBackdrop');
    
    function openSidebar() {
        if (sidebar) sidebar.classList.add('show');
        if (backdrop) backdrop.classList.add('show');
        document.body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('show');
        if (backdrop) backdrop.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (sidebar && sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeSidebar();
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', function() {
            closeSidebar();
        });
    }

    // Close on window resize if scaled above tablet breakpoint
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    });

    // Generic Table Live Filter
    const tableSearchInput = document.getElementById('tableSearchInput');
    if (tableSearchInput) {
        tableSearchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.custom-table tbody tr');
            
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Auto dismiss flash alerts after 5 seconds
    const alerts = document.querySelectorAll('.custom-alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });
});

/**
 * Universal Confirmation Dialog for Delete Actions
 */
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this record? This action cannot be undone.');
}
