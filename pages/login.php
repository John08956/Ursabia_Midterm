<?php
require_once __DIR__ . '/../Backend/Auth.php';
require_once __DIR__ . '/../Backend/Database.php';
app_session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        $message = 'Your session expired. Please try again.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        if ($db && $database->initializeDefaultTables()) {
            $email = strtolower(trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : ''));
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            $statement = $db->prepare('SELECT user_id, username, first_name, last_name, password_hash FROM users WHERE username = :username LIMIT 1');
            $statement->execute(['username' => $email]);
            $user = $statement->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['display_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
                header('Location: index.php');
                exit;
            }
        }
        $message = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CRMC Attendance</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="theme.css">
</head>
<body>
    <main class="auth-panel">
        <p class="brand">CRMC Attendance System</p>
        <h1>Login</h1>
        <?php if ($message !== ''): ?><div class="notice"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="POST">
            <?php csrf_field(); ?>
            <label for="email">Gmail address<input id="email" type="email" name="email" autocomplete="email" maxlength="254" required></label>
            <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
            <button type="submit">Login</button>
        </form>
        <p class="auth-link">No account yet? <a href="register.php">Register</a></p>
    </main>
</body>
</html>
