<?php
session_start();
require_once '../../../backend/config/db.php';

// Security check
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: ../../../index.php");
    exit;
}

// Fetch all sections with grade info
$sql_sections = "SELECT 
                    s.section_id, s.section_name, s.capacity, 
                    g.grade_level, g.stream,
                    CONCAT(t.first_name, ' ', t.last_name) AS homeroom_teacher_name
                 FROM sections s
                 JOIN grades g ON s.grade_id = g.grade_id
                 LEFT JOIN teachers t ON s.homeroom_teacher_id = t.teacher_id
                 ORDER BY g.grade_level, g.stream, s.section_name ASC";
$result_sections = mysqli_query($conn, $sql_sections);
$sections = mysqli_fetch_all($result_sections, MYSQLI_ASSOC);

// Fetch all grades for the dropdown
$sql_grades = "SELECT * FROM grades ORDER BY grade_level ASC";
$result_grades = mysqli_query($conn, $sql_grades);
$grades = mysqli_fetch_all($result_grades, MYSQLI_ASSOC);

$page_title = "Manage Sections";
include_once('../../includes/header.php');
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Manage Sections</h2>
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Admin Dashboard</a>
    </div>
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Grade Level</th>
                                <th>Section Name</th>
                                <th>Homeroom Teacher</th>
                                <th>Capacity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sections as $section): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars('Grade ' . $section['grade_level'] . ' (' . $section['stream'] . ')'); ?></td>
                                    <td><?php echo htmlspecialchars($section['section_name']); ?></td>
                                    <td>
                                        <?php if (!empty($section['homeroom_teacher_name'])): ?>
                                            <span class="badge bg-success"><?php echo htmlspecialchars($section['homeroom_teacher_name']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($section['capacity']); ?></td>
                                    <td>
                                        <a href="../generate/sections_id.php?section_id=<?php echo $section['section_id']; ?>" class="btn btn-sm btn-info" title="Print Section ID Cards"><i class="fas fa-id-card"></i></a>
                                        <a href="../edit/sections.php?id=<?php echo $section['section_id']; ?>" class="btn btn-sm btn-warning" title="Edit Section"><i class="fas fa-edit"></i></a>
                                        <a href="../delete/sections.php?id=<?php echo $section['section_id']; ?>" class="btn btn-sm btn-danger" title="Delete Section"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="mb-0">Add New Section</h3>
                </div>
                <div class="card-body">
                    <form action="../../../backend/admin/handle/sections.php" method="POST">
                        <input type="hidden" name="action" value="add_section">
                        <div class="mb-3">
                            <label for="grade_id" class="form-label">Grade Level</label>
                            <select class="form-select" id="grade_id" name="grade_id" required>
                                <option value="" selected disabled>Select a grade...</option>
                                <?php foreach ($grades as $grade): ?>
                                    <option value="<?php echo $grade['grade_id']; ?>"><?php echo htmlspecialchars('Grade ' . $grade['grade_level'] . ' - ' . $grade['stream']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="section_name" class="form-label">Section Name (e.g., A, B)</label>
                            <input type="text" class="form-control" id="section_name" name="section_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Section</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var deleteSectionModal = document.getElementById('deleteSectionModal');
    deleteSectionModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var sectionId = button.getAttribute('data-section-id');
        var sectionInfo = button.getAttribute('data-section-info');

        deleteSectionModal.querySelector('#sectionInfo').textContent = sectionInfo;
        deleteSectionModal.querySelector('#deleteSectionId').value = sectionId;
    });
});
</script>

<?php include_once('../../includes/footer.php'); ?>