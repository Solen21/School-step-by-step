<?php
// Start session
session_start();

// Load environment variables (from .env file)
require_once __DIR__ . '/backend/config/db.php'; // DB connection

// Check if user is already logged in
if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'student':
            header("Location: frontend/pages/student_dashboard.php");
            exit;
        case 'teacher':
            header("Location: frontend/pages/teacher_dashboard.php");
            exit;
        case 'guardian':
            header("Location: frontend/pages/guardian_dashboard.php");
            exit;
        case 'homeroom_teacher':
            header("Location: frontend/pages/hrt_dashboard.php");
            exit;
        case 'director':
            header("Location: frontend/pages/director_dashboard.php");
            exit;
        case 'super_director':
            header("Location: frontend/pages/super_director_dashboard.php"); // Corrected path
            exit;
        case 'class_representative':
            header("Location: frontend/pages/classrep_dashboard.php");
            exit;
        case 'registrar':
            header("Location: frontend/pages/registrar_dashboard.php");
            exit;
        case 'property_manager':
            header("Location: frontend/pages/property_manager_dashboard.php");
            exit;
        default:
            header("Location: frontend/pages/login.php");
            exit;
    }
} else {
    // If not logged in, go to login page
    header("Location: frontend/pages/login.php");
    exit;
}
