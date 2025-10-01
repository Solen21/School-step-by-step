<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../database/index.php");
    exit;
}

// Check if the user has the super_director role. If not, redirect.
$allowed_roles = ['super_director'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    // Redirect non-super_directors away.
    header("location: /African-chool-management-system/dashboard/index.php");
    exit;
}

$page_title = "Super Director Dashboard";
// Go up two directories to find the 'includes' folder.
include_once('../includes/header.php');
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Super Director Dashboard</h2>
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i></a>
    </div>

    <div class="alert alert-danger">
        <h4 class="alert-heading">Welcome, Super Director 100%s!</h4>
        <p>You have ultimate oversight and control over the entire system.</p>
    </div>

    <div class="row">

        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-user-cog fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Management</h5>
                    <p class="card-text">Add, edit, and manage all systems.</p>
                    <a href="../admin/management/index.php" class="btn btn-primary">Go to Management</a>
                </div>
            </div>
        </div>


        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-cogs fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">System Configuration</h5>
                    <p class="card-text">Manage core settings, roles, and system logs.</p>
                    <a href="../admin/manage_system_config.php" class="btn btn-danger">System Settings</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-landmark fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Financial Overview</h5>
                    <p class="card-text">View financial reports, budgets, and school-wide health.</p>
                    <a href="../finance/index.php" class="btn btn-warning">View Finances</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-sitemap fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Strategic Planning</h5>
                    <p class="card-text">Access long-term performance data and school plans.</p>
                    <a href="#" class="btn btn-primary">Strategic Tools</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-gavel fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Manage Discipline</h5>
                    <p class="card-text">Log and manage student disciplinary incidents.</p>
                    <a href="manage_discipline.php" class="btn btn-danger">Go to Discipline</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-newspaper fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Manage News</h5>
                    <p class="card-text">Create, edit, and publish school news articles.</p>
                    <a href="manage_news.php" class="btn btn-info">Go to News</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-clipboard-list fa-3x text-secondary mb-3"></i>
                    <h5 class="card-title">View System Logs</h5>
                    <p class="card-text">Monitor system activities, errors, and user actions.</p>
                    <a href="view_system_logs.php" class="btn btn-secondary">View Logs</a>
                </div>
            </div>
        </div>
    </div>
</div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-tasks fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Assign Subjects</h5>
                    <p class="card-text">Assign subjects to specific grade levels.</p>
                    <a href="../admin/assign_subjects.php" class="btn btn-danger">Go to Assignments</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-bullhorn fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Announcements</h5>
                    <p class="card-text">Create and manage school-wide notifications.</p>
                    <a href="../admin/manage_notifications.php" class="btn btn-info">Go to Notifications</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-random fa-3x text-dark mb-3"></i>
                    <h5 class="card-title">Auto-Assign Sections</h5>
                    <p class="card-text">Automatically distribute students into sections based on scores.</p>
                    <a href="auto_assign_sections.php" class="btn btn-dark">Go to Auto-Assign</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-user-clock fa-3x text-primary-emphasis mb-3"></i>
                    <h5 class="card-title">Attendance Reports</h5>
                    <p class="card-text">View and filter attendance reports by grade or section.</p>
                    <a href="../admin/view_attendance_reports.php" class="btn btn-primary-emphasis">View Reports</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-chart-bar fa-3x text-success mb-3"></i>
                    <h5 class="card-title">View Reports</h5>
                    <p class="card-text">Generate and view reports on student and school data.</p>
                    <a href="../admin/report_section_averages.php" class="btn btn-success">Go to Reports</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-calendar-alt fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Generate Timetable</h5>
                    <p class="card-text">Automatically generate the master class schedule.</p>
                    <a href="../admin/auto_assign_timetable.php" class="btn btn-warning">Go to Timetable Generation</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-user-tie fa-3x text-info-emphasis mb-3"></i>
                    <h5 class="card-title">Teacher Assignments</h5>
                    <p class="card-text">Assign teachers to subjects and sections.</p>
                    <a href="../admin/manage_teacher_assignments.php" class="btn btn-info-emphasis">Go to Assignments</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-search fa-3x text-primary-emphasis mb-3"></i>
                    <h5 class="card-title">View Subject Assignments</h5>
                    <p class="card-text">See which grades a specific subject is assigned to.</p>
                    <a href="../admin/view_subject_assignments.php" class="btn btn-primary-emphasis">View Assignments</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-user-check fa-3x text-primary-emphasis mb-3"></i>
                    <h5 class="card-title">Assign Homeroom Teachers</h5>
                    <p class="card-text">Assign a homeroom teacher to each section.</p>
                    <a href="../admin/assign_homeroom_teacher.php" class="btn btn-primary-emphasis">Go to Assignments</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-brain fa-3x text-info-emphasis mb-3"></i>
                    <h5 class="card-title">Performance Predictions</h5>
                    <p class="card-text">View AI-driven predictions of student performance and risk.</p>
                    <a href="../admin/manage_performance_predictions.php" class="btn btn-info-emphasis">View Predictions</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-table fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">View Timetables</h5>
                    <p class="card-text">View the generated class schedule for any section.</p>
                    <a href="../admin/view_section_timetable.php" class="btn btn-primary">Go to Timetable Viewer</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-gavel fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Manage Discipline</h5>
                    <p class="card-text">Log and manage student disciplinary incidents.</p>
                    <a href="manage_discipline.php" class="btn btn-danger">Go to Discipline</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-book-open fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Manage Library</h5>
                    <p class="card-text">Add, edit, and manage the textbook library inventory.</p>
                    <a href="../admin/manage_library.php" class="btn btn-success">Go to Library</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('../includes/footer.php'); ?>