<?php
require_once __DIR__ . '/config.php';

class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch(PDOException $exception) {
            error_log('Database connection failed: ' . $exception->getMessage());
        }
        return $this->conn;
    }

    public function initializeDefaultTables() {
        if (!$this->conn) {
            $this->getConnection();
        }

        if (!$this->conn) {
            return false;
        }

        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS users (
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(254) NOT NULL UNIQUE,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                department VARCHAR(100) NOT NULL,
                position VARCHAR(50) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $user_columns = $this->conn->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
        foreach (['first_name', 'last_name', 'department', 'position'] as $column) {
            if (!in_array($column, $user_columns, true)) {
            $column_length = $column === 'position' ? 50 : 100;
            $this->conn->exec("ALTER TABLE users ADD COLUMN {$column} VARCHAR({$column_length}) NULL");
            }
        }

        $username_length = $this->conn->prepare("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'username'");
        $username_length->execute();
        if ((int)$username_length->fetchColumn() < 254) {
            $this->conn->exec('ALTER TABLE users MODIFY username VARCHAR(254) NOT NULL');
        }

        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS members (
                member_id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                course VARCHAR(100) NOT NULL,
                year_level VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS events (
                event_id INT AUTO_INCREMENT PRIMARY KEY,
                event_name VARCHAR(150) NOT NULL UNIQUE,
                description TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS attendance_records (
                attendance_id INT AUTO_INCREMENT PRIMARY KEY,
                member_id INT NOT NULL,
                event_id INT NOT NULL,
                status VARCHAR(30) NOT NULL,
                remarks VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_attendance_member (member_id),
                INDEX idx_attendance_event (event_id),
                FOREIGN KEY (event_id) REFERENCES events(event_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        return true;
    }
}
?>