<?php
include_once("connection.php");
$con = connection();

$success = false;
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Only validate on server-side after form submission
  $fullname = trim($_POST["fullname"]);
  $email = trim($_POST["email"]);
  $password = trim($_POST["password"]);
  $confirmPassword = trim($_POST["confirm_password"]);

  // Validate inputs (Server-Side)
  if (empty($fullname)) {
    $errors[] = "Full name is required.";
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
  }

  if (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters.";
  }

  if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
  }

  // Check if email already exists
  $check = $con->prepare("SELECT * FROM users WHERE email = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $checkResult = $check->get_result();

  if ($checkResult->num_rows > 0) {
    $errors[] = "Email already registered.";
  }

  // Register user if no errors (Server-Side)
  if (empty($errors)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insert = $con->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $fullname, $email, $hashedPassword);

    if ($insert->execute()) {
      $success = true;
      header("Refresh: 2; url=dashboard.php");
    } else {
      $errors[] = "Error in registration. Try again.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quizzleberry - Register</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(to bottom right, rgba(151, 71, 255, 0.2), rgba(75, 44, 145, 0.4));
    }

    .container {
      display: flex;
      max-width: 1200px;
      width: 100%;
      padding: 2rem;
      align-items: center;
      justify-content: space-between;
    }

    .auth-form {
      width: 100%;
      max-width: 400px;
      position: relative;
    }

    .form-container {
      width: 100%;
      transition: all 0.3s ease;
    }

    .register-form {
      width: 100%;
      transition: all 0.3s ease;
    }

    h1 {
      margin-bottom: 1.5rem;
      font-size: 1.875rem;
      font-weight: bold;
      text-align: center;
    }

    .brand-name {
      color: #9747ff;
      font-size: 2.5rem;
      display: block;
      margin-top: 0.5rem;
      letter-spacing: 1px;
      text-shadow: 0 2px 4px rgba(151, 71, 255, 0.3);
    }

    .subtitle {
      margin-bottom: 2rem;
      font-size: 1.125rem;
      text-align: center;
    }

    .google-btn {
      display: flex;
      width: 100%;
      justify-content: center;
      align-items: center;
      padding: 0.75rem 0;
      margin-bottom: 1.5rem;
      background-color: white;
      border: none;
      border-radius: 9999px;
      font-weight: 500;
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: background-color 0.2s;
    }

    .google-btn:hover {
      background-color: #f9f9f9;
    }

    .divider {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .divider-line {
      flex: 1;
      height: 1px;
      background-color: #d1d5db;
    }

    .divider-text {
      padding: 0 1rem;
      color: #6b7280;
    }

    .input-group {
      position: relative;
      margin-bottom: 1rem;
    }

    .input-field {
      width: 100%;
      padding: 0.75rem 1rem;
      padding-right: 2.5rem;
      border: 1px solid #e5e7eb;
      border-radius: 9999px;
      background-color: white;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .input-field:focus {
      outline: none;
      border-color: #4b2c91;
    }

    .input-icon {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .checkbox {
      height: 1rem;
      width: 1rem;
      border-radius: 0.25rem;
      border-color: #d1d5db;
      accent-color: #4b2c91;
    }

    .checkbox-label {
      margin-left: 0.5rem;
      font-size: 0.875rem;
      color: #4b5563;
    }

    .auth-btn {
      width: 100%;
      padding: 0.75rem 0;
      margin-bottom: 1.5rem;
      background-color: #4b2c91;
      color: white;
      border: none;
      border-radius: 9999px;
      font-weight: 500;
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: background-color 0.2s;
    }

    .auth-btn:hover {
      background-color: rgba(75, 44, 145, 0.9);
    }

    .toggle-text {
      text-align: center;
      font-size: 0.875rem;
      color: #4b5563;
    }

    .toggle-link {
      font-weight: 500;
      color: #4b2c91;
      text-decoration: none;
      cursor: pointer;
    }

    .mascot-container {
      display: flex;
      justify-content: center;
    }

    .mascot-image {
      max-width: 350px;
      height: auto;
    }

    .success-message {
      display: none;
      background-color: #10b981;
      color: white;
      padding: 1rem;
      border-radius: 0.5rem;
      margin-bottom: 1.5rem;
      text-align: center;
      animation: fadeIn 0.5s;
    }

    .php-success-message {
      display: block;
      background-color: #10b981;
      color: white;
      padding: 1rem;
      border-radius: 0.5rem;
      margin-bottom: 1.5rem;
      text-align: center;
      animation: fadeIn 0.5s;
    }

    .php-error-message {
      display: block;
      background-color: #ef4444;
      color: white;
      padding: 1rem;
      border-radius: 0.5rem;
      margin-bottom: 1.5rem;
      text-align: center;
      animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .mascot-animation {
      animation: bounce 0.5s;
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }

    .password-toggle {
      cursor: pointer;
    }

    .error-message {
      color: #ef4444;
      font-size: 0.75rem;
      margin-top: 0.25rem;
      margin-left: 1rem;
      display: none;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      .mascot-container {
        margin-top: 2rem;
      }

      .mascot-image {
        max-width: 250px;
      }

      h1, .subtitle {
        text-align: center;
      }
      
      .brand-name {
        font-size: 2.25rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="auth-form">
      <div class="form-container">
        <!-- Register Form -->
        <div class="register-form" id="registerForm">
          <h1>JOIN <span class="brand-name">QUIZZLEBERRY</span></h1>
          <p class="subtitle">Create your account</p>
          
          <?php if (!empty($errors)): ?>
            <div class="php-error-message">
              <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php if ($success): ?>
            <div class="php-success-message">
              Registration successful! Redirecting to dashboard...
            </div>
          <?php endif; ?>

           <!-- Google Sign Up Button -->
  <button class="google-btn" type="button" onclick="window.location.href='google-login.php'">
    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" style="width:20px;height:20px;margin-right:8px;">
    Sign up with Google
  </button>

  <div class="divider">
    <div class="divider-line"></div>
    <div class="divider-text">or</div>
    <div class="divider-line"></div>
  </div>
          <form method="POST" action="register.php" autocomplete="off">
            <div class="input-group">
              <input type="text" name="fullname" placeholder="Full Name" class="input-field" value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" required>
              <div class="input-icon">
                <!-- User icon SVG -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
          </div>
            <div class="input-group">
              <input type="email" name="email" placeholder="Email" class="input-field" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
              <div class="input-icon">
                <!-- Email icon SVG -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>
            <div class="input-group">
              <input type="password" name="password" id="registerPassword" placeholder="Password" class="input-field" required>
              <div class="input-icon password-toggle" onclick="togglePassword('registerPassword', 'registerEyeIcon')">
                <svg id="registerEyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>
            <div class="input-group">
              <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password" class="input-field" required>
              <div class="input-icon password-toggle" onclick="togglePassword('confirmPassword', 'confirmEyeIcon')">
                <svg id="confirmEyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>
            <div class="checkbox-group">
              <input type="checkbox" id="terms" class="checkbox" required>
              <label for="terms" class="checkbox-label">I agree to the Terms and Privacy Policy</label>
            </div>
            <button type="submit" class="auth-btn">Sign up</button>
          </form>
          <p class="toggle-text">
            Already have an account? <a class="toggle-link" href="login.php">Log in</a>
          </p>
        </div>
      </div>
    </div>
    <div class="mascot-container">
      <img id="mascot" src="111.png" alt="Quizzleberry Mascot" class="mascot-image">
    </div>
  </div>
  <script>
    // Toggle password visibility
    function togglePassword(inputId, iconId) {
      const passwordInput = document.getElementById(inputId);
      const eyeIcon = document.getElementById(iconId);
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = `
          <path d="M17.94 17.94C16.2306 19.243 14.1491 19.9649 12 20C5 20 1 12 1 12C2.24389 9.68192 3.96914 7.65663 6.06 6.06M9.9 4.24C10.5883 4.0789 11.2931 3.99836 12 4C19 4 23 12 23 12C22.393 13.1356 21.6691 14.2047 20.84 15.19M14.12 14.12C13.8454 14.4148 13.5141 14.6512 13.1462 14.8151C12.7782 14.9791 12.3809 15.0673 11.9781 15.0744C11.5753 15.0815 11.1752 15.0074 10.8016 14.8565C10.4281 14.7056 10.0887 14.4811 9.80385 14.1962C9.51897 13.9113 9.29439 13.572 9.14351 13.1984C8.99262 12.8249 8.91853 12.4247 8.92563 12.0219C8.93274 11.6191 9.02091 11.2219 9.18488 10.8539C9.34884 10.4859 9.58525 10.1547 9.88 9.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M1 1L23 23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        `;
      } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = `
          <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        `;
      }
    }
  </script>
</body>
</html>