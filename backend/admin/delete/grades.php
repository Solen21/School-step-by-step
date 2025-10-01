<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db.php';

// Security check: ensure user is logged in and has the correct role
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error_message'] = "You are not authorized to perform this action.";
    // Redirect to a safe page
    header("location: ../../../frontend/admin/management/index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // =================================================
    //  HANDLE DELETE GRADE
    // =================================================
    if ($_POST['action'] == 'delete_grade') {
        $grade_id = filter_input(INPUT_POST, 'grade_id', FILTER_VALIDATE_INT);

        if (!$grade_id) {
            $_SESSION['error_message'] = "Invalid grade ID provided.";
        } else {
            // The database schema uses ON DELETE CASCADE for the 'sections' and 'grade_subjects' tables,
            // so deleting a grade will automatically delete its associated sections and subject assignments.
            $sql = "DELETE FROM grades WHERE grade_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $grade_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Grade and all its associated sections have been deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Error deleting grade. It might be in use elsewhere.";
            }
            $stmt->close();
        }
        header("location: ../../../frontend/admin/management/grades.php");
        exit;
    }

}

$_SESSION['error_message'] = "Invalid request.";
header("location: ../../../frontend/admin/management/index.php");
exit;
?>

