 <?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// db.php connection
require_once __DIR__ . '/../../backend/config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <!-- <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="libs/bootstrap.min.css">
    <link rel="stylesheet" href="libs/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="libs/select2.min.css">
    <link rel="stylesheet" href="libs/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="libs/all.min.css"> -->

    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS for Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- Select2 CSS for Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>
<?php
    // Auto-run the overdue book check for admins, once per hour.
    if (isset($_SESSION['loggedin']) && in_array($_SESSION['role'], ['admin', 'super_director', 'director'])) {
        // Check if an hour has passed since the last check
        if (!isset($_SESSION['overdue_checked_time']) || (time() - $_SESSION['overdue_checked_time'] > 3600)) {
            // The check script needs the $conn variable.
            require_once __DIR__ . '/../../backend/config/db.php';
        }
    }
// ?>
<?php
    // Include the dynamic navigation bar
    @include_once(__DIR__ . '/nav.php');
?>