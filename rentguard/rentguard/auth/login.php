<?php
session_start();   // ← Make sure session is started
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/encryption.php'; // Add this for decryption

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Check if user exists and verify password (supports both plain text and encrypted)
        $password_valid = false;
        
        if ($user) {
            // Try to decrypt the password first (if it's encrypted)
            try {
                $decrypted_password = aes_decrypt($user['password']);
                // Check if decryption worked and password matches
                if ($decrypted_password && $decrypted_password !== $user['password']) {
                    $password_valid = ($password === $decrypted_password);
                } else {
                    // It's plain text
                    $password_valid = ($password === $user['password']);
                }
            } catch (Exception $e) {
                // Decryption failed, treat as plain text
                $password_valid = ($password === $user['password']);
            }
        }
        
        if ($user && $password_valid) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                redirect(BASE_URL . 'admin/dashboard.php');
            } else {
                redirect(BASE_URL . 'customer/dashboard.php');
            }
            exit();
        } else {
            $error = 'Invalid email or password.';
            // Clear the fields by sending a flag to JavaScript
            $clear_fields = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RentGuard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0A2540 0%, #1E2937 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 450px;
            width: 100%;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .brand-header h1 {
            color: #0A2540;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .brand-header p {
            color: #64748B;
            font-size: 14px;
        }
        .form-label {
            color: #1E2937;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .password-input-wrapper {
            position: relative;
        }
        .form-control {
            border: 2px solid #E2E8F0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #10B981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748B;
            transition: color 0.3s ease;
            z-index: 10;
        }
        .toggle-password:hover {
            color: #10B981;
        }
        .btn-login {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            color: white;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
            color: white;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #64748B;
        }
        .register-link a {
            color: #10B981;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .register-link a:hover {
            color: #059669;
            text-decoration: underline;
        }
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        .demo-credentials {
            background: #F0F9FF;
            border: 2px solid #E0F2FE;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .demo-credentials:hover {
            background: #E0F2FE;
            transform: scale(1.02);
        }
        .demo-credentials strong {
            color: #0369A1;
        }
        .demo-credentials code {
            background: white;
            padding: 2px 6px;
            border-radius: 6px;
            font-weight: 600;
        }
        .input-icon {
            position: relative;
        }
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
        }
        .input-icon .form-control {
            padding-left: 45px;
        }
        .clear-btn {
            position: absolute;
            right: 45px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94A3B8;
            font-size: 12px;
            display: none;
        }
        .clear-btn:hover {
            color: #EF4444;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-header">
            <h1><i class="fas fa-shield-alt"></i> RentGuard</h1>
            <p>Secure Car Rental Management</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="demo-credentials" onclick="fillDemoCredentials()">
            <strong><i class="fas fa-info-circle"></i> Demo Credentials:</strong><br>
            Email: <code>admin@rentguard.com</code><br>
            Password: <code>admin123</code>
            <small class="text-muted" style="display: block; margin-top: 5px;">(Click to auto-fill)</small>
        </div>

        <form method="POST" id="loginForm">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" name="email" id="email" required autocomplete="off">
                    <span class="clear-btn" id="clearEmail" onclick="clearField('email')">
                        <i class="fas fa-times-circle"></i>
                    </span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="password-input-wrapper">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-login" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Clear individual field
        function clearField(fieldId) {
            const field = document.getElementById(fieldId);
            field.value = '';
            field.focus();
            
            // Hide clear button
            const clearBtn = document.getElementById('clear' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1));
            if (clearBtn) {
                clearBtn.style.display = 'none';
            }
        }

        // Show/hide clear button based on input
        document.getElementById('email').addEventListener('input', function() {
            const clearBtn = document.getElementById('clearEmail');
            if (this.value.length > 0) {
                clearBtn.style.display = 'block';
            } else {
                clearBtn.style.display = 'none';
            }
        });

        // Auto-fill demo credentials
        function fillDemoCredentials() {
            document.getElementById('email').value = 'admin@rentguard.com';
            document.getElementById('password').value = 'admin123';
            
            // Show clear buttons
            document.getElementById('clearEmail').style.display = 'block';
            
            // Optional: Add visual feedback
            const demoBox = document.querySelector('.demo-credentials');
            demoBox.style.backgroundColor = '#DBEAFE';
            setTimeout(() => {
                demoBox.style.backgroundColor = '#F0F9FF';
            }, 500);
        }

        // Clear form fields if there was an error
        <?php if (isset($clear_fields) && $clear_fields === true): ?>
        window.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('email').focus();
        });
        <?php endif; ?>

        // Optional: Add loading effect on submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('loginBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            submitBtn.disabled = true;
        });

        // Add keyboard support (Enter key)
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });

        // Remove clear button when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.input-icon')) {
                const clearBtns = document.querySelectorAll('.clear-btn');
                clearBtns.forEach(btn => {
                    if (document.getElementById('email').value.length === 0) {
                        btn.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>