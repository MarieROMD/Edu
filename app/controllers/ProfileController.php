<?php
class ProfileController {

    private function requireAuth(): void {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /Edu/public/login"); exit;
        }
    }

    public function show(): void {
        $this->requireAuth();
        $user      = (new User)->findById($_SESSION['user_id']);
        $success   = $_GET['success'] ?? null;
        $error     = $_GET['error']   ?? null;
        require __DIR__ . '/../views/profile/show.php';
    }

    public function update(): void {
        $this->requireAuth();
        $userModel = new User;
        $userId    = (int)$_SESSION['user_id'];

        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email']    ?? '');
        $password  =      $_POST['password'] ?? '';
        $confirm   =      $_POST['confirm']  ?? '';

        if (strlen($username) < 2) {
            header("Location: /Edu/public/profile?error=username"); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: /Edu/public/profile?error=email"); exit;
        }

        $ok = $userModel->updateProfile($userId, $username, $email);
        if (!$ok) {
            header("Location: /Edu/public/profile?error=emailused"); exit;
        }

        if (!empty($password)) {
            if (strlen($password) < 8) {
                header("Location: /Edu/public/profile?error=pwd"); exit;
            }
            if ($password !== $confirm) {
                header("Location: /Edu/public/profile?error=confirm"); exit;
            }
            $userModel->updatePassword($userId, $password);
        }

        $_SESSION['username'] = $username;
        header("Location: /Edu/public/profile?success=1"); exit;
    }
}