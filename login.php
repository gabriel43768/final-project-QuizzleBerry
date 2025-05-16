<?php
include_once("connection.php");
$con = connection();


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
      
      <button class="google-btn">Log in with Google</button>
      
      <div class="divider">
        <div class="divider-line"></div>
        <span class="divider-text">Or</span>
        <div class="divider-line"></div>
      </div>
      
      <div class="input-group">
        <input type="email" id="email" placeholder="Email" class="input-field" value="demo@quizzleberry.com">
        <div class="input-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 11H5V21H19V11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M17 9V8C17 5.23858 14.7614 3 12 3C9.23858 3 7 5.23858 7 8V9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>
      
      <div class="input-group">
        <input type="password" id="password" placeholder="Password" class="input-field" value="berry123">
        <div class="input-icon password-toggle" onclick="togglePassword()">
          <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>
      
      <div class="checkbox-group">
        <input type="checkbox" id="remember" class="checkbox" checked>
        <label for="remember" class="checkbox-label">Remember for 30 days</label>
      </div>
      
      <button id="loginButton" class="login-btn" onclick="handleLogin()">Log in</button>
      
      <p class="signup-text">
        Don't have any account? <a href="#" class="signup-link">Sign up</a>
      </p>
    </div>
    
    <div class="mascot-container">
      <img id="mascot" src="111.png" alt="Quizzleberry Mascot" class="mascot-image">
    </div>
  </div>

  <script>
    // Toggle password visibility
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      
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

    // Handle login
    function handleLogin() {
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      const successMessage = document.getElementById('successMessage');
      const mascot = document.getElementById('mascot');
      const loginButton = document.getElementById('loginButton');
      
      // Simple validation
      if (!email || !password) {
        alert('Please enter both email and password');
        return;
      }
      
      // Simulate login process
      loginButton.textContent = 'Logging in...';
      loginButton.disabled = true;
      
      setTimeout(() => {
        // Show success message
        successMessage.style.display = 'block';
        
        // Animate mascot
        mascot.classList.add('mascot-animation');
        
        // Reset button
        loginButton.textContent = 'Logged In!';
        loginButton.style.backgroundColor = '#10b981';
        
        // Redirect to dashboard after a short delay
        setTimeout(() => {
          window.location.href = 'index.html'; // Redirect to the dashboard
        }, 1500);
      }, 1000);
    }
  </script>
</body>
</html>

