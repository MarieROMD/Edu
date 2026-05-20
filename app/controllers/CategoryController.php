<?php
class CategoryController {

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Accès refusé']);
            exit;
        }
    }

    private function json(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function save(): void {
        $this->requireAdmin();
        $id  = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $nom = trim($_POST['nom'] ?? '');

        if ($nom === '') {
            $this->json(['error' => 'Le nom est requis.']);
        }

        $model = new Category;

        if ($id) {
            
            if ($model->nameExistsExcept($nom, $id)) {
                $this->json(['error' => 'Ce nom de catégorie existe déjà.']);
            }
            $model->update($id, $nom);
            $this->json(['success' => true, 'message' => 'Catégorie mise à jour.']);
        } else {
            if ($model->nameExists($nom)) {
                $this->json(['error' => 'Ce nom de catégorie existe déjà.']);
            }
            $model->create($nom);
            $this->json(['success' => true, 'message' => 'Catégorie créée.']);
        }
    }

    public function delete(): void {
        $this->requireAdmin();
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $this->json(['error' => 'ID invalide.']);
        }

        $model = new Category;
        if (!$model->findById($id)) {
            $this->json(['error' => 'Catégorie introuvable.']);
        }

        $model->delete($id);
        $this->json(['success' => true, 'message' => 'Catégorie supprimée.']);
    }
}