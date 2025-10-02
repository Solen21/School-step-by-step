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

// Fetch sections for the dropdown
$sql_sections = "SELECT s.section_id, s.section_name, g.grade_level, g.stream 
                 FROM sections s 
                 JOIN grades g ON s.grade_id = g.grade_id 
                 ORDER BY g.grade_level, s.section_name";
$result_sections = mysqli_query($conn, $sql_sections);
$sections = mysqli_fetch_all($result_sections, MYSQLI_ASSOC);

// Fetch guardians for the search/select dropdown
$sql_guardians = "SELECT 
                    g.guardian_id, g.full_name, g.phone, u.id_number 
                  FROM guardians g
                  LEFT JOIN users u ON g.user_id = u.user_id
                  ORDER BY g.full_name";
$result_guardians = mysqli_query($conn, $sql_guardians);
$guardians = mysqli_fetch_all($result_guardians, MYSQLI_ASSOC);

$page_title = "Add New Student";
include_once('../../includes/header.php');
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Add New Student</h2>
        <a href="students.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Students List</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="../../../backend/admin/handle/students.php" method="POST" enctype="multipart/form-data" class="needs-validation" id="addStudentForm" novalidate>
                <input type="hidden" name="action" value="add_student">

                <h5 class="mb-3">Personal Information</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="col-md-4">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middle_name" name="middle_name" required>
                    </div>
                    <div class="col-md-4">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="col-md-4">
                        <label for="dOb" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="dOb" name="dOb" required>
                    </div>
                    <div class="col-md-4">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="" selected disabled>Choose...</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                     <div class="col-md-4">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="col-md-4">
                        <label for="blood_type" class="form-label">Blood Type (Optional)</label>
                        <input type="text" class="form-control" id="blood_type" name="blood_type">
                    </div>
                     <div class="col-md-4">
                        <label for="photo_path" class="form-label">Student Photo</label>
                        <input type="file" class="form-control" id="photo_path" name="photo_path" accept="image/*">
                    </div>
                    <div class="col-md-4">
                        <label for="document_path" class="form-label">Supporting Document (Optional)</label>
                        <input type="file" class="form-control" id="document_path" name="document_path" accept=".pdf,.doc,.docx,.jpg,.png">
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Address Information</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" class="form-control" id="nationality" name="nationality" value="Ethiopian" required>
                    </div>
                    <div class="col-md-3">
                        <label for="region" class="form-label">Region</label>
                        <input type="text" class="form-control" id="region" name="region" value="Amhara" required>
                    </div>
                    <div class="col-md-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" value="Debre Markos" required>
                    </div>
                    <div class="col-md-3">
                        <label for="wereda" class="form-label">Wereda</label>
                        <input type="text" class="form-control" id="wereda" name="wereda" required>
                    </div>
                    <div class="col-md-3">
                        <label for="kebele" class="form-label">Kebele</label>
                        <input type="text" class="form-control" id="kebele" name="kebele" required>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Academic Information</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="section_id" class="form-label">Assign to Section</label>
                        <select class="form-select" id="section_id" name="section_id" required>
                            <option value="" selected disabled>Choose a section...</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo $section['section_id']; ?>">
                                    <?php echo htmlspecialchars('Grade ' . $section['grade_level'] . ' ' . $section['stream'] . ' - ' . $section['section_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="last_school" class="form-label">Last School Attended</label>
                        <input type="text" class="form-control" id="last_school" name="last_school" required>
                    </div>
                    <div class="col-md-2">
                        <label for="last_grade" class="form-label">Last Grade Completed</label>
                        <input type="text" class="form-control" id="last_grade" name="last_grade" required>
                    </div>
                    <div class="col-md-2">
                        <label for="last_score" class="form-label">Last Score/Average</label>
                        <input type="number" step="0.01" class="form-control" id="last_score" name="last_score" required>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Guardian Information</h5>

                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="guardian_option" id="select_existing_guardian" value="existing" checked>
                        <label class="form-check-label" for="select_existing_guardian">Select Existing Guardian</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="guardian_option" id="register_new_guardian" value="new">
                        <label class="form-check-label" for="register_new_guardian">Register New Guardian</label>
                    </div>
                </div>

                <!-- Section for selecting an existing guardian -->
                <div id="existing_guardian_section" class="row g-3">
                    <div class="col-md-6">
                        <label for="guardian_id" class="form-label">Search and Select Guardian</label>
                        <select class="form-select" id="guardian_id" name="guardian_id">
                            <option value="" selected>Search by name, ID, or phone...</option>
                            <?php foreach ($guardians as $guardian): ?>
                                <option value="<?php echo $guardian['guardian_id']; ?>" data-search="<?php echo htmlspecialchars(strtolower($guardian['full_name'] . ' ' . $guardian['id_number'] . ' ' . $guardian['phone'])); ?>">
                                    <?php echo htmlspecialchars($guardian['full_name'] . ' (' . $guardian['id_number'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Section for registering a new guardian -->
                <div id="new_guardian_section" class="row g-3 d-none">
                    <div class="col-md-4">
                        <label for="guardian_full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="guardian_full_name" name="guardian_full_name">
                    </div>
                    <div class="col-md-4">
                        <label for="guardian_phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="guardian_phone" name="guardian_phone">
                    </div>
                    <div class="col-md-4">
                        <label for="guardian_nationality" class="form-label">Nationality</label>
                        <input type="text" class="form-control" id="guardian_nationality" name="guardian_nationality" value="Ethiopian">
                    </div>
                    <div class="col-md-3">
                        <label for="guardian_region" class="form-label">Region</label>
                        <input type="text" class="form-control" id="guardian_region" name="guardian_region" value="Amhara">
                    </div>
                    <div class="col-md-3">
                        <label for="guardian_city" class="form-label">City</label>
                        <input type="text" class="form-control" id="guardian_city" name="guardian_city" value="Debre Markos">
                    </div>
                    <div class="col-md-3">
                        <label for="guardian_wereda" class="form-label">Wereda</label>
                        <input type="text" class="form-control" id="guardian_wereda" name="guardian_wereda">
                    </div>
                    <div class="col-md-3">
                        <label for="guardian_kebele" class="form-label">Kebele</label>
                        <input type="text" class="form-control" id="guardian_kebele" name="guardian_kebele">
                    </div>
                </div>

                <!-- Relation field, common to both options -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="guardian_relation" class="form-label">Relation to Student</label>
                        <input type="text" class="form-control" id="guardian_relation" name="guardian_relation" placeholder="e.g., Father, Mother, Aunt" required>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Register Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const guardianOptionRadios = document.querySelectorAll('input[name="guardian_option"]');
    const existingGuardianSection = document.getElementById('existing_guardian_section');
    const newGuardianSection = document.getElementById('new_guardian_section');
    
    const guardianIdSelect = document.getElementById('guardian_id');
    const newGuardianFields = newGuardianSection.querySelectorAll('input');

    function toggleGuardianSections() {
        if (document.getElementById('select_existing_guardian').checked) {
            existingGuardianSection.classList.remove('d-none');
            newGuardianSection.classList.add('d-none');
            // Make new guardian fields not required
            newGuardianFields.forEach(input => input.required = false);
            // Make existing guardian select required
            guardianIdSelect.required = true;
        } else {
            existingGuardianSection.classList.add('d-none');
            newGuardianSection.classList.remove('d-none');
            // Make new guardian fields required
            document.getElementById('guardian_full_name').required = true;
            document.getElementById('guardian_phone').required = true;
            document.getElementById('guardian_wereda').required = true;
            document.getElementById('guardian_kebele').required = true;
            // Make existing guardian select not required
            guardianIdSelect.required = false;
        }
    }

    guardianOptionRadios.forEach(radio => {
        radio.addEventListener('change', toggleGuardianSections);
    });

    // Initial setup
    toggleGuardianSections();
});
</script>

<?php include_once('../../includes/footer.php'); ?>