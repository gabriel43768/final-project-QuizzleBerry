<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzleberry Dashboard</title>
    <!-- PDF.js library for PDF processing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <style>
        :root {
            --primary: #4b2c91;
            --primary-light: #9747ff;
            --primary-dark: #3a2170;
            --secondary: #10b981;
            --light-bg: #f8f9fa;
            --border: #e5e7eb;
            --text: #333;
            --text-light: #6b7280;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 240px;
            background-color: var(--white);
            border-right: 1px solid var(--border);
            padding: 1.5rem 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
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
        
        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0 1rem;
        }
        
        .sidebar-nav-item {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        
        .sidebar-nav-link:hover, .sidebar-nav-link.active {
            background-color: rgba(151, 71, 255, 0.1);
            color: var(--primary);
        }
        
        .sidebar-nav-link i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.75rem;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .search-bar {
            display: flex;
            align-items: center;
            background-color: var(--white);
            border: 1px solid var(--border);
            border-radius: 9999px;
            padding: 0.5rem 1rem;
            width: 300px;
        }
        
        .search-bar input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            padding: 0.25rem 0;
        }
        
        .search-bar i {
            color: var(--text-light);
            margin-right: 0.5rem;
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
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            padding: 1.5rem;
            color: var(--white);
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
        
        .progress-section {
            background-color: var(--white);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .progress-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-dark);
        }
        
        .progress-bars {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        .progress-item {
            text-align: center;
        }
        
        .progress-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: conic-gradient(var(--primary) 0% var(--progress), #e5e7eb var(--progress) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            position: relative;
        }
        
        .progress-circle::before {
            content: '';
            position: absolute;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--white);
        }
        
        .progress-value {
            position: relative;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .progress-label {
            font-size: 0.875rem;
            color: var(--text);
        }
        
        .recent-activity {
            background-color: var(--white);
            border-radius: 0.75rem;
            padding: 1.5rem;
        }
        
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(151, 71, 255, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: var(--text-light);
        }
        
        .activity-score {
            font-weight: 600;
            color: var(--secondary);
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
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .option-card {
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 1.5rem;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .option-card:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .option-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .option-icon {
            font-size: 2rem;
            color: var(--primary);
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
        
        /* Upload Area */
        .upload-area {
            border: 2px dashed var(--primary-light);
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            background-color: rgba(151, 71, 255, 0.05);
            margin-bottom: 1.5rem;
        }
        
        .upload-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .upload-text {
            margin-bottom: 1rem;
            color: var(--text);
        }
        
        .upload-input {
            display: none;
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
        
        /* Loading Spinner */
        .spinner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: var(--primary);
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* AI Processing Results */
        .ai-results {
            margin-top: 1.5rem;
        }
        
        .ai-results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .ai-results-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-dark);
        }
        
        .ai-results-subtitle {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }
        
        .ai-results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .ai-result-card {
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        
        .ai-result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }
        
        .ai-result-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .ai-result-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .ai-result-count {
            font-size: 0.875rem;
            color: var(--text-light);
        }
        
        .pdf-preview {
            margin-top: 1.5rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 1rem;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .pdf-preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .pdf-preview-title {
            font-weight: 600;
        }
        
        .pdf-preview-content {
            font-size: 0.875rem;
            line-height: 1.6;
        }
        
        .pdf-preview-page {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed var(--border);
        }
        
        .pdf-preview-page:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 1rem;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            body {
                flex-direction: column;
            }
            
            .sidebar-nav {
                display: flex;
                overflow-x: auto;
                padding: 0.5rem 0;
            }
            
            .sidebar-nav-item {
                margin-right: 0.5rem;
                margin-bottom: 0;
            }
            
            .sidebar-footer {
                display: none;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .search-bar {
                width: 100%;
            }
            
            .card-grid {
                grid-template-columns: 1fr;
            }
            
            .progress-bars {
                grid-template-columns: 1fr;
            }
            
            .option-grid {
                grid-template-columns: 1fr;
            }
            
            .modal {
                width: 90%;
            }
            
            .ai-results-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Icons (using Unicode for simplicity) */
        .icon {
            font-style: normal;
            font-family: sans-serif;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="111.png" alt="Quizzleberry Logo" class="sidebar-logo">
            <div class="sidebar-brand">Quizzleberry</div>
        </div>
        
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="index.html" class="sidebar-nav-link active">
                    <i class="icon">🏠</i> Home
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="library.html" class="sidebar-nav-link">
                    <i class="icon">📚</i> My Library
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="explore.html" class="sidebar-nav-link">
                    <i class="icon">🔍</i> Explore
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="progress.html" class="sidebar-nav-link">
                    <i class="icon">📊</i> Progress
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="leaderboard.html" class="sidebar-nav-link">
                    <i class="icon">🏆</i> Leaderboard
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="settings.html" class="sidebar-nav-link">
                    <i class="icon">⚙️</i> Settings
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="login.html" class="sidebar-nav-link">
                    <i class="icon">👤</i> Register
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">D</div>
                <div class="user-name">Demo User</div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="search-bar">
                <i class="icon">🔍</i>
                <input type="text" placeholder="Search your quizzes...">
            </div>
            
            <button class="create-btn" id="create-quiz-btn">
                <i class="icon">➕</i> Create Quiz
            </button>
        </div>
        
        <div class="progress-section">
            <div class="progress-header">
                <h2 class="progress-title">Your Learning Progress</h2>
                <a href="#" style="color: var(--primary); text-decoration: none;">View All</a>
            </div>
            
            <div class="progress-bars">
                <div class="progress-item">
                    <div class="progress-circle" style="--progress: 75%;">
                        <div class="progress-value">75%</div>
                    </div>
                    <div class="progress-label">Science Quiz</div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-circle" style="--progress: 40%;">
                        <div class="progress-value">40%</div>
                    </div>
                    <div class="progress-label">Math Challenge</div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-circle" style="--progress: 90%;">
                        <div class="progress-value">90%</div>
                    </div>
                    <div class="progress-label">History Facts</div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-circle" style="--progress: 10%;">
                        <div class="progress-value">10%</div>
                    </div>
                    <div class="progress-label">Geography</div>
                </div>
            </div>
        </div>
        
        <h2 class="section-title">Your Quiz Sets</h2>
        
        <div class="card-grid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Science Quiz</h3>
                    <div class="card-subtitle">Biology, Chemistry, Physics</div>
                </div>
                <div class="card-body">
                    <div class="card-stats">
                        <div class="stat">
                            <div class="stat-value">42</div>
                            <div class="stat-label">Questions</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">75%</div>
                            <div class="stat-label">Mastered</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">3</div>
                            <div class="stat-label">Days Streak</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-btn">Study</button>
                        <button class="card-btn primary">Quiz</button>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Math Challenge</h3>
                    <div class="card-subtitle">Algebra, Geometry, Calculus</div>
                </div>
                <div class="card-body">
                    <div class="card-stats">
                        <div class="stat">
                            <div class="stat-value">35</div>
                            <div class="stat-label">Questions</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">40%</div>
                            <div class="stat-label">Mastered</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">1</div>
                            <div class="stat-label">Days Streak</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-btn">Study</button>
                        <button class="card-btn primary">Quiz</button>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">History Facts</h3>
                    <div class="card-subtitle">Ancient, Medieval, Modern</div>
                </div>
                <div class="card-body">
                    <div class="card-stats">
                        <div class="stat">
                            <div class="stat-value">50</div>
                            <div class="stat-label">Questions</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">90%</div>
                            <div class="stat-label">Mastered</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">7</div>
                            <div class="stat-label">Days Streak</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-btn">Study</button>
                        <button class="card-btn primary">Quiz</button>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Geography</h3>
                    <div class="card-subtitle">Countries, Capitals, Landmarks</div>
                </div>
                <div class="card-body">
                    <div class="card-stats">
                        <div class="stat">
                            <div class="stat-value">28</div>
                            <div class="stat-label">Questions</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">10%</div>
                            <div class="stat-label">Mastered</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">0</div>
                            <div class="stat-label">Days Streak</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-btn">Study</button>
                        <button class="card-btn primary">Quiz</button>
                    </div>
                </div>
            </div>
        </div>
        
        <h2 class="section-title">Recent Activity</h2>
        
        <div class="recent-activity">
            <ul class="activity-list">
                <li class="activity-item">
                    <div class="activity-icon">🧪</div>
                    <div class="activity-content">
                        <div class="activity-title">Completed Science Quiz</div>
                        <div class="activity-time">Today, 10:30 AM</div>
                    </div>
                    <div class="activity-score">85%</div>
                </li>
                
                <li class="activity-item">
                    <div class="activity-icon">📜</div>
                    <div class="activity-content">
                        <div class="activity-title">Studied History Facts</div>
                        <div class="activity-time">Yesterday, 3:45 PM</div>
                    </div>
                    <div class="activity-score">20 min</div>
                </li>
                
                <li class="activity-item">
                    <div class="activity-icon">🔢</div>
                    <div class="activity-content">
                        <div class="activity-title">Created Math Challenge</div>
                        <div class="activity-time">2 days ago</div>
                    </div>
                    <div class="activity-score">35 Q</div>
                </li>
                
                <li class="activity-item">
                    <div class="activity-icon">🌍</div>
                    <div class="activity-content">
                        <div class="activity-title">Started Geography</div>
                        <div class="activity-time">3 days ago</div>
                    </div>
                    <div class="activity-score">New</div>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Create Quiz Modal -->
    <div class="modal-overlay" id="create-quiz-modal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Create New</h3>
                <button class="modal-close" id="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Initial Options View -->
                <div id="initial-options">
                    <div class="option-grid">
                        <div class="option-card" data-option="flashcards">
                            <div class="option-header">
                                <div class="option-icon">📝</div>
                                <div>
                                    <h4 class="option-title">Flashcard Set</h4>
                                    <p class="option-description">Create digital flashcards for quick study sessions</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="option-card" data-option="study-guide">
                            <div class="option-header">
                                <div class="option-icon">📚</div>
                                <div>
                                    <h4 class="option-title">Study Guide</h4>
                                    <p class="option-description">Organize comprehensive notes and materials</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="option-card" data-option="practice-test">
                            <div class="option-header">
                                <div class="option-icon">✅</div>
                                <div>
                                    <h4 class="option-title">Practice Test</h4>
                                    <p class="option-description">Create quizzes with various question types</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="option-card" data-option="folder">
                            <div class="option-header">
                                <div class="option-icon">📁</div>
                                <div>
                                    <h4 class="option-title">Folder</h4>
                                    <p class="option-description">Organize your study materials in folders</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="option-card" data-option="class">
                            <div class="option-header">
                                <div class="option-icon">🎓</div>
                                <div>
                                    <h4 class="option-title">Class</h4>
                                    <p class="option-description">Create a class with multiple study materials</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Upload View (Common for all options) -->
                <div id="upload-view" style="display: none;">
                    <div class="back-button-container">
                        <button class="btn btn-outline btn-sm back-button">
                            <span class="icon">←</span> Back
                        </button>
                        <h3 id="upload-title">Upload Content</h3>
                    </div>
                    
                    <p class="ai-results-subtitle">Our AI will analyze your document and organize it into study materials</p>
                    
                    <div class="upload-area" id="pdf-upload-area">
                        <div class="upload-icon">📄</div>
                        <p class="upload-text">Drag and drop your PDF or document here, or click to browse</p>
                        <p class="upload-text" style="font-size: 0.875rem; color: var(--text-light);">Supported formats: PDF, DOCX, TXT</p>
                        <input type="file" id="pdf-upload" class="upload-input" accept=".pdf,.docx,.txt">
                        <button class="btn btn-primary" id="browse-pdf">Select File</button>
                    </div>
                    
                    <div id="pdf-preview" class="pdf-preview" style="display: none;">
                        <div class="pdf-preview-header">
                            <h4 class="pdf-preview-title">Document Preview</h4>
                            <span id="pdf-page-count"></span>
                        </div>
                        <div class="pdf-preview-content" id="pdf-preview-content">
                            <!-- PDF content will be displayed here -->
                        </div>
                    </div>
                    
                    <button class="btn btn-primary btn-block" id="analyze-pdf" style="display: none;">Analyze with AI</button>
                    
                    <!-- AI Processing View -->
                    <div id="ai-processing" class="spinner-container" style="display: none;">
                        <div class="spinner"></div>
                        <p>AI is analyzing your document...</p>
                        <p style="font-size: 0.875rem; color: var(--text-light); margin-top: 0.5rem;">This may take a few moments</p>
                    </div>
                    
                    <!-- AI Results View -->
                    <div id="ai-results" class="ai-results" style="display: none;">
                        <div class="ai-results-header">
                            <h3 class="ai-results-title">AI Analysis Results</h3>
                        </div>
                        <p class="ai-results-subtitle">Our AI has analyzed your document and created the following study materials:</p>
                        
                        <div class="ai-results-grid">
                            <div class="ai-result-card" data-result="flashcards">
                                <div class="ai-result-icon">📝</div>
                                <h4 class="ai-result-title">Flashcards</h4>
                                <p class="ai-result-count">24 cards generated</p>
                            </div>
                            
                            <div class="ai-result-card" data-result="study-guide">
                                <div class="ai-result-icon">📚</div>
                                <h4 class="ai-result-title">Study Guide</h4>
                                <p class="ai-result-count">5 sections organized</p>
                            </div>
                            
                            <div class="ai-result-card" data-result="practice-test">
                                <div class="ai-result-icon">✅</div>
                                <h4 class="ai-result-title">Practice Test</h4>
                                <p class="ai-result-count">15 questions created</p>
                            </div>
                            
                            <div class="ai-result-card" data-result="summary">
                                <div class="ai-result-icon">📋</div>
                                <h4 class="ai-result-title">Summary</h4>
                                <p class="ai-result-count">Key concepts extracted</p>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary btn-block">Save All Materials</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Set up PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
        
        // Modal functionality
        const modal = document.getElementById('create-quiz-modal');
        const createBtn = document.getElementById('create-quiz-btn');
        const closeBtn = document.getElementById('modal-close');
        const modalBody = document.getElementById('modal-body');
        const initialOptions = document.getElementById('initial-options');
        const uploadView = document.getElementById('upload-view');
        const uploadTitle = document.getElementById('upload-title');
        const backButtons = document.querySelectorAll('.back-button');
        const optionCards = document.querySelectorAll('.option-card');
        const pdfUploadArea = document.getElementById('pdf-upload-area');
        const pdfUpload = document.getElementById('pdf-upload');
        const browsePdfBtn = document.getElementById('browse-pdf');
        const pdfPreview = document.getElementById('pdf-preview');
        const pdfPreviewContent = document.getElementById('pdf-preview-content');
        const pdfPageCount = document.getElementById('pdf-page-count');
        const analyzePdfBtn = document.getElementById('analyze-pdf');
        const aiProcessing = document.getElementById('ai-processing');
        const aiResults = document.getElementById('ai-results');
        
        let currentOption = '';
        let uploadedFile = null;
        
        // Open modal
        createBtn.addEventListener('click', () => {
            modal.classList.add('active');
            // Reset to initial view
            showInitialOptions();
        });
        
        // Close modal
        closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
            resetViews();
        });
        
        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
                resetViews();
            }
        });
        
        // Show initial options
        function showInitialOptions() {
            initialOptions.style.display = 'block';
            uploadView.style.display = 'none';
            resetViews();
        }
        
        // Reset views
        function resetViews() {
            pdfPreview.style.display = 'none';
            analyzePdfBtn.style.display = 'none';
            aiProcessing.style.display = 'none';
            aiResults.style.display = 'none';
            pdfPreviewContent.innerHTML = '';
            uploadedFile = null;
        }
        
        // Handle option card clicks
        optionCards.forEach(card => {
            card.addEventListener('click', () => {
                currentOption = card.getAttribute('data-option');
                initialOptions.style.display = 'none';
                uploadView.style.display = 'block';
                
                // Set the title based on the selected option
                const formattedOption = currentOption.replace('-', ' ');
                uploadTitle.textContent = `Upload content for ${formattedOption.charAt(0).toUpperCase() + formattedOption.slice(1)}`;
            });
        });
        
        // Handle back buttons
        backButtons.forEach(button => {
            button.addEventListener('click', showInitialOptions);
        });
        
        // Handle file browse button
        browsePdfBtn.addEventListener('click', () => {
            pdfUpload.click();
        });
        
        // Handle file selection
        pdfUpload.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                uploadedFile = e.target.files[0];
                browsePdfBtn.textContent = `Selected: ${uploadedFile.name}`;
                
                // Show the analyze button
                analyzePdfBtn.style.display = 'block';
                
                // Preview the PDF if it's a PDF file
                if (uploadedFile.type === 'application/pdf') {
                    previewPDF(uploadedFile);
                } else if (uploadedFile.type === 'text/plain') {
                    previewTextFile(uploadedFile);
                } else if (uploadedFile.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                    // For DOCX files, just show a message
                    pdfPreview.style.display = 'block';
                    pdfPageCount.textContent = 'DOCX file';
                    pdfPreviewContent.innerHTML = '<p>DOCX preview not available. Click "Analyze with AI" to process the document.</p>';
                }
            }
        });
        
        // Preview PDF file
        function previewPDF(file) {
            const fileReader = new FileReader();
            
            fileReader.onload = function() {
                const typedarray = new Uint8Array(this.result);
                
                // Load the PDF
                pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
                    pdfPreview.style.display = 'block';
                    pdfPageCount.textContent = `${pdf.numPages} pages`;
                    pdfPreviewContent.innerHTML = '';
                    
                    // Only load the first 3 pages for preview
                    const pagesToLoad = Math.min(pdf.numPages, 3);
                    
                    for (let i = 1; i <= pagesToLoad; i++) {
                        pdf.getPage(i).then(function(page) {
                            page.getTextContent().then(function(textContent) {
                                const pageDiv = document.createElement('div');
                                pageDiv.className = 'pdf-preview-page';
                                
                                const pageHeader = document.createElement('h5');
                                pageHeader.textContent = `Page ${i}`;
                                pageDiv.appendChild(pageHeader);
                                
                                const pageText = document.createElement('p');
                                let lastY = textContent.items[0].transform[5];
                                let text = '';
                                
                                for (let j = 0; j < textContent.items.length; j++) {
                                    const item = textContent.items[j];
                                    
                                    // Add a new line if the y position changes significantly
                                    if (j > 0 && Math.abs(item.transform[5] - lastY) > 5) {
                                        text += '\n';
                                    }
                                    
                                    text += item.str + ' ';
                                    lastY = item.transform[5];
                                }
                                
                                pageText.textContent = text;
                                pageDiv.appendChild(pageText);
                                
                                pdfPreviewContent.appendChild(pageDiv);
                            });
                        });
                    }
                    
                    if (pdf.numPages > 3) {
                        const morePages = document.createElement('p');
                        morePages.textContent = `... and ${pdf.numPages - 3} more pages`;
                        morePages.style.textAlign = 'center';
                        morePages.style.fontStyle = 'italic';
                        morePages.style.color = 'var(--text-light)';
                        pdfPreviewContent.appendChild(morePages);
                    }
                });
            };
            
            fileReader.readAsArrayBuffer(file);
        }
        
        // Preview text file
        function previewTextFile(file) {
            const fileReader = new FileReader();
            
            fileReader.onload = function() {
                pdfPreview.style.display = 'block';
                pdfPageCount.textContent = 'Text file';
                
                const text = this.result;
                const lines = text.split('\n');
                
                // Only show the first 100 lines for preview
                const linesToShow = Math.min(lines.length, 100);
                let previewText = '';
                
                for (let i = 0; i < linesToShow; i++) {
                    previewText += lines[i] + '\n';
                }
                
                if (lines.length > 100) {
                    previewText += '\n... and more lines';
                }
                
                pdfPreviewContent.innerHTML = `<pre>${previewText}</pre>`;
            };
            
            fileReader.readAsText(file);
        }
        
        // Handle analyze button click
        analyzePdfBtn.addEventListener('click', () => {
            // Hide the upload area and preview
            pdfUploadArea.style.display = 'none';
            pdfPreview.style.display = 'none';
            analyzePdfBtn.style.display = 'none';
            
            // Show the processing spinner
            aiProcessing.style.display = 'flex';
            
            // Simulate AI processing
            setTimeout(() => {
                // Hide the processing spinner
                aiProcessing.style.display = 'none';
                
                // Show the AI results
                aiResults.style.display = 'block';
                
                // Update the result counts based on the file size
                const fileSize = uploadedFile.size;
                const flashcardCount = Math.floor(fileSize / 10000) + 5; // Simulate card count based on file size
                const sectionCount = Math.floor(fileSize / 50000) + 2;
                const questionCount = Math.floor(fileSize / 15000) + 3;
                
                document.querySelector('[data-result="flashcards"] .ai-result-count').textContent = `${flashcardCount} cards generated`;
                document.querySelector('[data-result="study-guide"] .ai-result-count').textContent = `${sectionCount} sections organized`;
                document.querySelector('[data-result="practice-test"] .ai-result-count').textContent = `${questionCount} questions created`;
            }, 3000); // Simulate 3 seconds of processing
        });
        
        // Handle AI result card clicks
        const aiResultCards = document.querySelectorAll('.ai-result-card');
        aiResultCards.forEach(card => {
            card.addEventListener('click', () => {
                const resultType = card.getAttribute('data-result');
                alert(`Opening ${resultType} generated from your document. This would show the AI-generated content in a real implementation.`);
            });
        });
        
        // File drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            pdfUploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            pdfUploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            pdfUploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            pdfUploadArea.style.borderColor = 'var(--primary)';
            pdfUploadArea.style.backgroundColor = 'rgba(151, 71, 255, 0.1)';
        }
        
        function unhighlight() {
            pdfUploadArea.style.borderColor = 'var(--primary-light)';
            pdfUploadArea.style.backgroundColor = 'rgba(151, 71, 255, 0.05)';
        }
        
        pdfUploadArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                uploadedFile = files[0];
                pdfUpload.files = files;
                browsePdfBtn.textContent = `Selected: ${uploadedFile.name}`;
                
                // Show the analyze button
                analyzePdfBtn.style.display = 'block';
                
                // Preview the file
                if (uploadedFile.type === 'application/pdf') {
                    previewPDF(uploadedFile);
                } else if (uploadedFile.type === 'text/plain') {
                    previewTextFile(uploadedFile);
                } else if (uploadedFile.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                    // For DOCX files, just show a message
                    pdfPreview.style.display = 'block';
                    pdfPageCount.textContent = 'DOCX file';
                    pdfPreviewContent.innerHTML = '<p>DOCX preview not available. Click "Analyze with AI" to process the document.</p>';
                }
            }
        }
    </script>
</body>
</html>