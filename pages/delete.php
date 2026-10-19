<?php
require_once __DIR__ . '/../Backend/Auth.php';
require_login();
require_once __DIR__ . '/../Backend/Database.php';
require_once __DIR__ . '/../classes/Member.php';
$database = new Database();
$db = $database->getConnection();
$database->initializeDefaultTables();
$memberModel = new Member($db);
$member_id = (int)($_POST['member_id'] ?? 0);
$delete_message = 'Unable to delete member.';
$delete_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_is_valid() && $member_id > 0) {
    if ($memberModel->deleteMember($member_id)) {
        $delete_message = 'Member deleted successfully.';
        $delete_success = true;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete_message = 'Invalid or expired request. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Member | CRMC Attendance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg:#0b1120; --line:#35435b; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; padding:24px 16px; background:var(--bg); color:#f8fafc; font:14px Arial,sans-serif; }
        .modal-card { width:100%; max-width:520px; margin:auto; padding:24px; background:#111c30; border:1px solid var(--line); border-radius:16px; box-shadow:0 18px 40px #0008; }
        .badge { display:inline-flex; gap:8px; align-items:center; margin-bottom:18px; padding:7px 11px; border:1px solid #7f1d1d; border-radius:9px; color:#fca5a5; font-size:11px; font-weight:700; }
        .badge i,h2 i { color:#f87171; }
        h2 { margin:0 0 15px; padding-bottom:12px; border-bottom:1px solid var(--line); font-size:23px; }
        p { margin:0 0 20px; color:#cbd5e1; }
        .back { display:inline-flex; align-items:center; gap:8px; padding:10px 13px; border-radius:9px; color:#fff; background:#334155; font-weight:700; text-decoration:none; }
        .back:hover { opacity:.85; }
        .success { color:#a7f3d0; } .failure { color:#fecaca; }
    </style>
    <link rel="stylesheet" href="theme.css">
</head>
<body>
    <div class="modal-card <?php echo $delete_success ? 'success' : 'failure'; ?>">
        <?php require __DIR__ . '/../modals/delete_modal.php'; ?>
    </div>
</body>
</html>
