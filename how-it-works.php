<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'How It Works - E-Vaccination Management System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Page Header Banner -->
<div class="py-5 bg-white border-bottom">
    <div class="container text-center max-w-700 mx-auto" style="max-width: 720px;">
        <span class="section-tag">System Architecture & Workflow</span>
        <h1 class="fw-extrabold font-outfit text-dark mb-3">How E-Vaccination Works</h1>
        <p class="text-muted">
            A step-by-step breakdown of how parents, healthcare providers, and administrators collaborate in one coordinated digital system.
        </p>
    </div>
</div>

<div class="container py-5">
    <!-- Parent Workflow -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="stat-icon emerald">
                <i class="bi bi-person-heart"></i>
            </div>
            <div>
                <h3 class="fw-bold font-outfit text-dark mb-0">For Parents & Guardians</h3>
                <span class="text-muted small">Managing your child's immunization journey effortlessly</span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card-3d p-4 h-100 bg-white">
                    <div class="badge bg-light text-teal border mb-3 px-3 py-1 rounded-pill fw-bold">Step 01</div>
                    <h5 class="fw-bold text-dark mb-2">Create Account</h5>
                    <p class="text-muted small mb-0">Register with your name, phone number, and email. Instant access to the Parent Portal.</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-3d p-4 h-100 bg-white">
                    <div class="badge bg-light text-teal border mb-3 px-3 py-1 rounded-pill fw-bold">Step 02</div>
                    <h5 class="fw-bold text-dark mb-2">Add Children</h5>
                    <p class="text-muted small mb-0">Enter your baby’s date of birth, gender, and medical notes. The system calculates upcoming routine dates.</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-3d p-4 h-100 bg-white">
                    <div class="badge bg-light text-teal border mb-3 px-3 py-1 rounded-pill fw-bold">Step 03</div>
                    <h5 class="fw-bold text-dark mb-2">Book Hospital</h5>
                    <p class="text-muted small mb-0">Select your preferred hospital, choose vaccine, pick date and time slot, and submit the booking request.</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-3d p-4 h-100 bg-white">
                    <div class="badge bg-light text-teal border mb-3 px-3 py-1 rounded-pill fw-bold">Step 04</div>
                    <h5 class="fw-bold text-dark mb-2">Get Certificate</h5>
                    <p class="text-muted small mb-0">After vaccination, download or print your child's verified official E-Vaccination Certificate.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hospital Workflow -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="stat-icon teal">
                <i class="bi bi-hospital"></i>
            </div>
            <div>
                <h3 class="fw-bold font-outfit text-dark mb-0">For Hospitals & Clinics</h3>
                <span class="text-muted small">Managing clinical appointments and stock availability</span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-3d p-4 h-100 bg-white">
                    <div class="stat-icon emerald mb-3" style="width: 44px; height: 44px;">
                        <i class="bi bi-capsule"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">1. Stock Inventory Toggle</h5>
                    <p class="text-muted small mb-0">Hospital staff can toggle vaccine availability (Available / Out of Stock) in real-time.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-3d p-4 h-100 bg-white">
                    <div class="stat-icon teal mb-3" style="width: 44px; height: 44px;">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">2. View Approved Appointments</h5>
                    <p class="text-muted small mb-0">Access daily schedules of children scheduled for immunization at your clinic.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-3d p-4 h-100 bg-white">
                    <div class="stat-icon coral mb-3" style="width: 44px; height: 44px;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">3. Confirm Administration</h5>
                    <p class="text-muted small mb-0">Mark vaccination as <strong>Vaccinated</strong> with batch number, instantly issuing the digital certificate.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Role & Verification Box -->
    <div class="card-3d p-4 p-md-5 bg-white">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="section-tag">System Oversight</span>
                <h3 class="fw-bold font-outfit text-dark mb-3">Administrator Control & Verification</h3>
                <p class="text-muted small mb-3">
                    The System Administrator acts as the central health authority. All appointment requests from parents pass through admin verification to ensure valid medical scheduling and hospital capacity limits.
                </p>
                <div class="d-flex flex-column gap-2 small text-dark">
                    <div><i class="bi bi-check-circle-fill text-teal me-2"></i><strong>Request Auditing:</strong> Review, Approve, or Reject parent appointment submissions.</div>
                    <div><i class="bi bi-check-circle-fill text-teal me-2"></i><strong>Catalog CRUD:</strong> Update vaccines, dosage requirements, and interval rules.</div>
                    <div><i class="bi bi-check-circle-fill text-teal me-2"></i><strong>Hospital Verification:</strong> Approve and monitor partner clinics.</div>
                    <div><i class="bi bi-check-circle-fill text-teal me-2"></i><strong>Date-Wise Reports:</strong> Generate audit reports for child immunizations.</div>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="p-4 rounded-4 text-white" style="background: linear-gradient(135deg, var(--dark-charcoal) 0%, #1E293B 100%);">
                    <i class="bi bi-shield-lock-fill text-teal" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold text-white mt-3 mb-2">Secured & Audited</h5>
                    <p class="text-light opacity-80 small mb-0">Role-protected sessions and encrypted password hashing prevent unauthorized access.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

