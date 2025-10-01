<?php
// File: data/migrations/001_initial_schema.php
// This script creates the database schema for the Ethio School system.
// Run this once during initial setup: php data/migrations/001_initial_schema.php
// Ensure backend/config/db.php exists for production connections, but this script handles initial DB creation.

// Include Composer's autoloader to load packages like Dotenv
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables if .env exists (optional for setup)
$dotenv = null;
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

// Database connection details (override with .env if available)
$servername = $_ENV['DB_HOST'] ?? "localhost";
$username = $_ENV['DB_USER'] ?? "root";
$password = $_ENV['DB_PASS'] ?? "";
$dbname = $_ENV['DB_NAME'] ?? "ethio_school"; // Using underscores is safer for database names

// Create connection without selecting a DB, to allow DB creation
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/*
-- WARNING: The following line will delete your entire database.
-- It is commented out for safety. Uncomment it only if you want to completely reset your database.
-- $sql_drop_db = "DROP DATABASE IF EXISTS `$dbname`";
-- mysqli_query($conn, $sql_drop_db);
*/

// Create database if it doesn't exist
$sql_create_db = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!mysqli_query($conn, $sql_create_db)) {
    die("Error creating database: " . mysqli_error($conn));
}

// Select the database
mysqli_select_db($conn, $dbname);

// ================= Roles =================
$sql_roles = "CREATE TABLE IF NOT EXISTS roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
)";

$sql_insert_roles = "INSERT IGNORE INTO roles (role_name) VALUES
('super_director'),
('director'),
('admin'),
('homeroom_teacher'),
('teacher'),
('class_representative'),
('student'),
('guardian')";

// ================= USERS =================
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    user_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(50) NULL,
    middle_name VARCHAR(50) NULL,
    last_name VARCHAR(50) NULL,
    full_name VARCHAR(152) GENERATED ALWAYS AS (TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, '')))) STORED,
    password VARCHAR(255) NOT NULL,
    role INT,
    email VARCHAR(255) NULL UNIQUE,
    phone VARCHAR(20),
    section_id INT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (role) REFERENCES roles(role_id)
)";

// ================= STUDENTS =================
$sql_students = "CREATE TABLE IF NOT EXISTS students (
    student_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL UNIQUE,
    id_number VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    dOb DATE NOT NULL,
    age INT(2) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    nationality VARCHAR(50) NOT NULL DEFAULT 'Ethiopian',
    region VARCHAR(50) NOT NULL DEFAULT 'Amhara',
    city VARCHAR(50) NOT NULL DEFAULT 'Debre Markos',
    wereda VARCHAR(100) NOT NULL,
    kebele VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    blood_type VARCHAR(5) NULL DEFAULT NULL,
    grade_level INT(2) NOT NULL,
    stream VARCHAR(50) NOT NULL,
    section_id INT NULL,
    last_school VARCHAR(100) NOT NULL,
    last_score FLOAT NOT NULL,
    last_grade VARCHAR(10) NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    photo_path VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE SET NULL
)";

// ================= DISCIPLINE REPORTS =================
$sql_discipline_reports = "CREATE TABLE IF NOT EXISTS `discipline_reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `incident_date` date NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `action_taken` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`report_id`),
  KEY `student_id` (`student_id`),
  KEY `reported_by` (`reported_by`),
  CONSTRAINT `discipline_reports_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `discipline_reports_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= GUARDIANS =================
$sql_guardians = "CREATE TABLE IF NOT EXISTS guardians (
    guardian_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    nationality VARCHAR(50) NOT NULL DEFAULT 'Ethiopian',
    region VARCHAR(50) NOT NULL DEFAULT 'Amhara',
    city VARCHAR(50) NOT NULL DEFAULT 'Debre Markos',
    wereda VARCHAR(100) NOT NULL,
    kebele VARCHAR(100) NOT NULL,
    phone VARCHAR(20)NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
)";

