<?php
class config {

    private static $host = "localhost";
    private static $dbname = "protex_contrats";
    private static $username = "root";
    private static $password = "";

    public static function getConnexion() {
        try {
            $conn = new PDO(
                "mysql:host=" . self::$host . ";dbname=" . self::$dbname,
                self::$username,
                self::$password
            );

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;

        } catch (Exception $e) {
            die('Erreur de connexion: ' . $e->getMessage());
        }
    }
}
?>