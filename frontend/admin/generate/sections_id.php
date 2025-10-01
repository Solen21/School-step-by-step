<?php
session_start();
require_once '../../../backend/config/db.php';

// Security check for authorized roles
$allowed_roles = ['admin', 'super_director', 'homeroom_teacher'];
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], $allowed_roles)) {
    header("location: ../dashboard/index.php");
    exit;
}

$section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : 0;

if ($section_id === 0) {
    $_SESSION['error_message'] = "No section specified.";
    header("location: ../management/sections.php");
    exit;
}

// --- Include the backend data provider to fetch all necessary data ---
require_once __DIR__ . '/../../../backend/admin/generate/section_id.php';

$page_title = "Print Section ID Cards";
include_once('../../includes/header.php');
?>

<div class="container-fluid mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="mb-0">Print ID Cards for Section</h2>
            <p class="lead">Section: <strong><?php echo htmlspecialchars($section_details['section_name'] . ' - Grade ' . $section_details['grade_level'] . ' (' . $section_details['stream'] . ')'); ?></strong></p>
        </div>
        <div>
            <a href="../management/sections.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Sections</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print All Cards</button>
        </div>
    </div>

    <?php if (empty($students)): ?>
        <div class="alert alert-info no-print">No students found in this section to generate ID cards.</div>
    <?php else: ?>
        <div class="id-card-wrapper">
            <?php foreach ($students as $student): ?>
                <?php
                    // --- Generate Expiry Date ---
                    $current_month = date('n');
                    $current_year = date('Y');
                    $expiry_year = ($current_month >= 9) ? $current_year + 1 : $current_year;
                    $expiry_date = 'July 15, ' . $expiry_year;
                ?>
                <?php
                    // --- Generate Profile URL for QR Code ---
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST']; // Get the host name
                    $profile_url = "{$protocol}://{$host}/African-chool-management-system/frontend/public/view_student_profile.php?id={$student['id_number']}";
                ?>
                <div class="id-card-container">
                    <!-- Front of the Card -->
                    <div class="id-card id-card-front">
                        <div class="header">
                            <img src="/African-chool-management-system/assets/images/school_logo.png" alt="Logo" class="logo">
                            <h5 class="m-0">STUDENT IDENTIFICATION</h5>
                        </div>
                        <div class="body">
                            <img src="<?php echo !empty($student['photo_path']) ? '/African-chool-management-system' . htmlspecialchars($student['photo_path']) : '/African-chool-management-system/assets/images/default_avatar.png'; ?>" alt="Student Photo" class="photo">
                            <div class="info">
                                <p class="name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                                <p><strong>ID:</strong> <?php echo htmlspecialchars($student['id_number']); ?></p>
                                <p><strong>Grade:</strong> <?php echo htmlspecialchars($student['grade_level'] . ' (' . $student['stream'] . ')'); ?></p>
                                <p><strong>Section:</strong> <?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></p>
                                <p><strong>Expires:</strong> <?php echo $expiry_date; ?></p>
                            </div>
                        </div>
                        <div class="footer">
                            African School Management System
                        </div>
                    </div>

                    <!-- Back of the Card -->
                    <div class="id-card id-card-back">
                        <div class="body">
                            <div class="info">
                                <p><strong>Guardian:</strong><br><?php echo htmlspecialchars($student['guardian_name'] ?? 'N/A'); ?></p>
                                <p><strong>Guardian Phone:</strong><br><?php echo htmlspecialchars($student['guardian_phone'] ?? 'N/A'); ?></p>
                                <hr style="margin: 8px 0;">
                                <p>If found, please return to:<br><strong>African School Management System</strong><br>Debre Markos, Ethiopia<br>Phone: +251 912 345 678</p>
                            </div>
                            <img src="/African-chool-management-system/helpers/generate_qr.php?data=<?php echo urlencode($profile_url); ?>" alt="QR Code" class="qr-code ms-auto">
                        </div>
                        <div class="footer"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once('../../includes/footer.php'); ?>