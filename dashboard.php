<?php
include_once("connection.php");
session_start();

$con = connection();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$errors = [];
$success = false;

// Fetch user data
$userQuery = $con->prepare("SELECT * FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();

// Handle form submission for updating user information
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_account'])) {
        $fullname = trim($_POST["fullname"]);
        $email = trim($_POST["email"]);

        // Validate inputs
        if (empty($fullname)) {
            $errors[] = "Full name is required.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        // Check if email is already used by another user
        $checkEmail = $con->prepare("SELECT * FROM users WHERE email = ? AND id != ?");
        $checkEmail->bind_param("si", $email, $userId);
        $checkEmail->execute();
        $checkResult = $checkEmail->get_result();

        if ($checkResult->num_rows > 0) {
            $errors[] = "Email is already in use by another account.";
        }

        // Update user information if no errors
        if (empty($errors)) {
            $updateQuery = $con->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
            $updateQuery->bind_param("ssi", $fullname, $email, $userId);

            if ($updateQuery->execute()) {
                $success = true;
                $user['fullname'] = $fullname;
                $user['email'] = $email;
            } else {
                $errors[] = "Failed to update account. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzleberry Dashboard</title>
    <style>
        :root {
           --primary: #4b2c91;
           --primary-light: #9747ff;
           --primary-dark: #3a2170;
           --secondary: #10b981;
           --flashcard-primary: #4ca64c;
           --flashcard-light: #5eba5e;
           --flashcard-dark: #3a8a3a;
           --light-bg: #f8f9fa;
           --border: #e5e7eb;
           --text: #333;
           --text-light: #6b7280;
           --white: #ffffff;
           --red: #ef4444;
           --green: #22c55e;
            
            /* Berry palette colors */
             --berry-purple-dark: #3a2170;
             --berry-purple: #5e3a96;
             --berry-purple-medium: #7e56c2;
             --berry-lavender: #a98bd3;
             --berry-green-light: #a3e6a3;
             --berry-green: #4ca64c;
}

        /* Berry Background Animation */
        .berry-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-6index: -1;
            overflow: hidden;
            opacity: 0.7;
        }

        .berry-bubble {
            position: absolute;
            border-radius: 50%;
            opacity: 0.6;
            filter: blur(8px);
            animation: float 15s infinite ease-in-out;
        }

        .berry-bubble:nth-child(1) {
            width: 150px;
            height: 150px;
            background-color: var(--berry-purple-dark);
            left: 10%;
            top: 20%;
            animation-delay: 0s;
            animation-duration: 25s;
        }

        .berry-bubble:nth-child(2) {
            width: 100px;
            height: 100px;
            background-color: var(--berry-purple);
            left: 30%;
            top: 60%;
            animation-delay: 2s;
            animation-duration: 18s;
        }

        .berry-bubble:nth-child(3) {
            width: 180px;
            height: 180px;
            background-color: var(--berry-purple-medium);
            right: 25%;
            top: 15%;
            animation-delay: 5s;
            animation-duration: 20s;
        }

        .berry-bubble:nth-child(4) {
            width: 120px;
            height: 120px;
            background-color: var(--berry-lavender);
            right: 10%;
            bottom: 20%;
            animation-delay: 1s;
            animation-duration: 22s;
        }

        .berry-bubble:nth-child(5) {
            width: 200px;
            height: 200px;
            background-color: var(--berry-pink-light);
            left: 40%;
            bottom: 10%;
            animation-delay: 3s;
            animation-duration: 19s;
        }

        .berry-bubble:nth-child(6) {
            width: 160px;
            height: 160px;
            background-color: var(--berry-pink);
            right: 35%;
            top: 50%;
            animation-delay: 7s;
            animation-duration: 23s;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
            25% {
                transform: translate(5%, 10%) rotate(5deg) scale(1.05);
            }
            50% {
                transform: translate(-5%, 15%) rotate(-5deg) scale(0.95);
            }
            75% {
                transform: translate(8%, 5%) rotate(8deg) scale(1.1);
            }
            100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
        }

        /* Update body background */
        body {
            background-color: var(--white);
            color: var(--text);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Update main content to have a gradient background */
        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(169, 139, 211, 0.3));
            backdrop-filter: blur(5px);
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            z-index: 1;
            position: relative;
        }

        /* Update sidebar to have a gradient background */
        .sidebar {
            width: 240px;
            background: linear-gradient(to bottom, #4b2c91, #7e56c2);
            backdrop-filter: blur(5px);
            border-right: 1px solid var(--border);
            padding: 1.5rem 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 2;
            color: white;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
    
        /* Sidebar */
        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1rem;
        }
        
        .sidebar-logo {
            height: 40px;
            margin-right: 10px;
        }
        
        /* Update sidebar-brand for better contrast */
        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: bold;
            color: white;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0 1rem;
        }
        
        .sidebar-nav-item {
            margin-bottom: 0.5rem;
        }
        
        /* Update sidebar-nav-link to have better contrast with the new background */
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .sidebar-nav-link:hover, .sidebar-nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .sidebar-nav-link i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        
        /* Main Content */
        
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        
        .create-btn {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 9999px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
        }
        
        .create-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .create-btn i {
            margin-right: 0.5rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--primary-dark);
        }
        
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .card {
            background-color: var(--white);
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            padding: 1.5rem;
            color: var(--white);
            position: relative;
        }
        
        .card-header.flashcard {
            background: linear-gradient(to right, var(--flashcard-primary), var(--flashcard-light));
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .card-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .stat {
            text-align: center;
            flex: 1;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: var(--text-light);
        }
        
        .card-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .card-btn {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid var(--border);
            background-color: var(--white);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .card-btn:hover {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }
        
        .card-btn.primary {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }
        
        .card-btn.primary:hover {
            background-color: var(--primary-dark);
        }
        
        .card-btn.flashcard {
            background-color: var(--flashcard-primary);
            color: var(--white);
            border-color: var(--flashcard-primary);
        }
        
        .card-btn.flashcard:hover {
            background-color: var(--flashcard-dark);
        }
        
        /* Card Menu */
        .card-menu {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            z-index: 10;
        }
        
        .card-menu-btn {
            background: none;
            border: none;
            color: var(--white);
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s;
        }
        
        .card-menu-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .card-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 160px;
            z-index: 20;
            overflow: hidden;
            display: none;
        }
        
        .card-menu-dropdown.active {
            display: block;
        }
        
        .card-menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text);
            text-decoration: none;
            transition: background-color 0.2s;
            cursor: pointer;
        }
        
        .card-menu-item:hover {
            background-color: var(--light-bg);
        }
        
        .card-menu-item i {
            margin-right: 0.75rem;
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }
        
        .card-menu-item.delete {
            color: var(--red);
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s, visibility 0.2s;
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background-color: var(--white);
            border-radius: 0.75rem;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(20px);
            transition: transform 0.3s;
        }

        /* Make the modal wider when in flashcard creation view */
        .modal.flashcard-mode {
            max-width: 1000px;
        }

        /* Make the modal wider when in study guide view */
        .modal.study-guide-mode {
            max-width: 1000px;
        }
        
        .modal-overlay.active .modal {
            transform: translateY(0);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-dark);
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        /* Option Cards */
        .option-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .option-card {
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 1.5rem;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .option-card:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .option-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .option-icon {
            font-size: 2rem;
            color: var(--primary);
            flex-shrink: 0;
        }
        
        .option-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .option-description {
            font-size: 0.875rem;
            color: var(--text-light);
        }
        
        /* Tabs */
        .tabs {
            margin-top: 1.5rem;
        }
        
        .tab-list {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }
        
        .tab-trigger {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            font-weight: 500;
            cursor: pointer;
            transition: color 0.2s, border-color 0.2s;
        }
        
        .tab-trigger.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.375rem;
            font-size: 1rem;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(75, 44, 145, 0.2);
        }
        
        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--text);
            border: 1px solid var(--border);
        }
        
        .btn-outline:hover {
            background-color: var(--light-bg);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .btn-icon {
            margin-right: 0.5rem;
        }
        
        /* Flashcard Form */
        .flashcard-form {
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .flashcard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .flashcard-title {
            font-weight: 600;
        }
        
        /* Back Button */
        .back-button-container {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .back-button {
            margin-right: 1rem;
        }
        
        /* New Flashcard Creation Styles */
        .flashcard-creation-container {
            background-color: var(--white);
            border-radius: 0.75rem;
            padding: 1.5rem;
        }

        .flashcard-creation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .flashcard-creation-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .flashcard-creation-actions {
            display: flex;
            gap: 0.75rem;
        }

        .flashcard-set-info {
            margin-bottom: 1.5rem;
        }

        .flashcard-card {
            background-color: var(--light-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .flashcard-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .flashcard-card-number {
            font-weight: 600;
            color: var(--primary);
        }

        .flashcard-card-actions {
            display: flex;
            gap: 0.5rem;
        }

        .flashcard-card-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .flashcard-field {
            position: relative;
        }

        .flashcard-field-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 0.25rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.375rem;
            font-size: 1rem;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(75, 44, 145, 0.2);
        }

        /* Fix the image delete button positioning */
        .image-delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: var(--white);
            border: 1px solid var(--border);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
        }

        .flashcard-image-upload {
            border: 1px dashed var(--border);
            border-radius: 0.375rem;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
            height: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-top: 1rem;
        }

        .flashcard-image-icon {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .flashcard-image-text {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        .add-card-button {
            background-color: transparent;
            border: 1px dashed var(--border);
            border-radius: 0.5rem;
            padding: 1rem;
            width: 100%;
            text-align: center;
            cursor: pointer;
            color: var(--primary);
            font-weight: 500;
            transition: background-color 0.2s, border-color 0.2s;
            margin-bottom: 1.5rem;
        }

        .add-card-button:hover {
            background-color: rgba(151, 71, 255, 0.05);
            border-color: var(--primary);
        }

        .flashcard-footer {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .flashcard-method-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
        }

        .flashcard-method-card {
            background-color: #2d3748;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            color: var(--white);
        }

        .flashcard-method-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }

        .flashcard-method-icon {
            font-size: 2.5rem;
            color: var(--primary-light);
            margin-bottom: 1rem;
        }

        .flashcard-method-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
        }

        .flashcard-toolbar {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .flashcard-toolbar-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid var(--border);
            background-color: var(--white);
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .flashcard-toolbar-button:hover {
            background-color: var(--light-bg);
        }

        /* Question Card Styles */
        .question-card {
            background-color: var(--light-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .question-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .question-card-number {
            font-weight: 600;
            color: var(--primary);
        }

        .question-card-actions {
            display: flex;
            gap: 0.5rem;
        }

        .question-type-selector {
            margin-bottom: 1.5rem;
        }

        .question-type-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .question-type-options {
            display: flex;
            gap: 1rem;
        }

        .question-type-option {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .question-type-option.active {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .multiple-choice-options {
            margin-top: 1rem;
        }

        .multiple-choice-option {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            gap: 0.5rem;
        }

        .option-letter {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background-color: var(--light-bg);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            flex-shrink: 0;
        }

        .option-input {
            flex: 1;
        }

        .correct-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .correct-option-label {
            font-weight: 500;
        }

        .add-option-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
        }

        .add-option-btn:hover {
            text-decoration: underline;
        }

        .true-false-options {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .true-false-option {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.375rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .true-false-option.selected {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .add-question-btn {
            background-color: transparent;
            border: 1px dashed var(--border);
            border-radius: 0.5rem;
            padding: 1rem;
            width: 100%;
            text-align: center;
            cursor: pointer;
            color: var(--primary);
            font-weight: 500;
            transition: background-color 0.2s, border-color 0.2s;
            margin-bottom: 1.5rem;
        }

        .add-question-btn:hover {
            background-color: rgba(151, 71, 255, 0.05);
            border-color: var(--primary);
        }

        /* Quiz and Flashcard Player Styles */
        .quiz-player-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s, visibility 0.2s;
        }
        
        .quiz-player-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .quiz-player {
            background-color: var(--white);
            border-radius: 0.75rem;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(20px);
            transition: transform 0.3s;
        }
        
        .quiz-player-overlay.active .quiz-player {
            transform: translateY(0);
        }
        
        .quiz-player-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .quiz-player-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-dark);
        }
        
        .quiz-player-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }
        
        .quiz-player-body {
            padding: 1.5rem;
        }
        
        .quiz-player-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-top: 1px solid var(--border);
        }
        
        .quiz-timer {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .quiz-timer-icon {
            font-size: 1.5rem;
        }
        
        .quiz-progress {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quiz-progress-bar {
            width: 200px;
            height: 8px;
            background-color: var(--border);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .quiz-progress-fill {
            height: 100%;
            background-color: var(--primary);
            transition: width 0.3s;
        }
        
        .quiz-progress-text {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-light);
        }
        
        .quiz-question {
            margin-bottom: 2rem;
        }
        
        .quiz-question-text {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .quiz-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .quiz-option {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .quiz-option:hover {
            background-color: var(--light-bg);
        }
        
        .quiz-option.selected {
            border-color: var(--primary);
            background-color: rgba(151, 71, 255, 0.1);
        }
        
        .quiz-option.correct {
            border-color: var(--green);
            background-color: rgba(34, 197, 94, 0.1);
        }
        
        .quiz-option.incorrect {
            border-color: var(--red);
            background-color: rgba(239, 68, 68, 0.1);
        }
        
        .quiz-option-letter {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--light-bg);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            flex-shrink: 0;
        }
        
        .quiz-option-text {
            flex: 1;
        }
        
        .quiz-result {
            text-align: center;
            padding: 2rem 0;
        }
        
        .quiz-result-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .quiz-result-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .quiz-result-score {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .quiz-result-message {
            font-size: 1rem;
            color: var(--text-light);
            margin-bottom: 2rem;
        }
        
        /* Flashcard Player Styles */
        .flashcard-player {
            perspective: 1000px;
            width: 100%;
            height: 300px;
            margin-bottom: 2rem;
        }
        
        .flashcard-inner {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
            transition: transform 0.6s;
            transform-style: preserve-3d;
            cursor: pointer;
        }
        
        .flashcard-inner.flipped {
            transform: rotateY(180deg);
        }
        
        .flashcard-front, .flashcard-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            border-radius: 0.75rem;
            background-color: var(--light-bg);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .flashcard-front {
            background-color: var(--white);
            color: var(--text);
        }
        
        .flashcard-back {
            background-color: var(--flashcard-light);
            color: var(--white);
            transform: rotateY(180deg);
        }
        
        .flashcard-text {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .flashcard-hint {
            position: absolute;
            bottom: 1rem;
            font-size: 0.875rem;
            color: var(--text-light);
            opacity: 0.7;
        }
        
        .flashcard-controls {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .flashcard-btn {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            background-color: var(--white);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .flashcard-btn:hover {
            background-color: var(--light-bg);
        }
        
        .flashcard-btn.primary {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }
        
        .flashcard-btn.primary:hover {
            background-color: var(--primary-dark);
        }
        
        .flashcard-progress {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .flashcard-progress-bar {
            flex: 1;
            height: 4px;
            background-color: var(--border);
            border-radius: 2px;
            margin: 0 1rem;
            overflow: hidden;
        }
        
        .flashcard-progress-fill {
            height: 100%;
            background-color: var(--primary);
            transition: width 0.3s;
        }
        
        .flashcard-counter {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-light);
        }
        
        /* Mastery tracking styles */
        .mastery-toggle-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
        }
        
        .mastery-toggle-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--white);
        }
        
        .mastery-toggle {
            position: relative;
            width: 40px;
            height: 20px;
        }
        
        .mastery-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .mastery-toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.3);
            transition: .4s;
            border-radius: 34px;
        }
        
        .mastery-toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .mastery-toggle-slider {
            background-color: var(--primary-light);
        }
        
        input:checked + .mastery-toggle-slider:before {
            transform: translateX(20px);
        }
        
        .mastery-buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1rem;
            padding: 0.5rem;
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
        }
        
        .mastery-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid;
            font-size: 1.25rem;
        }
        
        .mastery-btn.cross {
            border-color: var(--red);
            color: var(--red);
        }
        
        .mastery-btn.cross:hover {
            background-color: var(--red);
            color: var(--white);
        }
        
        .mastery-btn.check {
            border-color: var(--green);
            color: var(--green);
        }
        
        .mastery-btn.check:hover {
            background-color: var(--green);
            color: var(--white);
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 0;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: var(--primary-light);
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-dark);
        }
        
        .empty-state-message {
            font-size: 1rem;
            color: var(--text-light);
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Timer settings for practice test */
        .timer-settings {
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: var(--light-bg);
            border-radius: 0.5rem;
        }
        
        .timer-settings-title {
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .timer-input-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .timer-input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 0.375rem;
            text-align: center;
        }

        /* Add CSS for active mastery buttons */
        .mastery-btn.active {
            background-color: currentColor;
            color: var(--white);
        }

        .icon svg {
            display: inline-block;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <!-- Berry Background Animation -->
    <div class="berry-background">
        <div class="berry-bubble"></div>
        <div class="berry-bubble"></div>
        <div class="berry-bubble"></div>
        <div class="berry-bubble"></div>
        <div class="berry-bubble"></div>
        <div class="berry-bubble"></div>
    </div>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="111.png" alt="Quizzleberry Logo" class="sidebar-logo">
            <div class="sidebar-brand">Quizzleberry</div>
        </div>
        
        <ul class="sidebar-nav">
    <li class="sidebar-nav-item">
        <a href="dashboard.html" class="sidebar-nav-link active">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-home"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </i> Home
        </a>
    </li>
    <li class="sidebar-nav-item">
        <a href="settings.html" class="sidebar-nav-link">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            </i> Settings
        </a>
    </li>
</ul>     
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            
            
            <button class="create-btn" id="create-quiz-btn">
                <i class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                </i> Create New
            </button>
        </div>
        
        <!-- Flashcard Sets Section -->
        <h2 class="section-title">Your Flashcard Sets</h2>
        <div class="card-grid" id="flashcard-sets-container">
            <!-- Flashcard sets will be dynamically added here -->
        </div>
        
        <!-- Quiz Sets Section -->
        <h2 class="section-title">Your Quiz Sets</h2>
        <div class="card-grid" id="quiz-sets-container">
            <!-- Quiz sets will be dynamically added here -->
        </div>
        
        <!-- Empty state for when no quizzes exist -->
        <div id="empty-state" style="display: none;">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <h3 class="empty-state-title">No Quiz Sets Yet</h3>
                <p class="empty-state-message">Create your first quiz set by clicking the "Create Quiz" button above.</p>
                <button class="btn btn-primary" id="empty-state-create-btn">
                    <i class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    </i> Create Your First Quiz
                </button>
            </div>
        </div>
    </div>
    
    <!-- Create Quiz Modal -->
    <div class="modal-overlay" id="create-quiz-modal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Create</h3>
                <button class="modal-close" id="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Initial Options View -->
                <div id="initial-options">
                    <div class="option-grid">
                        <div class="option-card" data-option="flashcards">
                            <div class="option-header">
                                <div class="option-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>
                                </div>
                                <div>
                                    <h4 class="option-title">Flashcard Set</h4>
                                    <p class="option-description">Create digital flashcards for quick study sessions</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="option-card" data-option="practice-test">
                            <div class="option-header">
                                <div class="option-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-square"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                </div>
                                <div>
                                    <h4 class="option-title">Quiz Set</h4>
                                    <p class="option-description">Create quizzes with various question types</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Flashcard Creation View -->
                <div id="flashcard-creation-view" style="display: none;">
                    <div class="back-button-container">
                        <button class="btn btn-sm btn-outline back-button" data-target="initial-options">
                            <i class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                            </i> Back
                        </button>
                    </div>
                    
                    <div class="flashcard-set-info">
                        <div class="form-group">
                            <label class="form-label" for="flashcard-set-title">Title</label>
                            <input type="text" id="flashcard-set-title" class="form-input" placeholder="Enter a title for your quiz set">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="flashcard-set-description">Description (optional)</label>
                            <input type="text" id="flashcard-set-description" class="form-input" placeholder="Add a description">
                        </div>
                    </div>
                    
                    <div id="flashcard-cards-container">
                        <!-- Card 1 -->
                        <div class="flashcard-card" data-card-id="1">
                            <div class="flashcard-card-header">
                                <div class="flashcard-card-number">1</div>
                                <div class="flashcard-card-actions">
                                    <button class="btn btn-sm btn-outline move-card-up" title="Move up">
                                        <i class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-up"><path d="m18 15-6-6-6 6"/></svg>
                                        </i>
                                    </button>
                                    <button class="btn btn-sm btn-outline move-card-down" title="Move down">
                                        <i class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down"><path d="m6 9 6 6 6-6"/></svg>
                                        </i>
                                    </button>
                                    <button class="btn btn-sm btn-outline delete-card" title="Delete card">
                                        <i class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        </i>
                                    </button>
                                </div>
                            </div>
                            <div class="flashcard-card-content">
                                <div class="flashcard-field">
                                    <div class="flashcard-field-label">Term</div>
                                    <input type="text" class="form-input flashcard-term" placeholder="Enter term">
                                </div>
                                <div class="flashcard-field">
                                    <div class="flashcard-field-label">Definition</div>
                                    <input type="text" class="form-input flashcard-definition" placeholder="Enter definition">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card 2 -->
                        <div class="flashcard-card" data-card-id="2">
                            <div class="flashcard-card-header">
                                <div class="flashcard-card-number">2</div>
                                <div class="flashcard-card-actions">
                                    <button class="btn btn-sm btn-outline move-card-up" title="Move up">
                                        <i class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-up"><path d="m18 15-6-6-6 6"/></svg>
                                        </i>
                                    </button>
                                    <button class="btn btn-sm btn-outline move-card-down" title="Move down">
                                        <i class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down"><path d="m6 9 6 6 6-6"/></svg>
                                        </i>
                                    </button>
                                    <button class="btn btn-sm btn-outline delete-card" title="Delete card">
                                        <i class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        </i>
                                    </button>
                                </div>
                            </div>
                            <div class="flashcard-card-content">
                                <div class="flashcard-field">
                                    <div class="flashcard-field-label">Term</div>
                                    <input type="text" class="form-input flashcard-term" placeholder="Enter term">
                                </div>
                                <div class="flashcard-field">
                                    <div class="flashcard-field-label">Definition</div>
                                    <input type="text" class="form-input flashcard-definition" placeholder="Enter definition">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button class="add-card-button" id="add-card-button">
                        <i class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </i> Add Card
                    </button>
                    
                    <div class="flashcard-footer">
                        <button class="btn btn-outline" id="cancel-flashcards">Cancel</button>
                        <button class="btn btn-primary" id="save-flashcards">Create</button>
                    </div>
                </div>
                
                <!-- Practice Test View -->
                <div id="practice-test-view" style="display: none;">
                    <div class="back-button-container">
                        <button class="btn btn-sm btn-outline back-button" data-target="initial-options">
                            <i class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                            </i> Back
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="test-title">Test Title</label>
                        <input type="text" id="test-title" class="form-input" placeholder="Enter a title for your quiz set">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="test-description">Description (optional)</label>
                        <input type="text" id="test-description" class="form-input" placeholder="Add a description">
                    </div>
                    
                    <!-- Timer Settings -->
                    <div class="timer-settings">
                        <div class="timer-settings-title">Time Limit</div>
                        <div class="timer-input-group">
                            <input type="number" id="test-timer-minutes" class="timer-input" min="1" max="180" value="30">
                            <label for="test-timer-minutes">minutes</label>
                        </div>
                    </div>
                    
                    <div id="questions-container">
                        <!-- Question 1 -->
                        <div class="question-card" data-question-id="1">
                            <div class="question-card-header">
                                <div class="question-card-number">Question 1</div>
                                <div class="question-card-actions">
                                    <button class="btn btn-sm btn-outline delete-question" title="Delete question">
                                        <i class="icon">🗑️</i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="question-1-text">Question</label>
                                <input type="text" id="question-1-text" class="form-input question-text" placeholder="Enter your question">
                            </div>
                            
                            <div class="question-type-selector">
                                <label class="question-type-label">Question Type</label>
                                <div class="question-type-options">
                                    <div class="question-type-option active" data-type="multiple-choice">Multiple Choice</div>
                                    <div class="question-type-option" data-type="true-false">True/False</div>
                                </div>
                            </div>
                            
                            <div class="question-type-content multiple-choice-content">
                                <div class="multiple-choice-options">
                                    <div class="multiple-choice-option">
                                        <div class="option-letter">A</div>
                                        <input type="text" class="form-input option-input" placeholder="Enter option A">
                                    </div>
                                    <div class="multiple-choice-option">
                                        <div class="option-letter">B</div>
                                        <input type="text" class="form-input option-input" placeholder="Enter option B">
                                    </div>
                                    <div class="multiple-choice-option">
                                        <div class="option-letter">C</div>
                                        <input type="text" class="form-input option-input" placeholder="Enter option C">
                                    </div>
                                    <div class="multiple-choice-option">
                                        <div class="option-letter">D</div>
                                        <input type="text" class="form-input option-input" placeholder="Enter option D">
                                    </div>
                                </div>
                                
                                <button class="add-option-btn">
                                    <i class="icon">+</i> Add Option
                                </button>
                                
                                <div class="correct-option">
                                    <label class="correct-option-label" for="question-1-correct">Correct Answer:</label>
                                    <select id="question-1-correct" class="form-input">
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="question-type-content true-false-content" style="display: none;">
                                <div class="true-false-options">
                                    <div class="true-false-option selected" data-value="true">True</div>
                                    <div class="true-false-option" data-value="false">False</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button class="add-question-btn" id="add-question-button">
                        <i class="icon">+</i> Add Question
                    </button>
                    
                    <div class="flashcard-footer">
                        <button class="btn btn-outline" id="cancel-test">Cancel</button>
                        <button class="btn btn-primary" id="create-test">Create</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quiz Player Modal -->
    <div class="quiz-player-overlay" id="quiz-player-modal">
        <div class="quiz-player">
            <div class="quiz-player-header">
                <h3 class="quiz-player-title" id="quiz-player-title">Quiz Title</h3>
                <button class="quiz-player-close" id="quiz-player-close">&times;</button>
            </div>
            <div class="quiz-player-body" id="quiz-player-body">
                <!-- Quiz content will be dynamically inserted here -->
            </div>
            <div class="quiz-player-footer">
                <div class="quiz-timer">
                    <i class="icon quiz-timer-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-timer"><path d="M10 2h4"/><path d="M12 14v-4"/><path d="M4 13a8 8 0 0 1 8-7 8 8 0 1 1-5.3 14L4 17.6"/><path d="M9 17H4v5"/></svg>
                    </i>
                    <span id="quiz-timer-display">00:00</span>
                </div>
                <div class="quiz-progress">
                    <div class="quiz-progress-bar">
                        <div class="quiz-progress-fill" id="quiz-progress-fill" style="width: 0%;"></div>
                    </div>
                    <div class="quiz-progress-text" id="quiz-progress-text">0/0</div>
                </div>
                <button class="btn btn-primary" id="quiz-next-btn">Next</button>
            </div>
        </div>
    </div>
    
    <!-- Flashcard Player Modal -->
    <div class="quiz-player-overlay" id="flashcard-player-modal">
        <div class="quiz-player">
            <div class="quiz-player-header">
                <h3 class="quiz-player-title" id="flashcard-player-title">Flashcard Set Title</h3>
                <button class="quiz-player-close" id="flashcard-player-close">&times;</button>
            </div>
            <div class="quiz-player-body" id="flashcard-player-body">
                <!-- Flashcard content will be dynamically inserted here -->
            </div>
            <div class="quiz-player-footer">
                <div class="mastery-toggle-container">
                    <span class="mastery-toggle-label">Track mastery</span>
                    <label class="mastery-toggle">
                        <input type="checkbox" id="mastery-toggle-checkbox">
                        <span class="mastery-toggle-slider"></span>
                    </label>
                </div>
                <div class="mastery-buttons" id="mastery-buttons" style="display: none;">
                    <button class="mastery-btn cross" id="mastery-cross-btn" title="Still learning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                    <button class="mastery-btn check" id="mastery-check-btn" title="Mastered">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                </div>
                <div class="flashcard-controls">
                    <button class="flashcard-btn" id="flashcard-prev-btn">
                        <i class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left"><path d="m15 18-6-6 6-6"/></svg>
                        </i> Previous
                    </button>
                    <button class="flashcard-btn" id="flashcard-flip-btn">
                        <i class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>
                        </i> Flip
                    </button>
                    <button class="flashcard-btn primary" id="flashcard-next-btn">
                        Next <i class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
                        </i>
                    </button>
                </div>
                <div class="flashcard-progress">
                    <div class="flashcard-counter" id="flashcard-counter">1/10</div>
                    <div class="flashcard-progress-bar">
                        <div class="flashcard-progress-fill" id="flashcard-progress-fill" style="width: 10%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Modal functionality
const modal = document.getElementById('create-quiz-modal');
const createBtn = document.getElementById('create-quiz-btn');
const closeBtn = document.getElementById('modal-close');
const modalBody = document.getElementById('modal-body');
const modalTitle = document.getElementById('modal-title');
const initialOptions = document.getElementById('initial-options');
const flashcardCreationView = document.getElementById('flashcard-creation-view');
const practiceTestView = document.getElementById('practice-test-view');
const backButtons = document.querySelectorAll('.back-button');
const optionCards = document.querySelectorAll('.option-card');
const addCardButton = document.getElementById('add-card-button');
const flashcardCardsContainer = document.getElementById('flashcard-cards-container');
const saveFlashcardsBtn = document.getElementById('save-flashcards');
const cancelFlashcardsBtn = document.getElementById('cancel-flashcards');
const createTestBtn = document.getElementById('create-test');
const cancelTestBtn = document.getElementById('cancel-test');
const addQuestionButton = document.getElementById('add-question-button');
const questionsContainer = document.getElementById('questions-container');
const quizSetsContainer = document.getElementById('quiz-sets-container');
const flashcardSetsContainer = document.getElementById('flashcard-sets-container');
const emptyState = document.getElementById('empty-state');
const emptyStateCreateBtn = document.getElementById('empty-state-create-btn');

// Quiz Player Elements
const quizPlayerModal = document.getElementById('quiz-player-modal');
const quizPlayerClose = document.getElementById('quiz-player-close');
const quizPlayerTitle = document.getElementById('quiz-player-title');
const quizPlayerBody = document.getElementById('quiz-player-body');
const quizTimerDisplay = document.getElementById('quiz-timer-display');
const quizProgressFill = document.getElementById('quiz-progress-fill');
const quizProgressText = document.getElementById('quiz-progress-text');
const quizNextBtn = document.getElementById('quiz-next-btn');

// Flashcard Player Elements
const flashcardPlayerModal = document.getElementById('flashcard-player-modal');
const flashcardPlayerClose = document.getElementById('flashcard-player-close');
const flashcardPlayerTitle = document.getElementById('flashcard-player-title');
const flashcardPlayerBody = document.getElementById('flashcard-player-body');
const flashcardPrevBtn = document.getElementById('flashcard-prev-btn');
const flashcardFlipBtn = document.getElementById('flashcard-flip-btn');
const flashcardNextBtn = document.getElementById('flashcard-next-btn');
const flashcardCounter = document.getElementById('flashcard-counter');
const flashcardProgressFill = document.getElementById('flashcard-progress-fill');
const masteryToggleCheckbox = document.getElementById('mastery-toggle-checkbox');
const masteryButtons = document.getElementById('mastery-buttons');
const masteryCrossBtn = document.getElementById('mastery-cross-btn');
const masteryCheckBtn = document.getElementById('mastery-check-btn');

let cardCounter = 2; // Start at 2 since we already have 2 cards
let questionCounter = 1; // Start at 1 since we already have 1 question

// Quiz Player Variables
let currentQuiz = null;
let currentQuizIndex = 0;
let quizUserAnswers = [];
let quizScores = {}; // Track quiz scores
let quizTimeLimit = 0; // Time limit in minutes

// Add this with other variable declarations
let quizTimer;
let quizStartTime;

// Flashcard Player Variables
let currentFlashcardSet = null;
let currentFlashcardIndex = 0;
let isFlashcardFlipped = false;
let flashcardMasteryData = {}; // Track mastery data for flashcards

// Initialize the dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadQuizSets();
    
    // Empty state create button
    if (emptyStateCreateBtn) {
        emptyStateCreateBtn.addEventListener('click', function() {
            modal.classList.add('active');
            showView(initialOptions);
            modalTitle.textContent = 'Create New';
        });
    }
    
    // Mastery toggle
    masteryToggleCheckbox.addEventListener('change', function() {
        if (this.checked) {
            masteryButtons.style.display = 'flex';
            document.querySelector('.flashcard-controls').style.display = 'none';
        } else {
            masteryButtons.style.display = 'none';
            document.querySelector('.flashcard-controls').style.display = 'flex';
        }
        
        // Save the mastery tracking preference for this set
        if (currentFlashcardSet) {
            const setId = currentFlashcardSet.created_at;
            if (!flashcardMasteryData[setId]) {
                flashcardMasteryData[setId] = {};
            }
            flashcardMasteryData[setId].trackingEnabled = this.checked;
            localStorage.setItem('flashcardMasteryData', JSON.stringify(flashcardMasteryData));
        }
    });
    
    // Mastery buttons
    masteryCrossBtn.addEventListener('click', function() {
        markCardMastery(false);
        moveToNextCard();
    });
    
    masteryCheckBtn.addEventListener('click', function() {
        markCardMastery(true);
        moveToNextCard();
    });
});

// Load quiz sets from localStorage
function loadQuizSets() {
    let flashcardSets = [];
    let practiceTests = [];
    
    try {
        const savedFlashcardSets = localStorage.getItem('flashcardSets');
        if (savedFlashcardSets) {
            flashcardSets = JSON.parse(savedFlashcardSets);
        }
        
        const savedPracticeTests = localStorage.getItem('practiceTests');
        if (savedPracticeTests) {
            practiceTests = JSON.parse(savedPracticeTests);
        }
        
        // Load quiz scores
        const savedScores = localStorage.getItem('quizScores');
        if (savedScores) {
            quizScores = JSON.parse(savedScores);
        }
        
        // Load flashcard mastery data
        const savedMasteryData = localStorage.getItem('flashcardMasteryData');
        if (savedMasteryData) {
            flashcardMasteryData = JSON.parse(savedMasteryData);
        }
    } catch (e) {
        console.error('Error loading quiz sets:', e);
    }
    
    // Check if we have any user-created content
    const hasUserContent = flashcardSets.length > 0 || practiceTests.length > 0;
    
    // Show/hide empty state
    emptyState.style.display = hasUserContent ? 'none' : 'block';
    
    // Clear existing user-created cards
    const existingFlashcardCards = flashcardSetsContainer.querySelectorAll('.user-created-card');
    existingFlashcardCards.forEach(card => card.remove());
    
    const existingQuizCards = quizSetsContainer.querySelectorAll('.user-created-card');
    existingQuizCards.forEach(card => card.remove());
    
    // Add flashcard sets to the dashboard
    flashcardSets.forEach(set => {
        addCardToGrid(set, 'flashcard');
    });
    
    // Add practice tests to the dashboard
    practiceTests.forEach(test => {
        addCardToGrid(test, 'practice-test');
    });
}

// Add a card to the grid
function addCardToGrid(item, type) {
    const card = document.createElement('div');
    card.className = 'card user-created-card';
    card.setAttribute('data-id', item.created_at);
    
    // Get the mastered percentage
    let masteredPercentage = "0%";
    if (type === 'practice-test') {
        // For practice tests, show the actual score if they've taken the test
        if (quizScores[item.created_at] !== undefined) {
            // Cap quiz scores at 100%
            const score = Math.min(quizScores[item.created_at], 100);
            masteredPercentage = score + "%";
        } else {
            // If they haven't taken the test yet, show 0%
            masteredPercentage = "0%";
        }
    } else if (type === 'flashcard' && flashcardMasteryData[item.created_at]) {
        const masteryData = flashcardMasteryData[item.created_at];
        const totalCards = item.cards.length;
        
        // Count only actual mastery data (exclude trackingEnabled property)
        // Only count cards that have been marked as mastered (true)
        const masteredCards = Object.entries(masteryData)
            .filter(([key, value]) => key !== 'trackingEnabled' && value === true)
            .length;
        
        // Calculate percentage based on cards that have been mastered
        // Cap at 100%
        const percentage = Math.min(Math.round((masteredCards / totalCards) * 100), 100);
        masteredPercentage = percentage + "%";
    }
    
    const cardContent = `
        <div class="card-header ${type === 'flashcard' ? 'flashcard' : ''}">
            <h3 class="card-title">${item.title}</h3>
            <div class="card-subtitle">${item.description || (type === 'flashcard' ? 'Flashcard Set' : 'Quiz Set')}</div>
            <div class="card-menu">
                <button class="card-menu-btn" aria-label="Card menu">
                    <i class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-more-vertical"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </i>
                </button>
                <div class="card-menu-dropdown">
                    <div class="card-menu-item edit-item">
                        <i class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        </i> Edit
                    </div>
                    <div class="card-menu-item delete-item">
                        <i class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                        </i> Delete
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="card-stats">
                <div class="stat">
                    <div class="stat-value">${type === 'flashcard' ? item.cards.length : item.questions.length}</div>
                    <div class="stat-label">${type === 'flashcard' ? 'Cards' : 'Questions'}</div>
                </div>
                <div class="stat">
                    <div class="stat-value">${masteredPercentage}</div>
                    <div class="stat-label">Mastered</div>
                </div>
            </div>
            <div class="card-actions">
                ${type === 'flashcard' ? 
                '<button class="card-btn flashcard" data-id="' + item.created_at + '" data-type="' + type + '">Study</button>' : 
                '<button class="card-btn primary" data-id="' + item.created_at + '" data-type="' + type + '">Quiz</button>'}
            </div>
        </div>
    `;
    
    card.innerHTML = cardContent;
    
    // Add the card to the appropriate container
    if (type === 'flashcard') {
        flashcardSetsContainer.appendChild(card);
    } else {
        quizSetsContainer.appendChild(card);
    }
    
    // Add event listeners to the buttons
    if (type === 'flashcard') {
        const studyBtn = card.querySelector('.card-btn.flashcard');
        if (studyBtn) {
            studyBtn.addEventListener('click', function() {
                // Find the flashcard set in localStorage
                let flashcardSets = [];
                const savedFlashcardSets = localStorage.getItem('flashcardSets');
                if (savedFlashcardSets) {
                    flashcardSets = JSON.parse(savedFlashcardSets);
                    const setToStudy = flashcardSets.find(set => set.created_at === parseInt(item.created_at));
                    if (setToStudy) {
                        openFlashcardPlayer(setToStudy, 'study');
                    } else {
                        console.error('Could not find flashcard set with ID:', item.created_at);
                    }
                } else {
                    console.error('No flashcard sets found in localStorage');
                }
            });
        }
    } else {
        const quizBtn = card.querySelector('.card-btn.primary');
        if (quizBtn) {
            quizBtn.addEventListener('click', function() {
                console.log('Quiz button clicked for ID:', this.getAttribute('data-id'));
                try {
                    const savedPracticeTests = localStorage.getItem('practiceTests');
                    if (!savedPracticeTests) {
                        alert('No practice tests found in localStorage');
                        return;
                    }
                    
                    const practiceTests = JSON.parse(savedPracticeTests);
                    const cardId = parseInt(this.getAttribute('data-id'));
                    console.log('Looking for test with ID:', cardId);
                    console.log('Available tests:', practiceTests);
                    
                    const testToTake = practiceTests.find(test => parseInt(test.created_at) === cardId);
                    if (testToTake) {
                        openQuizPlayer(testToTake, 'quiz');
                    } else {
                        alert('Could not find practice test with ID: ' + cardId);
                    }
                } catch (error) {
                    console.error('Error loading quiz:', error);
                    alert('An error occurred while loading the quiz. Please try again.');
                }
            });
        }
    }
        
    // Add event listeners for card menu
    const menuBtn = card.querySelector('.card-menu-btn');
    const menuDropdown = card.querySelector('.card-menu-dropdown');
    
    menuBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        menuDropdown.classList.toggle('active');
        
        // Close other open menus
        document.querySelectorAll('.card-menu-dropdown.active').forEach(dropdown => {
            if (dropdown !== menuDropdown) {
                dropdown.classList.remove('active');
            }
        });
    });
    
    // Edit button
    const editBtn = card.querySelector('.edit-item');
    editBtn.addEventListener('click', function() {
        // Implement edit functionality
        menuDropdown.classList.remove('active');
        
        if (type === 'flashcard') {
            // Find the flashcard set in localStorage
            let flashcardSets = [];
            const savedFlashcardSets = localStorage.getItem('flashcardSets');
            if (savedFlashcardSets) {
                flashcardSets = JSON.parse(savedFlashcardSets);
                const setToEdit = flashcardSets.find(set => set.created_at === parseInt(item.created_at));
                
                if (setToEdit) {
                    // Open modal in edit mode
                    modal.classList.add('active');
                    showView(flashcardCreationView);
                    modalTitle.textContent = 'Edit Flashcard Set';
                    
                    // Populate form fields
                    document.getElementById('flashcard-set-title').value = setToEdit.title;
                    document.getElementById('flashcard-set-description').value = setToEdit.description || '';
                    
                    // Clear existing cards
                    flashcardCardsContainer.innerHTML = '';
                    
                    // Add cards from the set
                    setToEdit.cards.forEach(card => {
                        const newCard = document.createElement('div');
                        newCard.className = 'flashcard-card';
                        newCard.setAttribute('data-card-id', card.id);
                        
                        newCard.innerHTML = `
                            <div class="flashcard-card-header">
                                <div class="flashcard-card-number">${card.id}</div>
                                <div class="flashcard-card-actions">
                                    <button class="btn btn-sm btn-outline move-card-up" title="Move up">
                                        <i class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-up"><path d="m18 15-6-6-6 6"/></svg>
                                        </i>
                                    </button>
                                    <button class="btn btn-sm btn-outline move-card-down" title="Move down">
                                        <i class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down"><path d="m6 9 6 6 6-6"/></svg>
                                        </i>
                                    </button>
                                    <button class="btn btn-sm btn-outline delete-card" title="Delete card">
                                        <i class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        </i>
                                    </button>
                                </div>
                            </div>
                            <div class="flashcard-card-content">
                                <div class="flashcard-field">
                                    <div class="flashcard-field-label">Term</div>
                                    <input type="text" class="form-input flashcard-term" placeholder="Enter term" value="${card.term}">
                                </div>
                                <div class="flashcard-field">
                                    <div class="flashcard-field-label">Definition</div>
                                    <input type="text" class="form-input flashcard-definition" placeholder="Enter definition" value="${card.definition}">
                                </div>
                            </div>
                        `;
                        
                        flashcardCardsContainer.appendChild(newCard);
                        setupCardEventListeners(newCard);
                    });
                    
                    // Update cardCounter
                    cardCounter = setToEdit.cards.length;
                    
                    // Change save button to update
                    saveFlashcardsBtn.textContent = 'Update';
                    saveFlashcardsBtn.setAttribute('data-edit-id', String(item.created_at));
                }
            }
        } else if (type === 'practice-test') {
            // Find the practice test in localStorage
            let practiceTests = [];
            const savedPracticeTests = localStorage.getItem('practiceTests');
            if (savedPracticeTests) {
                practiceTests = JSON.parse(savedPracticeTests);
                const testToEdit = practiceTests.find(test => test.created_at === parseInt(item.created_at));
                
                if (testToEdit) {
                    // Open modal in edit mode
                    modal.classList.add('active');
                    showView(practiceTestView);
                    modalTitle.textContent = 'Edit Quiz';
                    
                    // Populate form fields
                    document.getElementById('test-title').value = testToEdit.title;
                    document.getElementById('test-description').value = testToEdit.description || '';
                    document.getElementById('test-timer-minutes').value = testToEdit.timeLimit || 30;
                    
                    // Clear existing questions
                    questionsContainer.innerHTML = '';
                    
                    // Add questions from the test
                    testToEdit.questions.forEach(question => {
                        const newQuestion = document.createElement('div');
                        newQuestion.className = 'question-card';
                        newQuestion.setAttribute('data-question-id', question.id);
                        
                        // Create question HTML based on type
                        let questionHTML = `
                            <div class="question-card-header">
                                <div class="question-card-number">Question ${question.id}</div>
                                <div class="question-card-actions">
                                    <button class="btn btn-sm btn-outline delete-question" title="Delete question">
                                        <i class="icon">🗑️</i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="question-${question.id}-text">Question</label>
                                <input type="text" id="question-${question.id}-text" class="form-input question-text" placeholder="Enter your question" value="${question.text}">
                            </div>
                            
                            <div class="question-type-selector">
                                <label class="question-type-label">Question Type</label>
                                <div class="question-type-options">
                                    <div class="question-type-option ${question.type === 'multiple-choice' ? 'active' : ''}" data-type="multiple-choice">Multiple Choice</div>
                                    <div class="question-type-option ${question.type === 'true-false' ? 'active' : ''}" data-type="true-false">True/False</div>
                                </div>
                            </div>
                        `;
                        
                        // Add type-specific content
                        if (question.type === 'multiple-choice') {
                            questionHTML += `
                                <div class="question-type-content multiple-choice-content">
                                    <div class="multiple-choice-options">
                            `;
                            
                            question.options.forEach(option => {
                                questionHTML += `
                                    <div class="multiple-choice-option">
                                        <div class="option-letter">${option.letter}</div>
                                        <input type="text" class="form-input option-input" placeholder="Enter option ${option.letter}" value="${option.text}">
                                    </div>
                                `;
                            });
                            
                            questionHTML += `
                                    </div>
                                    
                                    <button class="add-option-btn">
                                        <i class="icon">+</i> Add Option
                                    </button>
                                    
                                    <div class="correct-option">
                                        <label class="correct-option-label" for="question-${question.id}-correct">Correct Answer:</label>
                                        <select id="question-${question.id}-correct" class="form-input">
                            `;
                            
                            question.options.forEach(option => {
                                questionHTML += `<option value="${option.letter}" ${option.letter === question.correctAnswer ? 'selected' : ''}>${option.letter}</option>`;
                            });
                            
                            questionHTML += `
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="question-type-content true-false-content" style="display: none;">
                                    <div class="true-false-options">
                                        <div class="true-false-option" data-value="true">True</div>
                                        <div class="true-false-option" data-value="false">False</div>
                                    </div>
                                </div>
                            `;
                        } else if (question.type === 'true-false') {
                            questionHTML += `
                                <div class="question-type-content multiple-choice-content" style="display: none;">
                                    <div class="multiple-choice-options">
                                        <div class="multiple-choice-option">
                                            <div class="option-letter">A</div>
                                            <input type="text" class="form-input option-input" placeholder="Enter option A">
                                        </div>
                                        <div class="multiple-choice-option">
                                            <div class="option-letter">B</div>
                                            <input type="text" class="form-input option-input" placeholder="Enter option B">
                                        </div>
                                    </div>
                                    
                                    <button class="add-option-btn">
                                        <i class="icon">+</i> Add Option
                                    </button>
                                    
                                    <div class="correct-option">
                                        <label class="correct-option-label" for="question-${question.id}-correct">Correct Answer:</label>
                                        <select id="question-${question.id}-correct" class="form-input">
                                            <option value="A">A</option>
                                            <option value="B">B</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="question-type-content true-false-content">
                                    <div class="true-false-options">
                                        <div class="true-false-option ${question.correctAnswer === 'A' ? 'selected' : ''}" data-value="true">True</div>
                                        <div class="true-false-option ${question.correctAnswer === 'B' ? 'selected' : ''}" data-value="false">False</div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        newQuestion.innerHTML = questionHTML;
                        questionsContainer.appendChild(newQuestion);
                        setupQuestionEventListeners(newQuestion);
                    });
                    
                    // Update questionCounter
                    questionCounter = testToEdit.questions.length;
                    
                    // Change save button to update
                    createTestBtn.textContent = 'Update';
                    createTestBtn.setAttribute('data-edit-id', String(item.created_at));
                }
            }
        }
    });
    
    // Delete button
    const deleteBtn = card.querySelector('.delete-item');
    deleteBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this item?')) {
            deleteItem(item.created_at, type);
        }
        menuDropdown.classList.remove('active');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!card.contains(e.target)) {
            menuDropdown.classList.remove('active');
        }
    });
}

// Delete an item
function deleteItem(id, type) {
    try {
        if (type === 'flashcard') {
            let flashcardSets = [];
            const savedFlashcardSets = localStorage.getItem('flashcardSets');
            if (savedFlashcardSets) {
                flashcardSets = JSON.parse(savedFlashcardSets);
                const updatedSets = flashcardSets.filter(set => set.created_at !== id);
                localStorage.setItem('flashcardSets', JSON.stringify(updatedSets));
            }
            
            // Remove mastery data
            if (flashcardMasteryData[id]) {
                delete flashcardMasteryData[id];
                localStorage.setItem('flashcardMasteryData', JSON.stringify(flashcardMasteryData));
            }
        } else {
            let practiceTests = [];
            const savedPracticeTests = localStorage.getItem('practiceTests');
            if (savedPracticeTests) {
                practiceTests = JSON.parse(savedPracticeTests);
                const updatedTests = practiceTests.filter(test => test.created_at !== id);
                localStorage.setItem('practiceTests', JSON.stringify(updatedTests));
            }
            
            // Remove score data
            if (quizScores[id]) {
                delete quizScores[id];
                localStorage.setItem('quizScores', JSON.stringify(quizScores));
            }
        }
        
        // Refresh the dashboard
        loadQuizSets();
    } catch (e) {
        console.error('Error deleting item:', e);
    }
}

// Open modal
createBtn.addEventListener('click', () => {
    modal.classList.add('active');
    // Reset to initial view
    showView(initialOptions);
    modalTitle.textContent = 'Create New';
});

// Close modal
closeBtn.addEventListener('click', () => {
    modal.classList.remove('active');
});

// Cancel buttons
cancelFlashcardsBtn.addEventListener('click', () => {
    modal.classList.remove('active');
});

cancelTestBtn.addEventListener('click', () => {
    modal.classList.remove('active');
});

// Back buttons
backButtons.forEach(button => {
    button.addEventListener('click', () => {
        const target = button.getAttribute('data-target');
        showView(document.getElementById(target));
        modalTitle.textContent = 'Create New';
    });
});

// Option cards
optionCards.forEach(card => {
    card.addEventListener('click', () => {
        const option = card.getAttribute('data-option');
        
        if (option === 'flashcards') {
            showView(flashcardCreationView);
            modalTitle.textContent = 'Create Flashcard Set';
            
            // Reset form
            document.getElementById('flashcard-set-title').value = '';
            document.getElementById('flashcard-set-description').value = '';
            
            // Reset card counter
            cardCounter = 2;
            
            // Reset save button
            saveFlashcardsBtn.textContent = 'Create';
            saveFlashcardsBtn.removeAttribute('data-edit-id');
            
            // Reset cards
            const cards = flashcardCardsContainer.querySelectorAll('.flashcard-card');
            cards.forEach((card, index) => {
                const termInput = card.querySelector('.flashcard-term');
                const definitionInput = card.querySelector('.flashcard-definition');
                
                termInput.value = '';
                definitionInput.value = '';
            });
        } else if (option === 'practice-test') {
            showView(practiceTestView);
            modalTitle.textContent = 'Create Practice Quiz';
            
            // Reset form
            document.getElementById('test-title').value = '';
            document.getElementById('test-description').value = '';
            document.getElementById('test-timer-minutes').value = '30';
            
            // Reset question counter
            questionCounter = 1;
            
            // Reset create button
            createTestBtn.textContent = 'Create';
            createTestBtn.removeAttribute('data-edit-id');
            
            // Reset questions
            const questions = questionsContainer.querySelectorAll('.question-card');
            questions.forEach((question, index) => {
                const questionInput = question.querySelector('.question-text');
                const optionInputs = question.querySelectorAll('.option-input');
                
                questionInput.value = '';
                optionInputs.forEach(input => {
                    input.value = '';
                });
            });
        }
    });
});

// Show a specific view
function showView(view) {
    // Hide all views
    initialOptions.style.display = 'none';
    flashcardCreationView.style.display = 'none';
    practiceTestView.style.display = 'none';
    
    // Show the requested view
    view.style.display = 'block';
}

// Add card button
addCardButton.addEventListener('click', () => {
    cardCounter++;
    
    const newCard = document.createElement('div');
    newCard.className = 'flashcard-card';
    newCard.setAttribute('data-card-id', cardCounter);
    
    newCard.innerHTML = `
        <div class="flashcard-card-header">
            <div class="flashcard-card-number">${cardCounter}</div>
            <div class="flashcard-card-actions">
                <button class="btn btn-sm btn-outline move-card-up" title="Move up">
                    <i class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-up"><path d="m18 15-6-6-6 6"/></svg>
                    </i>
                </button>
                <button class="btn btn-sm btn-outline move-card-down" title="Move down">
                    <i class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down"><path d="m6 9 6 6 6-6"/></svg>
                    </i>
                </button>
                <button class="btn btn-sm btn-outline delete-card" title="Delete card">
                    <i class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                    </i>
                </button>
            </div>
        </div>
        <div class="flashcard-card-content">
            <div class="flashcard-field">
                <div class="flashcard-field-label">Term</div>
                <input type="text" class="form-input flashcard-term" placeholder="Enter term">
            </div>
            <div class="flashcard-field">
                <div class="flashcard-field-label">Definition</div>
                <input type="text" class="form-input flashcard-definition" placeholder="Enter definition">
            </div>
        </div>
    `;
    
    flashcardCardsContainer.appendChild(newCard);
    setupCardEventListeners(newCard);
});

// Setup card event listeners
function setupCardEventListeners(card) {
    const moveUpBtn = card.querySelector('.move-card-up');
    const moveDownBtn = card.querySelector('.move-card-down');
    const deleteBtn = card.querySelector('.delete-card');
    
    moveUpBtn.addEventListener('click', () => {
        const prevCard = card.previousElementSibling;
        if (prevCard) {
            flashcardCardsContainer.insertBefore(card, prevCard);
            updateCardNumbers();
        }
    });
    
    moveDownBtn.addEventListener('click', () => {
        const nextCard = card.nextElementSibling;
        if (nextCard) {
            flashcardCardsContainer.insertBefore(nextCard, card);
            updateCardNumbers();
        }
    });
    
    deleteBtn.addEventListener('click', () => {
        if (flashcardCardsContainer.querySelectorAll('.flashcard-card').length > 1) {
            card.remove();
            updateCardNumbers();
        } else {
            alert('You must have at least one card in the set.');
        }
    });
}

// Update card numbers
function updateCardNumbers() {
    const cards = flashcardCardsContainer.querySelectorAll('.flashcard-card');
    cards.forEach((card, index) => {
        const cardNumber = card.querySelector('.flashcard-card-number');
        cardNumber.textContent = index + 1;
        card.setAttribute('data-card-id', index + 1);
    });
}

// Save flashcards
saveFlashcardsBtn.addEventListener('click', () => {
    const title = document.getElementById('flashcard-set-title').value.trim();
    const description = document.getElementById('flashcard-set-description').value.trim();
    
    if (!title) {
        alert('Please enter a title for your quiz set.');
        return;
    }
    
    const cards = [];
    const cardElements = flashcardCardsContainer.querySelectorAll('.flashcard-card');
    
    let hasEmptyFields = false;
    
    cardElements.forEach((card, index) => {
        const term = card.querySelector('.flashcard-term').value.trim();
        const definition = card.querySelector('.flashcard-definition').value.trim();
        
        if (!term || !definition) {
            hasEmptyFields = true;
            return;
        }
        
        cards.push({
            id: index + 1,
            term,
            definition
        });
    });
    
    if (hasEmptyFields) {
        alert('Please fill in all term and definition fields.');
        return;
    }
    
    if (cards.length === 0) {
        alert('Please add at least one card to your flashcard set.');
        return;
    }
    
    try {
        // Check if we're editing an existing set
        const editId = saveFlashcardsBtn.getAttribute('data-edit-id');
        
        if (editId) {
            // Update existing set
            let flashcardSets = [];
            const savedFlashcardSets = localStorage.getItem('flashcardSets');
            
            if (savedFlashcardSets) {
                flashcardSets = JSON.parse(savedFlashcardSets);
                
                // Find the set to update
                const setIndex = flashcardSets.findIndex(set => set.created_at === parseInt(editId));
                
                if (setIndex !== -1) {
                    // Update the set
                    flashcardSets[setIndex] = {
                        ...flashcardSets[setIndex],
                        title,
                        description,
                        cards,
                        updated_at: Date.now()
                    };
                    
                    localStorage.setItem('flashcardSets', JSON.stringify(flashcardSets));
                    
                    alert('Flashcard set updated successfully!');
                    modal.classList.remove('active');
                    loadQuizSets();
                } else {
                    alert('Could not find the flashcard set to update.');
                }
            }
        } else {
            // Create new set
            const newSet = {
                created_at: Date.now(),
                title,
                description,
                cards
            };
            
            let flashcardSets = [];
            const savedFlashcardSets = localStorage.getItem('flashcardSets');
            
            if (savedFlashcardSets) {
                flashcardSets = JSON.parse(savedFlashcardSets);
            }
            
            flashcardSets.push(newSet);
            localStorage.setItem('flashcardSets', JSON.stringify(flashcardSets));
            
            alert('Flashcard set created successfully!');
            modal.classList.remove('active');
            loadQuizSets();
        }
    } catch (e) {
        console.error('Error saving flashcard set:', e);
        alert('An error occurred while saving the flashcard set. Please try again.');
    }
});

// Add question button
addQuestionButton.addEventListener('click', () => {
    questionCounter++;
    
    const newQuestion = document.createElement('div');
    newQuestion.className = 'question-card';
    newQuestion.setAttribute('data-question-id', questionCounter);
    
    newQuestion.innerHTML = `
        <div class="question-card-header">
            <div class="question-card-number">Question ${questionCounter}</div>
            <div class="question-card-actions">
                <button class="btn btn-sm btn-outline delete-question" title="Delete question">
                    <i class="icon">🗑️</i>
                </button>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="question-${questionCounter}-text">Question</label>
            <input type="text" id="question-${questionCounter}-text" class="form-input question-text" placeholder="Enter your question">
        </div>
        
        <div class="question-type-selector">
            <label class="question-type-label">Question Type</label>
            <div class="question-type-options">
                <div class="question-type-option active" data-type="multiple-choice">Multiple Choice</div>
                <div class="question-type-option" data-type="true-false">True/False</div>
            </div>
        </div>
        
        <div class="question-type-content multiple-choice-content">
            <div class="multiple-choice-options">
                <div class="multiple-choice-option">
                    <div class="option-letter">A</div>
                    <input type="text" class="form-input option-input" placeholder="Enter option A">
                </div>
                <div class="multiple-choice-option">
                    <div class="option-letter">B</div>
                    <input type="text" class="form-input option-input" placeholder="Enter option B">
                </div>
                <div class="multiple-choice-option">
                    <div class="option-letter">C</div>
                    <input type="text" class="form-input option-input" placeholder="Enter option C">
                </div>
                <div class="multiple-choice-option">
                    <div class="option-letter">D</div>
                    <input type="text" class="form-input option-input" placeholder="Enter option D">
                </div>
            </div>
            
            <button class="add-option-btn">
                <i class="icon">+</i> Add Option
            </button>
            
            <div class="correct-option">
                <label class="correct-option-label" for="question-${questionCounter}-correct">Correct Answer:</label>
                <select id="question-${questionCounter}-correct" class="form-input">
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>
        </div>
        
        <div class="question-type-content true-false-content" style="display: none;">
            <div class="true-false-options">
                <div class="true-false-option selected" data-value="true">True</div>
                <div class="true-false-option" data-value="false">False</div>
            </div>
        </div>
    `;
    
    questionsContainer.appendChild(newQuestion);
    setupQuestionEventListeners(newQuestion);
});

// Setup question event listeners
function setupQuestionEventListeners(question) {
    const deleteBtn = question.querySelector('.delete-question');
    const typeOptions = question.querySelectorAll('.question-type-option');
    const multipleChoiceContent = question.querySelector('.multiple-choice-content');
    const trueFalseContent = question.querySelector('.true-false-content');
    const addOptionBtn = question.querySelector('.add-option-btn');
    const trueFalseOptions = question.querySelectorAll('.true-false-option');
    
    deleteBtn.addEventListener('click', () => {
        if (questionsContainer.querySelectorAll('.question-card').length > 1) {
            question.remove();
            updateQuestionNumbers();
        } else {
            alert('You must have at least one question in the test.');
        }
    });
    
    typeOptions.forEach(option => {
        option.addEventListener('click', () => {
            // Remove active class from all options
            typeOptions.forEach(opt => opt.classList.remove('active'));
            
            // Add active class to clicked option
            option.classList.add('active');
            
            // Show/hide content based on selected type
            const type = option.getAttribute('data-type');
            
            if (type === 'multiple-choice') {
                multipleChoiceContent.style.display = 'block';
                trueFalseContent.style.display = 'none';
            } else if (type === 'true-false') {
                multipleChoiceContent.style.display = 'none';
                trueFalseContent.style.display = 'block';
            }
        });
    });
    
    addOptionBtn.addEventListener('click', () => {
        const optionsContainer = question.querySelector('.multiple-choice-options');
        const options = optionsContainer.querySelectorAll('.multiple-choice-option');
        
        if (options.length >= 6) {
            alert('You can add a maximum of 6 options.');
            return;
        }
        
        const newOptionLetter = String.fromCharCode(65 + options.length); // A, B, C, ...
        
        const newOption = document.createElement('div');
        newOption.className = 'multiple-choice-option';
        
        newOption.innerHTML = `
            <div class="option-letter">${newOptionLetter}</div>
            <input type="text" class="form-input option-input" placeholder="Enter option ${newOptionLetter}">
        `;
        
        optionsContainer.appendChild(newOption);
        
        // Add new option to correct answer dropdown
        const correctAnswerSelect = question.querySelector('select');
        const newOptionElement = document.createElement('option');
        newOptionElement.value = newOptionLetter;
        newOptionElement.textContent = newOptionLetter;
        correctAnswerSelect.appendChild(newOptionElement);
    });
    
    trueFalseOptions.forEach(option => {
        option.addEventListener('click', () => {
            // Remove selected class from all options
            trueFalseOptions.forEach(opt => opt.classList.remove('selected'));

            // Add selected class to clicked option
            option.classList.add('selected');

            // Update correct answer value
            question.dataset.correctAnswer = option.dataset.value;
        });
    });
}

// Update question numbers
function updateQuestionNumbers() {
    const questions = questionsContainer.querySelectorAll('.question-card');
    questions.forEach((question, index) => {
        const questionNumber = question.querySelector('.question-card-number');
        questionNumber.textContent = `Question ${index + 1}`;
        question.setAttribute('data-question-id', index + 1);
    });
}

// Create test
createTestBtn.addEventListener('click', () => {
    const title = document.getElementById('test-title').value.trim();
    const description = document.getElementById('test-description').value.trim();
    const timeLimit = parseInt(document.getElementById('test-timer-minutes').value);

    if (!title) {
        alert('Please enter a title for your quiz set.');
        return;
    }

    const questions = [];
    const questionElements = questionsContainer.querySelectorAll('.question-card');

    let hasEmptyFields = false;

    questionElements.forEach((question, index) => {
        const questionText = question.querySelector('.question-text').value.trim();
        const questionType = question.querySelector('.question-type-option.active').dataset.type;

        if (!questionText) {
            hasEmptyFields = true;
            return;
        }

        let questionData = {
            id: index + 1,
            text: questionText,
            type: questionType
        };

        if (questionType === 'multiple-choice') {
            const optionElements = question.querySelectorAll('.multiple-choice-option');
            const options = [];

            optionElements.forEach(optionElement => {
                const optionText = optionElement.querySelector('.option-input').value.trim();
                const optionLetter = optionElement.querySelector('.option-letter').textContent;

                if (!optionText) {
                    hasEmptyFields = true;
                    return;
                }

                options.push({
                    letter: optionLetter,
                    text: optionText
                });
            });

            const correctAnswerSelect = question.querySelector('select');
            const correctAnswer = correctAnswerSelect.value;

            questionData.options = options;
            questionData.correctAnswer = correctAnswer;
        } else if (questionType === 'true-false') {
            questionData.correctAnswer = question.dataset.correctAnswer === 'true' ? 'A' : 'B';
            questionData.options = [
                { letter: 'A', text: 'True' },
                { letter: 'B', text: 'False' }
            ];
        }

        questions.push(questionData);
    });

    if (hasEmptyFields) {
        alert('Please fill in all question and option fields.');
        return;
    }

    if (questions.length === 0) {
        alert('Please add at least one question to your practice test.');
        return;
    }

    try {
        // Check if we're editing an existing test
        const editId = createTestBtn.getAttribute('data-edit-id');

        if (editId) {
            // Update existing test
            let practiceTests = [];
            const savedPracticeTests = localStorage.getItem('practiceTests');

            if (savedPracticeTests) {
                practiceTests = JSON.parse(savedPracticeTests);

                // Find the test to update
                const testIndex = practiceTests.findIndex(test => test.created_at === parseInt(editId));

                if (testIndex !== -1) {
                    // Update the test
                    practiceTests[testIndex] = {
                        ...practiceTests[testIndex],
                        title,
                        description,
                        timeLimit,
                        questions,
                        updated_at: Date.now()
                    };

                    localStorage.setItem('practiceTests', JSON.stringify(practiceTests));

                    alert('Practice test updated successfully!');
                    modal.classList.remove('active');
                    loadQuizSets();
                } else {
                    alert('Could not find the practice test to update.');
                }
            }
        } else {
            // Create new test
            const newTest = {
                created_at: Date.now(),
                title,
                description,
                timeLimit,
                questions
            };

            let practiceTests = [];
            const savedPracticeTests = localStorage.getItem('practiceTests');

            if (savedPracticeTests) {
                practiceTests = JSON.parse(savedPracticeTests);
            }

            practiceTests.push(newTest);
            localStorage.setItem('practiceTests', JSON.stringify(practiceTests));

            alert('Practice test created successfully!');
            modal.classList.remove('active');
            loadQuizSets();
        }
    } catch (e) {
        console.error('Error saving practice test:', e);
        alert('An error occurred while saving the practice test. Please try again.');
    }
});

// Flashcard Player Functions
function openFlashcardPlayer(flashcardSet, mode) {
    console.log('Opening flashcard player with set:', flashcardSet);
    
    if (!flashcardSet || !flashcardSet.cards || flashcardSet.cards.length === 0) {
        alert('Error: This flashcard set has no cards. Please edit the set to add cards.');
        return;
    }
    
    try {
        currentFlashcardSet = flashcardSet;
        currentFlashcardIndex = 0;
        isFlashcardFlipped = false;
        
        // Set title
        flashcardPlayerTitle.textContent = flashcardSet.title || 'Untitled Flashcard Set';
        
        // Show first card
        showFlashcard(currentFlashcardIndex);
        
        // Update progress
        updateFlashcardProgress();
        
        // Check if mastery tracking is enabled for this set
        const setId = flashcardSet.created_at;
        if (flashcardMasteryData[setId] && flashcardMasteryData[setId].trackingEnabled) {
            masteryToggleCheckbox.checked = true;
            masteryButtons.style.display = 'flex';
            document.querySelector('.flashcard-controls').style.display = 'none';
        } else {
            masteryToggleCheckbox.checked = false;
            masteryButtons.style.display = 'none';
            document.querySelector('.flashcard-controls').style.display = 'flex';
        }
        
        // Show modal
        flashcardPlayerModal.classList.add('active');
        
        // Set up event listeners
        flashcardFlipBtn.onclick = flipFlashcard;
        flashcardPrevBtn.onclick = function() {
            if (currentFlashcardIndex > 0) {
                currentFlashcardIndex--;
                isFlashcardFlipped = false;
                showFlashcard(currentFlashcardIndex);
                updateFlashcardProgress();
            }
        };
        flashcardNextBtn.onclick = function() {
            moveToNextCard();
        };
    } catch (error) {
        console.error('Error in openFlashcardPlayer:', error);
        alert('An error occurred while loading the flashcards. Please try again.');
    }
}

function showFlashcard(index) {
    if (!currentFlashcardSet || !currentFlashcardSet.cards || index >= currentFlashcardSet.cards.length) {
        console.error('Invalid flashcard set or card index');
        return;
    }
    
    const card = currentFlashcardSet.cards[index];
    
    let flashcardHTML = `
        <div class="flashcard-player">
            <div class="flashcard-inner ${isFlashcardFlipped ? 'flipped' : ''}">
                <div class="flashcard-front">
                    <div class="flashcard-text">${card.term || 'No term'}</div>
                    <div class="flashcard-hint">Click to flip</div>
                </div>
                <div class="flashcard-back">
                    <div class="flashcard-text">${card.definition || 'No definition'}</div>
                    <div class="flashcard-hint">Click to flip back</div>
                </div>
            </div>
        </div>
    `;
    
    flashcardPlayerBody.innerHTML = flashcardHTML;
    
    // Add click event to flip card
    const flashcardInner = flashcardPlayerBody.querySelector('.flashcard-inner');
    if (flashcardInner) {
        flashcardInner.addEventListener('click', flipFlashcard);
    }
}

function flipFlashcard() {
    isFlashcardFlipped = !isFlashcardFlipped;
    const flashcardInner = flashcardPlayerBody.querySelector('.flashcard-inner');
    if (flashcardInner) {
        if (isFlashcardFlipped) {
            flashcardInner.classList.add('flipped');
        } else {
            flashcardInner.classList.remove('flipped');
        }
    }
}

function updateFlashcardProgress() {
    if (!currentFlashcardSet || !currentFlashcardSet.cards) {
        return;
    }
    
    const progress = ((currentFlashcardIndex + 1) / currentFlashcardSet.cards.length) * 100;
    flashcardProgressFill.style.width = `${progress}%`;
    flashcardCounter.textContent = `${currentFlashcardIndex + 1}/${currentFlashcardSet.cards.length}`;
    
    // Disable/enable prev button
    flashcardPrevBtn.disabled = currentFlashcardIndex === 0;
    
    // Update next button text for last card
    if (currentFlashcardIndex === currentFlashcardSet.cards.length - 1) {
        flashcardNextBtn.textContent = 'Finish';
    } else {
        flashcardNextBtn.textContent = 'Next';
        flashcardNextBtn.innerHTML = `Next <i class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
        </i>`;
    }
}

function moveToNextCard() {
    currentFlashcardIndex++;
    
    // Check if we've reached the end of the flashcards
    if (currentFlashcardIndex >= currentFlashcardSet.cards.length) {
        showFlashcardComplete();
        return;
    }
    
    // Show next card
    isFlashcardFlipped = false;
    showFlashcard(currentFlashcardIndex);
    
    // Update progress
    updateFlashcardProgress();
}

function showFlashcardComplete() {
    let completionHTML = `
        <div class="quiz-result">
            <div class="quiz-result-icon">🎉</div>
            <h3 class="quiz-result-title">Set Completed!</h3>
            <p class="quiz-result-message">You've gone through all the flashcards in this set.</p>
            <button class="btn btn-primary" id="flashcard-restart-btn">Start Over</button>
        </div>
    `;
    
    flashcardPlayerBody.innerHTML = completionHTML;
    
    // Add event listener to restart button
    document.getElementById('flashcard-restart-btn').addEventListener('click', function() {
        currentFlashcardIndex = 0;
        isFlashcardFlipped = false;
        showFlashcard(currentFlashcardIndex);
        updateFlashcardProgress();
    });
}

function markCardMastery(isMastered) {
    if (!currentFlashcardSet) return;
    
    const setId = currentFlashcardSet.created_at;
    const cardIndex = currentFlashcardIndex;
    
    if (!flashcardMasteryData[setId]) {
        flashcardMasteryData[setId] = { trackingEnabled: true };
    }
    
    flashcardMasteryData[setId][cardIndex] = isMastered;
    localStorage.setItem('flashcardMasteryData', JSON.stringify(flashcardMasteryData));
    
    // Update the mastery button UI
    if (isMastered) {
        masteryCheckBtn.classList.add('active');
        masteryCrossBtn.classList.remove('active');
    } else {
        masteryCrossBtn.classList.add('active');
        masteryCheckBtn.classList.remove('active');
    }
}

// Update Quiz Timer
function updateQuizTimer() {
    const elapsedTime = Math.floor((Date.now() - quizStartTime) / 1000);
    const totalSeconds = quizTimeLimit * 60;
    const remainingSeconds = Math.max(0, totalSeconds - elapsedTime);

    const minutes = Math.floor(remainingSeconds / 60).toString().padStart(2, '0');
    const seconds = (remainingSeconds % 60).toString().padStart(2, '0');
    quizTimerDisplay.textContent = `${minutes}:${seconds}`;

    // Check if time is up
    if (remainingSeconds === 0) {
        clearInterval(quizTimer);
        showQuizResults();
    }
}

// Show Quiz Question
function showQuizQuestion(index) {
    if (!currentQuiz || !currentQuiz.questions || index >= currentQuiz.questions.length) {
        console.error('Invalid quiz or question index');
        return;
    }

    const question = currentQuiz.questions[index];

    let questionHTML = `
        <div class="quiz-question">
            <div class="quiz-question-text">${question.text || 'No question text'}</div>
            <div class="quiz-options">
    `;

    if (question.options && Array.isArray(question.options)) {
        question.options.forEach(option => {
            const isSelected = quizUserAnswers[index] === option.letter;
            questionHTML += `
                <div class="quiz-option ${isSelected ? 'selected' : ''}" data-letter="${option.letter}">
                    <div class="quiz-option-letter">${option.letter}</div>
                    <div class="quiz-option-text">${option.text || 'No option text'}</div>
                </div>
            `;
        });
    } else {
        questionHTML += `<div class="quiz-option">No options available</div>`;
    }

    questionHTML += `
            </div>
        </div>
    `;

    quizPlayerBody.innerHTML = questionHTML;

    // Add event listeners to options
    const options = quizPlayerBody.querySelectorAll('.quiz-option');
    options.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            options.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Store user answer
            quizUserAnswers[currentQuizIndex] = this.getAttribute('data-letter');
        });
    });
}

// Update Quiz Progress
function updateQuizProgress() {
    if (!currentQuiz || !currentQuiz.questions) {
        return;
    }

    const progress = ((currentQuizIndex + 1) / currentQuiz.questions.length) * 100;
    quizProgressFill.style.width = `${progress}%`;
    quizProgressText.textContent = `${currentQuizIndex + 1}/${currentQuiz.questions.length}`;
}

// Handle Quiz Next Click
function handleQuizNextClick() {
    // Check if user has selected an answer
    if (!quizUserAnswers[currentQuizIndex]) {
        alert('Please select an answer before proceeding.');
        return;
    }

    currentQuizIndex++;

    // Check if we've reached the end of the quiz
    if (currentQuizIndex >= currentQuiz.questions.length) {
        showQuizResults();
        return;
    }

    // Show next question
    showQuizQuestion(currentQuizIndex);

    // Update progress
    updateQuizProgress();
}

// Show Quiz Results
function showQuizResults() {
    // Stop timer
    clearInterval(quizTimer);

    // Calculate score
    let correctAnswers = 0;
    quizUserAnswers.forEach((answer, index) => {
        if (currentQuiz.questions[index] && answer === currentQuiz.questions[index].correctAnswer) {
            correctAnswers++;
        }
    });

    const score = Math.round((correctAnswers / currentQuiz.questions.length) * 100);

    // Save score to localStorage
    quizScores[currentQuiz.created_at] = score;
    localStorage.setItem('quizScores', JSON.stringify(quizScores));

    // Generate resultsHTML
    let resultsHTML = `
        <div class="quiz-result">
            <div class="quiz-result-icon">🏆</div>
            <h3 class="quiz-result-title">Quiz Completed!</h3>
            <div class="quiz-result-score">${score}%</div>
            <p class="quiz-result-message">You got ${correctAnswers} out of ${currentQuiz.questions.length} questions correct.</p>
            <button class="btn btn-primary" id="quiz-review-btn">Review Answers</button>
        </div>
    `;

    quizPlayerBody.innerHTML = resultsHTML;

    // Update next button
    quizNextBtn.textContent = 'Close';
    quizNextBtn.onclick = function() {
        quizPlayerModal.classList.remove('active');
        // Refresh dashboard to show updated mastered percentage
        loadQuizSets();
    };

    // Add event listener to review button
    document.getElementById('quiz-review-btn').addEventListener('click', showQuizReview);
}

// Show Quiz Review
function showQuizReview() {
    let reviewHTML = `<div class="quiz-review">`;

    currentQuiz.questions.forEach((question, index) => {
        const userAnswer = quizUserAnswers[index];
        const isCorrect = userAnswer === question.correctAnswer;
        
        reviewHTML += `
            <div class="quiz-question">
                <div class="quiz-question-text">${index + 1}. ${question.text}</div>
                <div class="quiz-options">
        `;
        
        question.options.forEach(option => {
            let optionClass = '';
            
            if (option.letter === question.correctAnswer) {
                optionClass = 'correct';
            } else if (option.letter === userAnswer && !isCorrect) {
                optionClass = 'incorrect';
            }
            
            reviewHTML += `
                <div class="quiz-option ${optionClass}" data-letter="${option.letter}">
                    <div class="quiz-option-letter">${option.letter}</div>
                    <div class="quiz-option-text">${option.text}</div>
                </div>
            `;
        });
        
        reviewHTML += `
                </div>
            </div>
        `;
    });

    reviewHTML += `</div>`;

    quizPlayerBody.innerHTML = reviewHTML;
}

function openQuizPlayer(quiz, mode) {
    console.log('Opening quiz player with quiz:', quiz);

    if (!quiz || !quiz.questions || quiz.questions.length === 0) {
        alert('Error: This quiz has no questions. Please edit the quiz to add questions.');
        return;
    }

    try {
        currentQuiz = quiz;
        currentQuizIndex = 0;
        quizUserAnswers = Array(quiz.questions.length).fill(null);
        
        // Set title
        quizPlayerTitle.textContent = quiz.title || 'Untitled Quiz';
        
        // Set time limit (use the saved time limit or default to 30 minutes)
        quizTimeLimit = quiz.timeLimit || 30;
        
        // Reset timer
        if (quizTimer) {
            clearInterval(quizTimer);
        }
        quizStartTime = Date.now();
        updateQuizTimer();
        quizTimer = setInterval(updateQuizTimer, 1000);
        
        // Show first question
        showQuizQuestion(currentQuizIndex);
        
        // Update progress
        updateQuizProgress();
        
        // Show modal
        quizPlayerModal.classList.add('active');
        
        // Set up next button
        quizNextBtn.textContent = 'Next';
        quizNextBtn.onclick = handleQuizNextClick;
    } catch (error) {
        console.error('Error in openQuizPlayer:', error);
        alert('An error occurred while loading the quiz. Please try again.');
    }
}

// Close modal
closeBtn.addEventListener('click', () => {
    modal.classList.remove('active');
});

// Close quiz player
quizPlayerClose.addEventListener('click', function() {
    quizPlayerModal.classList.remove('active');
    if (window.quizTimer) {
        clearInterval(window.quizTimer);
    }
    // Refresh dashboard to show updated mastery
    loadQuizSets();
});

// Close flashcard player
flashcardPlayerClose.addEventListener('click', function() {
    flashcardPlayerModal.classList.remove('active');
    // Refresh dashboard to show updated mastery
    loadQuizSets();
});

// Utility: Delegate event for dynamic elements
function delegate(parent, selector, type, handler) {
    parent.addEventListener(type, function(event) {
        let target = event.target;
        while (target && target !== parent) {
            if (target.matches(selector)) {
                handler.call(target, event);
                break;
            }
            target = target.parentElement;
        }
    });
}

// Setup event delegation for dynamic flashcard and question controls
document.addEventListener('DOMContentLoaded', function() {
    // Delegate flashcard card controls
    delegate(flashcardCardsContainer, '.delete-card', 'click', function() {
        // Implementation for delete card
    });
});

</script>
</body>
</html>