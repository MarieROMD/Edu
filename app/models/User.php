<?php
class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    public function emailExistsExcept(string $email, int $excludeId): bool {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $excludeId]);
        return $stmt->rowCount() > 0;
    }

    public function create(string $username, string $email, string $password, string $role = 'membre'): void {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO users(username, email, password, role) VALUES(?, ?, ?, ?)"
        );
        $stmt->execute([$username, $email, $hash, $role]);
    }

    public function update(int $id, string $username, string $email, string $role): void {
        $stmt = $this->db->prepare(
            "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?"
        );
        $stmt->execute([$username, $email, $role, $id]);
    }

    public function updatePassword(int $id, string $password): void {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getAll(): array {
        return $this->db->query(
            "SELECT id, username, email, role FROM users ORDER BY id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateProfile(int $id, string $username, string $email): bool {
    if ($this->emailExistsExcept($email, $id)) return false;
    $this->db->prepare(
        "UPDATE users SET username=?, email=? WHERE id=?"
    )->execute([$username, $email, $id]);
    return true;
}
}