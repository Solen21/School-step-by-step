<?php
session_start();

require_once __DIR__ . '/../../config/db.php';

// Security check: ensure user is logged in and has the correct role
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error_message'] = "You are not authorized to perform this action.";
    header("location: ../../../frontend/admin/management/students.php");
    exit;
}

$student_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$student_id) {
    $_SESSION['error_message'] = "Invalid student ID provided.";
    header("location: ../../../frontend/admin/management/students.php");
    exit;
}

// The student record has a user_id associated with it. Deleting the student
// should also delete the corresponding user account to keep the system clean.
// The database is set up with ON DELETE CASCADE for the students table's user_id foreign key,
// so deleting the user will automatically delete the student record.

// First, get the user_id from the student_id
$stmt_get_user = $conn->prepare("SELECT user_id FROM students WHERE student_id = ?");
$stmt_get_user->bind_param("i", $student_id);
$stmt_get_user->execute();
$result = $stmt_get_user->get_result();
$student = $result->fetch_assoc();
$stmt_get_user->close();

if ($student && isset($student['user_id'])) {
    $user_id = $student['user_id'];
    $stmt_delete_user = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt_delete_user->bind_param("i", $user_id);
    if ($stmt_delete_user->execute()) {
        $_SESSION['success_message'] = "Student and associated user account deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting student. Please try again.";
    }
    $stmt_delete_user->close();
} else {
    $_SESSION['error_message'] = "Could not find the student or associated user to delete.";
}

header("location: ../../../frontend/admin/management/students.php");
exit;