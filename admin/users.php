<?php
/**
 * Admin User Accounts Management
 * E-Vaccination Management System
 */
$page_title = 'User Accounts';
$page_header = 'System User Accounts';
$page_subheader = 'Manage registered parent and hospital accounts and status';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

// Handle Toggle Status / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id > 0 && $user_id !== 1) { // Prevent modifying primary admin
        if ($action === 'toggle_status') {
            $curr = sanitize($_POST['current_status'] ?? 'active');
            $new_status = ($curr === 'active') ? 'inactive' : 'active';
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $user_id]);
            set_flash('info', 'User status updated to ' . $new_status . '.');
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            set_flash('success', 'User account deleted.');
        }
        header("Location: users.php");
        exit();
    }
}

// Role filter
$role_filter = sanitize($_GET['role'] ?? 'all');
$where_clause = "";
$params = [];

if ($role_filter !== 'all') {
    $where_clause = "WHERE role = ?";
    $params[] = $role_filter;
}

$query = "SELECT * FROM users $where_clause ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div class="btn-group shadow-sm bg-white p-1 rounded-3 border">
                    <a href="users.php?role=all" class="btn btn-sm <?php echo ($role_filter === 'all') ? 'btn-emerald' : 'btn-light'; ?>">All Accounts</a>
                    <a href="users.php?role=parent" class="btn btn-sm <?php echo ($role_filter === 'parent') ? 'btn-emerald' : 'btn-light'; ?>">Parents</a>
                    <a href="users.php?role=hospital" class="btn btn-sm <?php echo ($role_filter === 'hospital') ? 'btn-emerald' : 'btn-light'; ?>">Hospitals</a>
                    <a href="users.php?role=admin" class="btn btn-sm <?php echo ($role_filter === 'admin') ? 'btn-emerald' : 'btn-light'; ?>">Admins</a>
                </div>

                <div style="max-width: 250px;">
                    <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Search users...">
                </div>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><strong class="text-dark"><?php echo htmlspecialchars($u['name']); ?></strong></td>
                                    <td><span class="text-muted small"><?php echo htmlspecialchars($u['email']); ?></span></td>
                                    <td><span class="text-dark small"><?php echo htmlspecialchars($u['phone']); ?></span></td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-capitalize"><?php echo htmlspecialchars($u['role']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($u['id'] !== 1): ?>
                                            <form action="users.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $u['status']; ?>">
                                                <button type="submit" class="btn p-0 border-0" title="Click to toggle status">
                                                    <?php echo status_badge($u['status']); ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <?php echo status_badge($u['status']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="text-muted small"><?php echo format_date($u['created_at']); ?></span></td>
                                    <td>
                                        <?php if ($u['id'] !== 1): ?>
                                            <form action="users.php" method="POST" onsubmit="return confirmDelete('Are you sure you want to delete this user?');" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Delete User">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary small">Primary Admin</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>



