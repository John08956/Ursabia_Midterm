# CRMC Attendance System

PHP and MySQL attendance management for CRMC faculty events.

## Features

- Faculty registration with name, email, department, position, and password confirmation
- Password hashing, PHP sessions, CSRF protection, and login/logout
- Member and event management, attendance recording, search, and activity log
- PDO transactions for registration, member creation/update/deletion, and event deletion
- Responsive white-and-maroon interface

## Local Setup

1. Start Apache and MySQL in XAMPP.
2. Create a MySQL database named `attendance_db` in phpMyAdmin and import `database.sql`.
3. Copy `Backend/config.example.php` to `Backend/config.php`. The default local values match XAMPP (`localhost`, `attendance_db`, `root`, blank password).
4. Open `http://localhost/Ursabia_Midterm/pages/register.php` to create an account.

## InfinityFree

Follow [DEPLOYMENT.md](DEPLOYMENT.md). Use the host, database name, username, and password shown in your InfinityFree control panel. Never commit `Backend/config.php` or publish database credentials.
