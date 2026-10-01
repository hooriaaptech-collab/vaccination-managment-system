<?php
/**
 * Hospital Appointment Management & Vaccination Status Update
 * E-Vaccination Management System
 */
$page_title = 'Hospital Appointments';
$page_header = 'Clinical Appointments & Status Updates';
$page_subheader = 'Administer vaccines and record digital immunization certificates for scheduled patients';
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

// Handle Vaccination Status Update (Vaccinated / Not Vaccinated)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $appointment_id = intval($_POST['appointment_id'] ?? 0);

    if ($appointment_id > 0) {
        // Fetch appointment details
        $chk = $pdo->prepare("
            SELECT a.*, c.name AS child_name 
            FROM appointments a 
            JOIN children c ON a.child_id = c.id
            WHERE a.id = ? AND a.hospital_id = ?
        ");
        $chk->execute([$appointment_id, $hospital_id]);
        $app = $chk->fetch();

        if ($app) {
            if ($action === 'mark_vaccinated') {
                $batch_number = sanitize($_POST['batch_number'] ?? 'STD-BATCH-01');
                $administered_by = sanitize($_POST['administered_by'] ?? 'Staff Nurse');
                $remarks = sanitize($_POST['remarks'] ?? '');
                $vac_date = sanitize($_POST['vaccination_date'] ?? date('Y-m-d'));
                $dose_num = intval($_POST['dose_number'] ?? 1);

                // Generate official certificate code
                $cert_code = generate_cert_code($app['child_name']);

                $pdo->beginTransaction();
                try {
                    // Update appointment status to Completed
                    $upd_app = $pdo->prepare("UPDATE appointments SET status = 'Completed', admin_remarks = ? WHERE id = ?");
                    $upd_app->execute(['Vaccination completed successfully at hospital.', $appointment_id]);

                    // Insert into vaccination_records
                    $ins_rec = $pdo->prepare("
                        INSERT INTO vaccination_records (appointment_id, child_id, vaccine_id, hospital_id, dose_number, vaccination_date, status, batch_number, administered_by, remarks, certificate_code) 
                        VALUES (?, ?, ?, ?, ?, ?, 'Vaccinated', ?, ?, ?, ?)
                    ");
                    $ins_rec->execute([
                        $appointment_id,
                        $app['child_id'],
                        $app['vaccine_id'],
                        $hospital_id,
                        $dose_num,
                        $vac_date,
                        $batch_number,
                        $administered_by,
                        $remarks,
                        $cert_code
                    ]);

                    $pdo->commit();
                    set_flash('success', 'Vaccination successfully recorded for ' . htmlspecialchars($app['child_name']) . '! Official certificate generated: ' . $cert_code);
                } catch (Exception $ex) {
                    $pdo->rollBack();
                    set_flash('danger', 'Failed to record vaccination: ' . $ex->getMessage());
                }
            } elseif ($action === 'mark_not_vaccinated') {
                $reason = sanitize($_POST['not_vac_reason'] ?? 'Patient did not attend appointment');
                
                $upd_app = $pdo->prepare("UPDATE appointments SET status = 'Cancelled', admin_remarks = ? WHERE id = ?");
                $upd_app->execute(['Marked Not Vaccinated: ' . $reason, $appointment_id]);
                
                // Record in vaccination_records as Not Vaccinated for medical tracking
                $cert_code = 'NOT-VAC-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 5));
                $ins_rec = $pdo->prepare("
                    INSERT INTO vaccination_records (appointment_id, child_id, vaccine_id, hospital_id, dose_number, vaccination_date, status, remarks, certificate_code) 
                    VALUES (?, ?, ?, ?, 1, ?, 'Not Vaccinated', ?, ?)
                ");
                $ins_rec->execute([$appointment_id, $app['child_id'], $app['vaccine_id'], $hospital_id, date('Y-m-d'), $reason, $cert_code]);

                set_flash('warning', 'Appointment marked as Not Vaccinated.');
            }
        }
        header("Location: appointments.php");
        exit();
    }
}

// Status Filter
$status_filter = sanitize($_GET['status'] ?? 'Approved');
$where_clause = "WHERE a.hospital_id = ?";
$params = [$hospital_id];

if ($status_filter !== 'all') {
    $where_clause .= " AND a.status = ?";
    $params[] = $status_filter;
}

$query = "
    SELECT a.*, c.name AS child_name, c.dob AS child_dob, c.gender AS child_gender, c.blood_group, c.medical_notes AS child_notes,
           u.name AS parent_name, u.phone AS parent_phone,
           v.vaccine_name, v.short_code, v.doses_required
    FROM appointments a
    JOIN children c ON a.child_id = c.id
    JOIN users u ON a.parent_id = u.id
    JOIN vaccines v ON a.vaccine_id = v.id
    $where_clause
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

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
                    <a href="appointments.php?status=Approved" class="btn btn-sm <?php echo ($status_filter === 'Approved') ? 'btn-warning text-dark fw-bold' : 'btn-light'; ?>">
                        Pending Vaccination (Approved)
                    </a>
                    <a href="appointments.php?status=Completed" class="btn btn-sm <?php echo ($status_filter === 'Completed') ? 'btn-success' : 'btn-light'; ?>">
                        Completed Doses
                    </a>
                    <a href="appointments.php?status=all" class="btn btn-sm <?php echo ($status_filter === 'all') ? 'btn-emerald' : 'btn-light'; ?>">
                        All Bookings (<?php echo count($appointments); ?>)
                    </a>
                </div>

                <div style="max-width: 280px; width: 100%;">
                    <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Filter patient or parent...">
                </div>
            </div>

            <!-- Appointments Table Card -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Booking Code</th>
                                <th>Child & Age</th>
                                <th>Parent Contact</th>
                                <th>Vaccine Required</th>
                                <th>Appointment Slot</th>
                                <th>Status</th>
                                <th>Clinical Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($appointments)): ?>
                                <?php foreach ($appointments as $app): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($app['booking_code']); ?></span>
                                            <div class="text-muted" style="font-size: 0.72rem;"><?php echo format_date($app['created_at']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['child_name']); ?></div>
                                            <span class="badge bg-light text-muted border small"><?php echo htmlspecialchars($app['child_gender']); ?> &bull; <?php echo calculate_age($app['child_dob']); ?></span>
                                            <?php if (!empty($app['child_notes'])): ?>
                                                <div class="text-danger small mt-1" style="font-size: 0.72rem;"><i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($app['child_notes']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark small"><?php echo htmlspecialchars($app['parent_name']); ?></div>
                                            <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($app['parent_phone']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-teal border fw-bold"><?php echo htmlspecialchars($app['short_code']); ?></span>
                                            <div class="text-muted small"><?php echo htmlspecialchars($app['vaccine_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="small fw-bold text-dark"><?php echo format_date($app['appointment_date']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($app['appointment_time']); ?></div>
                                        </td>
                                        <td>
                                            <?php echo status_badge($app['status']); ?>
                                        </td>
                                        <td>
                                            <?php if ($app['status'] === 'Approved'): ?>
                                                <div class="d-flex gap-1.5">
                                                    <!-- Mark Vaccinated Modal Trigger -->
                                                    <button type="button" class="btn btn-success btn-sm py-1 px-2.5" data-bs-toggle="modal" data-bs-target="#vaccinateModal<?php echo $app['id']; ?>">
                                                        <i class="bi bi-check2-circle me-1"></i> Vaccinated
                                                    </button>
                                                    
                                                    <!-- Mark Not Vaccinated Modal Trigger -->
                                                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" data-bs-toggle="modal" data-bs-target="#notVaccinateModal<?php echo $app['id']; ?>" title="Mark Not Vaccinated / Missed">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>

                                                <!-- Mark Vaccinated Modal -->
                                                <div class="modal fade" id="vaccinateModal<?php echo $app['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form action="appointments.php" method="POST">
                                                                <input type="hidden" name="action" value="mark_vaccinated">
                                                                <input type="hidden" name="appointment_id" value="<?php echo $app['id']; ?>">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title text-success"><i class="bi bi-check-circle-fill me-2"></i> Record Vaccination Completion</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="p-3 bg-light rounded-3 mb-3 small">
                                                                        <div><strong>Child:</strong> <?php echo htmlspecialchars($app['child_name']); ?></div>
                                                                        <div><strong>Vaccine:</strong> <?php echo htmlspecialchars($app['vaccine_name']); ?> (<?php echo htmlspecialchars($app['short_code']); ?>)</div>
                                                                    </div>

                                                                    <div class="row g-3">
                                                                        <div class="col-md-6">
                                                                            <label class="form-label fw-semibold small text-dark">Date Administered</label>
                                                                            <input type="date" name="vaccination_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label fw-semibold small text-dark">Dose Number</label>
                                                                            <input type="number" name="dose_number" class="form-control" value="1" min="1" max="5">
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label fw-semibold small text-dark">Vaccine Batch / Lot No.</label>
                                                                            <input type="text" name="batch_number" class="form-control" placeholder="e.g. VAC-2026-09A" value="EPI-<?php echo date('Y'); ?>-<?php echo strtoupper(substr(md5($app['id']), 0, 4)); ?>">
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label fw-semibold small text-dark">Administering Doctor / Nurse</label>
                                                                            <input type="text" name="administered_by" class="form-control" placeholder="Staff Nurse / Dr." value="Duty Pediatrician">
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <label class="form-label fw-semibold small text-dark">Clinical Remarks (Optional)</label>
                                                                            <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Injection given on left upper arm, no immediate adverse reaction observed.">Routine dose administered successfully.</textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-success btn-sm px-3"><i class="bi bi-award-fill me-1"></i> Confirm & Generate Certificate</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Mark Not Vaccinated Modal -->
                                                <div class="modal fade" id="notVaccinateModal<?php echo $app['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form action="appointments.php" method="POST">
                                                                <input type="hidden" name="action" value="mark_not_vaccinated">
                                                                <input type="hidden" name="appointment_id" value="<?php echo $app['id']; ?>">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title text-danger"><i class="bi bi-x-circle-fill me-2"></i> Record Non-Administration</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="small text-muted mb-3">
                                                                        Record why the child was not vaccinated during this scheduled appointment.
                                                                    </p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold small text-dark">Reason / Medical Contraindication <span class="text-danger">*</span></label>
                                                                        <select name="not_vac_reason" class="form-select" required>
                                                                            <option value="Patient did not show up (No-show)">Patient did not show up (No-show)</option>
                                                                            <option value="Child had fever / acute illness (Deferred)">Child had acute fever/illness (Deferred)</option>
                                                                            <option value="Parent requested date rescheduling">Parent requested date rescheduling</option>
                                                                            <option value="Medical contraindication observed by doctor">Medical contraindication observed by doctor</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger btn-sm px-3"><i class="bi bi-x-lg me-1"></i> Save as Not Vaccinated</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php elseif ($app['status'] === 'Completed'): ?>
                                                <span class="badge bg-success-subtle text-success p-2 small">
                                                    <i class="bi bi-award-fill me-1"></i> Vaccinated & Recorded
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">Archived</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-check fs-3 d-block mb-2"></i>
                                        No appointments found for the selected filter.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

