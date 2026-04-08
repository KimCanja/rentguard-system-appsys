<?php
require_once 'config/constants.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isAdmin()) {
        redirect(BASE_URL . 'rentguard/admin/dashboard.php');
    } else {
        redirect(BASE_URL . 'rentguard/customer/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RentGuard - Secure Car Rental Management</title>
    <!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #0A2540 0%, #1E2937 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .hero h1 {
            color: white;
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        .feature-card {
            text-align: center;
            padding: 30px;
        }
        .feature-icon {
            font-size: 48px;
            color: var(--accent);
            margin-bottom: 20px;
        }
        .feature-card h3 {
            color: var(--primary);
            margin-bottom: 15px;
        }
        .feature-card p {
            color: var(--gray);
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1><i class="fas fa-shield-alt"></i> RentGuard</h1>
            <p>Professional Car Rental Management System</p>
            <p style="font-size: 16px; opacity: 0.8;">Secure bookings, damage tracking, and repeat-offender alerts</p>
            <div class="mt-5">
                <a href="auth/login.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="auth/register.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Key Features</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3>Vehicle Management</h3>
                        <p>Easily manage your fleet with detailed vehicle profiles, pricing, and availability tracking.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <h3>Photo Evidence</h3>
                        <p>Digital check-in/check-out with before and after photos for damage prevention and documentation.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3>Damage Tracking</h3>
                        <p>Comprehensive damage reporting system with severity levels and repeat-offender alerts.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3>Repeat Offender Alerts</h3>
                        <p>Automatic flagging of customers with multiple damage incidents for informed decision-making.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3>Booking Management</h3>
                        <p>Streamlined rental booking system with approval workflow and status tracking.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Admin Dashboard</h3>
                        <p>Comprehensive analytics and reporting for fleet management and business insights.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Credentials Section -->
   <!-- <section class="py-5" style="background: #F8FAFC;">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Demo Credentials</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Try the system with these demo credentials:</p>
                            <div class="alert alert-info mb-3">
                                <strong>Admin Account:</strong><br>
                                Email: <code>admin2@rentguard.com</code><br>
                                Password: <code>admin2</code>
                            </div>
                            <p class="text-muted mb-0">
                                <i class="fas fa-lightbulb"></i> You can also register a new customer account to explore the customer portal.
                            </p>
                        </div>-->
                    <!--</div>
                </div>
            </div>
        </div>
    </section>-->

    <!-- Footer -->
    <footer style="background: var(--primary); color: white; padding: 30px 0; text-align: center;">
        <div class="container">
            <p class="mb-0">&copy; 2026 RentGuard. All rights reserved.</p>
            <p class="text-muted small mt-2">Professional Car Rental Management System</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
