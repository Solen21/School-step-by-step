<?php
session_start();
require_once '../../../backend/config/db.php';

// Security check
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: ../dashboard/index.php");
    exit;
}

// Fetch all subjects with grade info
$sql_subjects = "SELECT 
                    s.subject_id, 
                    s.name, 
                    COUNT(gs.grade_id) as grade_count
                 FROM subjects s
                 LEFT JOIN grade_subjects gs ON s.subject_id = gs.subject_id
                 GROUP BY s.subject_id, s.name
                 ORDER BY s.name ASC";
$result_subjects = mysqli_query($conn, $sql_subjects);
$subjects = mysqli_fetch_all($result_subjects, MYSQLI_ASSOC);

$page_title = "Manage Subjects";
include_once('../../includes/header.php');
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Manage Subjects</h2>
        <div>
            <a href="assign_subjects.php" class="btn btn-info"><i class="fas fa-tasks"></i> Assign to Grades</a>
            <a href="../assignments/subjects.php" class="btn btn-outline-secondary ms-2"><i class="fas fa-arrow-left"></i> Back to Admin Dashboard</a>
        </div>
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
                                <th>Subject Name</th>
                                <th>Assigned to Grades</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subjects)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No subjects found. Add one to get started.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($subject['grade_count']); ?></span></td>
                                        <td>
                                            <a href="view_subject.php?id=<?php echo $subject['subject_id']; ?>" class="btn btn-sm btn-primary" title="View Subject"><i class="fas fa-eye"></i></a>
                                            <a href="edit_subject.php?id=<?php echo $subject['subject_id']; ?>" class="btn btn-sm btn-warning" title="Edit Subject"><i class="fas fa-edit"></i></a>
                                            <button type="button" class="btn btn-sm btn-danger" title="Delete Subject"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteSubjectModal" 
                                                    data-subject-id="<?php echo $subject['subject_id']; ?>"
                                                    data-subject-info="<?php echo htmlspecialchars($subject['name']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
                    <h3 class="mb-0">Add New Subject</h3>
                </div>
                <div class="card-body">
                    <form action="../../../backend/admin/handle/subjects.php" method="POST">
                        <input type="hidden" name="action" value="add_subject">
                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Subject</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Subject Confirmation Modal -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1" aria-labelledby="deleteSubjectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteSubjectModalLabel">Confirm Subject Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete the subject <strong id="subjectInfo"></strong>?
        <p class="text-danger mt-2"><strong>Warning:</strong> This will also delete all associated student grades and teacher assignments. This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form action="../../auth/handle_academics.php" method="POST" class="d-inline">
            <input type="hidden" name="action" value="delete_subject">
            <input type="hidden" name="subject_id" id="deleteSubjectId">
            <button type="submit" class="btn btn-danger">Confirm Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var deleteSubjectModal = document.getElementById('deleteSubjectModal');
    deleteSubjectModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var subjectId = button.getAttribute('data-subject-id');
        var subjectInfo = button.getAttribute('data-subject-info');

        deleteSubjectModal.querySelector('#subjectInfo').textContent = subjectInfo;
        deleteSubjectModal.querySelector('#deleteSubjectId').value = subjectId;
    });
});
</script>

<?php include_once('../../includes/footer.php'); ?>
