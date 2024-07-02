<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$exam_id = isset($_POST['exam_id']) ? $_POST['exam_id'] : null;
$answers = isset($_POST['answers']) ? $_POST['answers'] : [];

if (!$exam_id || empty($answers)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid exam data']);
    exit();
}

try {
    $conn->begin_transaction();

    foreach ($answers as $question_id => $answer) {
        $stmt = $conn->prepare('SELECT correct_answer FROM Questions WHERE id = ?');
        $stmt->bind_param('i', $question_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $is_correct = ($answer === $result['correct_answer']) ? 1 : 0;

        $stmt = $conn->prepare('INSERT INTO Answers (question_id, user_id, answer_text, is_correct) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iisi', $question_id, $user_id, $answer, $is_correct);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Exam submitted successfully']);
} catch (Exception $e) {
    $conn->rollback();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to submit exam. Please try again.']);
}