<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';

try {
    $sql = "INSERT INTO poste (
                contenu,
                date_publication,
                note,
                auteur,
                nb_likes,
                nb_commentaires,
                id_agence
            ) VALUES (
                :contenu,
                :date_publication,
                :note,
                :auteur,
                :nb_likes,
                :nb_commentaires,
                :id_agence
            )";

    $stmt = $pdo->prepare($sql);

    $ok = $stmt->execute([
        ':contenu' => 'TEST INSERT DIRECT',
        ':date_publication' => date('Y-m-d'),
        ':note' => 5,
        ':auteur' => 'TEST',
        ':nb_likes' => 0,
        ':nb_commentaires' => 0,
        ':id_agence' => 1
    ]);

    if ($ok) {
        echo "INSERT OK - ID = " . $pdo->lastInsertId();
    } else {
        echo "INSERT FAILED";
    }

} catch (Throwable $e) {
    echo "ERREUR : " . $e->getMessage();
}
?>