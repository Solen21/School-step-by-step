<?php
// File: data/migrations/index.php
// A simple UI to execute the database migration script.

session_start();

// Simple auth check (expand with proper auth later)
// Only 'super_director' should be able to run migrations.
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['super_director'])) {
    // You can redirect or show an error message.
    // For a simple script, we'll just die with an error.
    header('HTTP/1.1 403 Forbidden');
    die('<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>');
}

$output = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    ob_start(); // Start output buffering to capture any `echo` statements from the script.
    try {
        // The migration script will be included and executed.
        // It handles its own database connection and logic.
        include __DIR__ . '/database_table.php';
        $output = ob_get_clean(); // Get the buffered output.
    } catch (Exception $e) {
        $error = 'An error occurred during migration: ' . $e->getMessage();
        if (ob_get_level() > 0) {
            ob_end_clean(); // Clean the buffer on error.
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Run Database Migrations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; }
        .output-box { background-color: #e9ecef; border-left: 5px solid #0d6efd; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h1 class="mb-0">Database Migration Runner</h1>
            </div>
            <div class="card-body">
                <p class="text-muted">Click the button below to run the database schema migration. This will create or update tables in the `ethio_school` database.</p>
                <p class="text-danger"><strong>Warning:</strong> This is a destructive operation. Ensure you have a database backup if you are running this on an existing system.</p>
                <form method="POST" onsubmit="return confirm('Are you sure you want to run the database migrations? This can alter the database structure.');">
                    <button type="submit" name="run_migration" class="btn btn-primary btn-lg">Run Migrations</button>
                </form>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger mt-4" role="alert">
                <h4 class="alert-heading">Migration Failed!</h4>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($output): ?>
            <div class="mt-4 p-4 output-box rounded">
                <h5 class="mb-3">Migration Output:</h5>
                <pre><code><?php echo htmlspecialchars($output); ?></code></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>