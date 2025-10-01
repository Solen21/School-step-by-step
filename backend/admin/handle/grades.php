<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db.php';

// Security check: ensure user is logged in and has the correct role
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error_message'] = "You are not authorized to perform this action.";
    header("location: ../../../frontend/admin/management/grades.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    if ($_POST['action'] == 'add_grade_and_sections') {
        // Sanitize and retrieve form data
        $grade_level = filter_input(INPUT_POST, 'grade_level', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 12]]);
        $stream = filter_input(INPUT_POST, 'stream', FILTER_SANITIZE_STRING);
        $num_sections = filter_input(INPUT_POST, 'num_sections', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

        // Validate input
        if (!$grade_level || !$stream || !$num_sections || !$capacity) {
            $_SESSION['error_message'] = "Invalid input provided. Please check the form and try again.";
            header("location: ../../../frontend/admin/management/grades.php");
            exit;
        }

        // Start a database transaction
        $conn->begin_transaction();

        try {
            // 1. Check if the grade and stream combination already exists
            $check_sql = "SELECT grade_id FROM grades WHERE grade_level = ? AND stream = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("is", $grade_level, $stream);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                throw new Exception("This grade level and stream combination already exists.");
            }
            $check_stmt->close();

            // 2. Insert the new grade
            $grade_sql = "INSERT INTO grades (grade_level, stream) VALUES (?, ?)";
            $grade_stmt = $conn->prepare($grade_sql);
            $grade_stmt->bind_param("is", $grade_level, $stream);
            $grade_stmt->execute();
            $new_grade_id = $conn->insert_id;
            $grade_stmt->close();

            // 3. Insert the new sections
            $section_sql = "INSERT INTO sections (grade_id, section_name, capacity) VALUES (?, ?, ?)";
            $section_stmt = $conn->prepare($section_sql);

            for ($i = 0; $i < $num_sections; $i++) {
                // Generate section name (A, B, C, ...)
                $section_name = chr(65 + $i);
                $section_stmt->bind_param("isi", $new_grade_id, $section_name, $capacity);
                $section_stmt->execute();
            }
            $section_stmt->close();

            // If all queries were successful, commit the transaction
            $conn->commit();
            $_SESSION['success_message'] = "Grade and " . $num_sections . " section(s) added successfully!";

        } catch (Exception $e) {
            // An error occurred, roll back the transaction
            $conn->rollback();
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }

        // Redirect back to the grades management page
        header("location: ../../../frontend/admin/management/grades.php");
        exit;
    }

} else {
    // If not a POST request or no action specified, redirect
    $_SESSION['error_message'] = "Invalid request.";
    header("location: ../../../frontend/admin/management/grades.php");
    exit;
}
?>