<?php
/**
 * About Page
 * E-Vaccination Management System
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'About Us - E-Vaccination Management System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Page Header Banner -->
<div class="py-5 bg-white border-bottom">
    <div class="container text-center max-w-700 mx-auto" style="max-width: 720px;">
        <span class="section-tag">About The Project</span>
        <h1 class="fw-extrabold font-outfit text-dark mb-3">Empowering Families & Pediatric Healthcare</h1>
        <p class="text-muted">
            The E-Vaccination Management System is a centralized digital health initiative dedicated to eliminating preventable childhood diseases through digitized record keeping and timely scheduling.
        </p>
    </div>
</div>

<div class="container py-5">
    <!-- Mission & Vision Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card-3d p-4 h-100 bg-white">
                <div class="stat-icon emerald mb-3">
                    <i class="bi bi-bullseye"></i>
                </div>
                <h4 class="fw-bold font-outfit text-dark mb-2">Our Core Mission</h4>
                <p class="text-muted small mb-0">
                    To provide an accessible, secure, and intuitive electronic vaccination platform that prevents missed immunization doses among infants and young children by bridging parents, healthcare centers, and health administrators into one unified ecosystem.
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-3d p-4 h-100 bg-white">
                <div class="stat-icon teal mb-3">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <h4 class="fw-bold font-outfit text-dark mb-2">Our Vision</h4>
                <p class="text-muted small mb-0">
                    A world where 100% of children achieve full routine immunization coverage without paper friction, lost yellow cards, or administrative hurdles, fostering healthier generations and resilient communities.
                </p>
            </div>
        </div>
    </div>

    <!-- WHO Immunization Principles -->
    <div class="card-3d p-4 p-md-5 bg-white mb-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="section-tag">Global Standards</span>
                <h3 class="fw-bold font-outfit text-dark mb-3">WHO & EPI Aligned Guidelines</h3>
                <p class="text-muted small mb-3">
                    Immunization is a proven tool for controlling and eliminating life-threatening infectious diseases. It is estimated to prevent between 3.5 to 5 million deaths each year globally.
                </p>
                <div class="d-flex flex-column gap-2.5">
                    <div class="d-flex align-items-center gap-2 small text-dark fw-medium">
                        <i class="bi bi-shield-check text-teal fs-5"></i> Routine schedule matching the Expanded Programme on Immunization (EPI).
                    </div>
                    <div class="d-flex align-items-center gap-2 small text-dark fw-medium">
                        <i class="bi bi-shield-check text-teal fs-5"></i> Cold-chain vaccine stock verification by registered clinics.
                    </div>
                    <div class="d-flex align-items-center gap-2 small text-dark fw-medium">
                        <i class="bi bi-shield-check text-teal fs-5"></i> Immutable digital vaccination records with verification codes.
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="p-4 bg-light rounded-4 border">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-teal me-2"></i> Key Project Highlights</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-muted mb-0">
                        <li><strong>1. Built on Open Technologies:</strong> Powered by clean PHP & MySQL for maximum academic clarity and high performance.</li>
                        <li><strong>2. Three-Tier Role Access:</strong> Segregated workflows for System Admin, Hospital Staff, and Parents.</li>
                        <li><strong>3. Dynamic Dose Calculations:</strong> Automated routine vaccine recommendations calculated from child birth date.</li>
                        <li><strong>4. Real-time Hospital Availability:</strong> Transparent appointment requests and hospital approvals.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="p-4 p-md-5 rounded-4 text-center text-white" style="background: linear-gradient(135deg, var(--primary-emerald) 0%, var(--teal-main) 100%);">
        <h3 class="fw-bold font-outfit text-white mb-2">Have questions or want to partner with us?</h3>
        <p class="text-light opacity-90 small mb-4">Our support team and pediatric coordinators are available 24/7 to assist you.</p>
        <a href="<?php echo base_url('contact.php'); ?>" class="btn btn-coral px-4">
            <i class="bi bi-envelope-fill me-1"></i> Contact Support Team
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

