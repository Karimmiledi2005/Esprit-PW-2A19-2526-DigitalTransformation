<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class Mailer
{
    private string $fromEmail = 'Medkarimmiledi@gmail.com'; // ← remplace par ton Gmail
    private string $fromName  = 'Protex';
    private string $password  = 'mrsyybpvjpxwysbx'; // ← mot de passe app Google

    private function createMailer(): PHPMailer
    {
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
                <h2 style='color:#2563eb'>Bienvenue {$prenom} {$nom} 👋</h2>
                <p>Votre compte <strong>AssuranceConnect</strong> a été créé avec succès.</p>
                <p>Vous pouvez dès maintenant vous connecter et gérer vos assurances.</p>
                <hr>
                <p style='color:#888;font-size:12px'>L'équipe AssuranceConnect</p>
            </div>
        ";
        $mail->send();
    }

    public function sendCompteBloque(string $toEmail, string $nom): void
    {
        $mail = $this->createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Votre compte a été suspendu - AssuranceConnect';
        $mail->Body    = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto'>
                <h2 style='color:#dc2626'>Compte suspendu</h2>
                <p>Bonjour <strong>{$nom}</strong>,</p>
                <p>Votre compte AssuranceConnect a été <strong>suspendu</strong> par un administrateur.</p>
                <p>Pour plus d'informations, contactez notre support.</p>
                <hr>
                <p style='color:#888;font-size:12px'>L'équipe AssuranceConnect</p>
            </div>
        ";
        $mail->send();
    }

    public function sendCompteDebloque(string $toEmail, string $nom): void
    {
        $mail = $this->createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Votre compte a été réactivé - AssuranceConnect';
        $mail->Body    = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto'>
                <h2 style='color:#16a34a'>Compte réactivé ✅</h2>
                <p>Bonjour <strong>{$nom}</strong>,</p>
                <p>Votre compte AssuranceConnect a été <strong>réactivé</strong>.</p>
                <p>Vous pouvez vous connecter normalement.</p>
                <hr>
                <p style='color:#888;font-size:12px'>L'équipe AssuranceConnect</p>
            </div>
        ";
        $mail->send();
    }

    public function sendOTP(string $toEmail, string $nom, string $otp): void
    {
        $mail = $this->createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Votre code de connexion - AssuranceConnect';
        $mail->Body    = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto'>
                <h2 style='color:#2563eb'>Code de vérification</h2>
                <p>Bonjour <strong>{$nom}</strong>,</p>
                <p>Votre code de connexion est :</p>
                <div style='text-align:center;margin:30px 0'>
                    <span style='font-size:40px;font-weight:bold;letter-spacing:12px;color:#2563eb'>
                        {$otp}
                    </span>
                </div>
                <p>⏱ Ce code expire dans <strong>5 minutes</strong>.</p>
                <p style='color:#888;font-size:12px'>Si vous n'avez pas demandé ce code, ignorez cet email.</p>
                <hr>
                <p style='color:#888;font-size:12px'>L'équipe AssuranceConnect</p>
            </div>
        ";
        $mail->send();
    }
}