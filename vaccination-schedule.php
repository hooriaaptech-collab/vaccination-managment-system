<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Routine Immunization Schedule - E-Vaccination Management System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Page Header Banner -->
<div class="py-5 bg-white border-bottom">
    <div class="container text-center max-w-700 mx-auto" style="max-width: 760px;">
        <span class="section-tag">Immunization Roadmap</span>
        <h1 class="fw-extrabold font-outfit text-dark mb-3">Routine Childhood Immunization Schedule</h1>
        <p class="text-muted">
            The national standard immunization timeline designed to protect infants and adolescents against 12+ deadly vaccine-preventable diseases.
        </p>
    </div>
</div>

<div class="container py-5">
    <!-- Quick Notice Banner -->
    <div class="alert alert-info border-0 rounded-4 p-4 mb-5 shadow-sm d-flex align-items-start gap-3" style="background: var(--primary-emerald-light);">
        <i class="bi bi-info-circle-fill text-teal fs-3"></i>
        <div>
            <h5 class="fw-bold text-dark mb-1">Why adhere strictly to the schedule?</h5>
            <p class="text-secondary small mb-0">
                Vaccines are scheduled at ages when a child’s immune system is most capable of responding, and when the risk of severe infection is highest. Delaying vaccines leaves children vulnerable during peak danger windows.
            </p>
        </div>
    </div>

    <!-- Routine Schedule Table Card -->
    <div class="table-card mb-5">
        <div class="table-card-header bg-light">
            <h5 class="fw-bold font-outfit text-dark mb-0"><i class="bi bi-calendar-range text-teal me-2"></i> Standard EPI Childhood Vaccination Timetable</h5>
            <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill">WHO Guidelines</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover custom-table">
                <thead>
                    <tr>
                        <th>Child Age</th>
                        <th>Vaccines & Doses</th>
                        <th>Target Diseases</th>
                        <th>Route / Method</th>
                        <th>Standard Importance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong class="text-dark">At Birth</strong><div class="text-muted small">0 - 24 Hours</div></td>
                        <td>
                            <div class="fw-bold text-teal">BCG + HepB-0 + OPV-0</div>
                            <div class="text-muted small">Single birth doses</div>
                        </td>
                        <td>Tuberculosis, Hepatitis B, Polio</td>
                        <td><span class="badge bg-light text-dark border">Intradermal & Oral</span></td>
                        <td><span class="badge bg-danger-subtle text-danger">Critical First Shot</span></td>
                    </tr>

                    <tr>
                        <td><strong class="text-dark">6 Weeks</strong><div class="text-muted small">1.5 Months</div></td>
                        <td>
                            <div class="fw-bold text-teal">Pentavalent-1 + OPV-1 + Rota-1 + PCV-1</div>
                            <div class="text-muted small">First combination series</div>
                        </td>
                        <td>Diphtheria, Tetanus, Pertussis, Hep B, Hib, Diarrhea, Pneumonia</td>
                        <td><span class="badge bg-light text-dark border">Intramuscular & Oral</span></td>
                        <td><span class="badge bg-success-subtle text-success">Primary Series</span></td>
                    </tr>

                    <tr>
                        <td><strong class="text-dark">10 Weeks</strong><div class="text-muted small">2.5 Months</div></td>
                        <td>
                            <div class="fw-bold text-teal">Pentavalent-2 + OPV-2 + Rota-2 + PCV-2</div>
                            <div class="text-muted small">Second series booster</div>
                        </td>
                        <td>Diphtheria, Tetanus, Pertussis, Hep B, Hib, Diarrhea, Pneumonia</td>
                        <td><span class="badge bg-light text-dark border">Intramuscular & Oral</span></td>
                        <td><span class="badge bg-success-subtle text-success">Primary Series</span></td>
                    </tr>

                    <tr>
                        <td><strong class="text-dark">14 Weeks</strong><div class="text-muted small">3.5 Months</div></td>
                        <td>
                            <div class="fw-bold text-teal">Pentavalent-3 + OPV-3 + IPV-1 + PCV-3</div>
                            <div class="text-muted small">Third series completion</div>
                        </td>
                        <td>Diphtheria, Tetanus, Pertussis, Hep B, Hib, Polio, Pneumonia</td>
                        <td><span class="badge bg-light text-dark border">Intramuscular</span></td>
                        <td><span class="badge bg-success-subtle text-success">Primary Series</span></td>
                    </tr>

                    <tr>
                        <td><strong class="text-dark">9 Months</strong><div class="text-muted small">Infancy stage</div></td>
                        <td>
                            <div class="fw-bold text-teal">Measles-Rubella 1 (MR-1) + TCV</div>
                            <div class="text-muted small">First measles & typhoid shot</div>
                        </td>
                        <td>Measles, Rubella, Typhoid Fever</td>
                        <td><span class="badge bg-light text-dark border">Subcutaneous</span></td>
                        <td><span class="badge bg-warning-subtle text-warning-emphasis">Rash & Fever Defense</span></td>
                    </tr>

                    <tr>
                        <td><strong class="text-dark">15 Months</strong><div class="text-muted small">Toddler stage</div></td>
                        <td>
                            <div class="fw-bold text-teal">Measles-Rubella 2 (MR-2)</div>
                            <div class="text-muted small">Second measles booster</div>
                        </td>
                        <td>Measles, Rubella Syndrome</td>
                        <td><span class="badge bg-light text-dark border">Subcutaneous</span></td>
                        <td><span class="badge bg-warning-subtle text-warning-emphasis">Long-term Immunity</span></td>
                    </tr>

                    <tr>
                        <td><strong class="text-dark">16 - 24 Months</strong><div class="text-muted small">Pre-school booster</div></td>
                        <td>
                            <div class="fw-bold text-teal">DTP Booster-1 + OPV Booster</div>
                            <div class="text-muted small">First childhood booster</div>
                        </td>
                        <td>Diphtheria, Tetanus, Pertussis, Polio</td>
                        <td><span class="badge bg-light text-dark border">Intramuscular & Oral</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">Booster Dose</span></td>
                    </tr>

                    <tr>
                        <td><strong class="text-dark">5 - 6 Years</strong><div class="text-muted small">School entry</div></td>
                        <td>
                            <div class="fw-bold text-teal">DTP Booster-2 / Tdap</div>
                            <div class="text-muted small">Pre-school immunization check</div>
                        </td>
                        <td>Diphtheria, Tetanus, Whooping Cough</td>
                        <td><span class="badge bg-light text-dark border">Intramuscular</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">Booster Dose</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Parent Action Banner -->
    <div class="card-3d p-4 p-md-5 bg-white text-center">
        <h4 class="fw-bold font-outfit text-dark mb-2">Want to generate a tailored timeline for your baby?</h4>
        <p class="text-muted small mb-4">Register your child’s birth date and receive automated milestone calculations in your parent portal.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?php echo base_url('register.php'); ?>" class="btn btn-emerald px-4">
                <i class="bi bi-person-plus-fill me-1"></i> Register My Child
            </a>
            <a href="<?php echo base_url('hospitals.php'); ?>" class="btn btn-outline-emerald px-4">
                <i class="bi bi-hospital me-1"></i> Find Nearby Centers
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

