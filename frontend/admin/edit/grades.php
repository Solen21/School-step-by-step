<?php
session_start();

// Include database connection
require_once __DIR__ . '/../../../backend/config/db.php';

// Security check: ensure user is logged in and has the correct role
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error_message'] = "You are not authorized to view this page.";
    header("location: ../../../index.php");
    exit;
}

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

$page_title = "Edit Grade";
include_once('../../includes/header.php');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Edit Grade</h2>
                    <a href="../management/grades.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="../../../backend/admin/edit/grades.php" method="POST">
                        <input type="hidden" name="action" value="edit_grade">
                        <input type="hidden" name="grade_id" value="<?php echo htmlspecialchars($grade['grade_id']); ?>">

                        <div class="mb-3">
                            <label for="grade_level" class="form-label">Grade Level</label>
                            <input type="number" class="form-control" id="grade_level" name="grade_level" value="<?php echo htmlspecialchars($grade['grade_level']); ?>" min="1" max="12" required>
                        </div>

                        <div class="mb-3">
                            <label for="stream" class="form-label">Stream</label>
                            <input type="text" class="form-control" id="stream" name="stream" value="<?php echo htmlspecialchars($grade['stream']); ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update Grade</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('../../includes/footer.php'); ?>