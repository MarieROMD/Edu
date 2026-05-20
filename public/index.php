<?php
session_start();

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';

require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Event.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Participation.php';
require_once __DIR__ . '/../app/models/Stats.php';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/EventController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';
require_once __DIR__ . '/../app/controllers/CategoryController.php';
require_once __DIR__ . '/../app/models/Feedback.php';
require_once __DIR__ . '/../app/controllers/FeedbackController.php';
$router = new Router('/Edu/public');

$router->get('/',             [HomeController::class,   'index']);
$router->post('/events/join', [EventController::class,  'join']);

$router->get('/login',      [AuthController::class, 'loginForm']);
$router->post('/login',     [AuthController::class, 'login']);
$router->get('/register',   [AuthController::class, 'registerForm']);
$router->post('/register',  [AuthController::class, 'register']);
$router->get('/logout',     [AuthController::class, 'logout']);


$router->get('/dashboard/admin',  [DashboardController::class, 'admin']);
$router->get('/dashboard/client', [DashboardController::class, 'client']);

$router->get('/admin/users', [DashboardController::class, 'users']);
$router->get('/admin/events', [DashboardController::class, 'events']);
$router->get('/admin/categories',        [DashboardController::class, 'categories']);
$router->post('/feedback/send',   [FeedbackController::class, 'send']);
$router->post('/feedback/delete', [FeedbackController::class, 'delete']);
$router->post('/feedback/delete-mine', [FeedbackController::class, 'deleteMine']);
$router->post('/feedback/update-mine', [FeedbackController::class, 'updateMine']);
$router->get('/admin/feedbacks',  [DashboardController::class, 'feedbacks']);

$router->post('/admin/users/save',   [UserController::class, 'save']);
$router->post('/admin/users/delete', [UserController::class, 'delete']);
$router->post('/admin/events/save',   [EventController::class, 'save']);
$router->post('/admin/events/delete', [EventController::class, 'delete']);
$router->post('/admin/categories/save',   [CategoryController::class, 'save']);
$router->post('/admin/categories/delete', [CategoryController::class, 'delete']);
$router->get('/admin/participations', [DashboardController::class, 'participations']);
$router->get('/events',          [EventController::class, 'index']);
$router->get('/events/create',   [EventController::class, 'createForm']);
$router->post('/events/store',   [EventController::class, 'store']);
$router->post('/events/delete',  [EventController::class, 'delete']);
$router->post('/events/join',  [EventController::class, 'join']);
$router->post('/events/leave', [EventController::class, 'leave']);

$router->dispatch();