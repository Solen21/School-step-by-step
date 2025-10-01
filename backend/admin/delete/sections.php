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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_section') {

    $section_id = filter_input(INPUT_POST, 'section_id', FILTER_VALIDATE_INT);

    if (!$section_id) {
        $_SESSION['error_message'] = "Invalid section ID provided.";
    } else {
        // Prepare the delete statement
        $sql = "DELETE FROM sections WHERE section_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $section_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Section deleted successfully.";
        } else {
            // This might fail if there are foreign key constraints (e.g., students assigned to this section)
            $_SESSION['error_message'] = "Error deleting section. It might still have students assigned to it.";
        }
        $stmt->close();
    }
    header("location: ../../../frontend/admin/management/sections.php");
    exit;
}

$_SESSION['error_message'] = "Invalid request.";
header("location: ../../../frontend/admin/management/sections.php");
exit;
?>