<?php
class HomeController {

    public function index(): void {
        $events     = (new Event)->getAllWithStats();
        $userEvents = [];

        if (isset($_SESSION['user_id'])) {
            $userEvents = (new Participation)->getEventIdsByUser($_SESSION['user_id']);
        }

        $totalEvents       = count($events);
        $totalParticipants = array_sum(array_column($events, 'total_participants'));

        require __DIR__ . '/../views/home/index.php';
    }
}