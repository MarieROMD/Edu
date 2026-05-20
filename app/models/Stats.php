<?php
class Stats {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getSummary(): array {
        return [
            'users'        => $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'events'       => $this->db->query("SELECT COUNT(*) FROM evenements")->fetchColumn(),
            'admins'       => $this->db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn(),
            'membres'      => $this->db->query("SELECT COUNT(*) FROM users WHERE role='membre'")->fetchColumn(),
            'events_today' => $this->db->query("SELECT COUNT(*) FROM evenements WHERE DATE(date_event)=CURDATE()")->fetchColumn(),
            'total_participations'=> $this->db->query("SELECT COUNT(*) FROM participations")->fetchColumn(),

            'new_users_7d' => $this->db->query("SELECT COUNT(*) FROM users WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn(),
            'categories' => (int) $this->db
      ->query("SELECT COUNT(*) FROM categories")
      ->fetchColumn(),
        ];
    }

    public function getRecentUsers(int $limit = 5): array {
        return $this->db->query(
            "SELECT username, email, role FROM users ORDER BY id DESC LIMIT $limit"
        )->fetchAll();
    }

    public function getRecentEvents(int $limit = 5): array {
        return $this->db->query(
            "SELECT titre, date_event FROM evenements ORDER BY date_event ASC LIMIT $limit"
        )->fetchAll();
    }

    public function getUsersChart(): array {
        $rows = $this->db->query("
            SELECT DATE(created_at) AS d, COUNT(*) AS total
            FROM users GROUP BY d ORDER BY d DESC LIMIT 7
        ")->fetchAll();
        $rows = array_reverse($rows);
        return [
            'labels' => array_column($rows, 'd'),
            'data'   => array_column($rows, 'total'),
        ];
    }
    public function getParticipationsByEvent(): array {
    return $this->db->query("
        SELECT e.id, e.titre, e.date_event, e.capacite,
               COUNT(p.id)                AS total_participants,
               (e.capacite - COUNT(p.id)) AS places_restantes,
               ROUND(COUNT(p.id) / e.capacite * 100) AS taux_remplissage
        FROM evenements e
        LEFT JOIN participations p ON e.id = p.event_id
        GROUP BY e.id, e.titre, e.date_event, e.capacite
        ORDER BY total_participants DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

public function getTotalParticipations(): int {
    return (int)$this->db->query(
        "SELECT COUNT(*) FROM participations"
    )->fetchColumn();
}
}