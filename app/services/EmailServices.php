<?php
class EmailService {

    private static string $fromEmail = 'noreply@eduevents.fr';
    private static string $fromName  = 'EduEvents';

    // ─── Email de confirmation d'inscription ───────────────────────────────
    public static function sendConfirmation(
        string $toEmail,
        string $toName,
        string $eventTitre,
        string $eventDate,
        int    $eventId
    ): bool {
        $subject = "✅ Inscription confirmée — " . $eventTitre;

        $dateFormatee = self::formatDate($eventDate);

        $body = self::template("Inscription confirmée !", "
            <p>Bonjour <strong>" . htmlspecialchars($toName) . "</strong>,</p>
            <p>Votre inscription à l'événement suivant a bien été enregistrée :</p>

            <div style='background:#F5F3FF;border-left:4px solid #534AB7;
                        padding:16px 20px;border-radius:8px;margin:20px 0;'>
                <div style='font-size:1.1rem;font-weight:600;color:#3C3489;margin-bottom:6px;'>
                    " . htmlspecialchars($eventTitre) . "
                </div>
                <div style='color:#534AB7;font-size:.9rem;'>📅 " . $dateFormatee . "</div>
            </div>

            <p style='color:#6b7280;font-size:.9rem;'>
                Vous recevrez un rappel 24h avant l'événement.<br>
                En cas d'empêchement, pensez à vous désinscrire depuis votre espace.
            </p>
        ");

        return self::send($toEmail, $subject, $body);
    }

    // ─── Email de rappel 24h avant l'événement ────────────────────────────
    public static function sendRappel(
        string $toEmail,
        string $toName,
        string $eventTitre,
        string $eventDate
    ): bool {
        $subject = "⏰ Rappel — " . $eventTitre . " c'est demain !";

        $dateFormatee = self::formatDate($eventDate);

        $body = self::template("C'est demain !", "
            <p>Bonjour <strong>" . htmlspecialchars($toName) . "</strong>,</p>
            <p>Petit rappel : vous êtes inscrit à un événement <strong>demain</strong> !</p>

            <div style='background:#E1F5EE;border-left:4px solid #0F6E56;
                        padding:16px 20px;border-radius:8px;margin:20px 0;'>
                <div style='font-size:1.1rem;font-weight:600;color:#085041;margin-bottom:6px;'>
                    " . htmlspecialchars($eventTitre) . "
                </div>
                <div style='color:#0F6E56;font-size:.9rem;'>📅 " . $dateFormatee . "</div>
            </div>

            <p style='color:#6b7280;font-size:.9rem;'>
                N'oubliez pas d'être à l'heure. À demain ! 🎉
            </p>
        ");

        return self::send($toEmail, $subject, $body);
    }

    // ─── Envoi bas niveau ─────────────────────────────────────────────────
    private static function send(string $to, string $subject, string $htmlBody): bool {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . self::$fromName . " <" . self::$fromEmail . ">\r\n";
        $headers .= "Reply-To: " . self::$fromEmail . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        return mail($to, $subject, $htmlBody, $headers);
    }

    // ─── Template HTML commun ─────────────────────────────────────────────
    private static function template(string $titre, string $contenu): string {
        return '<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f8f7f5;font-family:system-ui,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f7f5;padding:40px 20px;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0"
             style="background:#fff;border-radius:16px;overflow:hidden;
                    border:1px solid #e5e3de;max-width:560px;width:100%;">

        <!-- Header -->
        <tr>
          <td style="background:#534AB7;padding:24px 32px;">
            <div style="font-size:1.3rem;font-weight:700;color:#fff;letter-spacing:-.3px;">
              ⚡ EduEvents
            </div>
          </td>
        </tr>

        <!-- Titre -->
        <tr>
          <td style="padding:28px 32px 0;">
            <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:#1a1a1a;">
              ' . htmlspecialchars($titre) . '
            </h1>
          </td>
        </tr>

        <!-- Contenu -->
        <tr>
          <td style="padding:16px 32px 32px;color:#374151;font-size:.95rem;line-height:1.7;">
            ' . $contenu . '
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f8f7f5;padding:20px 32px;
                     border-top:1px solid #e5e3de;font-size:.8rem;color:#9ca3af;">
            Cet email a été envoyé automatiquement par EduEvents.<br>
            Merci de ne pas répondre à ce message.
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';
    }

    // ─── Formatage date ───────────────────────────────────────────────────
    private static function formatDate(string $dateStr): string {
        $mois = [
            1=>'janvier',2=>'février',3=>'mars',4=>'avril',
            5=>'mai',6=>'juin',7=>'juillet',8=>'août',
            9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'
        ];
        $ts = strtotime($dateStr);
        if ($ts === false) return $dateStr;
        return date('d', $ts) . ' ' . $mois[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    }
}