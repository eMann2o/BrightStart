<?php
session_start();
require 'db.php';

$user_email = $_SESSION['email'];
$lesson_id = $_POST['lesson_id'];
$answers = $_POST['quiz'] ?? [];

// Fetch user_id
$user = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$user->execute([$user_email]);
$user_id = $user->fetchColumn();

// Check if already passed
$check = $pdo->prepare("SELECT status FROM progress WHERE user_id = ? AND lesson_id = ?");
$check->execute([$user_id, $lesson_id]);
$status = $check->fetchColumn();

if ($status === 'completed') {
    echo "<script>alert('✅ You have already passed this quiz. No need to retake.'); window.location.href='courses.php';</script>";
    exit;
}

$score = 0;
$total_mcq = 0;
$attempt_time = date('Y-m-d H:i:s'); // ⏱️ Used to group all answers in this attempt

foreach ($answers as $quiz_id => $answer) {
    $quiz = $pdo->prepare("SELECT * FROM lesson_quizzes WHERE id = ?");
    $quiz->execute([$quiz_id]);
    $question = $quiz->fetch();

    if (!$question) continue;

    $is_correct = null;
    if ($question['type'] === 'mcq') {
        $total_mcq++;
        $is_correct = strtoupper($answer) === $question['correct_option'] ? 1 : 0;
        if ($is_correct) $score++;
    }

    // Log each answer with attempt timestamp
    $stmt = $pdo->prepare("INSERT INTO lesson_quiz_answers (user_id, quiz_id, answer, is_correct, created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $quiz_id, $answer, $is_correct, $attempt_time]);
}

// Determine pass/fail
$passed = ($score >= 4);
$message = $passed ? "🎉 You passed the quiz! You scored {$score}/5" : "❌ You did not pass. You scored {$score}/{$total_mcq}.";

// Update progress if passed
if ($passed) {
    $stmt = $pdo->prepare("INSERT INTO progress (user_id, lesson_id, status, updated_at) 
        VALUES (?, ?, 'completed', NOW()) 
        ON DUPLICATE KEY UPDATE status='completed', updated_at=NOW()");
    $stmt->execute([$user_id, $lesson_id]);
}

echo "<script>alert('$message'); window.location.href='courses.php';</script>";
?>
