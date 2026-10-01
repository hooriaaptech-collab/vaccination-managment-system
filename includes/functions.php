<?php
/**
 * Common Helper Functions
 * E-Vaccination Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize User Input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Set a Flash Message (for alerts/notifications)
 * Types: success, danger, warning, info
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display Flash Message if present and clear it
 */
function display_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        $type = $flash['type'];
        $message = $flash['message'];
        
        $icon = 'bi-info-circle-fill';
        if ($type === 'success') $icon = 'bi-check-circle-fill';
        if ($type === 'danger') $icon = 'bi-exclamation-triangle-fill';
        if ($type === 'warning') $icon = 'bi-exclamation-circle-fill';
        
        echo '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show custom-alert shadow-sm" role="alert">
                <i class="bi ' . $icon . ' me-2"></i> ' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['flash']);
    }
}

/**
 * Format Date to standard readable format (e.g. 15 Oct, 2026)
 */
function format_date($date_string) {
    if (empty($date_string) || $date_string === '0000-00-00') {
        return 'N/A';
    }
    return date('d M, Y', strtotime($date_string));
}

/**
 * Calculate Age from Date of Birth
 */
function calculate_age($dob) {
    if (empty($dob)) return 'N/A';
    
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $diff = $today->diff($birthDate);
    
    if ($diff->y > 0) {
        return $diff->y . ' yr' . ($diff->y > 1 ? 's' : '') . ($diff->m > 0 ? ', ' . $diff->m . ' mo' : '');
    } elseif ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ($diff->d > 0 ? ', ' . $diff->d . ' d' : '');
    } else {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
    }
}

/**
 * Generate Status Badge HTML
 */
function status_badge($status) {
    $status = trim($status);
    $class = 'badge ';
    $icon = '';
    
    switch (strtolower($status)) {
        case 'active':
        case 'available':
        case 'approved':
        case 'completed':
        case 'vaccinated':
            $class .= 'bg-success-subtle text-success border border-success-subtle';
            $icon = '<i class="bi bi-check-circle me-1"></i>';
            break;
            
        case 'pending':
        case 'unread':
            $class .= 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
            $icon = '<i class="bi bi-clock-history me-1"></i>';
            break;
            
        case 'inactive':
        case 'unavailable':
        case 'rejected':
        case 'cancelled':
        case 'not vaccinated':
            $class .= 'bg-danger-subtle text-danger border border-danger-subtle';
            $icon = '<i class="bi bi-x-circle me-1"></i>';
            break;
            
        default:
            $class .= 'bg-secondary-subtle text-secondary border border-secondary-subtle';
            $icon = '<i class="bi bi-dot"></i>';
            break;
    }
    
    return '<span class="' . $class . ' px-2.5 py-1.5 rounded-pill fw-medium d-inline-flex align-items-center">' . $icon . htmlspecialchars($status) . '</span>';
}

/**
 * Generate Unique Booking Code
 */
function generate_booking_code() {
    return 'EVAC-' . date('Y') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
}

/**
 * Generate Certificate Verification Code
 */
function generate_cert_code($child_name) {
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $child_name), 0, 3));
    if (strlen($prefix) < 3) $prefix = 'VAC';
    return 'CERT-' . $prefix . '-' . date('Y') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
}

/**
 * Get Base URL path
 */
function base_url($path = '') {
    // Project root relative to document root
    $root = '/09c/vcaccination-managment-system/';
    return $root . ltrim($path, '/');
}
?>

