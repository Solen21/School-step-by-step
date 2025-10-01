<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db.php';

// Security check: ensure user is logged in and has the correct role
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error_message'] = "You are not authorized to perform this action.";
    header("location: ../../../frontend/admin/management/grades.php");
    header("location: ../../../frontend/admin/management/grades.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit_grade') {

    // Sanitize and retrieve form data
    $grade_id = filter_input(INPUT_POST, 'grade_id', FILTER_VALIDATE_INT);
    $grade_level = filter_input(INPUT_POST, 'grade_level', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 12]]);
    $stream = filter_input(INPUT_POST, 'stream', FILTER_SANITIZE_STRING);

    // Validate input
    if (!$grade_id || !$grade_level || empty($stream)) {
        $_SESSION['error_message'] = "Invalid input provided. Please check the form and try again.";
        // Redirect back to the edit page if there's an error
        header("location: grades.php?id=" . $grade_id);
        header("location: ../../../frontend/admin/edit/grades.php?id=" . $grade_id);
        exit;
    }

    // Check if another grade with the same level and stream already exists
    $check_sql = "SELECT grade_id FROM grades WHERE grade_level = ? AND stream = ? AND grade_id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("isi", $grade_level, $stream, $grade_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Another grade with the same level and stream already exists.";
        $check_stmt->close();
        header("location: grades.php?id=" . $grade_id);
        header("location: ../../../frontend/admin/edit/grades.php?id=" . $grade_id);
        exit;
    }
    $check_stmt->close();

    // Prepare the update statement
    $update_sql = "UPDATE grades SET grade_level = ?, stream = ? WHERE grade_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("isi", $grade_level, $stream, $grade_id);

    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Grade updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating grade. Please try again.";
    }
    $update_stmt->close();

    header("location: ../../../frontend/management/grades.php");
    header("location: ../../../frontend/admin/management/grades.php");
    exit;

} else {
    // If not a POST request or no action specified, redirect
    $_SESSION['error_message'] = "Invalid request.";
    header("location: ../../../frontend/management/grades.php");
    header("location: ../../../frontend/admin/management/grades.php");
    exit;
}
?>