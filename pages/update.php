<?php
require_once __DIR__ . '/../Backend/Auth.php';
require_login();
require_once __DIR__ . '/../Backend/Database.php';
require_once __DIR__ . '/../classes/Member.php';

$database = new Database();
$db = $database->getConnection();
$database->initializeDefaultTables();
$memberModel = new Member($db);

$message = "";
$message_type = "success";

$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$member = null;

if ($member_id > 0) {
    $member = $memberModel->getMemberById($member_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        $message = "<div class='alert-danger'>Your session expired. Please try again.</div>";
        $message_type = 'error';
    } else {
    $member_id = isset($_POST['member_id']) ? (int)$_POST['member_id'] : 0;
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $year_level = trim($_POST['year_level'] ?? '');

    if ($member_id > 0 && $first_name !== '' && $last_name !== '' && $course !== '' && $year_level !== '') {
        if ($memberModel->updateMember($member_id, $first_name, $last_name, $course, $year_level)) {
            $message = "<div class='alert-success'>Member updated successfully!</div>";
            $member = $memberModel->getMemberById($member_id);
        } else {
            $message = "<div class='alert-danger'>Unable to update member.</div>";
            $message_type = "error";
        }
    } else {
        $message = "<div class='alert-danger'>Please fill in all fields.</div>";
        $message_type = "error";
    }
    }
}

if (!$member) {
    $member = [
        'member_id' => $member_id,
        'first_name' => '',
        'last_name' => '',
        'course' => '',
        'year_level' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Member | Cebu Roosevelt Memorial Colleges</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #dc2626;
            --primary-hover: #ef4444;
            --accent: #ff4d4d;
            --bg-dark: #090d16;
            --card-bg: rgba(15, 23, 42, 0.75);
            --border-glow: rgba(239, 68, 68, 0.5);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: #f8fafc;
            margin: 0;
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            position: relative;
            overflow-x: hidden;
        }

        .pure-css-bg {
            position: fixed;
            inset: 0;
            z-index: -2;
            background: radial-gradient(circle at center, #111827 0%, #070a10 100%);
            overflow: hidden;
        }

        .bg-particle {
            position: absolute;
            background: rgba(239, 68, 68, 0.4);
            border-radius: 50%;
            animation: floatParticle 15s infinite linear;
        }

        @keyframes floatParticle {
            0% { transform: translateY(105vh) translateX(0); opacity: 0; }
            50% { opacity: 0.7; }
            100% { transform: translateY(-10vh) translateX(50px); opacity: 0; }
        }

        .ambient-glow {
            position: fixed;
            width: 600px;
            height: 600px;
            background: rgba(220, 38, 38, 0.15);
            border-radius: 50%;
            filter: blur(120px);
            z-index: -1;
            pointer-events: none;
            animation: pulseGlow 8s ease-in-out infinite alternate;
        }
        .glow-top { top: -150px; left: -150px; }
        .glow-bottom { bottom: -150px; right: -150px; animation-delay: -4s; background: rgba(185, 28, 28, 0.12); }

        @keyframes pulseGlow {
            0% { transform: scale(0.9); opacity: 0.4; }
            100% { transform: scale(1.15); opacity: 0.8; }
        }

        .modal-card {
            width: 100%;
            max-width: 700px;
            background: var(--card-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 16px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border: 2px solid var(--border-glow);
            padding: 24px;
            position: relative;
            z-index: 1;
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            background: rgba(220, 38, 38, 0.1);
            padding: 7px 11px;
            border-radius: 9px;
            width: fit-content;
            border: 1px solid rgba(239, 68, 68, 0.3);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0;
            color: #fca5a5;
            text-transform: uppercase;
        }

        .badge i {
            color: var(--accent);
            font-size: 14px;
        }

        .back {
            background: rgba(51, 65, 85, 0.7);
            color: #cbd5e1;
            padding: 10px 13px;
            border-radius: 9px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.2s ease;
            margin-bottom: 18px;
        }
        .back:hover {
            background: rgba(51, 65, 85, 1);
            color: #ffffff;
            transform: translateY(-2px);
        }

        h2 {
            color: #ffffff;
            margin-top: 0;
            font-size: 23px;
            font-weight: 800;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.5px;
        }

        h2 i {
            color: var(--accent);
            background: rgba(220, 38, 38, 0.2);
            padding: 0;
            border-radius: 0;
            font-size: 18px;
            border: 1px solid rgba(239, 68, 68, 0.4);
            box-shadow: 0 0 20px rgba(220, 38, 38, 0.3);
        }

        .alert-success, .alert-danger {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            display: block;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 7px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 9px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(15, 23, 42, 0.8);
            color: #f8fafc;
            font: inherit;
        }

        input:focus {
            outline: none;
            border-color: rgba(239, 68, 68, 0.8);
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.15);
        }

        button[type="submit"] {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 9px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 800;
            margin-top: 5px;
            box-shadow: 0 5px 14px rgba(220, 38, 38, 0.25);
        }
        .notice {
            padding: 12px 14px;
            margin-bottom: 18px;
            border-radius: 9px;
            color: #a7f3d0;
            background: rgba(6, 78, 59, 0.4);
            border: 1px solid rgba(16, 185, 129, 0.4);
        }
        .notice.error {
            color: #fecaca;
            background: rgba(127, 29, 29, 0.4);
            border-color: rgba(239, 68, 68, 0.4);
        }
        .save { width: 100%; }
    </style>
    <link rel="stylesheet" href="theme.css">
</head>
<body>
    <?php require __DIR__ . '/../modals/update_modal.php'; ?>
</body>
</html>
