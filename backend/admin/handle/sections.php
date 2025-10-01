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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    if ($_POST['action'] == 'add_section') {
        // Sanitize and retrieve form data
        $grade_id = filter_input(INPUT_POST, 'grade_id', FILTER_VALIDATE_INT);
        $section_name = trim(filter_input(INPUT_POST, 'section_name', FILTER_SANITIZE_STRING));
        $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);

        // Validate input
        if (!$grade_id || empty($section_name) || !$capacity) {
            $_SESSION['error_message'] = "Invalid input provided. Please fill all fields and try again.";
            header("location: ../../../frontend/admin/management/sections.php");
            exit;
        }

        // Check if section with the same name already exists for this grade
        $check_sql = "SELECT section_id FROM sections WHERE grade_id = ? AND section_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $grade_id, $section_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $_SESSION['error_message'] = "A section with the name '$section_name' already exists for this grade level.";
        } else {
            // Insert the new section
            $insert_sql = "INSERT INTO sections (grade_id, section_name, capacity) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("isi", $grade_id, $section_name, $capacity);

            if ($insert_stmt->execute()) {
                $_SESSION['success_message'] = "Section '$section_name' added successfully!";
            } else {
                $_SESSION['error_message'] = "Error adding section. Please try again.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();

        header("location: ../../../frontend/admin/management/sections.php");
        exit;
    }
} else {
    // If not a POST request or no action specified, redirect
    $_SESSION['error_message'] = "Invalid request.";
    header("location: ../../../frontend/admin/management/sections.php");
    exit;
}
?>