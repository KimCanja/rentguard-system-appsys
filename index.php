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
    <title>TCRCJ - Secure Car Rental Management</title>
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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
        .catchy-badge {
            background: linear-gradient(135deg, var(--accent) 0%, #0A2540 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1><i class="fas fa-shield-alt"></i> Tagum City Rent Car Jhunrider</h1>
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

    <!-- Catchy: Why Choose Us Section (Replaced How It Works) -->
    <section class="py-5" style="background: #F8FAFC;">
        <div class="container">
            <div class="text-center">
                <span class="catchy-badge"><i class="fas fa-crown"></i> Why Renters Love Us</span>
                <h2 style="color: var(--primary); font-size: 36px; margin-bottom: 15px;">Rent With Confidence.<span style="color: var(--accent);"> Return With Peace.</span></h2>
                <p style="color: var(--gray); font-size: 18px; max-width: 600px; margin: 0 auto 50px auto;">Before and after photos protect you from false damage claims.</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card" style="height: 100%;">
                        <div class="feature-icon">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h3>Stop Disputes Cold</h3>
                        <p>Photo evidence means you always have proof. No more "he said, she said" arguments over damages.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card" style="height: 100%;">
                        <div class="feature-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Spot Problem Customers</h3>
                        <p>Automatic alerts flag repeat offenders before you hand over the keys.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card" style="height: 100%;">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Save Hours Weekly</h3>
                        <p>Streamlined bookings, digital check-ins, and automated tracking. Less paperwork, more renting.</p>
                    </div>
                </div>
            </div>
            
            <!-- Social Proof Mini Bar -->
            <div class="row mt-5 pt-3 text-center">
                <div class="col-4">
                    <h3 style="color: var(--accent); font-weight: 700;">500+</h3>
                    <p style="color: var(--gray);">Vehicles Protected</p>
                </div>
                <div class="col-4">
                    <h3 style="color: var(--accent); font-weight: 700;">98%</h3>
                    <p style="color: var(--gray);">Dispute Resolution</p>
                </div>
                <div class="col-4">
                    <h3 style="color: var(--accent); font-weight: 700;">24/7</h3>
                    <p style="color: var(--gray);">System Uptime</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section -->
    <section class="py-5" style="background: white;">
        <div class="container">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card text-center" style="padding: 50px 30px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                        <div class="feature-icon">
                            <i class="fas fa-quote-left" style="font-size: 36px; opacity: 0.3;"></i>
                        </div>
                        <p style="font-size: 20px; font-style: italic; color: var(--gray); margin: 20px 0;">
                            "TCRCJ helped us reduce vehicle damage disputes by 65% in just 3 months. 
                            The photo evidence feature is a game-changer for our rental business."
                        </p>
                        <div>
                            <i class="fas fa-star" style="color: #FFD700;"></i>
                            <i class="fas fa-star" style="color: #FFD700;"></i>
                            <i class="fas fa-star" style="color: #FFD700;"></i>
                            <i class="fas fa-star" style="color: #FFD700;"></i>
                            <i class="fas fa-star" style="color: #FFD700;"></i>
                        </div>
                        <h5 style="margin-top: 20px; color: var(--primary);">— M**** R****</h5>
                        <p class="text-muted">Fleet Manager, Tagum Rentals</p>
                        
                        <div class="mt-4">
                            <a href="auth/register.php" class="btn" style="background: var(--accent); color: white; border-radius: 50px; padding: 10px 25px;">
                                <i class="fas fa-chalkboard-user"></i> Join 500+ Satisfied Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    

    <!-- Footer -->
    <footer style="background: var(--primary); color: white; padding: 30px 0; text-align: center;">
        <div class="container">
            <p class="mb-0">&copy; 2026 TCRCJ. All rights reserved.</p>
            <p class="text-muted small mt-2">Professional Car Rental Management System</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>