<?php
require_once __DIR__ . '/../Backend/Auth.php';
require_login();
require_once __DIR__ . '/../Backend/Database.php';
require_once __DIR__ . '/../classes/Attendance.php';
$database = new Database();
$db = $database->getConnection();
$database->initializeDefaultTables();
$attendanceModel = new Attendance($db);
$logs = $attendanceModel->getActivityLogs()->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log | CRMC Attendance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#dc2626; --dark-red:#991b1b; --bg:#0b1120; --panel:#111c30; --line:#35435b; --muted:#cbd5e1; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; padding:24px 16px; background:var(--bg); color:#f8fafc; font:14px Arial,sans-serif; }
        .page { width:100%; max-width:1100px; margin:auto; padding:24px; background:rgba(17,28,48,.96); border:1px solid var(--line); border-radius:16px; box-shadow:0 18px 40px #0008; }
        .badge { display:inline-flex; gap:8px; align-items:center; margin-bottom:14px; padding:7px 11px; border:1px solid #7f1d1d; border-radius:9px; color:#fca5a5; font-size:11px; font-weight:700; }
        .badge i,h2 i { color:#f87171; }
        h2 { margin:0 0 20px; padding-bottom:12px; border-bottom:1px solid var(--line); font-size:23px; }
        .back { display:inline-flex; align-items:center; gap:8px; margin-bottom:18px; padding:10px 13px; border-radius:9px; color:white; background:#334155; font-weight:700; text-decoration:none; }
        .back:hover { opacity:.85; }
        .table-wrap { overflow-x:auto; border:1px solid var(--line); border-radius:11px; }
        table { width:100%; min-width:760px; border-collapse:collapse; }
        th,td { padding:12px; border-bottom:1px solid #243148; text-align:left; }
        th { color:#94a3b8; background:#172338; font-size:11px; text-transform:uppercase; }
        tr:last-child td { border-bottom:0; } tr:hover { background:#17233888; }
        .member { font-weight:700; } .event { color:#cbd5e1; }
        .remark { display:inline-block; padding:5px 8px; border-radius:7px; color:#a7f3d0; background:#064e3b88; border:1px solid #10b98166; font-size:12px; }
        .time { color:#cbd5e1; white-space:nowrap; } .time i { margin-right:6px; color:#f87171; }
        .empty { padding:30px; text-align:center; color:#94a3b8; }
        @media (max-width:600px) { body { padding:10px; } .page { padding:16px; } h2 { font-size:20px; } }
    </style>
    <link rel="stylesheet" href="theme.css">
</head>
<body>
    <main class="page">
        <div class="badge"><i class="fa-solid fa-shield-halved"></i> CRMC Attendance System</div>
        <a class="back" href="index.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        <h2><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Member ID</th><th>Name</th><th>Event</th><th>Remarks</th><th>Timestamp</th></tr></thead>
                <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>#<?php echo (int)($log['member_id'] ?? 0); ?></td>
                            <td class="member"><?php echo htmlspecialchars(trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? ''))); ?></td>
                            <td class="event"><?php echo htmlspecialchars($log['event_name'] ?? ''); ?></td>
                            <td><span class="remark"><?php echo htmlspecialchars($log['remarks'] ?? ''); ?></span></td>
                            <td class="time"><i class="fa-regular fa-clock"></i><?php echo htmlspecialchars($log['created_at'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty">No attendance records found yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
