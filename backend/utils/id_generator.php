<?php

/**
 * Generates a new unique student ID.
 * It finds the highest existing numeric student ID from the users table,
 * increments it, and pads it with leading zeros to a length of 6.
 *
 * @param mysqli $conn The database connection object.
 * @return string The new student ID.
 */
function generate_student_id(mysqli $conn): string {
    // Query for the maximum numeric ID number among students.
    // The `role` check ensures we only look at student IDs.
    $sql = "SELECT MAX(CAST(u.id_number AS UNSIGNED)) as max_id 
            FROM users u
            JOIN roles r ON u.role = r.role_id
            WHERE r.role_name = 'student' AND u.id_number REGEXP '^[0-9]+$'";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ?? 0;

    // Increment and format the new ID
    $new_id = $max_id + 1;
    return str_pad((string)$new_id, 6, '0', STR_PAD_LEFT);
}

/**
 * Generates a new unique guardian ID.
 * It finds the highest existing numeric guardian ID from the users table,
 * increments it, and pads it with leading zeros to a length of 6, prefixed with 'G-'.
 *
 * @param mysqli $conn The database connection object.
 * @return string The new guardian ID.
 */
function generate_guardian_id(mysqli $conn): string {
    // Query for the maximum numeric part of the ID number among guardians.
    // The `role` check ensures we only look at guardian IDs.
    $sql = "SELECT MAX(CAST(SUBSTRING(u.id_number, 3) AS UNSIGNED)) as max_id 
            FROM users u
            JOIN roles r ON u.role = r.role_id
            WHERE r.role_name = 'guardian' AND u.id_number LIKE 'G-%'";
            
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ?? 0;

    // Increment and format the new ID with a 'G-' prefix
    $new_id = $max_id + 1;
    return 'G-' . str_pad((string)$new_id, 6, '0', STR_PAD_LEFT);
}