// ================= STUDENT GUARDIAN MAP =================
$sql_student_guardian_map = "CREATE TABLE IF NOT EXISTS student_guardian_map (
    map_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    guardian_id INT(11) NOT NULL,
    relation VARCHAR(50) NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (guardian_id) REFERENCES guardians(guardian_id) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_guardian` (`student_id`, `guardian_id`)
)";

// ================= TEACHERS =================
$sql_teachers = "CREATE TABLE IF NOT EXISTS teachers (
    teacher_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    nationality VARCHAR(50) NOT NULL DEFAULT 'Ethiopian',
    region VARCHAR(50) NOT NULL DEFAULT 'Amhara',
    city VARCHAR(50) NOT NULL DEFAULT 'Debre Markos',
    wereda VARCHAR(100) NOT NULL,
    kebele VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    grade_level_specialization VARCHAR(255) NOT NULL,
    subject_id INT NOT NULL,
    hire_date DATE NOT NULL,
    photo_path VARCHAR(255) DEFAULT NULL,
    document_path VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
)";

// ================= CLASSROOMS =================
$sql_classrooms = "CREATE TABLE IF NOT EXISTS classrooms (
    classroom_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    capacity INT NOT NULL
)";

// ================= GRADES ========================
$sql_grades = "CREATE TABLE IF NOT EXISTS grades (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    grade_level INT NOT NULL,
    stream VARCHAR(50)
)";

// ================= SECTIONS =====================
$sql_sections = "CREATE TABLE IF NOT EXISTS sections (
    section_id INT AUTO_INCREMENT PRIMARY KEY,
    grade_id INT NOT NULL,
    section_name VARCHAR(10),
    homeroom_teacher_id INT NULL,
    capacity INT,
    FOREIGN KEY (grade_id) REFERENCES grades(grade_id) ON DELETE CASCADE,
    FOREIGN KEY (homeroom_teacher_id) REFERENCES teachers(teacher_id) ON DELETE SET NULL
)";

// ================= SUBJECTS ======================
$sql_subjects = "CREATE TABLE IF NOT EXISTS subjects (
    subject_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NULL UNIQUE
)";

// ================= GRADE SUBJECTS (many-to-many mapping) ======================
$sql_grade_subjects = "CREATE TABLE IF NOT EXISTS grade_subjects (
    grade_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    grade_id INT NOT NULL,
    subject_id INT NOT NULL,
    periods_per_week INT NOT NULL DEFAULT 1,
    FOREIGN KEY (grade_id) REFERENCES grades(grade_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    UNIQUE KEY (grade_id, subject_id)
)";

// ================= SUBJECT ASSIGNMENTS =================
$sql_subject_assignments = "CREATE TABLE IF NOT EXISTS subject_assignments (
    assignment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    subject_id INT(11) NOT NULL,
    section_id INT(11) NOT NULL,
    teacher_id INT(11) NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (section_id) REFERENCES sections(section_id)
)";

// ================= SCHEDULE PERIODS (Template) =================
$sql_schedule_periods = "CREATE TABLE IF NOT EXISTS schedule_periods (
    period_id INT AUTO_INCREMENT PRIMARY KEY,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    period_number INT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,    
    shift ENUM('Morning', 'Afternoon') NOT NULL,
    is_break BOOLEAN DEFAULT 0
)";

// ================= CLASS SCHEDULE (The actual timetable) =================
$sql_class_schedule = "CREATE TABLE IF NOT EXISTS class_schedule (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    section_id INT(11) NOT NULL,
    period_id INT(11) NOT NULL,
    subject_assignment_id INT(11) NOT NULL,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES schedule_periods(period_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_assignment_id) REFERENCES subject_assignments(assignment_id) ON DELETE CASCADE
)";

// ================= ATTENDANCE =================
$sql_attendance = "CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    section_id INT(11) NOT NULL,    
    date DATE,
    status ENUM('Present','Absent','Late','Excused') NOT NULL DEFAULT 'Present',
    comments TEXT NULL,
    locked BOOLEAN DEFAULT 0,
    marked_by INT(11) NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE
)";

// ================= STUDENT GRADES (Marks) =================
$sql_student_grades = "CREATE TABLE IF NOT EXISTS student_grades (
    student_grade_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    subject_id INT(11) NOT NULL,
    submitted_teacher_id INT(11) NOT NULL,
    test DECIMAL(5,2) NOT NULL,
    assignment DECIMAL(5,2) NOT NULL,
    activity DECIMAL(5,2) NOT NULL,
    exercise DECIMAL(5,2) NOT NULL,
    midterm DECIMAL(5,2) NOT NULL,
    final DECIMAL(5,2) NOT NULL,
    total DECIMAL(5,2) NOT NULL,
    updated_by VARCHAR(100) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_subject_grade` (`student_id`, `subject_id`)
)";

