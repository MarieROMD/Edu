<?php
class NotifController {

    public function stream(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403); exit;
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');

        $db     = Database::getConnection();
        $lastId = (int)($_GET['last'] ?? 0);

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM feedbacks WHERE id > ?"
        );
        $stmt->execute([$lastId]);
        $newFeedbacks = (int)$stmt->fetchColumn();

        $stmt2 = $db->query("SELECT MAX(id) FROM feedbacks");
        $maxId = (int)$stmt2->fetchColumn();

        $data = json_encode([
            'feedbacks' => $newFeedbacks,
            'maxId'     => $maxId,
        ]);

        echo "data: $data\n\n";
        ob_flush(); flush();
    }
}