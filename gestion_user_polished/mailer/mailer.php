<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class Mailer
{
    private string $fromEmail = 'muledikarim@gmail.com';
    private string $fromName  = 'Protex';
    private string $password  = 'tpgpkkqwvnypvovj';

    private function createMailer(): PHPMailer
    {
        if (!class_exists(PHPMailer::class)) {
            throw new \RuntimeException('PHPMailer indisponible');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->fromEmail;
        $mail->Password   = $this->password;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom($this->fromEmail, $this->fromName);
        return $mail;
    }

    public function sendWelcome(string $toEmail, string $nom, string $prenom): void
    {
        $mail = $this->createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenue sur AssuranceConnect';
        $mail->Body    = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto'>
                <h2 style='color:#2563eb'>Bienvenue {$prenom} {$nom} ðŸ‘‹</h2>
                <p>Votre compte <strong>AssuranceConnect</strong> a Ã©tÃ© crÃ©Ã© avec succÃ¨s.</p>
                <p>Vous pouvez dÃ¨s maintenant vous connecter et gÃ©rer vos assurances.</p>
                <hr>
                <p style='color:#888;font-size:12px'>L'Ã©quipe AssuranceConnect</p>
            </div>
        ";
        $mail->send();
    }

    public function sendCompteBloque(string $toEmail, string $nom): void
    {
        $mail = $this->createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Votre compte a Ã©tÃ© suspendu - AssuranceConnect';
        $mail->Body    = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto'>
                <h2 style='color:#dc2626'>Compte suspendu</h2>
                <p>Bonjour <strong>{$nom}</strong>,</p>
                <p>Votre compte AssuranceConnect a Ã©tÃ© <strong>suspendu</strong> par un administrateur.</p>
                <p>Pour plus d'informations, contactez notre support.</p>
                <hr>
                <p style='color:#888;font-size:12px'>L'Ã©quipe AssuranceConnect</p>
            </div>
        ";
        $mail->send();
    }

    public function sendCompteDebloque(string $toEmail, string $nom): void
    {
        $mail = $this->createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Votre compte a Ã©tÃ© rÃ©activÃ© - AssuranceConnect';
        $mail->Body    = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto'>
                <h2 style='color:#16a34a'>Compte rÃ©activÃ© âœ…</h2>
                <p>Bonjour <strong>{$nom}</strong>,</p>
                <p>Votre compte AssuranceConnect a Ã©tÃ© <strong>rÃ©activÃ©</strong>.</p>
                <p>Vous pouvez vous connecter normalement.</p>
                <hr>
                <p style='color:#888;font-size:12px'>L'Ã©quipe AssuranceConnect</p>
            </div>
        ";
        $mail->send();
    }


    public function sendPasswordReset(string $toEmail, string $prenom, string $link): void
    {
        $mail = $this->createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'RÃ©initialisation de votre mot de passe - Protex';
        $mail->Body    = "
            <div style='font-family:sans-serif; max-width:500px; margin:auto; background:#0a0f1e; color:#fff; padding:40px; border-radius:24px; border:1px solid rgba(255,107,26,0.3)'>
                <div style='text-align:center; margin-bottom:30px'>
                    <h1 style='color:#FF6B1A; margin:0'>Protex</h1>
                </div>
                <h2 style='color:#fff'>Bonjour {$prenom} ðŸ‘‹</h2>
                <p style='color:rgba(255,255,255,0.8); line-height:1.6'>Vous avez demandÃ© la rÃ©initialisation de votre mot de passe Protex.</p>
                <p style='color:rgba(255,255,255,0.8); line-height:1.6'>Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>
                <div style='text-align:center; margin:40px 0'>
                    <a href='{$link}' style='background:#FF6B1A; color:#fff; padding:14px 30px; border-radius:10px; text-decoration:none; font-weight:600; display:inline-block'>RÃ©initialiser mon mot de passe</a>
                </div>
                <p style='color:rgba(255,255,255,0.6); font-size:13px'>Ce lien expirera dans 15 minutes.</p>
                <hr style='border:0; border-top:1px solid rgba(255,255,255,0.1); margin:30px 0'>
                <p style='color:rgba(255,255,255,0.5); font-size:12px; text-align:center'>L'Ã©quipe Protex</p>
            </div>
        ";
        $mail->send();
    }

    public function sendOTP(string $toEmail, string $nom, string $otp): void
    {
        $mail = $this->createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Votre code de vérification - Protex';
        $mail->Body    = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;background:#0a0f1e;color:#fff;padding:40px;border-radius:24px;border:1px solid rgba(255,107,26,0.3)'>
                <div style='text-align:center;margin-bottom:30px'>
                    <h1 style='color:#FF6B1A;margin:0'>Protex</h1>
                </div>
                <h2 style='color:#FF6B1A;text-align:center'>Code de vérification</h2>
                <p style='text-align:center;color:rgba(255,255,255,0.8)'>Bonjour <strong>{$nom}</strong>,</p>
                <p style='text-align:center;color:rgba(255,255,255,0.8)'>Voici votre code de connexion sécurisé :</p>
                <div style='text-align:center;margin:30px 0;background:rgba(255,107,26,0.1);padding:20px;border-radius:12px'>
                    <span style='font-size:42px;font-weight:bold;letter-spacing:10px;color:#FF6B1A'>
                        {$otp}
                    </span>
                </div>
                <p style='text-align:center;color:rgba(255,255,255,0.6);font-size:14px'>⚠️ Ce code expire dans <strong>5 minutes</strong>.</p>
                <hr style='border:0;border-top:1px solid rgba(255,255,255,0.1);margin:30px 0'>
                <p style='color:rgba(255,255,255,0.4);font-size:12px;text-align:center'>Si vous n'avez pas demandé ce code, vous pouvez ignorer cet e-mail en toute sécurité.</p>
                <p style='color:rgba(255,255,255,0.4);font-size:12px;text-align:center'>L'équipe Protex</p>
            </div>
        ";
        $mail->send();
    }

    public function sendSOS(string $toEmail, string $toPrenom, string $senderNom, string $senderPrenom, ?string $mapsLink = null, ?int $accuracy = null): void
    {
        $mail = $this->createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'ðŸ†˜ ALERTE SOS â€” ' . $senderPrenom . ' ' . $senderNom . ' a besoin d\'aide !';
        $heure = date('d/m/Y Ã  H:i:s');

        // Bloc position GPS
        if ($mapsLink) {
            $precisionStr = $accuracy ? " (prÃ©cision Â±{$accuracy}m)" : '';
            $locationBlock = "
                <div style='background:rgba(255,71,87,0.15);border:1px solid rgba(255,71,87,0.5);border-radius:14px;padding:20px;margin:20px 0;text-align:center'>
                    <div style='font-size:28px'>ðŸ“</div>
                    <p style='color:#fff;font-weight:700;margin:8px 0 4px'>Position GPS localisÃ©e{$precisionStr}</p>
                    <p style='color:rgba(255,255,255,0.6);font-size:12px;margin:0 0 14px'>CoordonnÃ©es transmises au moment de l'alerte</p>
                    <a href='{$mapsLink}' target='_blank'
                       style='background:#ff4757;color:#fff;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px;display:inline-block'>
                        ðŸ—ºï¸ Voir la position sur Google Maps
                    </a>
                </div>";
        } else {
            $locationBlock = "
                <div style='background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.15);border-radius:14px;padding:16px;margin:20px 0;text-align:center'>
                    <p style='color:rgba(255,255,255,0.5);margin:0;font-size:13px'>
                        âš ï¸ Position GPS non disponible â€” la gÃ©olocalisation a Ã©tÃ© refusÃ©e ou dÃ©sactivÃ©e
                    </p>
                </div>";
        }

        $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:580px;margin:auto;background:#0a0f1e;color:#fff;padding:40px;border-radius:20px;border:2px solid #ff4757'>
                <div style='text-align:center;margin-bottom:24px'>
                    <div style='font-size:64px;line-height:1'>ðŸ†˜</div>
                    <h1 style='color:#ff4757;margin:10px 0;font-size:28px;letter-spacing:2px'>ALERTE SOS</h1>
                    <p style='color:rgba(255,255,255,0.5);font-size:13px;margin:0'>{$heure}</p>
                </div>

                <p style='font-size:16px;color:rgba(255,255,255,0.9);margin-bottom:8px'>Bonjour <strong>{$toPrenom}</strong>,</p>
                <p style='font-size:15px;color:rgba(255,255,255,0.8);line-height:1.7;margin-bottom:20px'>
                    Votre contact de confiance <strong style='color:#ff4757'>{$senderPrenom} {$senderNom}</strong>
                    a dÃ©clenchÃ© une <strong>alerte SOS d'urgence</strong> sur la plateforme <strong>Protex</strong>.
                    <br>Vous avez Ã©tÃ© dÃ©signÃ©(e) comme <strong>contact de confiance</strong>.
                </p>

                {$locationBlock}

                <div style='text-align:center;margin:28px 0'>
                    <p style='color:rgba(255,255,255,0.7);margin-bottom:16px;font-weight:600'>Contactez les secours immÃ©diatement :</p>
                    <a href='tel:15' style='background:#ff4757;color:#fff;padding:13px 24px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px;display:inline-block;margin:5px'>
                        ðŸ“ž SAMU â€” 15
                    </a>
                    <a href='tel:17' style='background:rgba(255,71,87,0.3);border:1px solid #ff4757;color:#fff;padding:13px 24px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px;display:inline-block;margin:5px'>
                        ðŸš” Police â€” 17
                    </a>
                    <a href='tel:18' style='background:rgba(255,71,87,0.3);border:1px solid #ff4757;color:#fff;padding:13px 24px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px;display:inline-block;margin:5px'>
                        ðŸš’ Pompiers â€” 18
                    </a>
                </div>

                <hr style='border:0;border-top:1px solid rgba(255,255,255,0.1);margin:28px 0'>
                <p style='color:rgba(255,255,255,0.35);font-size:11px;text-align:center;margin:0'>
                    Cet email a Ã©tÃ© envoyÃ© automatiquement par <strong>Protex</strong> Â· Ne pas rÃ©pondre Ã  cet email
                </p>
            </div>
        ";
        $mail->send();
    }
}
