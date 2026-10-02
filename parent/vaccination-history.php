<?php
/**
 * Parent Vaccination History & Official Digital Certificate Generator
 * E-Vaccination Management System
 */
$page_title = 'Vaccination History';
$page_header = 'Immunization History & Official Certificates';
$page_subheader = 'View verified childhood vaccination records and generate printable immunization certificates';
$is_dashboard = true;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('parent');

$parent_id = current_user_id();

// Fetch all vaccination records for this parent's children
$query = "
    SELECT vr.*, c.name AS child_name, c.dob AS child_dob, c.gender AS child_gender, c.blood_group,
           u.name AS parent_name,
           h.hospital_name, h.address AS hospital_address, h.city AS hospital_city, h.phone AS hospital_phone,
           v.vaccine_name, v.short_code, v.target_disease
    FROM vaccination_records vr
    JOIN children c ON vr.child_id = c.id
    JOIN users u ON c.parent_id = u.id
    JOIN hospitals h ON vr.hospital_id = h.id
    JOIN vaccines v ON vr.vaccine_id = v.id
    WHERE c.parent_id = ? AND vr.status = 'Vaccinated'
    ORDER BY vr.vaccination_date DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$parent_id]);
$records = $stmt->fetchAll();

// Check if specific certificate requested for direct print
$cert_id = intval($_GET['cert_id'] ?? 0);
$selected_cert = null;
if ($cert_id > 0) {
    foreach ($records as $r) {
        if ($r['id'] == $cert_id) {
            $selected_cert = $r;
            break;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dashboard-main">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-content">
            <?php display_flash(); ?>

            <!-- Modal for Single Certificate View & Print -->
            <?php if ($selected_cert): ?>
                <div class="card-3d p-4 p-md-5 bg-white mb-5 border-teal print-area" style="border: 2px solid var(--teal-main);">
                    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                        <span class="badge bg-success text-white px-3 py-1.5 rounded-pill">Verified Record</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-emerald btn-sm px-3" onclick="window.print()">
                                <i class="bi bi-printer-fill me-1"></i> Print Official Certificate
                            </button>
                            <a href="vaccination-history.php" class="btn btn-outline-secondary btn-sm">Close View</a>
                        </div>
                    </div>

                    <!-- Certificate Card Template -->
                    <div class="certificate-box">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="brand-icon" style="width: 55px; height: 55px; font-size: 1.8rem;">
                                    <i class="bi bi-shield-plus"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold font-outfit text-dark mb-0">E-VACCINATION MANAGEMENT SYSTEM</h3>
                                    <span class="text-teal fw-bold font-outfit small text-uppercase letter-spacing-1">Official National Immunization Certificate</span>
                                </div>
                            </div>
                            <div class="cert-stamp d-none d-sm-flex">
                                <i class="bi bi-patch-check-fill fs-5 text-teal"></i>
                                <span>OFFICIAL<br>VERIFIED</span>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted fw-bold small mb-2">Child & Beneficiary Details</h6>
                                <div class="p-3 bg-light rounded-3">
                                    <div class="fs-5 fw-bold text-dark mb-1"><?php echo htmlspecialchars($selected_cert['child_name']); ?></div>
                                    <div class="small text-secondary"><strong>Date of Birth:</strong> <?php echo format_date($selected_cert['child_dob']); ?></div>
                                    <div class="small text-secondary"><strong>Gender:</strong> <?php echo htmlspecialchars($selected_cert['child_gender']); ?> &bull; <strong>Blood Group:</strong> <?php echo htmlspecialchars($selected_cert['blood_group'] ?: 'N/A'); ?></div>
                                    <div class="small text-secondary"><strong>Parent / Guardian:</strong> <?php echo htmlspecialchars($selected_cert['parent_name']); ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted fw-bold small mb-2">Immunization Administration</h6>
                                <div class="p-3 bg-light rounded-3">
                                    <div class="fs-5 fw-bold text-teal mb-1"><?php echo htmlspecialchars($selected_cert['vaccine_name']); ?></div>
                                    <div class="small text-secondary"><strong>Short Code:</strong> <?php echo htmlspecialchars($selected_cert['short_code']); ?> (Dose #<?php echo $selected_cert['dose_number']; ?>)</div>
                                    <div class="small text-secondary"><strong>Target Disease:</strong> <?php echo htmlspecialchars($selected_cert['target_disease']); ?></div>
                                    <div class="small text-secondary"><strong>Date Administered:</strong> <span class="text-dark fw-bold"><?php echo format_date($selected_cert['vaccination_date']); ?></span></div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 border rounded-3 bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                    <div>
                                        <div class="small text-muted">Administered By Healthcare Facility:</div>
                                        <strong class="text-dark"><?php echo htmlspecialchars($selected_cert['hospital_name']); ?></strong>
                                        <div class="small text-muted"><?php echo htmlspecialchars($selected_cert['hospital_address'] . ', ' . $selected_cert['hospital_city']); ?> (Ph: <?php echo htmlspecialchars($selected_cert['hospital_phone']); ?>)</div>
                                    </div>
                                    <div class="text-md-end">
                                        <div class="small text-muted">Batch No / Clinician:</div>
                                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($selected_cert['batch_number'] ?: 'STANDARD-EPI'); ?> &bull; <?php echo htmlspecialchars($selected_cert['administered_by'] ?: 'Staff Nurse'); ?></span>
                                        <div class="mt-1 font-monospace fw-bold text-teal small"><?php echo htmlspecialchars($selected_cert['certificate_code']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3 d-flex justify-content-between align-items-center text-muted small">
                            <span>This certificate is cryptographically recorded in the centralized E-Vaccination database.</span>
                            <span class="font-monospace">CODE: <?php echo htmlspecialchars($selected_cert['certificate_code']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Table of All Completed Records -->
            <div class="table-card">
                <div class="table-card-header">
                    <div>
                        <h5 class="fw-bold font-outfit text-dark mb-0">Completed Immunization Log (<?php echo count($records); ?> Doses)</h5>
                        <span class="text-muted small">Permanent health passport records for your family</span>
                    </div>
                    <div style="max-width: 250px;">
                        <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Search history...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Child Name</th>
                                <th>Vaccine & Dose</th>
                                <th>Administering Hospital</th>
                                <th>Date Administered</th>
                                <th>Batch / Clinical Info</th>
                                <th>Certificate Verification</th>
                                <th>Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($records)): ?>
                                <?php foreach ($records as $r): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($r['child_name']); ?></div>
                                            <span class="text-muted small">Age: <?php echo calculate_age($r['child_dob']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-emerald-subtle text-success"><?php echo htmlspecialchars($r['short_code']); ?> (Dose #<?php echo $r['dose_number']; ?>)</span>
                                            <div class="text-muted small"><?php echo htmlspecialchars($r['vaccine_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small fw-medium text-dark"><?php echo htmlspecialchars($r['hospital_name']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($r['hospital_city']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small fw-bold text-dark"><?php echo format_date($r['vaccination_date']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small text-dark"><?php echo htmlspecialchars($r['batch_number'] ?: 'Standard Batch'); ?></div>
                                            <div class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($r['administered_by'] ?: 'Pediatric Clinic'); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-teal border font-monospace small">
                                                <?php echo htmlspecialchars($r['certificate_code']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="vaccination-history.php?cert_id=<?php echo $r['id']; ?>" class="btn btn-emerald btn-sm py-1 px-2.5">
                                                <i class="bi bi-printer-fill me-1"></i> View Card
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-award fs-3 d-block mb-2"></i>
                                        No administered vaccination records recorded yet. Once your child is vaccinated at a partner hospital, the certificate will appear here.
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



