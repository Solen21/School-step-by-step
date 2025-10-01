<?php

if (!isset($conn) || !isset($section_id)) {
    die("Error: This script requires a database connection and a section ID.");
}

// --- Fetch Section Details ---
$stmt_section = $conn->prepare("SELECT s.section_name, g.grade_level, g.stream FROM sections s JOIN grades g ON s.grade_id = g.grade_id WHERE s.section_id = ?");
$stmt_section->bind_param("i", $section_id);
$stmt_section->execute();
$section_details = $stmt_section->get_result()->fetch_assoc();

if (!$section_details) {
    $_SESSION['error_message'] = "Section not found.";
    header("location: ../management/sections.php"); // Adjusted path
    exit;
}

// --- Fetch all students in this section with their primary guardian info ---
$sql_students = "SELECT 
                    s.student_id, s.id_number, s.first_name, s.middle_name, s.last_name, s.photo_path,
                    s.grade_level, s.stream,
                    sec.section_name,
                    (SELECT g.full_name FROM guardians g JOIN student_guardian_map sgm ON g.guardian_id = sgm.guardian_id WHERE sgm.student_id = s.student_id LIMIT 1) AS guardian_name,
                    (SELECT g.phone FROM guardians g JOIN student_guardian_map sgm ON g.guardian_id = sgm.guardian_id WHERE sgm.student_id = s.student_id LIMIT 1) AS guardian_phone
                 FROM students s
                 LEFT JOIN sections sec ON s.section_id = sec.section_id
                 WHERE s.section_id = ?
                 ORDER BY s.last_name, s.first_name";

$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("i", $section_id);
$stmt_students->execute();
$students = $stmt_students->get_result()->fetch_all(MYSQLI_ASSOC);

