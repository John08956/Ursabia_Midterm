<?php
class Member {
    private $conn;
    private $table_name = 'members';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getMembers() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY member_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getMemberById($member_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE member_id = :member_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createMember($first_name, $last_name, $course, $year_level) {
        $query = "INSERT INTO " . $this->table_name . " (first_name, last_name, course, year_level) VALUES (:first_name, :last_name, :course, :year_level)";
        $stmt = $this->conn->prepare($query);

        $first_name = trim($first_name);
        $last_name = trim($last_name);
        $course = trim($course);
        $year_level = trim($year_level);

        $this->conn->beginTransaction();
        try {
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':course', $course);
            $stmt->bindParam(':year_level', $year_level);
            $result = $stmt->execute();
            $this->conn->commit();
            return $result;
        } catch (Throwable $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $exception;
        }
    }

    public function addMember($first_name, $last_name, $course, $year_level) {
        return $this->createMember($first_name, $last_name, $course, $year_level);
    }

    public function updateMember($member_id, $first_name, $last_name, $course, $year_level) {
        $query = "UPDATE " . $this->table_name . " SET first_name = :first_name, last_name = :last_name, course = :course, year_level = :year_level WHERE member_id = :member_id";
        $stmt = $this->conn->prepare($query);

        $member_id = (int)$member_id;
        $first_name = trim($first_name);
        $last_name = trim($last_name);
        $course = trim($course);
        $year_level = trim($year_level);

        $this->conn->beginTransaction();
        try {
            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':course', $course);
            $stmt->bindParam(':year_level', $year_level);
            $result = $stmt->execute();
            $this->conn->commit();
            return $result;
        } catch (Throwable $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $exception;
        }
    }

    public function deleteMember($member_id) {
        $this->conn->beginTransaction();
        try {
            $attendance = $this->conn->prepare('DELETE FROM attendance_records WHERE member_id = :member_id');
            $attendance->execute(['member_id' => (int)$member_id]);

            $member = $this->conn->prepare('DELETE FROM ' . $this->table_name . ' WHERE member_id = :member_id');
            $member->execute(['member_id' => (int)$member_id]);
            $deleted = $member->rowCount() > 0;

            $this->conn->commit();
            return $deleted;
        } catch (Throwable $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $exception;
        }
    }

    public function searchMembers($keyword) {
        $query = "SELECT * FROM " . $this->table_name . "
            WHERE first_name LIKE :keyword
               OR last_name LIKE :keyword
               OR course LIKE :keyword
               OR year_level LIKE :keyword
               OR member_id LIKE :keyword
            ORDER BY member_id DESC";
        $stmt = $this->conn->prepare($query);
        $search_keyword = '%' . trim($keyword) . '%';
        $stmt->bindValue(':keyword', $search_keyword, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
    }

    public function getCount() {
        return (int)$this->conn->query("SELECT COUNT(*) FROM {$this->table_name}")->fetchColumn();
    }
}
