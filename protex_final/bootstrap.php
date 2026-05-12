<?php
/**
 * bootstrap.php — Point d'entrée commun
 * FIX P-03 : vendor/autoload.php rendu optionnel (conditionné à file_exists)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jeton CSRF unique par session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers/CsrfHelper.php';
require_once __DIR__ . '/helpers/RoleHelper.php';

// Composer autoload (optionnel — PHPMailer peut être chargé manuellement)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Variables d'environnement depuis config.env.php (déjà chargé dans config.php via require)
// Les constantes MAIL_* sont définies par config_services.php si présent
if (file_exists(__DIR__ . '/config_services.php')) {
    require_once __DIR__ . '/config_services.php';
}

// Modèles
require_once __DIR__ . '/model/User.php';
require_once __DIR__ . '/model/Contrat.php';
require_once __DIR__ . '/model/Sinistre.php';
require_once __DIR__ . '/model/Traitement.php';
require_once __DIR__ . '/model/ReclamationModel.php';
require_once __DIR__ . '/model/ReponseModel.php';
require_once __DIR__ . '/model/Devis.php';
require_once __DIR__ . '/model/Offre.php';
require_once __DIR__ . '/model/Paiement.php';
require_once __DIR__ . '/model/Roulette.php';

// Contrôleurs
require_once __DIR__ . '/controller/ContratController.php';
require_once __DIR__ . '/controller/SinistreController.php';
require_once __DIR__ . '/controller/TraitementController.php';
require_once __DIR__ . '/controller/ReclamationController.php';
require_once __DIR__ . '/controller/ReponseController.php';

// Services
// FIX P-05 : EmailService unique — controller/EmailService.php en priorité
if (!class_exists('EmailService')) {
    if (file_exists(__DIR__ . '/controller/EmailService.php')) {
        require_once __DIR__ . '/controller/EmailService.php';
    }
}

// Service antifraud (optionnel)
if (!class_exists('FraudeService') && file_exists(__DIR__ . '/controller/FraudeService.php')) {
    require_once __DIR__ . '/controller/FraudeService.php';
}

// Mailer léger
if (file_exists(__DIR__ . '/mailer/mailer.php')) {
    require_once __DIR__ . '/mailer/mailer.php';
}
