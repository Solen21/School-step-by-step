<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db.php';

// Security check: ensure user is logged in and has the correct role
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error_message'] = "You are not authorized to perform this action.";
    header("location: ../../../frontend/admin/management/sections.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit_section') {

    // Sanitize and retrieve form data
    $section_id = filter_input(INPUT_POST, 'section_id', FILTER_VALIDATE_INT);
    $section_name = trim(filter_input(INPUT_POST, 'section_name', FILTER_SANITIZE_STRING));
    $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);

    // Validate input
    if (!$section_id || empty($section_name) || !$capacity) {
        $_SESSION['error_message'] = "Invalid input provided. Please check the form and try again.";
        header("location: ../../../frontend/admin/edit/sections.php?id=" . $section_id);
        exit;
    }

    // Get the grade_id for the current section to check for duplicates within the same grade
    $grade_stmt = $conn->prepare("SELECT grade_id FROM sections WHERE section_id = ?");
    $grade_stmt->bind_param("i", $section_id);
    $grade_stmt->execute();
    $grade_result = $grade_stmt->get_result();
    $grade_data = $grade_result->fetch_assoc();
    $grade_id = $grade_data['grade_id'];
    $grade_stmt->close();

    // Check if another section with the same name already exists in the same grade
    $check_sql = "SELECT section_id FROM sections WHERE grade_id = ? AND section_name = ? AND section_id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("isi", $grade_id, $section_name, $section_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Another section with this name already exists for this grade.";
        header("location: ../../../frontend/admin/edit/sections.php?id=" . $section_id);
        exit;
    }
    $check_stmt->close();

    // Prepare the update statement
    $update_sql = "UPDATE sections SET section_name = ?, capacity = ? WHERE section_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $section_name, $capacity, $section_id);

    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Section updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating section. Please try again.";
    }
    $update_stmt->close();

    header("location: ../../../frontend/admin/management/sections.php");
    exit;
}

$_SESSION['error_message'] = "Invalid request.";
header("location: ../../../frontend/admin/management/sections.php");
exit;
?>

