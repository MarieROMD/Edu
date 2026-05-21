<?php
class DashboardController {

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: /Edu/public/login");
            exit;
        }
    }

    public function admin(): void {
        $this->requireAdmin();
        $statsModel   = new Stats;
        $feedbackModel = new Feedback;

        $stats        = $statsModel->getSummary();
        $recentUsers  = $statsModel->getRecentUsers();
        $recentEvents = $statsModel->getRecentEvents();
        $chart        = $statsModel->getUsersChart();
          $feedbacks     = $feedbackModel->getAll();       
        $feedbackStats = $feedbackModel->getStats(); 
        $activeTab    = 'dashboard';
        require __DIR__ . '/../views/dashboard/admin.php';
    }

    public function users(): void {
        $this->requireAdmin();
        $stats     = (new Stats)->getSummary();
         $feedbackStats = (new Feedback)->getStats();   

        $users     = (new User)->getAll();
        $activeTab = 'users';
        require __DIR__ . '/../views/dashboard/admin.php';
    }
public function events(): void {
    $this->requireAdmin();
    $stats     = (new Stats)->getSummary();
        $feedbackStats = (new Feedback)->getStats();   

    $events    = (new Event)->getAllWithStats();
    $activeTab = 'events';
    require __DIR__ . '/../views/dashboard/admin.php';
}
 public function categories(): void {
      $this->requireAdmin();
      $stats      = (new Stats)->getSummary();
          $feedbackStats = (new Feedback)->getStats();   

      $categories = (new Category)->getAll();
      $activeTab  = 'categories';
      require __DIR__ . '/../views/dashboard/admin.php';
  }
  public function participations(): void {
    $this->requireAdmin();
    $statsModel      = new Stats;
    $stats           = $statsModel->getSummary();
        $feedbackStats = (new Feedback)->getStats();   

    $eventStats      = $statsModel->getParticipationsByEvent();
    $activeTab       = 'participations';
    require __DIR__ . '/../views/dashboard/admin.php';
}

public function feedbacks(): void {
    $this->requireAdmin();
    $feedbackModel = new Feedback;
    $stats         = (new Stats)->getSummary();
    $feedbacks     = $feedbackModel->getAll();
    $feedbackStats = $feedbackModel->getStats();
    $activeTab     = 'feedbacks';
    require __DIR__ . '/../views/dashboard/admin.php';
}
    public function client(): void {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /Edu/public/login");
            exit;
        }
       $userId        = (int)$_SESSION['user_id'];
    $participation = new Participation;
    $eventModel    = new Event;

  
    $events        = $eventModel->getAllWithStats();

   
    $userEventIds  = $participation->getEventIdsByUser($userId);

   
    $myEvents      = $participation->getMyEvents($userId);
    $myFeedbacks   = (new Feedback)->getByUser($userId);  


    require __DIR__ . '/../views/dashboard/client.php';
    }
}