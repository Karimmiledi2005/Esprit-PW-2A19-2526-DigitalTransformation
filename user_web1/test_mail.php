<?php
require_once __DIR__ . '/mailer/Mailer.php';

try {
    $mailer = new Mailer();
    $mailer->sendWelcome('Medkarimmiledi@gmail.com', 'Test', 'Karim');
    echo "✅ Email envoyé avec succès !";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}