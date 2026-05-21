<?php
ob_start();

require_once __DIR__ . '/../../vendor/autoload.php';

class ExportController {

    public function eventParticipants(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: /Edu/public/login"); exit;
        }

        $eventId      = (int)($_GET['id'] ?? 0);
        $event        = (new Event)->findById($eventId);
        $participants = (new Participation)->getByEvent($eventId);

        if (!$event) { http_response_code(404); echo "Event introuvable"; return; }

        $ts       = isset($event['date_event']) ? strtotime($event['date_event']) : false;
        $subtitle = $ts !== false ? date('d M Y', $ts) : '—';

        $this->generatePdf(
            title:    "Participants — " . $event['titre'],
            subtitle: $subtitle,
            headers:  ['#', 'Nom', 'Email', 'Présence'],
            rows:     array_map(fn($p, $i) => [
                $i + 1,
                $p['username']  ?? '—',
                $p['email']     ?? '—',
                !empty($p['presence']) ? 'Oui' : 'Non',
            ], $participants, array_keys($participants)),
            filename: "participants_event_{$eventId}.pdf"
        );
    }

    public function myEvents(): void {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /Edu/public/login"); exit;
        }

        $myEvents = (new Participation)->getMyEvents($_SESSION['user_id']);
        $username = htmlspecialchars($_SESSION['username'] ?? 'Membre', ENT_QUOTES, 'UTF-8');

        $this->generatePdf(
            title:    "Mes inscriptions — " . $username,
            subtitle: "Exporté le " . date('d M Y'),
            headers:  ['#', 'Événement', 'Date', 'Places restantes'],
            rows:     array_map(fn($e, $i) => [
                $i + 1,
                $e['titre'] ?? '—',
                isset($e['date_event']) && strtotime($e['date_event']) !== false
                    ? date('d M Y', strtotime($e['date_event']))
                    : '—',
                (int)($e['places_restantes'] ?? 0),
            ], $myEvents, array_keys($myEvents)),
            filename: "mes_inscriptions.pdf"
        );
    }

    private function generatePdf(
        string $title,
        string $subtitle,
        array  $headers,
        array  $rows,
        string $filename
    ): void {
        ob_end_clean();

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('EduEvents');
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(83, 74, 183);
        $pdf->Cell(0, 10, $title, 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 6, $subtitle, 0, 1, 'L');
        $pdf->Ln(4);

        $count = count($headers);
        if ($count === 0) return;
        $colW = round(267 / $count, 2);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(83, 74, 183);
        $pdf->SetTextColor(255, 255, 255);
        foreach ($headers as $h) {
            $pdf->Cell($colW, 8, $h, 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(30, 30, 30);
        foreach ($rows as $i => $row) {
            $even = $i % 2 === 0;
            $pdf->SetFillColor(
                $even ? 245 : 255,
                $even ? 244 : 255,
                $even ? 240 : 255
            );
            foreach ($row as $cell) {
                $pdf->Cell($colW, 7, (string)$cell, 1, 0, 'L', true);
            }
            $pdf->Ln();
        }

        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
        $pdf->Output($safeFilename, 'D');
    }
}