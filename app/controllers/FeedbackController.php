<?php
class FeedbackController {

    public function send(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Non connecté']); return;
        }

        $message = trim($_POST['message'] ?? '');
        $eventId = !empty($_POST['event_id']) ? (int)$_POST['event_id'] : null;
        $note    = !empty($_POST['note'])     ? (int)$_POST['note']     : null;

        if (strlen($message) < 5) {
            echo json_encode(['status' => 'error', 'message' => 'Message trop court (min. 5 caractères)']); return;
        }
        if ($note !== null && ($note < 1 || $note > 5)) {
            echo json_encode(['status' => 'error', 'message' => 'Note invalide (1 à 5)']); return;
        }

        try {
            (new Feedback)->create($_SESSION['user_id'], $eventId, $message, $note);
            echo json_encode(['status' => 'success', 'message' => 'Feedback envoyé, merci !']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

public function deleteMine(): void {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Non connecté']); return;
    }

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID invalide']); return;
    }

    try {
        $ok = (new Feedback)->deleteByUser($id, (int)$_SESSION['user_id']);
        if ($ok) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Feedback introuvable']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

public function updateMine(): void {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Non connecté']); return;
    }

    $id      = (int)($_POST['id']      ?? 0);
    $message = trim($_POST['message']  ?? '');
    $note    = !empty($_POST['note'])  ? (int)$_POST['note'] : null;

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID invalide']); return;
    }
    if (strlen($message) < 5) {
        echo json_encode(['status' => 'error', 'message' => 'Message trop court (min. 5 caractères)']); return;
    }
    if ($note !== null && ($note < 1 || $note > 5)) {
        echo json_encode(['status' => 'error', 'message' => 'Note invalide']); return;
    }

    try {
        $ok = (new Feedback)->updateByUser($id, (int)$_SESSION['user_id'], $message, $note);
        if ($ok) {
            echo json_encode(['status' => 'success', 'message' => 'Feedback modifié', 'note' => $note, 'message_text' => $message]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Feedback introuvable']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

    public function delete(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Accès refusé']); return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID invalide']); return;
        }

        try {
            (new Feedback)->delete($id);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}