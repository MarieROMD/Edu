<?php
class EventController {

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Accès refusé']);
            exit;
        }
    }

    public function join(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté']); return;
        }

        $eventId = (int)($_POST['id'] ?? 0);
        if (!$eventId) {
            echo json_encode(['success' => false, 'message' => 'Événement invalide']); return;
        }

        $participation = new Participation;
        $success = $participation->join($_SESSION['user_id'], $eventId);

        if ($success) {
            $user  = (new User)->findById($_SESSION['user_id']);
            $event = (new Event)->findById($eventId);
            if ($user && $event) {
                EmailService::sendConfirmation(
                    $user['email'],
                    $user['username'],
                    $event['titre'],
                    $event['date_event'],
                    $event['id']
                );
            }
            //───────────────────────────────────
            echo json_encode(['success' => true, 'message' => 'Inscription réussie']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Événement complet ou déjà inscrit']);
        }
    }

    public function save(): void {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $id          = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $titre       = trim($_POST['titre']       ?? '');
        $description = trim($_POST['description'] ?? '');
        $dateEvent   = trim($_POST['date_event']  ?? '');
        $capacite    = (int)($_POST['capacite']   ?? 0);
        $imageUrl    = trim($_POST['image_url']   ?? '');

        if (strlen($titre) < 2) {
            echo json_encode(['status' => 'error', 'message' => 'Titre trop court']); return;
        }
        if (empty($dateEvent)) {
            echo json_encode(['status' => 'error', 'message' => 'Date requise']); return;
        }
        if ($capacite < 1) {
            echo json_encode(['status' => 'error', 'message' => 'Capacité invalide']); return;
        }

        $eventModel = new Event;

        try {
            if ($id) {
                $eventModel->update($id, $titre, $description, $dateEvent, $capacite, $imageUrl);
                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Événement mis à jour',
                ]);
            } else {
                $eventModel->create($titre, $description, $dateEvent, $capacite, $imageUrl);
                $newId = $eventModel->getLastInsertedId();
                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Événement créé',
                    'event'   => [
                        'id'          => $newId,
                        'titre'       => $titre,
                        'description' => $description,
                        'date_event'  => $dateEvent,
                        'capacite'    => $capacite,
                        'image_url'   => $imageUrl,
                    ]
                ]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete(): void {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID invalide']); return;
        }

        try {
            (new Event)->delete($id);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    public function leave(): void {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Non connecté'
        ]);
        return;
    }

    $eventId = (int)($_POST['id'] ?? 0);

    if (!$eventId) {
        echo json_encode([
            'success' => false,
            'message' => 'Événement invalide'
        ]);
        return;
    }

    try {
        $participation = new Participation();

        if (!$participation->isJoined($_SESSION['user_id'], $eventId)) {
            echo json_encode([
                'success' => false,
                'message' => 'Vous n’êtes pas inscrit à cet événement'
            ]);
            return;
        }

        $participation->leave($_SESSION['user_id'], $eventId);

        echo json_encode([
            'success' => true,
            'message' => 'Désinscription réussie'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
}