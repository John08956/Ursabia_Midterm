<?php
require_once __DIR__ . '/../Backend/Auth.php';
require_login();
require_once __DIR__ . '/../Backend/Database.php';
require_once __DIR__ . '/../classes/Member.php';
$database = new Database();
$db = $database->getConnection();
$database->initializeDefaultTables();
$memberModel = new Member($db);
$message = '';
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        $message = 'Your session expired. Please try again.';
        $message_type = 'error';
    } else {
        try {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $course = trim($_POST['course'] ?? '');
            $year_level = trim($_POST['year_level'] ?? '');
            if ($first_name === '' || $last_name === '' || $course === '' || $year_level === '') {
                throw new InvalidArgumentException('Please fill in all fields.');
            }
            $memberModel->addMember($first_name, $last_name, $course, $year_level);
            $message = 'Member added successfully.';
        } catch (Exception $exception) {
            $message = 'Unable to add member: ' . $exception->getMessage();
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member | CRMC Attendance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#dc2626; --dark-red:#991b1b; --bg:#0b1120; --panel:#111c30; --line:#35435b; --muted:#cbd5e1; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; padding:24px 16px; background:var(--bg); color:#f8fafc; font:14px Arial,sans-serif; }
        .modal-card { width:100%; max-width:680px; margin:auto; padding:24px; background:rgba(17,28,48,.96); border:1px solid var(--line); border-radius:16px; box-shadow:0 18px 40px #0008; }
        .badge { display:inline-flex; gap:8px; align-items:center; margin-bottom:14px; padding:7px 11px; border:1px solid #7f1d1d; border-radius:9px; color:#fca5a5; font-size:11px; font-weight:700; }
        .badge i,h2 i { color:#f87171; }
        h2 { margin:0 0 20px; padding-bottom:12px; border-bottom:1px solid var(--line); font-size:23px; }
        .back,.save { display:inline-flex; align-items:center; gap:8px; border:0; border-radius:9px; color:white; font-weight:700; text-decoration:none; cursor:pointer; }
        .back { margin-bottom:18px; padding:10px 13px; background:#334155; }
        form { display:grid; gap:15px; }
        label { display:block; margin-bottom:7px; color:var(--muted); font-weight:700; }
        input,select { width:100%; min-height:40px; padding:10px 12px; border:1px solid var(--line); border-radius:9px; background:#0b1425; color:white; font:inherit; }
        option { background:#0b1425; }
        input:focus,select:focus { outline:2px solid #fca5a5; outline-offset:2px; border-color:#ef4444; }
        .save { justify-content:center; padding:12px; background:linear-gradient(135deg,var(--red),var(--dark-red)); }
        .save:hover,.back:hover { opacity:.85; }
        .notice { padding:12px 14px; margin-bottom:18px; border-radius:9px; color:#a7f3d0; background:#064e3b66; border:1px solid #10b98166; }
        .notice.error { color:#fecaca; background:#7f1d1d66; border-color:#ef444466; }
        @media (max-width:600px) { body { padding:10px; } .modal-card { padding:16px; } h2 { font-size:20px; } }
    </style>
    <link rel="stylesheet" href="theme.css">
</head>
<body>
    <?php require __DIR__ . '/../modals/add_modal.php'; ?>
</body>
</html>
