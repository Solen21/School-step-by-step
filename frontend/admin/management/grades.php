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

// Fetch all grades
$sql = "SELECT * FROM grades ORDER BY grade_level ASC, stream ASC";
$result = mysqli_query($conn, $sql);
$grades = mysqli_fetch_all($result, MYSQLI_ASSOC);

$page_title = "Manage Grade Levels";
include_once('../../includes/header.php');
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Manage Grade Levels</h2>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Admin Dashboard</a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Grade Level</th>
                                <th>Stream</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($grades)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No grades found. Add one to get started.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($grades as $grade): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['grade_level']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['stream']); ?></td>
                                        <td>
                                            <a href="../edit/grades.php?id=<?php echo $grade['grade_id']; ?>" class="btn btn-sm btn-warning" title="Edit Grade"><i class="fas fa-edit"></i></a>
                                            <a href="../delete/grades.php?id=<?php echo $grade['grade_id']; ?>" class="btn btn-sm btn-danger" title="Delete Grade"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="mb-0">Add New Grade</h3>
                </div>
                <div class="card-body">
                    <form action="../../../backend/admin/handle/grades.php" method="POST" id="addGradeForm">
                        <input type="hidden" name="action" value="add_grade_and_sections">
                        <div class="mb-3">
                            <label for="grade_level" class="form-label">Grade Level (e.g., 9, 10)</label>
                            <input type="number" class="form-control" id="grade_level" name="grade_level" min="1" max="12" required>
                        </div>
                        <div class="mb-3">
                            <label for="stream" class="form-label">Stream</label>
                            <select class="form-select" id="stream" name="stream" required>
                                <option value="" selected disabled>Select a stream...</option>
                                <option value="Natural">Natural</option>
                                <option value="Social">Social</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="num_sections" class="form-label">Number of Sections to Create</label>
                            <input type="number" class="form-control" id="num_sections" name="num_sections" min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Default Section Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" value="30" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Grade</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function () {
    var deleteGradeModal = document.getElementById('deleteGradeModal');
    deleteGradeModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var gradeId = button.getAttribute('data-grade-id');
        var gradeInfo = button.getAttribute('data-grade-info');

        deleteGradeModal.querySelector('#gradeInfo').textContent = gradeInfo;
        deleteGradeModal.querySelector('#deleteGradeId').value = gradeId;
    });
});
</script>

<?php include_once('../../includes/footer.php'); ?>
