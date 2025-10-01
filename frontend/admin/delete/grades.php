<?php
session_start();

// Security check
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: ../../index.php");
    exit;
}

// Include database connection
require_once __DIR__ . '/../../../backend/config/db.php';

// Get grade ID from URL and validate it
$grade_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$grade_id) {
    $_SESSION['error_message'] = "Invalid grade ID provided.";
    header("location: ../management/grades.php");
    exit;
}

// Fetch the grade details from the database
$stmt = $conn->prepare("SELECT * FROM grades WHERE grade_id = ?");
$stmt->bind_param("i", $grade_id);
$stmt->execute();
$result = $stmt->get_result();
$grade = $result->fetch_assoc();
$stmt->close();

// If no grade is found, redirect with an error
if (!$grade) {
    $_SESSION['error_message'] = "Grade not found.";
    header("location: ../management/grades.php");
    exit;
}

$page_title = "Confirm Delete Grade";
include_once('../../includes/header.php');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h2 class="mb-0">Confirm Grade Deletion</h2>
                </div>
                <div class="card-body">
                    <p>Are you sure you want to delete the grade <strong><?php echo htmlspecialchars('Grade ' . $grade['grade_level'] . ' (' . $grade['stream'] . ')'); ?></strong>?</p>
                    <p class="text-danger"><strong>Warning:</strong> This action will also delete all associated sections and subject assignments. This action cannot be undone.</p>
                    <form action="../../../backend/admin/delete/grades.php" method="POST" class="mt-3">
                        <input type="hidden" name="action" value="delete_grade">
                        <input type="hidden" name="grade_id" value="<?php echo htmlspecialchars($grade['grade_id']); ?>">
                        <a href="../management/grades.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Yes, Delete Grade</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('../../includes/footer.php'); ?>