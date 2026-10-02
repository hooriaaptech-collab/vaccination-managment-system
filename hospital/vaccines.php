<?php
/**
 * Hospital Vaccine Inventory & Stock Availability Management
 * E-Vaccination Management System
 */
$page_title = 'Vaccine Stock';
$page_header = 'Vaccine Inventory & Availability';
$page_subheader = 'Manage and update real-time vaccine stock availability at your healthcare center';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('hospital');

$user_id = current_user_id();
$h_stmt = $pdo->prepare("SELECT id, hospital_name FROM hospitals WHERE user_id = ? LIMIT 1");
$h_stmt->execute([$user_id]);
$hospital = $h_stmt->fetch();
$hospital_id = $hospital['id'];

// Handle Toggle Availability
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $vaccine_id = intval($_POST['vaccine_id'] ?? 0);
    $current_status = sanitize($_POST['current_status'] ?? 'Available');

    if ($vaccine_id > 0) {
        $new_status = ($current_status === 'Available') ? 'Unavailable' : 'Available';

        // Upsert into hospital_vaccines
        $stmt = $pdo->prepare("
            INSERT INTO hospital_vaccines (hospital_id, vaccine_id, status) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE status = VALUES(status)
        ");
        if ($stmt->execute([$hospital_id, $vaccine_id, $new_status])) {
            set_flash('info', 'Vaccine stock status updated to ' . $new_status . '.');
        }
        header("Location: vaccines.php");
        exit();
    }
}

// Fetch all standard vaccines joined with this hospital's specific stock status
$query = "
    SELECT v.*, COALESCE(hv.status, 'Available') AS hospital_stock_status
    FROM vaccines v
    LEFT JOIN hospital_vaccines hv ON v.id = hv.vaccine_id AND hv.hospital_id = ?
    ORDER BY v.id ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$hospital_id]);
$vaccines = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold font-outfit text-dark mb-0">Clinic Vaccine Stock Status</h5>
                    <span class="text-muted small">Parents can view stock availability before booking appointments at <strong><?php echo htmlspecialchars($hospital['hospital_name']); ?></strong></span>
                </div>
                <div style="max-width: 260px;">
                    <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Search vaccine stock...">
                </div>
            </div>

            <!-- Vaccines Inventory Table Card -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Vaccine & Short Code</th>
                                <th>Target Disease</th>
                                <th>Recommended Age</th>
                                <th>Global Catalog Status</th>
                                <th>Center Stock Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vaccines as $vac): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-teal border fw-bold"><?php echo htmlspecialchars($vac['short_code']); ?></span>
                                        <div class="fw-bold text-dark mt-1"><?php echo htmlspecialchars($vac['vaccine_name']); ?></div>
                                    </td>
                                    <td><span class="small text-muted"><?php echo htmlspecialchars($vac['target_disease']); ?></span></td>
                                    <td><span class="badge bg-emerald-subtle text-success small"><?php echo htmlspecialchars($vac['recommended_age']); ?></span></td>
                                    <td><?php echo status_badge($vac['status']); ?></td>
                                    <td>
                                        <?php if ($vac['hospital_stock_status'] === 'Available'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-medium">
                                                <i class="bi bi-check-circle-fill me-1"></i> In Stock (Available)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill fw-medium">
                                                <i class="bi bi-x-circle-fill me-1"></i> Out of Stock
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="vaccines.php" method="POST">
                                            <input type="hidden" name="action" value="toggle_stock">
                                            <input type="hidden" name="vaccine_id" value="<?php echo $vac['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $vac['hospital_stock_status']; ?>">
                                            <?php if ($vac['hospital_stock_status'] === 'Available'): ?>
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2.5" title="Mark Out of Stock">
                                                    <i class="bi bi-dash-circle me-1"></i> Set Out of Stock
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-emerald btn-sm py-1 px-2.5" title="Mark Available">
                                                    <i class="bi bi-check-circle me-1"></i> Set In Stock
                                                </button>
                                            <?php endif; ?>
                                        </form>
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



