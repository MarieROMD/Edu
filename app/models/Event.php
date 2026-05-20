<?php
class Event {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAllWithStats(): array {
        return $this->db->query("
            SELECT e.id, e.titre, e.description, e.date_event,
                   e.capacite, e.image_url,
                   COUNT(p.id)                AS total_participants,
                   (e.capacite - COUNT(p.id)) AS places_restantes
            FROM evenements e
            LEFT JOIN participations p ON e.id = p.event_id
            GROUP BY e.id
            ORDER BY e.date_event ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcoming(): array {
        return $this->db->query("
            SELECT e.id, e.titre, e.description, e.date_event,
                   e.capacite, e.image_url,
                   COUNT(p.id)                AS total_participants,
                   (e.capacite - COUNT(p.id)) AS places_restantes
            FROM evenements e
            LEFT JOIN participations p ON e.id = p.event_id
            WHERE e.date_event >= CURDATE()
            GROUP BY e.id
            ORDER BY e.date_event ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM evenements WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(string $titre, string $description, string $dateEvent, int $capacite, string $imageUrl = ''): void {
        $stmt = $this->db->prepare(
            "INSERT INTO evenements(titre, description, date_event, capacite, image_url)
             VALUES(?, ?, ?, ?, ?)"
        );
        $stmt->execute([$titre, $description, $dateEvent, $capacite, $imageUrl]);
    }

    public function getLastInsertedId(): int {
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $titre, string $description, string $dateEvent, int $capacite, string $imageUrl = ''): void {
        $stmt = $this->db->prepare(
            "UPDATE evenements SET titre=?, description=?, date_event=?, capacite=?, image_url=? WHERE id=?"
        );
        $stmt->execute([$titre, $description, $dateEvent, $capacite, $imageUrl, $id]);
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM participations WHERE event_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM evenements WHERE id = ?")->execute([$id]);
    }
}