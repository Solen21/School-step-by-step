<?php
session_start();

// Security check
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: ../../../index.php");
    exit;
}

// Include database connection
require_once __DIR__ . '/../../../backend/config/db.php';

// Get section ID from URL and validate it
$section_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$section_id) {
    $_SESSION['error_message'] = "Invalid section ID provided.";
    header("location: ../management/sections.php");
    exit;
}

// Fetch the section details from the database
$stmt = $conn->prepare("SELECT s.section_name, g.grade_level, g.stream FROM sections s JOIN grades g ON s.grade_id = g.grade_id WHERE s.section_id = ?");
$stmt->bind_param("i", $section_id);
$stmt->execute();
$result = $stmt->get_result();
$section = $result->fetch_assoc();
$stmt->close();

// If no section is found, redirect with an error
if (!$section) {
    $_SESSION['error_message'] = "Section not found.";
    header("location: ../management/sections.php");
    exit;
}

$page_title = "Confirm Delete Section";
include_once('../../includes/header.php');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h2 class="mb-0">Confirm Section Deletion</h2>
                </div>
                <div class="card-body">
                    <p>Are you sure you want to delete <strong>Section <?php echo htmlspecialchars($section['section_name']); ?></strong> of <strong>Grade <?php echo htmlspecialchars($section['grade_level'] . ' (' . $section['stream'] . ')'); ?></strong>?</p>
                    <p class="text-danger"><strong>Warning:</strong> This may affect student assignments. This action cannot be undone.</p>
                    <form action="../../../backend/admin/delete/sections.php" method="POST" class="mt-3">
                        <input type="hidden" name="action" value="delete_section">
                        <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($section_id); ?>">
                        <a href="../management/sections.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Yes, Delete Section</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('../../includes/footer.php'); ?>