<?php
require_once __DIR__ . '/../config/db.php';
// The User model is not used here to ensure we use the correct DB connection and logic.

class AuthController {
    public function login($username, $password) {
        global $conn; // Use the mysqli connection from db.php

        // Prepare a statement to prevent SQL injection
        // Join with the roles table to get the role_name
        $sql = "SELECT u.user_id, u.password, r.role_name 
                FROM users u 
                JOIN roles r ON u.role = r.role_id 
                WHERE u.id_number = ? AND u.status = 'active'";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            // Handle prepare error
            error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            return false;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_role'] = $user['role_name']; // Store the role NAME
                return true;
            }
        }

        return false;
    }
}