// ================= MARKS (New Grade System) =================
$sql_marks = "CREATE TABLE IF NOT EXISTS marks (
    mark_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    assessment_type VARCHAR(100) NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    total_marks DECIMAL(5,2) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    recorded_by INT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE SET NULL,
    UNIQUE KEY `unique_mark` (student_id, subject_id, assessment_type, academic_year)
)";

// ================= MESSAGES =================
$sql_messages = "CREATE TABLE IF NOT EXISTS `messages` (
    message_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) NOT NULL,
    recipient_id INT(11) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sender_deleted TINYINT(1) NOT NULL DEFAULT 0,
    receiver_deleted TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

// ================= MESSAGE ATTACHMENTS =================
$sql_message_attachments = "CREATE TABLE IF NOT EXISTS message_attachments (
    attachment_id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT(11) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(message_id) ON DELETE CASCADE
)";

// ================= REPORTS =================
$sql_reports = "CREATE TABLE IF NOT EXISTS reports (
    report_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    generated_by_user_id INT(11) NOT NULL,
    classroom_id INT(11) NULL,
    type ENUM('Attendance','Behavior','Academic') NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by_user_id) REFERENCES users(user_id),
    FOREIGN KEY (classroom_id) REFERENCES classrooms(classroom_id) ON DELETE SET NULL
)";

// ================= NEWS =================
$sql_news = "CREATE TABLE IF NOT EXISTS news (
    news_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    link VARCHAR(255),
    created_by INT,
    status ENUM('published', 'draft') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
)";

// ================= GALLERY =================
$sql_gallery = "CREATE TABLE IF NOT EXISTS gallery (
    gallery_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT,
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id) ON DELETE SET NULL
)";

// ================= ABSENCE =================
$sql_absent = "CREATE TABLE IF NOT EXISTS `absence_excuses` (
  `excuse_id` int(11) NOT NULL AUTO_INCREMENT,
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `explanation` text NOT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`excuse_id`),
  UNIQUE KEY `unique_excuse` (`attendance_id`),
  KEY `student_id` (`student_id`),
  KEY `reviewed_by` (`reviewed_by`),
  FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`attendance_id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= GRADE DEADLINES =================
$sql_grade_deadlines = "CREATE TABLE IF NOT EXISTS grade_deadlines (
    deadline_id INT AUTO_INCREMENT PRIMARY KEY,
    section_id INT(11) NOT NULL,
    subject_id INT(11) NOT NULL,
    deadline_date DATE NOT NULL,
    set_by INT(11) NULL,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (set_by) REFERENCES users(user_id) ON DELETE SET NULL,
    UNIQUE KEY `unique_deadline` (`section_id`, `subject_id`)
)";

// ================= NOTIFICATIONS =================
$sql_notifications = "CREATE TABLE IF NOT EXISTS notifications (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target_role` varchar(50) NOT NULL DEFAULT 'all',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= GRADE LOGS =================
$sql_grade_logs = "CREATE TABLE IF NOT EXISTS grade_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    grade_id INT(11) NOT NULL,
    student_id INT(11) NOT NULL,
    subject_id INT(11) NOT NULL,
    field_changed VARCHAR(50) NOT NULL,
    old_value VARCHAR(255),
    new_value VARCHAR(255),
    changed_by_user_id INT(11),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,    
    FOREIGN KEY (changed_by_user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (grade_id) REFERENCES grades(grade_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= SYSTEM LOGS =================
$sql_system_logs = "CREATE TABLE IF NOT EXISTS system_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NULL,
    id_number_attempt VARCHAR(255),
    action VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= ANNOUNCEMENTS =================
$sql_announcements = "CREATE TABLE IF NOT EXISTS announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    section_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= EXAM ROOMS =================
$sql_exam_rooms = "CREATE TABLE IF NOT EXISTS exam_rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    capacity INT NOT NULL
)";

// ================= EXAMS =================
$sql_exams = "CREATE TABLE IF NOT EXISTS exams (
    exam_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    exam_date DATE NOT NULL,
    semester ENUM('1', '2') NOT NULL,
    type ENUM('Midterm', 'Final') NOT NULL
)";

