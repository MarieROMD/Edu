<?php
class Category {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        return $this->db->query(
            "SELECT id, nom FROM categories ORDER BY id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function nameExists(string $nom): bool {
        $stmt = $this->db->prepare("SELECT id FROM categories WHERE nom = ?");
        $stmt->execute([$nom]);
        return $stmt->rowCount() > 0;
    }

    public function nameExistsExcept(string $nom, int $excludeId): bool {
        $stmt = $this->db->prepare("SELECT id FROM categories WHERE nom = ? AND id != ?");
        $stmt->execute([$nom, $excludeId]);
        return $stmt->rowCount() > 0;
    }

    public function create(string $nom): void {
        $stmt = $this->db->prepare("INSERT INTO categories (nom) VALUES (?)");
        $stmt->execute([trim($nom)]);
    }

    public function update(int $id, string $nom): void {
        $stmt = $this->db->prepare("UPDATE categories SET nom = ? WHERE id = ?");
        $stmt->execute([trim($nom), $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
    }
}