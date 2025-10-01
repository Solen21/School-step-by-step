<?php
session_start();
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($auth->login($username, $password)) {
        header("Location: ../../index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
include_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="../css/login.css">
<div class="login-container">
    <h2>School Portal Login</h2>
    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST" class="login-form">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>