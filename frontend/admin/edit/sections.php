<?php
session_start();
require_once __DIR__ . '/../../../backend/config/db.php';

// Security check
$allowed_roles = ['admin', 'super_director', 'director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: ../../../index.php");
    exit;
}

// Check if section ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: ../management/sections.php");
    exit;
}

$section_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($section_id === false) {
    header("location: ../management/sections.php");
    exit;
}

// Fetch section's current data
$sql = "SELECT s.section_id, s.section_name, s.capacity, g.grade_level, g.stream 
        FROM sections s 
        JOIN grades g ON s.grade_id = g.grade_id
        WHERE s.section_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $section_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$section = mysqli_fetch_assoc($result);

if (!$section) {
    $_SESSION['error_message'] = "Section not found.";
    header("location: ../management/sections.php");
    exit;
}

$page_title = "Edit Section";
include_once('../../includes/header.php');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Edit Section</h2>
                    <a href="../management/sections.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
                <div class="card-body">
                    <form action="../../../backend/admin/edit/sections.php" method="POST">
                        <input type="hidden" name="action" value="edit_section">
                        <input type="hidden" name="section_id" value="<?php echo $section['section_id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Grade Level</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars('Grade ' . $section['grade_level'] . ' (' . $section['stream'] . ')'); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="section_name" class="form-label">Section Name</label>
                            <input type="text" class="form-control" id="section_name" name="section_name" value="<?php echo htmlspecialchars($section['section_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" value="<?php echo htmlspecialchars($section['capacity']); ?>" min="1" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Section</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('../../includes/footer.php'); ?>