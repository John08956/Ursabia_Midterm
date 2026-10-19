<?php
class Event {
    private $conn;
    private $table_name = 'events';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addEvent($event_name, $description = '') {
        $query = "INSERT INTO {$this->table_name} (event_name, description)
                  VALUES (:event_name, :description)
                  ON DUPLICATE KEY UPDATE description = VALUES(description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':event_name', trim($event_name));
        $stmt->bindValue(':description', trim($description));
        return $stmt->execute();
    }

    public function getEvents() {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table_name} ORDER BY event_name ASC");
        $stmt->execute();
        return $stmt;
    }

    public function deleteEvent($event_id) {
        $this->conn->beginTransaction();
        try {
            $attendance = $this->conn->prepare('DELETE FROM attendance_records WHERE event_id = ?');
            $attendance->execute([(int)$event_id]);
            $event = $this->conn->prepare("DELETE FROM {$this->table_name} WHERE event_id = ?");
            $result = $event->execute([(int)$event_id]);
            $this->conn->commit();
            return $result;
        } catch (PDOException $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $exception;
        }
    }
}
