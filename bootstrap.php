<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Génération d'un jeton CSRF unique s'il n'existe pas
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/connexion.php';
require_once __DIR__ . '/helpers/CsrfHelper.php';

// Load service configuration (SMTP credentials, feature flags)
if (file_exists(__DIR__ . '/config_services.php')) {
    require_once __DIR__ . '/config_services.php';
}

require_once __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!isset($_ENV[$name])) $_ENV[$name] = $value;
            if (!isset($_SERVER[$name])) $_SERVER[$name] = $value;
            putenv("$name=$value");
        }
    }
}
require_once __DIR__ . '/model/User.php';
require_once __DIR__ . '/model/Contrat.php';
require_once __DIR__ . '/model/Sinistre.php';
require_once __DIR__ . '/model/Traitement.php';
require_once __DIR__ . '/model/ReclamationModel.php';
require_once __DIR__ . '/model/ReponseModel.php';

require_once __DIR__ . '/controller/ContratController.php';
require_once __DIR__ . '/controller/SinistreController.php';
require_once __DIR__ . '/controller/TraitementController.php';
require_once __DIR__ . '/controller/ReclamationController.php';
require_once __DIR__ . '/controller/ReponseController.php';
require_once __DIR__ . '/helpers/RoleHelper.php';


// Safe includes for optional services
if (file_exists(__DIR__ . '/controller/FraudeService.php')) {
    require_once __DIR__ . '/controller/FraudeService.php';
} elseif (file_exists(__DIR__ . '/model/FraudeService.php')) {
    require_once __DIR__ . '/model/FraudeService.php';
}

// Optional lightweight mailer helper (application-level mail utils)
if (file_exists(__DIR__ . '/mailer/mailer.php')) {
    require_once __DIR__ . '/mailer/mailer.php';
}
// Ensure service-level static mailer is available when present.
if (file_exists(__DIR__ . '/service/EmailService.php')) {
    require_once __DIR__ . '/service/EmailService.php';
} elseif (file_exists(__DIR__ . '/model/EmailService.php')) {
    require_once __DIR__ . '/model/EmailService.php';
}
