<?php
session_start();
require 'db.php';

if (!isset($_GET['lesson_id']) || !isset($_SESSION['email'])) {
    echo "Missing lesson or user.";
    exit;
}

// Get user ID
$email = $_SESSION['email'];
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
$user_id = $user['id'];

$lesson_id = $_GET['lesson_id'];

// Fetch the current lesson
$stmt = $pdo->prepare("SELECT id, course_id, title, content, video, file_attachment, file_name FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(); 

if (!$lesson) {
    echo "Lesson not found.";
    exit;
}

$course_id = $lesson['course_id'];

// Find the previous lesson (assuming lower ID = earlier)
$stmt = $pdo->prepare("SELECT id FROM lessons WHERE course_id = ? AND id < ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$course_id, $lesson_id]);
$prevLesson = $stmt->fetch();

if ($prevLesson) {
    $stmt = $pdo->prepare("SELECT status FROM progress WHERE user_id = ? AND lesson_id = ?");
    $stmt->execute([$user_id, $prevLesson['id']]);
    $progress = $stmt->fetch();

    if (!$progress || strtolower($progress['status']) !== 'completed') {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
        <script>
        Swal.fire({
            icon: 'warning',
            title: 'Access Blocked',
            text: 'Please complete the previous lesson before proceeding.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'courses.php';
        });
        </script>
        </body>
        </html>
        <?php
        exit;
    }
}

// Save video and PDF to temporary files
$videoFile = 'temp_video_' . uniqid() . '.mp4';
$pdfFile = 'temp_pdf_' . uniqid() . '_' . basename($lesson['file_name']);
file_put_contents($videoFile, $lesson['video']);
file_put_contents($pdfFile, $lesson['file_attachment']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lesson['title']) ?></title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <style>
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .lesson-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .lesson-title {
            font-size: 2.5rem;
            color: #2d3748;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .lesson-subtitle {
            color: #718096;
            font-size: 1.1rem;
        }

        .video-container {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: 0 auto 30px;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 600;
            cursor: pointer;
            z-index: 10;
            transition: opacity 0.3s ease;
        }

        .video-overlay:hover {
            background: rgba(0,0,0,0.9);
        }

        .video-overlay.hidden {
            display: none;
        }

        #courseVideo {
            width: 100%;
            height: auto;
            display: block;
            outline: none;
        }

        .controls {
            background: #2d3748;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .play-pause {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 80px;
        }

        .play-pause:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }

        .progress-container {
            flex: 1;
            height: 6px;
            background: #4a5568;
            border-radius: 3px;
            position: relative;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 0%;
            border-radius: 3px;
            transition: width 0.1s ease;
        }

        .time-display {
            color: #cbd5e0;
            font-size: 0.9rem;
            font-weight: 500;
            min-width: 100px;
            text-align: right;
        }



        .completion-message {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 20px;
            display: none;
            animation: slideIn 0.5s ease;
        }

        .completion-message.show {
            display: block;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .warning-message {
            background: #fed7d7;
            color: #c53030;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
            border: 1px solid #feb2b2;
        }

        .security-notice {
            background: #bee3f8;
            color: #2b6cb0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #90cdf4;
        }

        @media (max-width: 1000px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            .lesson-title {
                font-size: 2rem;
            }

            .controls {
                padding: 12px 15px;
                gap: 10px;
            }

            .play-pause {
                padding: 8px 16px;
                font-size: 0.9rem;
                min-width: 70px;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
        iframe {
            width: 100%;
            height: 500px;
            border: 1px solid #ddd;
            margin: 20px 0;
            display: block;
        }

        .quiz-section {
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 40px;
            border-radius: 20px;
            margin-top: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            position: relative;
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .quiz-title {
            font-size: 2.2em;
            margin: 0 0 10px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            background: linear-gradient(45deg, #fff, #f0f8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .quiz-subtitle {
            font-size: 1.1em;
            opacity: 0.9;
            font-weight: 300;
            margin: 0;
        }

        .question {
            background: rgba(255,255,255,0.9);
            color: #333;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .question:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .question-number {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .question-text {
            display: inline-block;
            font-size: 1.2em;
            font-weight: 500;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        .options {
            margin: 20px 0;
            padding-left: 0;
        }

        .option-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px;
            margin-top: 15px;
        }

        .option-label {
            background: rgba(0, 225, 255, 0.79);
            border: 2px solid rgba(255,255,255,0.3);
            padding: 15px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .option-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .option-label:hover::before {
            left: 100%;
        }

        .option-label:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.5);
            transform: translateX(5px);
        }

        .option-label input[type="radio"] {
            margin-right: 12px;
            transform: scale(1.3);
            accent-color: #ff6b6b;
        }

        .textarea-container {
            position: relative;
            margin-top: 15px;
        }

        .custom-textarea {
            width: 95%;
            min-height: 120px;
            padding: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 12px;
            font-family: inherit;
            font-size: 16px;
            resize: vertical;
            background: rgba(255, 255, 255, 0.77);
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }

        .custom-textarea::placeholder {
            color: rgba(0, 0, 0, 0.7);
        }

        .custom-textarea:focus {
            outline: none;
            border-color:rgb(0, 0, 0);
            background: rgba(255,255,255,0.15);
        }

        .form-actions {
            text-align: center;
            margin-top: 40px;
            position: relative;
            z-index: 1;
        }

        .submit-btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.3);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .progress-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .progress-step {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transition: all 0.3s ease;
        }

        .progress-step.active {
            background: #ff6b6b;
            box-shadow: 0 0 15px rgba(255,107,107,0.5);
        }

        .question-counter {
            position: absolute;
            top: 20px;
            right: 25px;
            background: rgba(0,0,0,0.3);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .success-message {
            color: #27ae60;
            background: #d4edda;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
            font-size: 1.1em;
        }

        hr {
            border: none;
            height: 1px;
            background: #eee;
            margin: 20px 0;
        }

        a{
            text-decoration: none;
        }
    </style>
    
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <h2>Bright<span>Start</span></h2>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active" onclick="window.location.href='dashboard.php';">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="menu-item" onclick="window.location.href='courses.php';">
                <i class="fas fa-book"></i>
                <span>Courses</span>
            </div>
            <div class="menu-item" onclick="window.location.href='users.php';">
                <i class="fas fa-users"></i>
                <span>Participants</span>
            </div>
            
            <div class="menu-item" onclick="window.location.href='messages.php';">
                <i class="fas fa-comment"></i>
                <span>Messages</span>
              
            </div>
            
            
        </div>
    </div>
    <div class="main-content">
        <div class="header">
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="header-actions">
                <button class="notification-btn" onclick="window.location.href='editpass.php';" title="Edit Password">
                    <i class="fa-solid fa-pencil"></i>
                </button>
                <div class="user-profile" >
                    <div class="user-avatar" style="color:blue;"onclick="window.location.href='profile.php';"><i class="fa-solid fa-user"></i></div>
                </div>
                
                <div class="user-profile" >
                    
                    <div class="user-info">
                        <div class="user-name">
                            <?php
                            $name = isset($_SESSION['name']) ? $_SESSION['name'] : "Unknown User";
                            echo htmlspecialchars($name);
                            ?>
                        </div>
                        <div class="user-role">
                            <?php
                            $role = isset($_SESSION['role']) ? $_SESSION['role'] : "Unknown User";
                            echo htmlspecialchars($role);
                            ?>
                        </div>
                    </div>
                    <div class="user-avatar" onclick="window.location.href='logout.php';"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                </div>
            </div>
        </div>
        <h1><?= htmlspecialchars($lesson['title']) ?></h1>
        <div class="cart" style="background-color: white; border-radius: 12px; margin-bottom: 30px; padding: 25px;">
            <h2><?php echo htmlspecialchars($lesson['title']); ?> Description</h2>
            <pre style="white-space: pre-wrap; word-wrap: break-word; overflow: auto; max-width: 1000px; font-family: inherit; padding: 10px; border-radius: 8px;"><?php echo htmlspecialchars($lesson['content']); ?></pre>
        </div>
        <!-- Replace this with your actual video source -->
         
        <div class="security-notice">
            🔒 This video player implements anti-cheat measures. Video skipping is disabled, and your progress is tracked accurately.
        </div>

        <div id="warningMessage" class="warning-message" style="display: none;">
            ⚠️ Please keep this tab active while watching the video. Switching tabs will pause the video.
        </div>

        <div class="video-container">
            <div id="videoOverlay" class="video-overlay hidden">
                ▶ Click to start the lesson
            </div>
            <video id="courseVideo" preload="metadata">
                <source src="<?= htmlspecialchars($videoFile) ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="controls">
                <button id="playPauseBtn" class="play-pause">Play</button>
                <div class="progress-container">
                    <div id="progressBar" class="progress-bar"></div>
                </div>
                <div class="time-display">
                    <span id="currentTime">0:00</span> / <span id="duration">0:00</span>
                </div>
            </div>
        </div>

        <!-- PDF preview -->
        <iframe src="<?= htmlspecialchars($pdfFile) ?>" width="100%" height="500px"></iframe>

        <?php
        $quiz = $pdo->prepare("SELECT * FROM lesson_quizzes WHERE lesson_id = ?");
        $quiz->execute([$lesson_id]);
        $questions = $quiz->fetchAll();

        // Get user ID
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$_SESSION['email']]);
        $user_id = $stmt->fetchColumn();

        // Check if quiz already passed
        $check = $pdo->prepare("SELECT status FROM progress WHERE user_id = ? AND lesson_id = ?");
        $check->execute([$user_id, $lesson_id]);
        $alreadyPassed = $check->fetchColumn() === 'completed';

        if ($alreadyPassed) {
            echo "<p style='color: green;'>✅ You’ve already passed this quiz. No need to retake it.</p>";
        } else {
        ?>

        <div class="quiz-section">
            <form method="POST" action="submit_quiz.php">
            <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">

            <?php
            $i = 1;
            foreach ($questions as $q):
            ?>
                <div class="question">
                    <div class="question-number"><?= $i ?></div>
                    <span class="question-text"><?= htmlspecialchars($q['question']) ?></span>

                    <?php if ($q['type'] == 'mcq'): ?>
                        <div class="options">
                            <div class="option-group">
                                <label class="option-label">
                                    <input type="radio" name="quiz[<?= $q['id'] ?>]" value="A" required>
                                    <?= htmlspecialchars($q['option_a']) ?>
                                </label>
                                <label class="option-label">
                                    <input type="radio" name="quiz[<?= $q['id'] ?>]" value="B">
                                    <?= htmlspecialchars($q['option_b']) ?>
                                </label>
                                <label class="option-label">
                                    <input type="radio" name="quiz[<?= $q['id'] ?>]" value="C">
                                    <?= htmlspecialchars($q['option_c']) ?>
                                </label>
                                <label class="option-label">
                                    <input type="radio" name="quiz[<?= $q['id'] ?>]" value="D">
                                    <?= htmlspecialchars($q['option_d']) ?>
                                </label>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="textarea-container">
                            <textarea name="quiz[<?= $q['id'] ?>]" class="custom-textarea" placeholder="Share your thoughts..." required></textarea>
                        </div>
                    <?php endif; ?>
                </div>
                <hr>
            <?php
                $i++;
            endforeach;
            ?>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Submit Assessment</button>
            </div>
        </form>

        </div>

        <?php
        } // end else
        ?>

        
    </div>

    <script>
        // Enhanced Video Learning Management System
        // Combines video tracking, anti-cheat features, and quiz integration

        class VideoLearningSystem {
            constructor(config = {}) {
                // Configuration
                this.config = {
                    requiredWatchPercentage: config.requiredWatchPercentage || 95,
                    seekTolerance: config.seekTolerance || 2,
                    maxTabSwitches: config.maxTabSwitches || 5,
                    saveProgressInterval: config.saveProgressInterval || 30000, // 30 seconds
                    ...config
                };

                // DOM elements
                this.video = document.getElementById('courseVideo');
                this.overlay = document.getElementById('videoOverlay');
                this.playPauseBtn = document.getElementById('playPauseBtn');
                this.progressBar = document.getElementById('progressBar');
                this.progressContainer = document.querySelector('.progress-container');
                this.currentTimeEl = document.getElementById('currentTime');
                this.durationEl = document.getElementById('duration');
                this.quizSection = document.querySelector('.quiz-section');
                this.completionMessage = document.getElementById('completionMessage');
                this.warningMessage = document.getElementById('warningMessage');

                // Tracking variables
                this.videoDuration = 0;
                this.maxWatchedTime = 0;
                this.watchData = {};
                this.isVideoCompleted = false;
                this.quizUnlocked = false;
                
                // Anti-cheat tracking
                this.tabSwitchCount = 0;
                this.seekAttempts = 0;
                this.playbackSpeed = 1;
                this.suspiciousActivity = [];
                
                // Progress saving
                this.lastSavedProgress = 0;
                this.progressSaveInterval = null;

                this.init();
            }

            init() {
                if (!this.video) {
                    console.error('Video element not found');
                    return;
                }

                this.setupEventListeners();
                this.hideQuizInitially();
                this.setupProgressSaving();
                this.addCustomStyles();
            }

            setupEventListeners() {
                // Video events
                this.video.addEventListener('loadedmetadata', () => this.onMetadataLoaded());
                this.video.addEventListener('timeupdate', () => this.onTimeUpdate());
                this.video.addEventListener('ended', () => this.onVideoEnded());
                this.video.addEventListener('seeking', () => this.onSeeking());
                this.video.addEventListener('ratechange', () => this.onRateChange());
                this.video.addEventListener('contextmenu', (e) => e.preventDefault());

                // Control events
                if (this.overlay) {
                    this.overlay.addEventListener('click', () => this.startVideo());
                }
                
                if (this.playPauseBtn) {
                    this.playPauseBtn.addEventListener('click', () => this.togglePlayPause());
                }

                if (this.progressContainer) {
                    this.progressContainer.addEventListener('click', (e) => this.seekVideo(e));
                }

                // Anti-cheat events
                document.addEventListener('visibilitychange', () => this.onVisibilityChange());
                document.addEventListener('keydown', (e) => this.onKeyDown(e));
                window.addEventListener('beforeunload', (e) => this.onBeforeUnload(e));
                window.addEventListener('blur', () => this.onWindowBlur());
                window.addEventListener('focus', () => this.onWindowFocus());
            }

            onMetadataLoaded() {
                this.videoDuration = this.video.duration;
                this.updateDurationDisplay();
                this.initializeWatchData();
                console.log(`Video loaded: ${this.formatTime(this.videoDuration)}`);
            }

            initializeWatchData() {
                this.watchData = {};
                for (let i = 0; i < Math.ceil(this.videoDuration); i++) {
                    this.watchData[i] = false;
                }
            }

            onTimeUpdate() {
                this.updateProgress();
                this.trackWatchedSegments();
                this.checkQuizUnlock();
                
                // Update max watched time
                if (this.video.currentTime > this.maxWatchedTime) {
                    this.maxWatchedTime = this.video.currentTime;
                }
            }

            trackWatchedSegments() {
                const currentSecond = Math.floor(this.video.currentTime);
                if (currentSecond < this.videoDuration && currentSecond >= 0) {
                    this.watchData[currentSecond] = true;
                }
            }

            updateProgress() {
                if (!this.videoDuration) return;

                const currentPercentage = (this.video.currentTime / this.videoDuration) * 100;
                const watchedSeconds = Object.values(this.watchData).filter(Boolean).length;
                const watchedPercentage = (watchedSeconds / this.videoDuration) * 100;

                // Update progress bar
                if (this.progressBar) {
                    this.progressBar.style.width = `${currentPercentage}%`;
                }

                // Update time display
                if (this.currentTimeEl) {
                    this.currentTimeEl.textContent = this.formatTime(this.video.currentTime);
                }

                // Update progress display in UI
                this.updateProgressDisplay(Math.min(watchedPercentage, 100));
            }

            updateProgressDisplay(percentage) {
                const progressDisplay = document.getElementById('videoProgressDisplay');
                if (progressDisplay) {
                    progressDisplay.textContent = Math.floor(percentage) + '%';
                    
                    // Color coding based on progress
                    if (percentage >= this.config.requiredWatchPercentage) {
                        progressDisplay.style.color = '#00b894';
                    } else if (percentage >= 75) {
                        progressDisplay.style.color = '#ffe200';
                    } else {
                        progressDisplay.style.color = '#d63031';
                    }
                }
            }

            checkQuizUnlock() {
                if (this.quizUnlocked || !this.videoDuration) return;

                const watchedSeconds = Object.values(this.watchData).filter(Boolean).length;
                const watchedPercentage = (watchedSeconds / this.videoDuration) * 100;

                if (watchedPercentage >= this.config.requiredWatchPercentage) {
                    this.unlockQuiz();
                }
            }

            unlockQuiz() {
                if (this.quizUnlocked) return;

                this.quizUnlocked = true;
                this.isVideoCompleted = true;

                // Hide locked message
                const lockedMessage = document.getElementById('videoLockedMessage');
                if (lockedMessage) {
                    lockedMessage.style.display = 'none';
                }

                // Show quiz with animation
                if (this.quizSection) {
                    this.showQuizWithAnimation();
                }

                // Show completion message
                if (this.completionMessage) {
                    this.completionMessage.classList.add('show');
                }

                this.showUnlockNotification();
                this.saveProgressToServer();
            }

            showQuizWithAnimation() {
                this.quizSection.style.display = 'block';
                this.quizSection.style.opacity = '0';
                this.quizSection.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    this.quizSection.style.transition = 'all 0.5s ease-out';
                    this.quizSection.style.opacity = '1';
                    this.quizSection.style.transform = 'translateY(0)';
                }, 100);

                setTimeout(() => {
                    this.quizSection.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 300);
            }

            onSeeking() {
                const seekTime = this.video.currentTime;
                
                // Prevent seeking beyond watched content
                if (seekTime > this.maxWatchedTime + this.config.seekTolerance) {
                    this.seekAttempts++;
                    this.video.currentTime = this.maxWatchedTime;
                    this.logSuspiciousActivity('seek_attempt', {
                        attemptedTime: seekTime,
                        maxAllowed: this.maxWatchedTime
                    });
                    this.showWarning('You must watch the video sequentially. Cannot skip to unwatched content.');
                }
            }

            onRateChange() {
                const newRate = this.video.playbackRate;
                if (newRate !== this.playbackSpeed) {
                    this.playbackSpeed = newRate;
                    this.logSuspiciousActivity('playback_speed_change', { rate: newRate });
                    
                    if (newRate > 2 || newRate < 0.5) {
                        this.showWarning('Unusual playback speed detected.');
                    }
                }
            }

            onVisibilityChange() {
                if (document.hidden && !this.video.paused) {
                    this.tabSwitchCount++;
                    this.video.pause();
                    if (this.playPauseBtn) {
                        this.playPauseBtn.textContent = 'Play';
                    }
                    this.logSuspiciousActivity('tab_switch', { count: this.tabSwitchCount });
                    this.showWarning('Video paused due to tab switch. Please keep this tab active while watching.');
                }
            }

            onWindowBlur() {
                this.logSuspiciousActivity('window_blur', { timestamp: Date.now() });
            }

            onWindowFocus() {
                this.logSuspiciousActivity('window_focus', { timestamp: Date.now() });
            }

            onBeforeUnload(e) {
                if (this.getWatchProgress() < this.config.requiredWatchPercentage && this.maxWatchedTime > 0) {
                    const message = 'You haven\'t completed the video yet. Are you sure you want to leave?';
                    e.returnValue = message;
                    return message;
                }
            }

            logSuspiciousActivity(type, data) {
                this.suspiciousActivity.push({
                    type,
                    data,
                    timestamp: Date.now(),
                    videoTime: this.video.currentTime
                });
                
                console.warn(`Suspicious activity detected: ${type}`, data);
            }

            startVideo() {
                if (this.overlay) {
                    this.overlay.classList.add('hidden');
                }
                this.video.play();
                if (this.playPauseBtn) {
                    this.playPauseBtn.textContent = 'Pause';
                }
            }

            togglePlayPause() {
                if (this.video.paused) {
                    this.video.play();
                    if (this.playPauseBtn) {
                        this.playPauseBtn.textContent = 'Pause';
                    }
                } else {
                    this.video.pause();
                    if (this.playPauseBtn) {
                        this.playPauseBtn.textContent = 'Play';
                    }
                }
            }

            seekVideo(e) {
                if (!this.videoDuration) return;

                const rect = this.progressContainer.getBoundingClientRect();
                const clickX = e.clientX - rect.left;
                const percentage = clickX / rect.width;
                const newTime = percentage * this.videoDuration;

                if (newTime <= this.maxWatchedTime) {
                    this.video.currentTime = newTime;
                } else {
                    this.showWarning('Cannot skip to unwatched content. Please watch sequentially.');
                }
            }

            showWarning(message) {
                if (this.warningMessage) {
                    this.warningMessage.textContent = `⚠️ ${message}`;
                    this.warningMessage.style.display = 'block';
                    setTimeout(() => {
                        this.warningMessage.style.display = 'none';
                    }, 3000);
                } else {
                    // Create temporary warning if element doesn't exist
                    const warning = document.createElement('div');
                    warning.innerHTML = `
                        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
                                    background: rgba(231, 76, 60, 0.95); color: white; padding: 20px;
                                    border-radius: 10px; z-index: 1000; text-align: center; font-weight: 600;">
                            ⚠️ ${message}
                        </div>
                    `;
                    document.body.appendChild(warning);
                    setTimeout(() => warning.remove(), 3000);
                }
            }

            showUnlockNotification() {
                const notification = document.createElement('div');
                notification.innerHTML = `
                    <div style="position: fixed; top: 20px; right: 20px;
                                background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
                                color: white; padding: 15px 20px; border-radius: 10px;
                                box-shadow: 0 5px 15px rgba(0,0,0,0.3); z-index: 1000; font-weight: 600;">
                        🎉 Quiz Unlocked! You can now take the assessment.
                    </div>
                `;
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 4000);
            }

            hideQuizInitially() {
                if (this.quizSection) {
                    this.quizSection.style.display = 'none';
                }

                if (!document.getElementById('videoLockedMessage')) {
                    const lockedMessage = document.createElement('div');
                    lockedMessage.id = 'videoLockedMessage';
                    lockedMessage.innerHTML = `
                        <div style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
                                    color: #8b4513; padding: 20px; margin: 20px; border-radius: 15px;
                                    text-align: center; font-weight: 600; border: 2px solid #f4a261;">
                            🔒 Complete ${this.config.requiredWatchPercentage}% of the video to unlock the quiz
                            <div style="margin-top: 10px; font-size: 1.2em; color: #d63031;">
                                Progress: <span id="videoProgressDisplay">0%</span>
                            </div>
                        </div>
                    `;

                    if (this.quizSection) {
                        this.quizSection.parentNode.insertBefore(lockedMessage, this.quizSection);
                    }
                }
            }

            setupProgressSaving() {
                this.progressSaveInterval = setInterval(() => {
                    this.saveProgressToServer();
                }, this.config.saveProgressInterval);
            }

            saveProgressToServer() {
                const progressData = {
                    watchedSeconds: Object.values(this.watchData).filter(Boolean).length,
                    totalSeconds: this.videoDuration,
                    maxWatchedTime: this.maxWatchedTime,
                    completionPercentage: this.getWatchProgress(),
                    tabSwitches: this.tabSwitchCount,
                    seekAttempts: this.seekAttempts,
                    suspiciousActivity: this.suspiciousActivity,
                    isCompleted: this.isVideoCompleted,
                    timestamp: Date.now()
                };

                // Replace with actual API call
                console.log('Saving progress:', progressData);
                
                // Example API call (uncomment and modify as needed):
                /*
                fetch('/api/save-video-progress', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(progressData)
                }).then(response => response.json())
                .then(data => console.log('Progress saved:', data))
                .catch(error => console.error('Error saving progress:', error));
                */
            }

            updateDurationDisplay() {
                if (this.durationEl && this.videoDuration) {
                    this.durationEl.textContent = this.formatTime(this.videoDuration);
                }
            }

            formatTime(seconds) {
                if (!seconds || isNaN(seconds)) return '0:00';
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.floor(seconds % 60);
                return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            }

            addCustomStyles() {
                if (!document.getElementById('videoLearningStyles')) {
                    const styles = document.createElement('style');
                    styles.id = 'videoLearningStyles';
                    styles.textContent = `
                        .hidden { display: none !important; }
                        .quiz-section { transition: all 0.5s ease-out; }
                        .video-overlay { cursor: pointer; }
                        .progress-container { cursor: pointer; }
                        .completion-message.show { 
                            opacity: 1; 
                            transform: translateY(0); 
                            visibility: visible; 
                        }
                        video { 
                            pointer-events: auto; 
                            -webkit-user-select: none; 
                            -moz-user-select: none; 
                            -ms-user-select: none; 
                            user-select: none; 
                        }
                        video::-webkit-media-controls-download-button { display: none !important; }
                        video::-webkit-media-controls-fullscreen-button { display: none !important; }
                    `;
                    document.head.appendChild(styles);
                }
            }

            // Public API methods
            getWatchProgress() {
                if (!this.videoDuration) return 0;
                const watchedSeconds = Object.values(this.watchData).filter(Boolean).length;
                return (watchedSeconds / this.videoDuration) * 100;
            }

            isQuizUnlocked() {
                return this.quizUnlocked;
            }

            getVideoStats() {
                return {
                    duration: this.videoDuration,
                    watchedTime: Object.values(this.watchData).filter(Boolean).length,
                    completionPercentage: this.getWatchProgress(),
                    maxWatchedTime: this.maxWatchedTime,
                    tabSwitches: this.tabSwitchCount,
                    seekAttempts: this.seekAttempts,
                    suspiciousActivityCount: this.suspiciousActivity.length,
                    isCompleted: this.isVideoCompleted
                };
            }

            destroy() {
                if (this.progressSaveInterval) {
                    clearInterval(this.progressSaveInterval);
                }
                // Final save before destroying
                this.saveProgressToServer();
            }
        }

        // Quiz form validation setup
        function setupQuizFormValidation() {
            const quizForm = document.querySelector('.quiz-section form');
            
            if (quizForm) {
                quizForm.addEventListener('submit', function(e) {
                    if (window.videoLearningSystem && !window.videoLearningSystem.isQuizUnlocked()) {
                        e.preventDefault();
                        alert('Please complete the required video percentage before submitting the quiz.');
                        return false;
                    }
                    
                    // Log final stats
                    if (window.videoLearningSystem) {
                        console.log('Quiz submitted with video stats:', 
                                window.videoLearningSystem.getVideoStats());
                    }
                });
            }
        }

        // Sidebar toggle (from original code)
        function setupSidebarToggle() {
            const menuToggle = document.querySelector('.menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    document.querySelector('.sidebar')?.classList.toggle('collapsed');
                    document.querySelector('.main-content')?.classList.toggle('expanded');
                });
            }
        }

        // Mark lesson completion (from original code)
        function markCompleted() {
            const lessonId = document.querySelector('[data-lesson-id]')?.getAttribute('data-lesson-id');
            const userId = document.querySelector('[data-user-id]')?.getAttribute('data-user-id');
            const videoFile = document.querySelector('[data-video-file]')?.getAttribute('data-video-file');
            
            if (lessonId && userId) {
                fetch(`mark_progress.php?lesson_id=${lessonId}&user_id=${userId}&temp_file=${encodeURIComponent(videoFile || '')}`)
                    .then(response => response.json())
                    .then(data => console.log("Progress marked:", data))
                    .catch(error => console.error("Error marking progress:", error));
            }
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the video learning system
            window.videoLearningSystem = new VideoLearningSystem({
                requiredWatchPercentage: 95,
                saveProgressInterval: 30000
            });
            
            // Setup additional functionality
            setupQuizFormValidation();
            setupSidebarToggle();
            
            console.log('Video Learning Management System initialized');
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (window.videoLearningSystem) {
                window.videoLearningSystem.destroy();
            }
        });
    </script>
</body>
</html>

