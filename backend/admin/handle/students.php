<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/id_generator.php'; // For generating IDs


// Security check: ensure user is logged in and has the correct role
$allowed_roles = ['admin', 'super_director'];
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error_message'] = "You are not authorized to perform this action.";
    header("location: ../../../frontend/admin/management/students.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'add_student':
            handleAddStudent($conn);
            break;
        // Future cases for 'edit_student' would go here
        default:
            $_SESSION['error_message'] = "Invalid action specified.";
            header("location: ../../../frontend/admin/management/students.php");
            exit;
    }
}

function handleAddStudent($conn) {
    // --- 1. Sanitize and Validate Input ---
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $dOb = $_POST['dOb'];
    $gender = $_POST['gender'];
    $phone = trim($_POST['phone']);
    $blood_type = !empty($_POST['blood_type']) ? trim($_POST['blood_type']) : null;
    $nationality = trim($_POST['nationality']);
    $region = trim($_POST['region']);
    $city = trim($_POST['city']);
    $wereda = trim($_POST['wereda']);
    $kebele = trim($_POST['kebele']);
    $section_id = (int)$_POST['section_id'];
    $last_school = trim($_POST['last_school']);
    $last_grade = trim($_POST['last_grade']);
    $last_score = (float)$_POST['last_score'];
    $guardian_relation = trim($_POST['guardian_relation']);

    // Basic validation
    if (empty($first_name) || empty($middle_name) || empty($last_name) || empty($dOb) || empty($gender) || empty($nationality) || empty($region) || empty($city) || empty($wereda) || empty($kebele) || empty($phone) || empty($last_school) || empty($last_grade) || empty($last_score) || empty($guardian_relation)) {
        $_SESSION['error_message'] = "Please fill in all required student and guardian fields.";
        header("location: ../../../frontend/admin/management/add_student.php");
        exit;
    }

    // --- 2. Handle Photo Upload ---
    $photo_path = null;
    if (isset($_FILES['photo_path']) && $_FILES['photo_path']['error'] == 0) {
        $upload_dir = '/uploads/students/photos/';
        $full_upload_path = __DIR__ . '/../../../' . $upload_dir;

        if (!is_dir($full_upload_path)) {
            mkdir($full_upload_path, 0777, true);
        }

        $file_extension = pathinfo($_FILES['photo_path']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid('student_', true) . '.' . $file_extension;
        $target_file = $full_upload_path . $unique_filename;

        // Validate file type and size (e.g., max 2MB)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_extension), $allowed_types) && $_FILES['photo_path']['size'] <= 2000000) {
            if (move_uploaded_file($_FILES['photo_path']['tmp_name'], $target_file)) {
                $photo_path = $upload_dir . $unique_filename;
            }
        }
    }

    // --- Handle Document Upload ---
    $document_path = null;
    if (isset($_FILES['document_path']) && $_FILES['document_path']['error'] == 0) {
        $upload_dir = '/uploads/students/documents/';
        $full_upload_path = __DIR__ . '/../../../' . $upload_dir;

        if (!is_dir($full_upload_path)) {
            mkdir($full_upload_path, 0777, true);
        }

        $file_extension = pathinfo($_FILES['document_path']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid('doc_', true) . '.' . $file_extension;
        $target_file = $full_upload_path . $unique_filename;

        // Validate file (e.g., max 5MB)
        if ($_FILES['document_path']['size'] <= 5000000) {
            if (move_uploaded_file($_FILES['document_path']['tmp_name'], $target_file)) {
                $document_path = $upload_dir . $unique_filename;
            }
        }
    }

    // --- 3. Database Transaction ---
    $conn->begin_transaction();

    try {
        // --- Create User Account ---
        $student_id_number = generate_student_id($conn);
        $default_password = password_hash(strtolower($first_name) . '123', PASSWORD_DEFAULT);
        
        // Get role_id for 'student'
        $stmt_role = $conn->prepare("SELECT role_id FROM roles WHERE role_name = 'student'");
        $stmt_role->execute();
        $result_role = $stmt_role->get_result();
        $role = $result_role->fetch_assoc();
        $student_role_id = $role['role_id'];
        $stmt_role->close();

        if (!$student_role_id) {
            throw new Exception("Student role not found in the database.");
        }

        $stmt_user = $conn->prepare("INSERT INTO users (id_number, first_name, middle_name, last_name, password, role, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_user->bind_param("sssssis", $student_id_number, $first_name, $middle_name, $last_name, $default_password, $student_role_id, $phone);
        $stmt_user->execute();
        $user_id = $conn->insert_id;
        $stmt_user->close();

        if ($user_id == 0) {
            throw new Exception("Failed to create user account.");
        }

        // --- Create Student Record ---
        // Calculate age
        $birthDate = new DateTime($dOb);
        $today = new DateTime('today');
        $age = $birthDate->diff($today)->y;

        // Get grade_level and stream from section_id
        $stmt_grade = $conn->prepare("SELECT g.grade_level, g.stream FROM sections s JOIN grades g ON s.grade_id = g.grade_id WHERE s.section_id = ?");
        $stmt_grade->bind_param("i", $section_id);
        $stmt_grade->execute();
        $grade_result = $stmt_grade->get_result()->fetch_assoc();
        $grade_level = $grade_result['grade_level'];
        $stream = $grade_result['stream'];
        $stmt_grade->close();

        $sql_student = "INSERT INTO students (user_id, id_number, first_name, middle_name, last_name, dOb, age, gender, nationality, region, city, wereda, kebele, phone, blood_type, grade_level, stream, section_id, last_school, last_score, last_grade, photo_path, document_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_student = $conn->prepare($sql_student);
        $stmt_student->bind_param(
            "isssssissssssssisidssss",
            $user_id, $student_id_number, $first_name, $middle_name, $last_name, $dOb, $age, $gender,
            $nationality, $region, $city, $wereda, $kebele, $phone, $blood_type,
            $grade_level, $stream, $section_id, $last_school, $last_score, $last_grade, $photo_path, $document_path
        );
        $stmt_student->execute();
        $student_id = $conn->insert_id;
        $stmt_student->close();

        if ($student_id == 0) {
            throw new Exception("Failed to create student record.");
        }

        // --- Handle Guardian ---
        $guardian_id = 0;
        if ($_POST['guardian_option'] === 'existing') {
            $guardian_id = (int)$_POST['guardian_id'];
            if (empty($guardian_id)) {
                throw new Exception("An existing guardian was not selected.");
            }
        } elseif ($_POST['guardian_option'] === 'new') {
            // Create new guardian user and record
            $g_full_name = trim($_POST['guardian_full_name']);
            $g_phone = trim($_POST['guardian_phone']);
            // ... get other guardian fields

            if (empty($g_full_name) || empty($g_phone)) {
                throw new Exception("Guardian full name and phone are required for new registration.");
            }

            $guardian_id_number = generate_guardian_id($conn);
            $g_password = password_hash(explode(' ', $g_full_name)[0] . '123', PASSWORD_DEFAULT);
            
            $stmt_g_role = $conn->prepare("SELECT role_id FROM roles WHERE role_name = 'guardian'");
            $stmt_g_role->execute();
            $g_role_id = $stmt_g_role->get_result()->fetch_assoc()['role_id'];
            $stmt_g_role->close();

            $stmt_g_user = $conn->prepare("INSERT INTO users (id_number, full_name, password, role, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt_g_user->bind_param("sssis", $guardian_id_number, $g_full_name, $g_password, $g_role_id, $g_phone);
            $stmt_g_user->execute();
            $g_user_id = $conn->insert_id;
            $stmt_g_user->close();

            $stmt_guardian = $conn->prepare("INSERT INTO guardians (user_id, full_name, phone, nationality, region, city, wereda, kebele) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_guardian->bind_param("isssssss", $g_user_id, $g_full_name, $g_phone, $_POST['guardian_nationality'], $_POST['guardian_region'], $_POST['guardian_city'], $_POST['guardian_wereda'], $_POST['guardian_kebele']);
            $stmt_guardian->execute();
            $guardian_id = $conn->insert_id;
            $stmt_guardian->close();
        }

        // --- Link Student and Guardian ---
        $stmt_map = $conn->prepare("INSERT INTO student_guardian_map (student_id, guardian_id, relation) VALUES (?, ?, ?)");
        $stmt_map->bind_param("iis", $student_id, $guardian_id, $guardian_relation);
        $stmt_map->execute();
        $stmt_map->close();

        // If all queries were successful, commit the transaction
        $conn->commit();
        $_SESSION['success_message'] = "Student registered successfully! ID Number: " . $student_id_number;

    } catch (Exception $e) {
        // An error occurred, roll back the transaction
        $conn->rollback();
        // Also delete the uploaded file if it exists
        if ($photo_path && file_exists(__DIR__ . '/../../../' . $photo_path)) {
            unlink(__DIR__ . '/../../../' . $photo_path);
        }
        if ($document_path && file_exists(__DIR__ . '/../../../' . $document_path)) {
            unlink(__DIR__ . '/../../../' . $document_path);
        }
        $_SESSION['error_message'] = "Error during registration: " . $e->getMessage();
        header("location: ../../../frontend/admin/management/add_student.php");
        exit;
    }

    header("location: ../../../frontend/admin/management/students.php");
    exit;
}
?>