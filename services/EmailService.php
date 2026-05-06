<?php
// ============================================================
//  services/EmailService.php
//  Envoi email HTML via PHPMailer + Gmail SMTP
//  Architecture identique au projet GaiaLumen
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config_services.php';

class EmailService
{
    // ── Configuration du badge selon le type de réponse ──────────────────────
    private static function typeConfig(string $type): array
    {
        return match ($type) {
            'rejet'   => [
                'couleur'  => '#ef4444',
                'bg'       => '#fef2f2',
                'emoji'    => '❌',
                'libelle'  => 'Réclamation rejetée',
                'intro'    => 'Nous avons examiné votre réclamation et malheureusement nous ne pouvons pas y donner suite pour la raison suivante :',
            ],
            default   => [
                'couleur'  => '#22c55e',
                'bg'       => '#f0fdf4',
                'emoji'    => '✅',
                'libelle'  => 'Réclamation résolue',
                'intro'    => 'Nous avons examiné votre réclamation et voici notre réponse :',
            ],
        };
    }

    // ── Construction du template HTML ────────────────────────────────────────
    private static function buildTemplate(string $objet, string $contenu, string $type): string
    {
        $cfg         = self::typeConfig($type);
        $contenuHtml = nl2br(htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'));

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0"
           style="background:#ffffff;border-radius:16px;overflow:hidden;
                  box-shadow:0 4px 24px rgba(0,0,0,.10);max-width:600px;width:100%;">

      <!-- EN-TÊTE BLEU PROTEX -->
      <tr>
        <td style="background:linear-gradient(135deg,#1e3a5f 0%,#23458f 100%);padding:36px 40px;text-align:center;">
          <h1 style="margin:0;color:#ffffff;font-size:28px;font-weight:800;letter-spacing:1px;">
            PROTEX
          </h1>
          <p style="margin:8px 0 0;color:rgba(255,255,255,.70);font-size:12px;letter-spacing:2px;text-transform:uppercase;">
            Assurance Digitale
          </p>
        </td>
      </tr>

      <!-- BADGE STATUT -->
      <tr>
        <td style="padding:32px 40px 0;text-align:center;">
          <span style="display:inline-block;background:{$cfg['couleur']};color:#fff;
                       font-size:14px;font-weight:700;padding:8px 24px;border-radius:24px;">
            {$cfg['emoji']} {$cfg['libelle']}
          </span>
        </td>
      </tr>

      <!-- CORPS -->
      <tr>
        <td style="padding:28px 40px;">

          <!-- Objet de la réclamation -->
          <table width="100%" cellpadding="0" cellspacing="0"
                 style="background:#f8fafc;border-radius:10px;overflow:hidden;margin-bottom:24px;">
            <tr>
              <td style="padding:16px 20px;">
                <span style="color:#94a3b8;font-size:11px;text-transform:uppercase;letter-spacing:1px;">
                  Réclamation concernée
                </span><br>
                <strong style="color:#1e293b;font-size:15px;">{$objet}</strong>
              </td>
            </tr>
          </table>

          <!-- Intro -->
          <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.7;">
            {$cfg['intro']}
          </p>

          <!-- Contenu de la réponse -->
          <div style="background:{$cfg['bg']};border-left:4px solid {$cfg['couleur']};
                      border-radius:0 10px 10px 0;padding:20px;
                      font-size:14px;color:#334155;line-height:1.8;margin-bottom:28px;">
            {$contenuHtml}
          </div>

          <p style="margin:0;color:#64748b;font-size:13px;line-height:1.7;">
            Pour toute question complémentaire, n'hésitez pas à nous contacter.<br>
            Merci de votre confiance.
          </p>
        </td>
      </tr>

      <!-- PIED DE PAGE -->
      <tr>
        <td style="background:#1e3a5f;padding:24px 40px;text-align:center;">
          <p style="margin:0;color:rgba(255,255,255,.60);font-size:12px;line-height:1.6;">
            © 2026 Protex Assurance<br>
            Email automatique, merci de ne pas y répondre.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    // ── Méthode publique : envoi de la notification ───────────────────────────
    public static function envoyerNotificationReponse(
        string $email,
        string $objet,
        string $contenu,
        string $type = 'reponse'   // 'reponse' ou 'rejet'
    ): bool {

        if (empty(trim($email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log('[PROTEX MAIL] Adresse email invalide ou manquante : ' . $email);
            return false;
        }

        $cfg  = self::typeConfig($type);
        $mail = new PHPMailer(true);

        try {
            // ── Configuration SMTP Gmail ───────────────────────────
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_SMTP_USER;
            $mail->Password   = MAIL_SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // ── Expéditeur et destinataire ─────────────────────────
            $mail->setFrom(MAIL_SMTP_USER, MAIL_FROM_NAME);
            $mail->addAddress($email);

            // ── Contenu ────────────────────────────────────────────
            $mail->isHTML(true);
            $mail->Subject = "{$cfg['emoji']} [Protex] {$cfg['libelle']} : {$objet}";
            $mail->Body    = self::buildTemplate($objet, $contenu, $type);
            $mail->AltBody = "Bonjour,\n\n{$cfg['intro']}\n\n{$contenu}\n\nCordialement,\nL'équipe Protex Assurance";

            $mail->send();
            error_log("[PROTEX MAIL OK] → {$email} | type: {$type} | objet: {$objet}");
            return true;

        } catch (Exception $e) {
            error_log('[PROTEX MAIL ERREUR] → ' . $email . ' : ' . $mail->ErrorInfo);
            return false;
        }
    }
}
