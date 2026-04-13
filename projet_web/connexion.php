<?php
// Vérifier si la classe n'existe pas déjà avant de la déclarer
if (!class_exists('connexion')) {
    class config
    {   
        private static $pdo = null;
        
        public static function getConnexion()
        {
            if (!isset(self::$pdo)) {
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "assurance";
                
                try {
                    self::$pdo = new PDO(
                        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
                        $username,
                        $password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false
                        ]
                    );
                } catch (Exception $e) {
                    die('Erreur de connexion à la base de données: ' . $e->getMessage());
                }
            }
            return self::$pdo;
        }
    }
}

// Initialisation de la connexion (optionnel)
// config::getConnexion();
?>