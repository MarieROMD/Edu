<?php
class Feedback {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function create(int $userId, ?int $eventId, string $message, ?int $note): void {
        $stmt = $this->db->prepare("
            INSERT INTO feedbacks(user_id, event_id, commentaire, note)
            VALUES(?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $eventId, $message, $note]);
    }
public function deleteByUser(int $id, int $userId): bool {
    $stmt = $this->db->prepare(
        "DELETE FROM feedbacks WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$id, $userId]);
    return $stmt->rowCount() > 0;
}

public function updateByUser(int $id, int $userId, string $message, ?int $note): bool {
    $stmt = $this->db->prepare(
        "UPDATE feedbacks SET commentaire = ?, note = ? WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$message, $note, $id, $userId]);
    return $stmt->rowCount() > 0;
}

    public function getAll(): array {
        return $this->db->query("
            SELECT f.id, f.commentaire AS message, f.note, f.created_at,
                   u.username, u.email,
                   e.titre AS event_titre
            FROM feedbacks f
            JOIN users u           ON u.id = f.user_id
            LEFT JOIN evenements e ON e.id = f.event_id
            ORDER BY f.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT f.id, f.commentaire AS message, f.note, f.created_at,
                   e.titre AS event_titre
            FROM feedbacks f
            LEFT JOIN evenements e ON e.id = f.event_id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEvent(int $eventId): array {
        $stmt = $this->db->prepare("
            SELECT f.commentaire AS message, f.note, f.created_at, u.username
            FROM feedbacks f
            JOIN users u ON u.id = f.user_id
            WHERE f.event_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM feedbacks WHERE id = ?")->execute([$id]);
    }

    public function getStats(): array {
        $total   = $this->db->query("SELECT COUNT(*) FROM feedbacks")->fetchColumn();
        $moyenne = $this->db->query("SELECT ROUND(AVG(note),1) FROM feedbacks WHERE note IS NOT NULL")->fetchColumn();
        $cinq    = $this->db->query("SELECT COUNT(*) FROM feedbacks WHERE note = 5")->fetchColumn();
        return [
            'total'   => (int)$total,
            'moyenne' => $moyenne ?: '—',
            'cinq'    => (int)$cinq,
        ];
    }
}