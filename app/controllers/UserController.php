<?php
class UserController {

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Accès refusé']);
            exit;
        }
    }

    public function save(): void {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $id       = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';
        $role     = in_array($_POST['role'] ?? '', ['admin', 'membre'])
                    ? $_POST['role'] : 'membre';

        if (strlen($username) < 2) {
            echo json_encode(['status' => 'error', 'message' => 'Nom trop court']); return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Email invalide']); return;
        }

        $userModel = new User;

        try {
            if ($id) {
                if ($userModel->emailExistsExcept($email, $id)) {
                    echo json_encode(['status' => 'error', 'message' => 'Email déjà utilisé']); return;
                }
                if (!empty($password) && strlen($password) < 8) {
                    echo json_encode(['status' => 'error', 'message' => 'Mot de passe trop court (min. 8)']); return;
                }

                $userModel->update($id, $username, $email, $role);

                if (!empty($password)) {
                    $userModel->updatePassword($id, $password);
                }

                echo json_encode(['status' => 'success', 'message' => 'Utilisateur mis à jour']);

            } else {
                if (empty($password) || strlen($password) < 8) {
                    echo json_encode(['status' => 'error', 'message' => 'Mot de passe requis (min. 8)']); return;
                }
                if ($userModel->emailExists($email)) {
                    echo json_encode(['status' => 'error', 'message' => 'Email déjà utilisé']); return;
                }

                $userModel->create($username, $email, $password, $role);
                $new = $userModel->findByEmail($email);

                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Utilisateur créé',
                    'user'    => [
                        'id'       => $new['id'],
                        'username' => $new['username'],
                        'email'    => $new['email'],
                        'role'     => $new['role'],
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
        if ($id === (int)$_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'message' => 'Impossible de vous supprimer vous-même']); return;
        }

        try {
            (new User)->delete($id);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}