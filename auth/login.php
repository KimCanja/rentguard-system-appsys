<?php
session_start();
require_once '../config/database.php';
require_once '../config/constants.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        // Get user from database
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Plain text password comparison
        if ($user && $password === $user['password']) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            //$_SESSION['logged_in'] = true;
            
            // Redirect based on role
           if ($user['role'] === 'admin') {
                header("Location: /rentguard/admin/dashboard.php");
                exit();
            } else {
                header("Location: /rentguard/customer/dashboard.php");
                exit();
            }
        } else {
            $error = 'Invalid email or password.';
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
        }
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
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
        .password-input-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748B;
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-header">
            <h1><i class="fas fa-shield-alt"></i> RentGuard</h1>
            <p>Secure Car Rental Management</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

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

            <button type="submit" class="btn-login" id="loginBtn">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
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

        function clearField(fieldId) {
            const field = document.getElementById(fieldId);
            field.value = '';
            field.focus();
            
            const clearBtn = document.getElementById('clear' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1));
            if (clearBtn) {
                clearBtn.style.display = 'none';
            }
        }

        document.getElementById('email').addEventListener('input', function() {
            const clearBtn = document.getElementById('clearEmail');
            if (this.value.length > 0) {
                clearBtn.style.display = 'block';
            } else {
                clearBtn.style.display = 'none';
            }
        });

        <?php if (isset($clear_fields) && $clear_fields === true): ?>
        window.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('email').focus();
        });
        <?php endif; ?>

        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('loginBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>