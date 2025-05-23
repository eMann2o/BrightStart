<?php
session_start();
require 'db.php';

if (!isset($_GET['lesson_id']) || !isset($_SESSION['email'])) {
    echo "Missing lesson or user.";
    exit;
}

// Get user ID from session email
$email = $_SESSION['email'];
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
$user_id = $user['id'];

$lesson_id = $_GET['lesson_id'];

// Fetch video from DB
$stmt = $pdo->prepare("SELECT title, video FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    echo "Lesson not found.";
    exit;
}

// Generate a unique file name
$uniqueFileName = 'temp_' . uniqid() . '.mp4';

// Save video blob to the unique file
file_put_contents($uniqueFileName, $lesson['video']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lesson['title']) ?></title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            background-color: #000;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        video {
            width: 100%;
            display: block;
        }
        
        .controls {
            display: flex;
            align-items: center;
            background-color: #333;
            padding: 10px;
            color: white;
        }
        
        .play-pause {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .play-pause:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        .progress-container {
            flex-grow: 1;
            height: 10px;
            background-color: #555;
            border-radius: 5px;
            margin: 0 10px;
            position: relative;
        }
        
        .progress-bar {
            height: 100%;
            background-color: #4CAF50;
            border-radius: 5px;
            width: 0%;
            transition: width 0.1s linear;
        }
        
        .time-display {
            font-size: 14px;
            min-width: 100px;
            text-align: center;
        }
        
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 20px;
            z-index: 10;
        }
        
        .hidden {
            display: none;
        }
        
        .stats {
            background-color: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .stats h2 {
            margin-top: 0;
            color: #333;
        }
        
        .stats-content {
            display: flex;
            justify-content: space-between;
        }
        
        .stat-box {
            flex: 1;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 5px;
            margin: 0 5px;
            text-align: center;
        }
        
        .stat-box h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #555;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
        }
        
        .completion-message {
            padding: 15px;
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
            display: none;
        }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($lesson['title']) ?></h1>
    <div class="video-container">
        <div id="videoOverlay" class="video-overlay">
            <?= htmlspecialchars($lesson['title']) ?>
        </div>
        <video id="courseVideo" preload="metadata" onended="markCompleted()">
            <source src="<?= htmlspecialchars($uniqueFileName) ?>" type="video/mp4">
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
    
    <div id="completionMessage" class="completion-message">
        Congratulations! You have completed this video lesson.
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const video = document.getElementById('courseVideo');
            const overlay = document.getElementById('videoOverlay');
            const playPauseBtn = document.getElementById('playPauseBtn');
            const progressBar = document.getElementById('progressBar');
            const currentTimeDisplay = document.getElementById('currentTime');
            const durationDisplay = document.getElementById('duration');
            const watchTimeValue = document.getElementById('watchTimeValue');
            const videoDurationValue = document.getElementById('videoDurationValue');
            const completionValue = document.getElementById('completionValue');
            const completionMessage = document.getElementById('completionMessage');
            
            // Variables for tracking
            let watchTime = 0;  // Total seconds watched
            let lastUpdateTime = 0;  // Last time we updated the watch time
            let videoDuration = 0;
            let isVideoCompleted = false;
            let isValidPlay = true;
            let watchData = {};
            
            // Initialize video tracking data structure
            function initializeWatchData() {
                // Segment the video duration into 1-second intervals for tracking
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
                watchTimeValue.textContent = formatTime(watchTime);
                const completionPercentage = Math.min(Math.round((watchTime / videoDuration) * 100), 100);
                completionValue.textContent = `${completionPercentage}%`;
                
                // Check if video is completed (we consider 95% watched as complete)
                if (completionPercentage >= 95 && !isVideoCompleted) {
                    isVideoCompleted = true;
                    completionMessage.style.display = 'block';
                    
                    // Here you would send data to your LMS backend
                    console.log('Video completed! Sending data to LMS:', {
                        watchTime: watchTime,
                        videoDuration: videoDuration,
                        completionPercentage: completionPercentage
                    });
                }
            }
            
            // Event: Video metadata loaded
            video.addEventListener('loadedmetadata', function() {
                videoDuration = video.duration;
                durationDisplay.textContent = formatTime(videoDuration);
                videoDurationValue.textContent = formatTime(videoDuration);
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
                isValidPlay = true;
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
            
            // Prevent seeking and ensure video can't be skipped
            video.addEventListener('seeking', function() {
                // If trying to seek forward, prevent it
                if (video.currentTime > lastUpdateTime + 1) {
                    video.currentTime = lastUpdateTime;
                    isValidPlay = false;
                    setTimeout(() => {
                        isValidPlay = true;
                    }, 100);
                }
                lastUpdateTime = video.currentTime;
            });
            
            // Block right-click to prevent downloading
            video.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Prevent keyboard shortcuts for seeking
            document.addEventListener('keydown', function(e) {
                // Prevent arrow keys, space bar
                if ([32, 37, 38, 39, 40].indexOf(e.keyCode) > -1 && document.activeElement !== playPauseBtn) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Handle visibility change (tab switching)
            document.addEventListener('visibilitychange', function() {
                if (document.hidden && !video.paused) {
                    video.pause();
                    playPauseBtn.textContent = 'Play';
                }
            });
            
            // Initialize the video player
            durationDisplay.textContent = '0:00';
        });
        
        function markCompleted() {
            fetch("mark_progress.php?lesson_id=<?= $lesson_id ?>&user_id=<?= $user_id ?>&temp_file=<?= urlencode($uniqueFileName) ?>")
                .then(response => console.log("Progress marked and cleanup triggered"));
        }
    </script>
</body>
</html>

