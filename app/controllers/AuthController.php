<?php
class AuthController {

    public function loginForm(): void {
        require __DIR__ . '/../views/auth/login.php';
    }
public function login(): void {
    $user = (new User)->findByEmail($_POST['email']);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: /Edu/public/dashboard/admin");

        } else {
            header("Location: /Edu/public/dashboard/client");
        }
        exit; 
        
    } else {
        $error = "Email ou mot de passe incorrect";
        require __DIR__ . '/../views/auth/login.php';
    }
}

    public function registerForm(): void {
        require __DIR__ . '/../views/auth/register.php';
    }

    public function register(): void {
        $userModel = new User;

        if ($userModel->emailExists($_POST['email'])) {
            $error = "Email déjà utilisé";
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        $userModel->create($_POST['username'], $_POST['email'], $_POST['password']);
header("Location: /Edu/public/index.php/login");
        exit;
    }

    public function logout(): void {
        session_destroy();
        header("Location: /Edu/public/");
        exit;
    }
}