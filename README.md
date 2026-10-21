# CRMC Attendance System

PHP and MySQL attendance management for CRMC faculty events.

## Features

- Faculty registration with name, email, department, position, and password confirmation
- Password hashing, PHP sessions, CSRF protection, and login/logout
- Member and event management, attendance recording, search, and activity log
- PDO transactions for registration, member creation/update/deletion, and event deletion
- Responsive white-and-maroon interface

## LMS Submission

- **Deployed system:** https://crmcattendance.wuaze.com
- **Public GitHub repository:** https://github.com/John08956/Ursabia_Midterm
- **Sessions:** PHP sessions are used for login state, logout, and CSRF tokens.
- **Three database transaction examples:**
  1. Faculty registration inserts the account and starts the authenticated session.
  2. Member creation, update, and deletion run inside PDO transactions.
  3. Event deletion removes related attendance records and the event inside one PDO transaction.

## Local Setup

1. Start Apache and MySQL in XAMPP.
2. Create a MySQL database named `attendance_db` in phpMyAdmin and import `database.sql`.
3. Copy `Backend/config.example.php` to `Backend/config.php`. The default local values match XAMPP (`localhost`, `attendance_db`, `root`, blank password).
4. Open `http://localhost/Ursabia_Midterm/pages/register.php` to create an account.

## InfinityFree

Follow [DEPLOYMENT.md](DEPLOYMENT.md). Use the host, database name, username, and password shown in your InfinityFree control panel. Never commit `Backend/config.php` or publish database credentials.
