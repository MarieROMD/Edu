<?php
class Participation {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function join(int $userId, int $eventId): bool {
        if ($this->isJoined($userId, $eventId)) {
            return false;
        }

         $stmt = $this->db->prepare("
        SELECT (e.capacite - COUNT(p.id)) AS places_restantes
        FROM evenements e
        LEFT JOIN participations p ON e.id = p.event_id
        WHERE e.id = ? GROUP BY e.id
    ");
    $stmt->execute([$eventId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || $row['places_restantes'] <= 0) return false;

    $token = bin2hex(random_bytes(16));

    $stmt = $this->db->prepare(
        "INSERT INTO participations(user_id, event_id, token) VALUES(?, ?, ?)"
    );
    $stmt->execute([$userId, $eventId, $token]);
    return true;
    }

    public function leave(int $userId, int $eventId): void {
        $stmt = $this->db->prepare(
            "DELETE FROM participations WHERE user_id = ? AND event_id = ?"
        );
        $stmt->execute([$userId, $eventId]);
    }

    public function isJoined(int $userId, int $eventId): bool {
        $stmt = $this->db->prepare(
            "SELECT id FROM participations WHERE user_id = ? AND event_id = ?"
        );
        $stmt->execute([$userId, $eventId]);
        return $stmt->rowCount() > 0;
    }

    public function getEventIdsByUser(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT event_id FROM participations WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'event_id');
    }

    public function getByEvent(int $eventId): array {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.email
            FROM participations p
            JOIN users u ON u.id = p.user_id
            WHERE p.event_id = ?
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMyEvents(int $userId): array {
    $stmt = $this->db->prepare("
        SELECT e.id, e.titre, e.date_event, e.capacite,
               e.image_url,
               COUNT(p2.id)                AS total_participants,
               (e.capacite - COUNT(p2.id)) AS places_restantes
        FROM participations p
        JOIN evenements e           ON e.id = p.event_id
        LEFT JOIN participations p2 ON p2.event_id = e.id
        WHERE p.user_id = ?
        GROUP BY e.id, e.titre, e.date_event, e.capacite, e.image_url
        ORDER BY e.date_event ASC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}
public function findByToken(string $token): array|false {
    $stmt = $this->db->prepare("
        SELECT p.*, u.username, u.email, e.titre AS event_titre, e.date_event
        FROM participations p
        JOIN users u      ON u.id = p.user_id
        JOIN evenements e ON e.id = p.event_id
        WHERE p.token = ?
    ");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function confirmPresence(string $token): bool {
    $stmt = $this->db->prepare(
        "UPDATE participations SET presence = 1 WHERE token = ?"
    );
    $stmt->execute([$token]);
    return $stmt->rowCount() > 0;
}

public function getWithToken(int $userId, int $eventId): array|false {
    $stmt = $this->db->prepare(
        "SELECT * FROM participations WHERE user_id = ? AND event_id = ?"
    );
    $stmt->execute([$userId, $eventId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

}