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

// Fetch all students with their grade and section information
$sql = "SELECT 
            s.student_id, s.id_number, s.first_name, s.middle_name, s.last_name, s.photo_path,
            g.grade_level, g.stream,
            sec.section_name
        FROM students s
        LEFT JOIN sections sec ON s.section_id = sec.section_id
        LEFT JOIN grades g ON sec.grade_id = g.grade_id
        ORDER BY g.grade_level, sec.section_name, s.last_name, s.first_name";

$result = mysqli_query($conn, $sql);
$students = mysqli_fetch_all($result, MYSQLI_ASSOC);

$page_title = "Manage Students";
include_once('../../includes/header.php');
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Manage Students</h2>
        <div>
            <a href="add_student.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add New Student</a>
            <a href="index.php" class="btn btn-outline-secondary ms-2"><i class="fas fa-arrow-left"></i> Back to Management</a>
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

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Photo</th>
                        <th>Full Name</th>
                        <th>ID Number</th>
                        <th>Grade & Section</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No students found. Add one to get started.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo !empty($student['photo_path']) ? '/ethio-school' . htmlspecialchars($student['photo_path']) : '/ethio-school/assets/images/default_avatar.png'; ?>" alt="Student Photo" class="rounded-circle" width="40" height="40">
                                </td>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['id_number']); ?></td>
                                <td>
                                    <?php if ($student['grade_level'] && $student['section_name']): ?>
                                        <?php echo htmlspecialchars('Grade ' . $student['grade_level'] . ' ' . $student['stream'] . ' - ' . $student['section_name']); ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="view_student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i></a>
                                    <a href="edit_student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-warning" title="Edit Student"><i class="fas fa-edit"></i></a>
                                    <a href="../delete/students.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-danger" title="Delete Student" onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once('../../includes/footer.php'); ?>








































































































































































































































































































































-