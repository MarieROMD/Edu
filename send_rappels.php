<?php
/**
 * Script de rappel 24h avant les événements
 * À lancer chaque jour via CRON :
 *   0 8 * * * php /chemin/vers/Edu/send_rappels.php >> /tmp/rappels.log 2>&1
 */

// ── Bootstrap ─────────────────────────────────────────────────────────────
define('APP_ROOT', __DIR__);

require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/app/models/Event.php';
require_once APP_ROOT . '/app/models/User.php';
require_once APP_ROOT . '/app/models/Participation.php';
require_once APP_ROOT . '/app/services/EmailService.php';

// ── Récupérer les events qui ont lieu DEMAIN ───────────────────────────────
$db = Database::getConnection();

$stmt = $db->prepare("
    SELECT p.user_id, p.rappel_envoye,
           u.email, u.username,
           e.id AS event_id, e.titre, e.date_event
    FROM participations p
    JOIN users u      ON u.id = p.user_id
    JOIN evenements e ON e.id = p.event_id
    WHERE DATE(e.date_event) = CURDATE() + INTERVAL 1 DAY
      AND p.rappel_envoye = 0
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo date('[Y-m-d H:i:s]') . " Rappels à envoyer : " . count($rows) . "\n";

$sent = 0;
$fail = 0;

foreach ($rows as $row) {
    $ok = EmailService::sendRappel(
        $row['email'],
        $row['username'],
        $row['titre'],
        $row['date_event']
    );

    if ($ok) {
        // Marquer comme envoyé pour ne pas renvoyer
        $upd = $db->prepare("
            UPDATE participations
            SET rappel_envoye = 1
            WHERE user_id = ? AND event_id = ?
        ");
        $upd->execute([$row['user_id'], $row['event_id']]);
        $sent++;
        echo "  ✅ Rappel envoyé à {$row['email']} pour « {$row['titre']} »\n";
    } else {
        $fail++;
        echo "  ❌ Échec envoi à {$row['email']} pour « {$row['titre']} »\n";
    }
}

echo date('[Y-m-d H:i:s]') . " Terminé — Envoyés: $sent | Échecs: $fail\n";