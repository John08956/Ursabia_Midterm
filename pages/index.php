<?php
require_once __DIR__ . '/../Backend/Auth.php';
require_login();
require_once __DIR__ . '/../Backend/Database.php';
require_once __DIR__ . '/../classes/Member.php';
require_once __DIR__ . '/../classes/Event.php';
require_once __DIR__ . '/../classes/Attendance.php';

$database = new Database();
$db = $database->getConnection();
$memberModel = new Member($db);
$eventModel = new Event($db);
$attendanceModel = new Attendance($db);
$message = '';
$message_type = 'success';
$events = [];
$selected_event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if ($db) {
    try {
        $database->initializeDefaultTables();
        $events = $eventModel->getEvents()->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $exception) {
        $message = 'Unable to load events.';
        $message_type = 'error';
    }
}

if ($selected_event_id <= 0 && !empty($events)) {
    $selected_event_id = (int)$events[0]['event_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['member_id'])) {
    if (!csrf_is_valid()) {
        $message = 'Your session expired. Please try again.';
        $message_type = 'error';
    } else {
        $member_id = (int)$_POST['member_id'];
        $event_id = (int)($_POST['event_id'] ?? 0);
        $action = $_POST['action'];

        if ($event_id <= 0) {
            $message = 'Please select an event before recording attendance.';
            $message_type = 'error';
        } elseif ($action === 'time_in' || $action === 'time_out') {
            try {
                $remarks = $action === 'time_in' ? 'Time In Recorded' : 'Time Out Recorded';
                $attendanceModel->addAttendance($member_id, $event_id, 'Present', $remarks);
                $message = $action === 'time_in'
                    ? 'Time In successfully recorded.'
                    : 'Time Out successfully recorded.';
            } catch (PDOException $exception) {
                $message = 'Unable to record attendance.';
                $message_type = 'error';
            }
        }
    }
}

$search_term = trim($_GET['q'] ?? '');
$member_statement = $search_term === ''
    ? $memberModel->getMembers()
    : $memberModel->searchMembers($search_term);
$members = $member_statement->fetchAll(PDO::FETCH_ASSOC);
$member_count = $memberModel->getCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#dc2626; --red-dark:#991b1b; --bg:#0f172a; --panel:#111c30; --line:#29364b; --muted:#94a3b8; }
        * { box-sizing:border-box; }
        body { margin:0; padding:24px 16px; min-height:100vh; background:#0b1120; color:#f8fafc; font:14px Arial,sans-serif; }
        .page { max-width:1100px; margin:auto; padding:24px; background:rgba(17,28,48,.94); border:1px solid #35435b; border-radius:16px; box-shadow:0 18px 40px #0008; }
        .badge { display:inline-flex; align-items:center; gap:8px; margin-bottom:12px; padding:7px 11px; border:1px solid #7f1d1d; border-radius:9px; color:#fca5a5; font-size:11px; font-weight:700; }
        .badge i { color:#f87171; }
        .badge + h1 { letter-spacing:.1px; }
        h1 { margin:0 0 18px; padding-bottom:12px; border-bottom:1px solid var(--line); font-size:23px; }
        h1 i { margin-right:8px; color:#f87171; }
        .actions,.search,.event-form { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        .actions { margin-bottom:18px; }
        a,.button { display:inline-flex; align-items:center; gap:7px; padding:10px 13px; border:0; border-radius:9px; color:#fff; background:linear-gradient(135deg,var(--red),var(--red-dark)); font-weight:700; text-decoration:none; cursor:pointer; }
        a:hover,.button:hover { opacity:.88; }
        .green { background:#116b5f; } .slate { background:#334155; }
        .search { margin-bottom:14px; }
        input,select { min-height:38px; padding:9px 11px; border:1px solid var(--line); border-radius:9px; background:#0b1425; color:#fff; font:inherit; }
        .search input { flex:1; min-width:220px; }
        .event-form { margin-bottom:18px; }
        .event-form select { min-width:220px; }
        label { color:#cbd5e1; font-weight:700; }
        .notice { margin:12px 0 16px; padding:12px 14px; border-radius:9px; color:#a7f3d0; background:#064e3b66; border:1px solid #10b98166; }
        .notice.error { color:#fecaca; background:#7f1d1d66; border-color:#ef444466; }
        .notice:empty { display:none; }
        .table-wrap::-webkit-scrollbar { height:8px; }
        .table-wrap::-webkit-scrollbar-thumb { background:#475569; border-radius:8px; }
        .table-wrap::-webkit-scrollbar-track { background:#172338; }
        .table-wrap { overflow-x:auto; border:1px solid var(--line); border-radius:11px; }
        table { width:100%; border-collapse:collapse; min-width:720px; }
        th,td { padding:11px 12px; border-bottom:1px solid #243148; text-align:left; }
        th { color:var(--muted); background:#172338; font-size:11px; text-transform:uppercase; }
        tr:last-child td { border-bottom:0; } tr:hover { background:#17233888; }
        .member-name { font-weight:700; } .actions-cell { text-align:center; white-space:nowrap; }
        .actions-cell a,.actions-cell button { padding:7px 9px; margin:2px; font-size:12px; }
        .edit { background:#1d4ed8; } .in { background:#047857; } .out { background:#a16207; }
        .delete { background:#991b1b; }
        .delete-form { display:inline; }
        .empty { padding:28px; text-align:center; color:var(--muted); }
        button:focus-visible,
        a:focus-visible,
        input:focus,
        select:focus {
            outline:2px solid #fca5a5;
            outline-offset:2px;
        }
        .event-form .button { background:#475569; }
        .event-form .button:hover { background:#64748b; }
        .search .button { min-width:92px; justify-content:center; }
        .actions a { min-height:38px; }
        .actions-cell form { vertical-align:middle; }
        .actions-cell button { font-family:inherit; }
        table a { transition:opacity .15s ease; }
        table a:hover { opacity:.8; }
        @media (max-width:800px) {
            .page { padding:20px; }
            .actions-cell { white-space:normal; min-width:250px; }
            .actions-cell a,
            .actions-cell button { display:inline-flex; }
        }
        @media (max-width:600px) {
            body { padding:10px; }
            .page { padding:16px; }
            h1 { font-size:20px; }
            .actions a { flex:1; justify-content:center; }
            .event-form label { width:100%; }
            .event-form select { flex:1; min-width:0; }
            .search input { min-width:0; }
        }
    </style>
    <link rel="stylesheet" href="theme.css">
</head>
<body>
    <main class="page">
        <header class="dashboard-heading">
            <div>
                <div class="badge"><i class="fa-solid fa-shield-halved"></i> CRMC Attendance System</div>
                <h1><i class="fa-solid fa-house"></i> Attendance Dashboard</h1>
            </div>
            <div class="signed-in">
                <span>Signed in as <strong><?php echo htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['username'] ?? ''); ?></strong></span>
                <form method="POST" action="logout.php" style="display:inline;margin-left:10px">
                    <?php csrf_field(); ?>
                    <button class="button slate" type="submit"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</button>
                </form>
            </div>
        </header>

        <nav class="actions">
            <a href="create.php"><i class="fa-solid fa-user-plus"></i> Add Member</a>
            <a href="events.php" class="green"><i class="fa-solid fa-calendar"></i> Events</a>
            <a href="activity_log.php" class="slate"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</a>
        </nav>

        <section class="stats-grid" aria-label="Attendance overview">
            <div class="stat-tile">
                <div><span>Total members</span><strong><?php echo $member_count; ?></strong></div>
                <i class="fa-solid fa-users" aria-hidden="true"></i>
            </div>
            <div class="stat-tile">
                <div><span>Active events</span><strong><?php echo count($events); ?></strong></div>
                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
            </div>
        </section>

        <div class="dashboard-tools">
            <form method="GET" action="index.php" class="search">
                <input name="q" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by name, course, or ID">
                <button class="button" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </form>

            <form method="GET" action="index.php" class="event-form">
                <label for="event_id">Event:</label>
                <select name="event_id" id="event_id">
                    <?php foreach ($events as $event): ?>
                        <option value="<?php echo (int)$event['event_id']; ?>" <?php echo $selected_event_id === (int)$event['event_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($event['event_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="button" type="submit">Select</button>
            </form>
        </div>

        <?php if ($message !== ''): ?>
            <div class="notice <?php echo $message_type === 'error' ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Member</th><th>Course & Year</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!empty($members)): ?>
                    <?php foreach ($members as $member): ?>
                        <?php $id = (int)($member['member_id'] ?? 0); ?>
                        <tr>
                            <td>#<?php echo $id; ?></td>
                            <td class="member-name"><?php echo htmlspecialchars(trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''))); ?></td>
                            <td><?php echo htmlspecialchars(($member['course'] ?? 'N/A') . ' - ' . ($member['year_level'] ?? 'N/A')); ?></td>
                            <td class="actions-cell">
                                <a class="edit" href="update.php?id=<?php echo $id; ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                                <form class="delete-form" method="POST" action="index.php">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="member_id" value="<?php echo $id; ?>">
                                    <input type="hidden" name="event_id" value="<?php echo $selected_event_id; ?>">
                                    <button class="button in" name="action" value="time_in" type="submit"><i class="fa-solid fa-right-to-bracket"></i> In</button>
                                </form>
                                <form class="delete-form" method="POST" action="index.php">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="member_id" value="<?php echo $id; ?>">
                                    <input type="hidden" name="event_id" value="<?php echo $selected_event_id; ?>">
                                    <button class="button out" name="action" value="time_out" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Out</button>
                                </form>
                                <form class="delete-form" method="POST" action="delete.php">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="member_id" value="<?php echo $id; ?>">
                                    <button class="button delete" type="submit"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="empty">No members found. Click â€œAdd Memberâ€ to get started.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
