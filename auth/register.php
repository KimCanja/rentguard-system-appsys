<?php
require_once '../config/database.php';
require_once '../config/constants.php';
// Comment out or remove encryption - we're using plain text now
require_once '../config/encryption.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $role = $_POST['role'] ?? 'customer';

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) != 12) {
        $error = 'Password must be exactly 12 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $error = 'Password must contain at least one special character (!@#$%^&*(),.?":{}|<>)';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email already registered.';
        } else {
            // STORE AS PLAIN TEXT (FOR DEVELOPMENT ONLY!)
            $plain_password = $password;

            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $plain_password, $role]);
                
                $user_id = $pdo->lastInsertId();

                // If customer, create customer profile
                if ($role === 'customer') {
                    $stmt = $pdo->prepare("INSERT INTO customers (user_id) VALUES (?)");
                    $stmt->execute([$user_id]);
                }

                $success = 'Registration successful! Redirecting to login...';
                header("refresh:2;url=login.php");
                exit();
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
    
    // Clear POST data to prevent form repopulation
    $_POST = array();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RentGuard</title>
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
            padding: 20px;
        }
        .register-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
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
            outline: none;
        }
        /* Remove invalid border color */
        .form-control:invalid {
            border-color: #E2E8F0;
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
            z-index: 10;
        }
        .toggle-password:hover {
            color: #10B981;
        }
        .password-strength {
            margin-top: 10px;
        }
        .strength-bar {
            height: 6px;
            border-radius: 3px;
            background: #E2E8F0;
            overflow: hidden;
            margin-bottom: 8px;
        }
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 3px;
        }
        .strength-text {
            font-size: 12px;
            font-weight: 600;
        }
        .char-counter {
            font-size: 12px;
            margin-top: 5px;
            text-align: right;
        }
        .char-counter.valid {
            color: #10B981;
        }
        .char-counter.invalid {
            color: #64748B;
        }
        .strength-very-weak { color: #EF4444; }
        .strength-weak { color: #F59E0B; }
        .strength-medium { color: #FBBF24; }
        .strength-strong { color: #10B981; }
        .strength-very-strong { color: #059669; }
        
        .password-requirements {
            margin-top: 10px;
            font-size: 12px;
        }
        .requirement {
            color: #64748B;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .requirement i {
            width: 16px;
            font-size: 12px;
        }
        .requirement.valid {
            color: #10B981;
        }
        .requirement.invalid {
            color: #64748B;
        }
        
        .btn-register {
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
        .btn-register:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
            color: white;
        }
        .btn-register:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #64748B;
        }
        .login-link a {
            color: #10B981;
            text-decoration: none;
            font-weight: 600;
        }
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
        }
        .role-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .role-option {
            flex: 1;
        }
        .role-option input[type="radio"] {
            display: none;
        }
        .role-option label {
            display: block;
            padding: 12px;
            border: 2px solid #E2E8F0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 0;
        }
        .role-option input[type="radio"]:checked + label {
            border-color: #10B981;
            background-color: rgba(16, 185, 129, 0.1);
            color: #10B981;
            font-weight: 600;
        }
        .info-icon {
            cursor: help;
            margin-left: 5px;
            color: #64748B;
            font-size: 12px;
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
        input:-webkit-autofill,
        input:-webkit-autofill:focus {
            transition: background-color 600000s 0s, color 600000s 0s;
        }
        
        /* Warning banner for development */
        .dev-warning {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand-header">
    <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 10px;">
        <img src="../uploads/profiles/RentCar.jpg"  alt="RentGuard" style="width: 45px; height: 45px; object-fit: contain;">
        <h1 style="margin: 0; font-size: 24px;">Tagum City Rent Car Jhunrider</h1>
    </div>
    <p>Create your account</p>
</div>

        

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="registerForm" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" id="fullname" autocomplete="off" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" id="email" autocomplete="off" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Account Type</label>
                <div class="role-selector">
                    <div class="role-option">
                        <input type="radio" id="customer" name="role" value="customer" checked>
                        <label for="customer"><i class="fas fa-user"></i> Customer</label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="admin" name="role" value="admin">
                        <label for="admin"><i class="fas fa-user-tie"></i> Admin</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Password 
                    <i class="fas fa-info-circle info-icon" title="Password must be exactly 12 characters with uppercase, lowercase, number, and special character"></i>
                </label>
                <div class="password-input-wrapper">
                    <input type="password" class="form-control" id="password" name="password" maxlength="12" autocomplete="new-password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                </div>
                
                <!-- Character Counter -->
                <div class="char-counter" id="charCounter">
                    <i class="fas fa-keyboard"></i> <span id="charCount">0</span>/12 characters
                </div>
                
                <!-- Password Strength Meter -->
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                </div>

                <!-- Password Requirements -->
                <div class="password-requirements">
                    <div class="requirement" id="reqLength">
                        <i class="fas fa-circle"></i> Exactly 12 characters
                    </div>
                    <div class="requirement" id="reqUppercase">
                        <i class="fas fa-circle"></i> At least 1 uppercase letter (A-Z)
                    </div>
                    <div class="requirement" id="reqLowercase">
                        <i class="fas fa-circle"></i> At least 1 lowercase letter (a-z)
                    </div>
                    <div class="requirement" id="reqNumber">
                        <i class="fas fa-circle"></i> At least 1 number (0-9)
                    </div>
                    <div class="requirement" id="reqSpecial">
                        <i class="fas fa-circle"></i> At least 1 special character (!@#$%^&*(),.?":{}|<>)
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="password-input-wrapper">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" maxlength="12" autocomplete="new-password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
                </div>
                <div id="passwordMatch" class="mt-2" style="font-size: 12px;"></div>
            </div>

            <button type="submit" class="btn btn-register" id="submitBtn">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        // Clear all form fields on page load (except after error)
        window.addEventListener('DOMContentLoaded', function() {
            // Only clear fields if there's no error message (fresh page load)
            <?php if (empty($error) && empty($success)): ?>
                document.getElementById('fullname').value = '';
                document.getElementById('email').value = '';
                document.getElementById('password').value = '';
                document.getElementById('confirm_password').value = '';
            <?php endif; ?>
            
            // Reset password strength meter
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            if (strengthFill) strengthFill.style.width = '0%';
            if (strengthText) strengthText.innerHTML = '';
            
            // Reset requirements
            const requirements = ['reqLength', 'reqUppercase', 'reqLowercase', 'reqNumber', 'reqSpecial'];
            requirements.forEach(req => {
                const element = document.getElementById(req);
                if (element) {
                    const icon = element.querySelector('i');
                    element.classList.remove('valid', 'invalid');
                    if (icon) {
                        icon.classList.remove('fa-check-circle');
                        icon.classList.add('fa-circle');
                    }
                }
            });
            
            // Reset password match message
            const matchDiv = document.getElementById('passwordMatch');
            if (matchDiv) matchDiv.innerHTML = '';
            
            // Reset char counter
            updateCharCount();
        });

        // Update character counter
        function updateCharCount() {
            const password = document.getElementById('password').value;
            const charCount = password.length;
            const charCounter = document.getElementById('charCounter');
            const charCountSpan = document.getElementById('charCount');
            
            charCountSpan.textContent = charCount;
            
            if (charCount === 12) {
                charCounter.classList.add('valid');
                charCounter.classList.remove('invalid');
            } else {
                charCounter.classList.add('invalid');
                charCounter.classList.remove('valid');
            }
        }

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Calculate password strength
        function calculatePasswordStrength(password) {
            let strength = 0;
            let criteria = 0;
            
            const isValidLength = password.length === 12;
            const isValidUppercase = /[A-Z]/.test(password);
            const isValidLowercase = /[a-z]/.test(password);
            const isValidNumber = /[0-9]/.test(password);
            const isValidSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            if (isValidLength) criteria++;
            if (isValidUppercase) criteria++;
            if (isValidLowercase) criteria++;
            if (isValidNumber) criteria++;
            if (isValidSpecial) criteria++;
            
            strength = (criteria / 5) * 100;
            
            let level = '';
            let color = '';
            
            if (strength === 100) {
                level = 'Perfect!';
                color = '#059669';
            } else if (strength >= 80) {
                level = 'Very Strong';
                color = '#10B981';
            } else if (strength >= 60) {
                level = 'Strong';
                color = '#FBBF24';
            } else if (strength >= 40) {
                level = 'Weak';
                color = '#F59E0B';
            } else {
                level = 'Very Weak';
                color = '#EF4444';
            }
            
            return { strength, level, color, criteria };
        }

        // Update requirements display
        function updateRequirements(password) {
            const reqLength = document.getElementById('reqLength');
            const reqUppercase = document.getElementById('reqUppercase');
            const reqLowercase = document.getElementById('reqLowercase');
            const reqNumber = document.getElementById('reqNumber');
            const reqSpecial = document.getElementById('reqSpecial');
            
            // Check each requirement
            const isValidLength = password.length === 12;
            const isValidUppercase = /[A-Z]/.test(password);
            const isValidLowercase = /[a-z]/.test(password);
            const isValidNumber = /[0-9]/.test(password);
            const isValidSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            // Update UI
            updateRequirementUI(reqLength, isValidLength);
            updateRequirementUI(reqUppercase, isValidUppercase);
            updateRequirementUI(reqLowercase, isValidLowercase);
            updateRequirementUI(reqNumber, isValidNumber);
            updateRequirementUI(reqSpecial, isValidSpecial);
            
            return {
                isValid: isValidLength && isValidUppercase && isValidLowercase && isValidNumber && isValidSpecial,
                criteria: {
                    length: isValidLength,
                    uppercase: isValidUppercase,
                    lowercase: isValidLowercase,
                    number: isValidNumber,
                    special: isValidSpecial
                }
            };
        }
        
        function updateRequirementUI(element, isValid) {
            const icon = element.querySelector('i');
            if (isValid) {
                element.classList.add('valid');
                element.classList.remove('invalid');
                icon.classList.remove('fa-circle');
                icon.classList.add('fa-check-circle');
            } else {
                element.classList.add('invalid');
                element.classList.remove('valid');
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-circle');
            }
        }

        // Check if passwords match
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return true;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<i class="fas fa-check-circle" style="color: #10B981;"></i> <span style="color: #10B981;">Passwords match!</span>';
                return true;
            } else {
                matchDiv.innerHTML = '<i class="fas fa-times-circle" style="color: #EF4444;"></i> <span style="color: #EF4444;">Passwords do not match!</span>';
                return false;
            }
        }

        // Prevent typing beyond 12 characters
        function enforceMaxLength(input) {
            if (input.value.length > 12) {
                input.value = input.value.slice(0, 12);
            }
            updateCharCount();
        }

        // Main validation function
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            const requirements = updateRequirements(password);
            const strengthData = calculatePasswordStrength(password);
            const doPasswordsMatch = password === confirmPassword;
            
            // Update strength meter
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            strengthFill.style.width = strengthData.strength + '%';
            strengthFill.style.backgroundColor = strengthData.color;
            
            if (password.length > 0) {
                strengthText.innerHTML = `<strong>Password Strength: <span style="color: ${strengthData.color}">${strengthData.level}</span></strong>`;
                strengthText.style.color = strengthData.color;
            } else {
                strengthText.innerHTML = '';
            }
            
            // Update char counter
            updateCharCount();
            
            // Enable/disable submit button
            const submitBtn = document.getElementById('submitBtn');
            const isValid = requirements.isValid && doPasswordsMatch && password.length === 12;
            submitBtn.disabled = !isValid;
            
            return isValid;
        }

        // Add event listeners
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('confirm_password');
        
        if (passwordField) {
            passwordField.addEventListener('input', function() {
                enforceMaxLength(this);
                validateForm();
                checkPasswordMatch();
            });
        }
        
        if (confirmField) {
            confirmField.addEventListener('input', function() {
                enforceMaxLength(this);
                validateForm();
                checkPasswordMatch();
            });
        }
        
        // Initial validation
        validateForm();
        
        // Prevent browser autofill
        setTimeout(function() {
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.value && input.type !== 'submit' && input.type !== 'button') {
                    if (input.type === 'email' || input.type === 'text') {
                        <?php if (empty($error)): ?>
                        input.value = '';
                        <?php endif; ?>
                    }
                }
            });
        }, 100);
    </script>
</body>
</html>
