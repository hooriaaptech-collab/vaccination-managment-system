<?php
/**
 * Hospital Administered Vaccination Records Log
 * E-Vaccination Management System
 */
$page_title = 'Administered Records';
$page_header = 'Administered Vaccination Records Log';
$page_subheader = 'Historical record of all immunization shots administered at this medical center';
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

// Fetch all vaccination records for this hospital
$query = "
    SELECT vr.*, c.name AS child_name, c.dob AS child_dob, c.gender AS child_gender,
           u.name AS parent_name, u.phone AS parent_phone,
           v.vaccine_name, v.short_code, v.target_disease
    FROM vaccination_records vr
    JOIN children c ON vr.child_id = c.id
    JOIN users u ON c.parent_id = u.id
    JOIN vaccines v ON vr.vaccine_id = v.id
    WHERE vr.hospital_id = ?
    ORDER BY vr.vaccination_date DESC, vr.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$hospital_id]);
$records = $stmt->fetchAll();

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
                    <h5 class="fw-bold font-outfit text-dark mb-0">Total Immunization Logs (<?php echo count($records); ?>)</h5>
                    <span class="text-muted small">Official clinical audit log of administered childhood doses</span>
                </div>
                <div class="d-flex gap-2">
                    <div style="max-width: 250px;">
                        <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Search logs...">
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer-fill me-1"></i> Print Log
                    </button>
                </div>
            </div>

            <!-- Records Table Card -->
            <div class="table-card print-area">
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Administered Date</th>
                                <th>Child & Parent Info</th>
                                <th>Vaccine & Dose</th>
                                <th>Batch / Clinician</th>
                                <th>Status</th>
                                <th>Certificate Verification Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($records)): ?>
                                <?php foreach ($records as $r): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-dark"><?php echo format_date($r['vaccination_date']); ?></strong>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($r['child_name']); ?></div>
                                            <span class="small text-muted">Parent: <?php echo htmlspecialchars($r['parent_name']); ?> (<?php echo htmlspecialchars($r['parent_phone']); ?>)</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-emerald-subtle text-success"><?php echo htmlspecialchars($r['short_code']); ?> (Dose #<?php echo $r['dose_number']; ?>)</span>
                                            <div class="text-muted small"><?php echo htmlspecialchars($r['vaccine_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small text-dark"><?php echo htmlspecialchars($r['batch_number'] ?: 'Standard'); ?></div>
                                            <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($r['administered_by'] ?: 'Pediatric Staff'); ?></div>
                                        </td>
                                        <td>
                                            <?php echo status_badge($r['status']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace small">
                                                <?php echo htmlspecialchars($r['certificate_code']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-journal-medical fs-3 d-block mb-2"></i>
                                        No administered vaccination records logged yet at this center.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>



