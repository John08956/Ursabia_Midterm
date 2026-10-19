<?php
class Attendance {
    private $conn;
    private $table_name = "attendance_records";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addAttendance($member_id, $event_id, $status, $remarks) {
        $query = "INSERT INTO " . $this->table_name . " (member_id, event_id, status, remarks) VALUES (:member_id, :event_id, :status, :remarks)";
        $stmt = $this->conn->prepare($query);

        $member_id = htmlspecialchars(strip_tags($member_id));
        $event_id = htmlspecialchars(strip_tags($event_id));
        $status = htmlspecialchars(strip_tags($status));
        $remarks = htmlspecialchars(strip_tags($remarks));

        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":remarks", $remarks);

        return $stmt->execute();
    }

    public function getAttendances() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function updateAttendance($attendance_id, $status, $remarks) {
        $query = "UPDATE " . $this->table_name . " SET status = :status, remarks = :remarks WHERE attendance_id = :attendance_id";
        $stmt = $this->conn->prepare($query);

        $attendance_id = htmlspecialchars(strip_tags($attendance_id));
        $status = htmlspecialchars(strip_tags($status));
        $remarks = htmlspecialchars(strip_tags($remarks));

        $stmt->bindParam(":attendance_id", $attendance_id);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":remarks", $remarks);

        return $stmt->execute();
    }

    public function deleteAttendance($attendance_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE attendance_id = :attendance_id";
        $stmt = $this->conn->prepare($query);

        $attendance_id = htmlspecialchars(strip_tags($attendance_id));
        $stmt->bindParam(":attendance_id", $attendance_id);

        return $stmt->execute();
    }

    public function searchAttendances($keyword) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE attendance_id LIKE :keyword OR member_id LIKE :keyword OR event_id LIKE :keyword OR status LIKE :keyword OR remarks LIKE :keyword";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        return $stmt;
    }

    public function getActivityLogs() {
        $query = "SELECT ar.*, m.first_name, m.last_name, e.event_name
                  FROM {$this->table_name} ar
                  JOIN members m ON ar.member_id = m.member_id
                  LEFT JOIN events e ON ar.event_id = e.event_id
                  ORDER BY ar.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatistics() {
        $query = "SELECT
                    COUNT(*) AS total_attendance,
                    SUM(status = 'Present') AS total_present,
                    SUM(status = 'Late') AS total_late
                  FROM {$this->table_name}";
        return $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);
    }
}
?>