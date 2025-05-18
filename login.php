<?php
include_once("connection.php");
$con = connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = mysqli_real_escape_string($con, $_POST['email']);
  $password = mysqli_real_escape_string($con, $_POST['password']);

  // Query to check if the user exists
  $sql = "SELECT * FROM users WHERE email = '$email'";
  $result = $con->query($sql);

  if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();

      // Verify the password
      if (password_verify($password, $user['password'])) {
          // Start a session and store user data
          session_start();
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['user_email'] = $user['email'];

          echo json_encode(['status' => 'success', 'message' => 'Login successful!']);
      } else {
          echo json_encode(['status' => 'error', 'message' => 'Invalid password.']);
      }
  } else {
      echo json_encode(['status' => 'error', 'message' => 'User not found.']);
  }
  exit;

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quizzleberry Login</title>
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

    .login-form {
      width: 100%;
      max-width: 400px;
    }

    h1 {
      margin-bottom: 1.5rem;
      font-size: 1.875rem;
      font-weight: bold;
      text-align: center;
    }

    .brand-name {
      color: #9747ff;
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
      text-decoration: none;
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

    .login-btn {
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

    .login-btn:hover {
      background-color: rgba(75, 44, 145, 0.9);
    }

    .signup-text {
      text-align: center;
      font-size: 0.875rem;
      color: #4b5563;
    }

    .signup-link {
      font-weight: 500;
      color: #4b2c91;
      text-decoration: none;
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
    }
  </style>
  
</head>
<body>
  <div class="container">
    <div class="login-form">
      <h1>WELCOME TO <span class="brand-name">QUIZZLEBERRY</span></h1>
      <p class="subtitle">Please enter your details</p>
      
      <div id="successMessage" class="success-message">
        Login successful! Redirecting to dashboard...
      </div>
      
   
          <a href="google-login.php" class="google-btn" style="text-decoration:none;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 10px;">
              <path d="M21.8055 10.0415H21V10H12V14H17.6515C16.827 16.3285 14.6115 18 12 18C8.6865 18 6 15.3135 6 12C6 8.6865 8.6865 6 12 6C13.5295 6 14.921 6.577 15.9805 7.5195L18.809 4.691C17.023 3.0265 14.634 2 12 2C6.4775 2 2 6.4775 2 12C2 17.5225 6.4775 22 12 22C17.5225 22 22 17.5225 22 12C22 11.3295 21.931 10.675 21.8055 10.0415Z" fill="#FFC107"/>
              <path d="M3.15295 7.3455L6.43845 9.755C7.32745 7.554 9.48045 6 12 6C13.5295 6 14.921 6.577 15.9805 7.5195L18.809 4.691C17.023 3.0265 14.634 2 12 2C8.15895 2 4.82795 4.1685 3.15295 7.3455Z" fill="#FF3D00"/>
              <path d="M12 22C14.583 22 16.93 21.0115 18.7045 19.404L15.6095 16.785C14.5718 17.5742 13.3038 18.001 12 18C9.39903 18 7.19053 16.3415 6.35853 14.027L3.09753 16.5395C4.75253 19.778 8.11353 22 12 22Z" fill="#4CAF50"/>
              <path d="M21.8055 10.0415H21V10H12V14H17.6515C17.2571 15.1082 16.5467 16.0766 15.608 16.7855L15.6095 16.7845L18.7045 19.4035C18.4855 19.6025 22 17 22 12C22 11.3295 21.931 10.675 21.8055 10.0415Z" fill="#1976D2"/>
            </svg>
            Log in with Google
          </a>
          
          <div class="divider">
            <div class="divider-line"></div>
            <span class="divider-text">Or</span>
            <div class="divider-line"></div>
          </div>

      <form id="loginForm" method="POST">
        <div class="input-group">
          <input type="email" name="email" id="email" placeholder="Email" class="input-field" required>
        </div>
        
        <div class="input-group">
          <input type="password" name="password" id="password" placeholder="Password" class="input-field" required>
        </div>
        
        <div class="checkbox-group">
          <input type="checkbox" id="remember" class="checkbox" checked>
          <label for="remember" class="checkbox-label">Remember for 30 days</label>
        </div>
        
        <button id="loginButton" class="login-btn" type="submit">Log in</button>
      </form>
      
      <p class="signup-text">
        Don't have any account? <a href="register.php" class="signup-link">Sign up</a>
      </p>
    </div>
    
    <div class="mascot-container">
      <img id="mascot" src="111.png" alt="Quizzleberry Mascot" class="mascot-image">
    </div>
  </div>

  <script>
    const loginForm = document.getElementById('loginForm');
    const successMessage = document.getElementById('successMessage');
    const loginButton = document.getElementById('loginButton');

    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(loginForm);
      loginButton.textContent = 'Logging in...';
      loginButton.disabled = true;

      try {
        const response = await fetch('login.php', {
          method: 'POST',
          body: formData,
        });

        const result = await response.json();

        if (result.status === 'success') {
          successMessage.style.display = 'block';
          setTimeout(() => {
            window.location.href = 'dashboard.php'; // Redirect to the dashboard
          }, 1500);
        } else {
          alert(result.message);
          loginButton.textContent = 'Log in';
          loginButton.disabled = false;
        }
      } catch (error) {
        alert('An error occurred. Please try again.');
        loginButton.textContent = 'Log in';
        loginButton.disabled = false;
      }
    });
  </script>
</body>
</html>