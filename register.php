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
  <script>
    // Client-Side Validation (JavaScript)
    function validateForm() {
      var fullname = document.getElementById("registerName").value;
      var email = document.getElementById("registerEmail").value;
      var password = document.getElementById("registerPassword").value;
      var errors = [];

      // Check if fullname is empty
      if (fullname == "") {
        errors.push("Full name is required.");
      }

      // Validate email
      var emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
      if (!email.match(emailPattern)) {
        errors.push("Invalid email format.");
      }

      // Check password length
      if (password.length < 6) {
        errors.push("Password must be at least 6 characters.");
      }

      // Display errors (if any)
      if (errors.length > 0) {
        var errorMessage = errors.join("\n");
        alert(errorMessage);
        return false;
      }

      return true;
    }
  </script>
  <style>
    {
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
    /* Your existing styles here */
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

          <form method="POST" onsubmit="return validateForm()">
            <div class="input-group">
              <input type="text" id="registerName" name="fullname" placeholder="Full Name" class="input-field">
            </div>

            <div class="input-group">
              <input type="email" id="registerEmail" name="email" placeholder="Email" class="input-field">
            </div>

            <div class="input-group">
              <input type="password" id="registerPassword" name="password" placeholder="Password" class="input-field">
            </div>

            <button type="submit" class="auth-btn">Sign Up</button>
          </form>
          
          <?php if (!empty($errors)): ?>
            <div class="error-message">
              <?php echo implode("<br>", $errors); ?>
            </div>
          <?php endif; ?>

          <?php if ($success): ?>
            <div class="success-message">
              Registration successful! Redirecting to dashboard...
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</body>
</html>