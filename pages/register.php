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
    $first_name = trim(is_string($_POST['first_name'] ?? null) ? $_POST['first_name'] : '');
    $last_name = trim(is_string($_POST['last_name'] ?? null) ? $_POST['last_name'] : '');
    $email = strtolower(trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : ''));
    $department = trim(is_string($_POST['department'] ?? null) ? $_POST['department'] : '');
    $position = trim(is_string($_POST['position'] ?? null) ? $_POST['position'] : '');
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirm_password = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
    $departments = [
        'BSIT Department', 'BSCS Department', 'BSED Department', 'BEED Department',
        'BSCRIM Department', 'BSBA Department', 'General Education',
    ];
    $positions = ['Faculty', 'Dean'];

    if (!csrf_is_valid()) {
        $message = 'Your session expired. Please try again.';
    } elseif ($first_name === '' || $last_name === '' || strlen($first_name) > 100 || strlen($last_name) > 100) {
        $message = 'Enter your first and last name (up to 100 characters each).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $message = 'Enter a valid Gmail or email address.';
    } elseif (!in_array($department, $departments, true)) {
        $message = 'Select a department from the list.';
    } elseif (!in_array($position, $positions, true)) {
        $message = 'Select your position from the list.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $message = 'Password must be between 8 and 72 characters.';
    } elseif (!hash_equals($password, $confirm_password)) {
        $message = 'The passwords do not match.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        if ($db && $database->initializeDefaultTables()) {
            try {
                $db->beginTransaction();
                $statement = $db->prepare('INSERT INTO users (username, first_name, last_name, department, position, password_hash) VALUES (:username, :first_name, :last_name, :department, :position, :password_hash)');
                $statement->execute([
                    'username' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'department' => $department,
                    'position' => $position,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $db->commit();
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$db->lastInsertId();
                $_SESSION['username'] = $email;
                $_SESSION['display_name'] = $first_name . ' ' . $last_name;
                header('Location: index.php');
                exit;
            } catch (PDOException $exception) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $message = $exception->getCode() === '23000'
                    ? 'That email address already has an account.'
                    : 'Unable to create the account right now.';
            }
        } else {
            $message = 'Unable to connect to the database.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CRMC Attendance</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="theme.css">
</head>
<body>
    <main class="auth-panel">
        <p class="brand">CRMC Attendance System</p>
        <h1>Faculty registration</h1>
        <?php if ($message !== ''): ?><div class="notice"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="POST">
            <?php csrf_field(); ?>
            <label for="first_name">First name<input id="first_name" name="first_name" autocomplete="given-name" maxlength="100" required></label>
            <label for="last_name">Last name<input id="last_name" name="last_name" autocomplete="family-name" maxlength="100" required></label>
            <label for="email">Gmail address<input id="email" type="email" name="email" autocomplete="email" maxlength="254" placeholder="name@gmail.com" required></label>
            <label for="department">Department
                <select id="department" name="department" required>
                    <option value="" disabled selected>Select department</option>
                    <option>BSIT Department</option>
                    <option>BSCS Department</option>
                    <option>BSED Department</option>
                    <option>BEED Department</option>
                    <option>BSCRIM Department</option>
                    <option>BSBA Department</option>
                    <option>General Education</option>
                </select>
            </label>
            <label for="position">Position
                <select id="position" name="position" required>
                    <option value="" disabled selected>Select position</option>
                    <option>Faculty</option>
                    <option>Dean</option>
                </select>
            </label>
            <label for="password">New password<input id="password" type="password" name="password" autocomplete="new-password" minlength="8" maxlength="72" required></label>
            <label for="confirm_password">Confirm password<input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" minlength="8" maxlength="72" required></label>
            <button type="submit">Register</button>
        </form>
        <p class="auth-link">Already registered? <a href="login.php">Login</a></p>
    </main>
</body>
</html>
