<?php
require_once __DIR__ . '/../Backend/Auth.php';
require_login();
require_once __DIR__ . '/../Backend/Database.php';
require_once __DIR__ . '/../classes/Event.php';
$database = new Database();
$db = $database->getConnection();
$database->initializeDefaultTables();
$eventModel = new Event($db);

$message = '';
$message_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        $message = 'Your session expired. Please try again.';
        $message_type = 'error';
    } else {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'delete') {
            $event_id = (int)($_POST['event_id'] ?? 0);

            if ($event_id > 0) {
                try {
                    $eventModel->deleteEvent($event_id);
                    $message = 'Event deleted successfully.';
                } catch (PDOException $e) {
                    $message = 'Unable to delete event.';
                    $message_type = 'error';
                }
            } else {
                $message = 'Invalid event selected.';
                $message_type = 'error';
            }
        } else {
            $event_name = trim($_POST['event_name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($event_name !== '') {
                $eventModel->addEvent($event_name, $description);
                $message = 'Event saved successfully.';
            } else {
                $message = 'Event name is required.';
                $message_type = 'error';
            }
        }
    }
}

$events = $eventModel->getEvents()->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #090d16;
            color: #f8fafc;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 980px;
            margin: 0 auto;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(239,68,68,0.35);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }
        .btn {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 12px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            text-decoration: none;
            font-weight: 700;
        }
        .form-wrap {
            display: grid;
            grid-template-columns: 1.2fr 1.8fr auto;
            gap: 12px;
            margin-bottom: 20px;
        }
        input, button {
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(15,23,42,0.8);
            color: white;
            font: inherit;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }
        th, td {
            padding: 16px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-align: left;
        }
        th {
            color: #fca5a5;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        .message {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 12px;
            background: rgba(16,185,129,0.15);
            border: 1px solid rgba(16,185,129,0.35);
            color: #a7f3d0;
        }
        .message.error {
            background: rgba(239,68,68,0.15);
            border-color: rgba(239,68,68,0.35);
            color: #fca5a5;
        }
        .btn-delete {
            padding: 8px 12px;
            border: 1px solid rgba(239,68,68,0.45);
            border-radius: 10px;
            background: rgba(239,68,68,0.2);
            color: #fca5a5;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-delete:hover {
            background: rgba(239,68,68,0.4);
            color: white;
        }
    </style>
    <link rel="stylesheet" href="theme.css">
</head>
<body>
    <div class="container">
        <div class="topbar">
            <h2 style="margin: 0;">Manage Events</h2>
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>

        <?php if ($message !== ''): ?>
            <div class="message <?php echo $message_type === 'error' ? 'error' : ''; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php csrf_field(); ?>
            <div class="form-wrap">
                <input type="text" name="event_name" placeholder="Event name e.g. Seminar 2026" required>
                <input type="text" name="description" placeholder="Short description (optional)">
                <button type="submit">Save Event</button>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event Name</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td>#<?php echo (int)$event['event_id']; ?></td>
                            <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($event['description'] ?? ''); ?></td>
                            <td>
                                <form method="POST" style="margin: 0;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="event_id" value="<?php echo (int)$event['event_id']; ?>">
                                    <button type="submit" class="btn-delete"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 30px;">No events yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