// ================= EXAM ASSIGNMENTS =================
$sql_exam_assignments = "CREATE TABLE IF NOT EXISTS exam_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    room_id INT NOT NULL,
    seat_number VARCHAR(10),
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES exam_rooms(room_id) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_exam` (`exam_id`, `student_id`)
)";

// ================= ACADEMIC CALENDAR =================
$sql_academic_calendar = "CREATE TABLE IF NOT EXISTS academic_calendar (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    type ENUM('Holiday', 'Exam Period', 'Term Start', 'Term End', 'Event') NOT NULL,
    description TEXT,
    created_by INT(11) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
)";

// ================= LEAVE REQUESTS =================
$sql_leave_requests = "CREATE TABLE IF NOT EXISTS leave_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT(11) NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leave_type ENUM('Sick Leave', 'Personal Leave', 'Vacation', 'Other') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NOT NULL,
    attachment_path VARCHAR(255) NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    reviewed_by INT(11) NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    reviewer_comments TEXT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= VISITOR PASSES =================
$sql_visitor_passes = "CREATE TABLE IF NOT EXISTS visitor_passes (
    pass_id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_name VARCHAR(255) NOT NULL,
    reason_for_visit VARCHAR(255) NOT NULL,
    person_to_visit VARCHAR(255) NOT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    issued_by_user_id INT(11) NULL,
    FOREIGN KEY (issued_by_user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= STUDENT PROFILE LOGS =================
$sql_student_profile_logs = "CREATE TABLE IF NOT EXISTS student_profile_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    changed_by_user_id INT(11) NULL,
    field_changed VARCHAR(50) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by_user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= PASSWORD RESETS =================
$sql_password_resets = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    INDEX (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= ACTIVITY LOGS =================
$sql_activity_logs = "CREATE TABLE IF NOT EXISTS `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `id_number` varchar(255) NOT NULL,
  `action_type` varchar(255) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `target_name` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_discipline_record = "CREATE TABLE IF NOT EXISTS discipline_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    reported_by INT(11) NULL,
    incident_date DATE NOT NULL,
    incident_type VARCHAR(255) NOT NULL,
    description TEXT,
    action_taken VARCHAR(255),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_news_categories = "CREATE TABLE IF NOT EXISTS `news_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$sql_news_article_categories = "CREATE TABLE IF NOT EXISTS `news_article_categories` (
  `news_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`news_id`,`category_id`),
  KEY `category_id` (`category_id`),
  FOREIGN KEY (`news_id`) REFERENCES `news` (`news_id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// ================= GAMIFICATION =================
$sql_student_gamification = "CREATE TABLE IF NOT EXISTS student_gamification (
    student_id INT(11) NOT NULL,
    total_points INT(11) NOT NULL DEFAULT 0,
    badges TEXT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_gamification_log = "CREATE TABLE IF NOT EXISTS gamification_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    points_earned INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= PERFORMANCE PREDICTIONS =================
$sql_performance_predictions = "CREATE TABLE IF NOT EXISTS performance_predictions (
    prediction_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    subject_id INT(11) NOT NULL,
    predicted_grade DECIMAL(5,2) NOT NULL,
    risk_level ENUM('Low', 'Medium', 'High') NOT NULL,
    risk_factors TEXT,
    prediction_date DATE NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    UNIQUE KEY `unique_prediction_per_day` (`student_id`, `subject_id`, `prediction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= EVENTS =================
$sql_events = "CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NULL,
    location VARCHAR(255),
    category VARCHAR(50) NOT NULL DEFAULT 'General',
    created_by INT(11) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    image_path VARCHAR(255) NULL,
    link VARCHAR(255) NULL,
    status ENUM('published', 'draft') NOT NULL DEFAULT 'draft',
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= GUARDIAN ALERTS LOG =================
$sql_guardian_alerts_log = "CREATE TABLE IF NOT EXISTS guardian_alerts_log (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    alert_type VARCHAR(50) NOT NULL,
    details TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `student_alert_type_idx` (`student_id`, `alert_type`),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= TEACHER EVALUATIONS =================
$sql_teacher_evaluations = "CREATE TABLE IF NOT EXISTS teacher_evaluations (
    evaluation_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    teacher_id INT(11) NOT NULL,
    subject_id INT(11) NOT NULL,
    section_id INT(11) NOT NULL,
    rating INT(1) NOT NULL COMMENT 'Rating from 1 to 5',
    comments TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    UNIQUE KEY `unique_evaluation` (`student_id`, `teacher_id`, `subject_id`, `section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= CLASS ASSIGNMENT HISTORY =================
$sql_class_assignment_history = "CREATE TABLE IF NOT EXISTS class_assignment_history (
    history_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    old_section_id INT NULL,
    new_section_id INT NOT NULL,
    changed_by_user_id INT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (old_section_id) REFERENCES sections(section_id),
    FOREIGN KEY (new_section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by_user_id) REFERENCES users(user_id) ON DELETE SET NULL
)";

$sql_system_config = "CREATE TABLE IF NOT EXISTS `system_config` (
  `config_key` varchar(255) NOT NULL,
  `config_value` text DEFAULT NULL,
  PRIMARY KEY (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= STRATEGIC PLANNING =================
$sql_strategic_plans = "CREATE TABLE IF NOT EXISTS `strategic_plans` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `start_year` year(4) NOT NULL,
  `end_year` year(4) NOT NULL,
  `vision_statement` text DEFAULT NULL,
  `mission_statement` text DEFAULT NULL,
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_strategic_goals = "CREATE TABLE IF NOT EXISTS `strategic_goals` (
  `goal_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`goal_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `strategic_goals_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `strategic_plans` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_strategic_initiatives = "CREATE TABLE IF NOT EXISTS `strategic_initiatives` (
  `initiative_id` int(11) NOT NULL AUTO_INCREMENT,
  `goal_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` enum('not_started','in_progress','completed','on_hold') NOT NULL DEFAULT 'not_started',
  PRIMARY KEY (`initiative_id`),
  KEY `goal_id` (`goal_id`),
  CONSTRAINT `strategic_initiatives_ibfk_1` FOREIGN KEY (`goal_id`) REFERENCES `strategic_goals` (`goal_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ================= FEE TYPES =================
$sql_fee_types = "CREATE TABLE IF NOT EXISTS `fee_types` (
  `fee_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `default_amount` decimal(10,2) NOT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`fee_type_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// ================= INVOICES =================
$sql_invoices = "CREATE TABLE IF NOT EXISTS `invoices` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_date` date NOT NULL,
  `status` enum('draft','unpaid','partially_paid','paid','void') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// ================= INVOICE ITEMS =================
$sql_invoice_items = "CREATE TABLE IF NOT EXISTS `invoice_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `fee_type_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `fee_type_id` (`fee_type_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`fee_type_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// ================= PAYMENTS =================
$sql_payments = "CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL COMMENT 'e.g., Cash, Bank Transfer, Online',
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('completed','pending','failed') NOT NULL DEFAULT 'completed',
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `student_id` (`student_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// ================= EXPENSES =================
$sql_expenses = "CREATE TABLE IF NOT EXISTS `expenses` (
  `expense_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL COMMENT 'e.g., Salaries, Utilities, Supplies',
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `receipt_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`expense_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// ================= EXAM PAPERS =================
$sql_exam_papers = "CREATE TABLE IF NOT EXISTS `exam_papers` (
  `exam_paper_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `instructions` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`exam_paper_id`),
  KEY `subject_id` (`subject_id`),
  KEY `grade_id` (`grade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Array of all table creation queries (ordered to respect dependencies)
$queries = [
    $sql_roles,
    $sql_users,
    $sql_grades,
    $sql_exam_rooms,
    $sql_classrooms,
    $sql_subjects,
    $sql_fee_types, // Added for fees
    $sql_news_categories, // Depends on nothing
    $sql_grade_subjects,
    $sql_schedule_periods,
    $sql_gallery,
    $sql_teachers, // Depends on users, subjects
    $sql_sections, // Depends on grades, teachers
    $sql_students, // Depends on users, sections
    $sql_guardians, // Depends on users
    $sql_student_guardian_map, // Depends on students, guardians
    $sql_discipline_reports, // Depends on students, users
    $sql_student_profile_logs, // Depends on students, users
    $sql_subject_assignments, // Depends on subjects, sections, teachers
    $sql_attendance, // Depends on students, sections
    $sql_class_schedule, // Depends on sections, schedule_periods, subject_assignments
    $sql_student_grades, // Depends on students, subjects, teachers
    $sql_marks, // Depends on students, subjects, users
    $sql_messages, // Depends on users
    $sql_message_attachments, // Depends on messages
    $sql_reports, // Depends on users, classrooms
    $sql_news, // Depends on users
    $sql_news_article_categories, // Depends on news, news_categories
    $sql_absent, // Depends on attendance, students, users
    $sql_grade_deadlines, // Depends on sections, subjects, users
    $sql_notifications, // Depends on users
    $sql_grade_logs, // Depends on grades, students, subjects, users
    $sql_system_logs, // Depends on users
    $sql_announcements, // Depends on users, sections
    $sql_exams, // No deps
    $sql_exam_assignments, // Depends on exams, students, exam_rooms
    $sql_academic_calendar, // Depends on users
    $sql_leave_requests, // Depends on teachers, users
    $sql_visitor_passes, // Depends on users
    $sql_activity_logs, // Depends on users
    $sql_discipline_record, // Depends on students, users
    $sql_student_gamification, // Depends on students
    $sql_gamification_log, // Depends on students
    $sql_performance_predictions, // Depends on students, subjects
    $sql_events, // Depends on users
    $sql_guardian_alerts_log, // Depends on students
    $sql_teacher_evaluations, // Depends on students, teachers, subjects, sections
    $sql_class_assignment_history, // Depends on students, sections, users
    $sql_system_config, // No deps
    $sql_strategic_plans, // No deps
    $sql_strategic_goals, // Depends on strategic_plans
    $sql_strategic_initiatives, // Depends on strategic_goals
    $sql_invoices, // Depends on students
    $sql_invoice_items, // Depends on invoices, fee_types
    $sql_payments, // Depends on invoices, students, users
    $sql_expenses // Depends on users
    
];

// Execute each query
foreach ($queries as $query) {
    if (!mysqli_query($conn, $query)) {
        die("Error creating table: " . mysqli_error($conn) . "\nProblematic Query: " . $query);
    }
}

// ================= CREATE DEFAULT ADMIN USER =================
// This will create an admin user if one doesn't already exist.
$admin_id_number = 'admin';
$admin_password = '4321';
$admin_role_name = 'admin';

// Now that tables are created, insert the default roles if the table is empty.
$check_roles_sql = "SELECT role_id FROM roles LIMIT 1";
$result_roles = mysqli_query($conn, $check_roles_sql);
if ($result_roles && mysqli_num_rows($result_roles) == 0) {
    // Only insert if the table is empty
    if (!mysqli_query($conn, $sql_insert_roles)) {
        die("Error inserting default roles: " . mysqli_error($conn));
    }
    echo "Default roles inserted successfully.\n";
}

// Check if admin user already exists to prevent errors on re-running the script
$sql_check_admin = "SELECT user_id FROM users WHERE id_number = ?";
$stmt_check = mysqli_prepare($conn, $sql_check_admin);
mysqli_stmt_bind_param($stmt_check, "s", $admin_id_number);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result) == 0) {
    // Get the role_id for 'admin'
    $sql_get_role = "SELECT role_id FROM roles WHERE role_name = ?";
    $stmt_get_role = mysqli_prepare($conn, $sql_get_role);
    mysqli_stmt_bind_param($stmt_get_role, "s", $admin_role_name);
    mysqli_stmt_execute($stmt_get_role);
    $role_result = mysqli_stmt_get_result($stmt_get_role);
    if ($admin_role_row = mysqli_fetch_assoc($role_result)) {
        $admin_role_id = $admin_role_row['role_id'];
        // Admin user does not exist, so create it
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $sql_insert_admin = "INSERT INTO users (id_number, password, role) VALUES (?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $sql_insert_admin);
        mysqli_stmt_bind_param($stmt_insert, "ssi", $admin_id_number, $hashed_password, $admin_role_id);
        mysqli_stmt_execute($stmt_insert);
    }
}

// ================= CREATE DEFAULT SUPER DIRECTOR USER =================
$super_director_id = 'super';
$super_director_pass = '4321';
$super_director_role = 'super_director';

$sql_check_super = "SELECT user_id FROM users WHERE id_number = ?";
$stmt_check_super = mysqli_prepare($conn, $sql_check_super);
mysqli_stmt_bind_param($stmt_check_super, "s", $super_director_id);
mysqli_stmt_execute($stmt_check_super);
$result_super = mysqli_stmt_get_result($stmt_check_super);

if (mysqli_num_rows($result_super) == 0) {
    $sql_get_role_id = "SELECT role_id FROM roles WHERE role_name = ?";
    $stmt_get_role_id = mysqli_prepare($conn, $sql_get_role_id);
    mysqli_stmt_bind_param($stmt_get_role_id, "s", $super_director_role);
    mysqli_stmt_execute($stmt_get_role_id);
    $role_res = mysqli_stmt_get_result($stmt_get_role_id);
    if ($role_row = mysqli_fetch_assoc($role_res)) {
        $super_role_id = $role_row['role_id'];
        $hashed_pass = password_hash($super_director_pass, PASSWORD_DEFAULT);
        $sql_insert_super = "INSERT INTO users (id_number, password, role) VALUES (?, ?, ?)";
        $stmt_insert_super = mysqli_prepare($conn, $sql_insert_super);
        mysqli_stmt_bind_param($stmt_insert_super, "ssi", $super_director_id, $hashed_pass, $super_role_id);
        mysqli_stmt_execute($stmt_insert_super);
    }
}

// ================= ROBUST SCHEMA PATCHING =================
// This block checks for common missing columns and adds them to prevent errors.
function column_exists($conn, $table, $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return mysqli_num_rows($result) > 0;
}

echo "Starting robust schema patching...\n";

// Migration: Rename 'created_by' to 'user_id' in the 'notifications' table if needed
if (column_exists($conn, 'notifications', 'created_by') && !column_exists($conn, 'notifications', 'user_id')) {
    // First, drop the old foreign key if it exists on 'created_by'
    $fk_query = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'notifications' AND COLUMN_NAME = 'created_by' AND REFERENCED_TABLE_NAME IS NOT NULL";
    $fk_result = mysqli_query($conn, $fk_query);
    if ($fk_row = mysqli_fetch_assoc($fk_result)) {
        $constraint_name = $fk_row['CONSTRAINT_NAME'];
        mysqli_query($conn, "ALTER TABLE `notifications` DROP FOREIGN KEY `$constraint_name`");
        echo "Dropped old foreign key `$constraint_name` on notifications.created_by.\n";
    }

    // Now, change the column name and add the new foreign key
    $alter_sql = "ALTER TABLE `notifications` 
                  CHANGE COLUMN `created_by` `user_id` INT(11) NOT NULL,
                  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE";
    
    if (mysqli_query($conn, $alter_sql)) {
        echo "Successfully migrated 'notifications' table: renamed 'created_by' to 'user_id' and updated foreign key.\n";
    } else {
        die("Error migrating 'notifications' table: " . mysqli_error($conn));
    }
}

// Additional patch for teachers table (from comment)
if (!column_exists($conn, 'teachers', 'grade_level_specialization')) {
    $alter_teachers = "ALTER TABLE `teachers` 
                       ADD `grade_level_specialization` ENUM('9','10','11','12') NOT NULL AFTER `document_path`,
                       ADD `department` VARCHAR(100) NOT NULL AFTER `grade_level_specialization`,
                       ADD `previous_service_period` VARCHAR(255) NULL AFTER `department`";
    if (mysqli_query($conn, $alter_teachers)) {
        echo "Added missing columns to teachers table.\n";
    } else {
        echo "Warning: Could not add columns to teachers: " . mysqli_error($conn) . "\n";
    }
}

echo "Database and all tables checked/created successfully!\n";
mysqli_close($conn);

// For production, update backend/config/db.php to include:
// <?php
// $servername = $_ENV['DB_HOST'] ?? 'localhost';
// ... (similar, but select DB directly)
// $conn = mysqli_connect($servername, $username, $password, $dbname);
// if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }
// ?>
?>