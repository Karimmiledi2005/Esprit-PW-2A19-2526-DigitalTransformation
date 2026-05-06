<?php
/**
 * env.php — Chargeur de variables d'environnement depuis .env
 * Appelé une seule fois au démarrage (via config/database.php ou index.php)
 */
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return; // Pas de fichier .env → on utilise les variables système
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Ignorer les commentaires
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        // Séparer clé=valeur
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Ne pas écraser une variable déjà définie par le système
        if (!array_key_exists($key, $_ENV) && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Charger le .env à la racine du projet (2 niveaux au-dessus de /config)
loadEnv(dirname(__DIR__) . '/.env');
