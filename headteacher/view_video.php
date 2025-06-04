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
$stmt = $pdo->prepare("SELECT id, course_id, title, video, file_attachment, file_name FROM lessons WHERE id = ?");
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

        @media (max-width: 768px) {
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
            <div class="menu-item" onclick="window.location.href='dashboard.php';">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="menu-item active" onclick="window.location.href='courses.php';">
                <i class="fas fa-book"></i>
                <span>Courses</span>
            </div>
            <div class="menu-item" onclick="window.location.href='videoupload.php';">
                <i class="fa-solid fa-upload"></i>
                <span>Upload Files</span>
            </div>
            
            <div class="menu-item" onclick="window.location.href='messages.php';">
                <i class="fas fa-comment"></i>
                <span>Messages</span>
              
            </div>
            <div class="menu-item" onclick="window.location.href='users.php';">
                <i class="fas fa-users"></i>
                <span>Participants</span>
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
        // Sidebar toggle functionality
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });
        
        function markCompleted() {
            fetch("mark_progress.php?lesson_id=<?= $lesson_id ?>&user_id=<?= $user_id ?>&temp_file=<?= htmlspecialchars($videoFile) ?>")
                .then(response => console.log("Progress marked and cleanup triggered"));
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const video = document.getElementById('courseVideo');
            const overlay = document.getElementById('videoOverlay');
            const playPauseBtn = document.getElementById('playPauseBtn');
            const progressBar = document.getElementById('progressBar');
            const currentTimeDisplay = document.getElementById('currentTime');
            const durationDisplay = document.getElementById('duration');

            const completionMessage = document.getElementById('completionMessage');
            const warningMessage = document.getElementById('warningMessage');
            
            // Variables for tracking
            let watchTime = 0;
            let lastUpdateTime = 0;
            let videoDuration = 0;
            let isVideoCompleted = false;
            let isValidPlay = true;
            let watchData = {};
            let tabSwitchCount = 0;
            let seekAttempts = 0;
            
            // Initialize video tracking data structure
            function initializeWatchData() {
                for (let i = 0; i < Math.ceil(videoDuration); i++) {
                    watchData[i] = false;
                }
            }
            
            // Format time (seconds) to MM:SS format
            function formatTime(seconds) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.floor(seconds % 60);
                return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            }

            
            // Calculate and update progress
            function updateProgress() {
                const progress = (video.currentTime / videoDuration) * 100;
                progressBar.style.width = `${progress}%`;
                currentTimeDisplay.textContent = formatTime(video.currentTime);
                
                // Mark the current second as watched
                const currentSecond = Math.floor(video.currentTime);
                if (currentSecond < videoDuration) {
                    watchData[currentSecond] = true;
                }
                
                // Calculate actual watch time based on marked seconds
                const watchedSeconds = Object.values(watchData).filter(Boolean).length;
                watchTime = watchedSeconds;
                
                // Update stats
                const completionPercentage = Math.min(Math.round((watchTime / videoDuration) * 100), 100);
                
                // Check if video is completed (95% watched)
                if (completionPercentage >= 95 && !isVideoCompleted) {
                    isVideoCompleted = true;
                    completionMessage.classList.add('show');
                    
                    // Simulate sending data to LMS backend
                    console.log('Video completed! Sending data to LMS:', {
                        watchTime: watchTime,
                        videoDuration: videoDuration,
                        completionPercentage: completionPercentage,
                        tabSwitches: tabSwitchCount,
                        seekAttempts: seekAttempts
                    });
                    
                    // You would replace this with actual API call
                    setTimeout(() => {
                        alert('Progress saved successfully!');
                    }, 1000);
                }
            }
            
            // Event: Video metadata loaded
            video.addEventListener('loadedmetadata', function() {
                videoDuration = video.duration;
                durationDisplay.textContent = formatTime(videoDuration);
                initializeWatchData();
            });
            
            // Event: Video time update
            video.addEventListener('timeupdate', function() {
                if (isValidPlay) {
                    updateProgress();
                }
            });
            
            // Event: Video ended
            video.addEventListener('ended', function() {
                playPauseBtn.textContent = 'Replay';
            });

            
            // Event: Play button clicked
            playPauseBtn.addEventListener('click', function() {
                if (video.paused) {
                    video.play();
                    playPauseBtn.textContent = 'Pause';
                } else {
                    video.pause();
                    playPauseBtn.textContent = 'Play';
                }
            });
            
            // Event: Overlay clicked
            overlay.addEventListener('click', function() {
                overlay.classList.add('hidden');
                video.play();
                playPauseBtn.textContent = 'Pause';
            });
            
            // Anti-cheat: Prevent seeking forward
            video.addEventListener('seeking', function() {
                if (video.currentTime > lastUpdateTime + 2) {
                    seekAttempts++;
                    video.currentTime = lastUpdateTime;
                    isValidPlay = false;
                    
                    // Show warning
                    warningMessage.textContent = '⚠️ Video skipping is not allowed. Please watch the video in sequence.';
                    warningMessage.style.display = 'block';
                    setTimeout(() => {
                        warningMessage.style.display = 'none';
                    }, 3000);
                    
                    setTimeout(() => {
                        isValidPlay = true;
                    }, 100);
                }
                lastUpdateTime = video.currentTime;
            });
            
            // Anti-cheat: Block right-click context menu
            video.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Anti-cheat: Prevent keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Prevent arrow keys, space bar when not focused on play button
                if ([32, 37, 38, 39, 40].indexOf(e.keyCode) > -1 && 
                    document.activeElement !== playPauseBtn) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Anti-cheat: Handle tab switching
            document.addEventListener('visibilitychange', function() {
                if (document.hidden && !video.paused) {
                    tabSwitchCount++;
                    video.pause();
                    playPauseBtn.textContent = 'Play';
                    
                    warningMessage.textContent = '⚠️ Video paused due to tab switch. Please keep this tab active while watching.';
                    warningMessage.style.display = 'block';
                    setTimeout(() => {
                        warningMessage.style.display = 'none';
                    }, 5000);
                }
            });
            
            // Prevent video download attempts
            video.addEventListener('loadstart', function() {
                video.removeAttribute('controls');
            });
            
            // Initialize
        });
    </script>
</body>
</html>

