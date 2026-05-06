<?php
/**
 * config/database.php
 * Connexion PDO — les credentials viennent du fichier .env (jamais en dur dans le code)
 */

require_once __DIR__ . '/env.php'; // Charger le .env

if (!class_exists('config')) {
    class config
    {
        private static ?PDO $pdo = null;

        public static function getConnexion(): PDO
        {
            if (self::$pdo === null) {
                $host   = getenv('DB_HOST') ?: 'localhost';
                $dbname = getenv('DB_NAME') ?: 'assurance';
                $user   = getenv('DB_USER') ?: 'root';
                $pass   = getenv('DB_PASS') ?: '';

                try {
                    self::$pdo = new PDO(
                        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                        $user,
                        $pass,
                        [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES   => false,
                        ]
                    );
                } catch (PDOException $e) {
                    // Ne jamais afficher le message d'erreur brut en production
                    error_log('DB Connection Error: ' . $e->getMessage());
                    die(json_encode([
                        'success' => false,
                        'message' => '⚙️ Erreur de connexion à la base de données. Contactez l\'administrateur.'
                    ]));
                }
            }
            return self::$pdo;
        }
    }
}
