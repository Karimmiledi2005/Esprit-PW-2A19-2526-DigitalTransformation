<?php
/**
 * Migration script: transfer JSON data from poste/agence columns
 * to the new relational tables (like_post, commentaire, avis_agence).
 * Run ONCE from CLI or browser after importing migration.sql.
 */

require_once __DIR__ . '/api/db.php';

echo "Migration des donnees JSON -> tables relationnelles\n\n";

// === 1. Migrate likes from poste.likes_json -> like_post ===
echo "--- Likes ---\n";
$posts = $pdo->query("SELECT id_poste, likes_json FROM poste WHERE likes_json IS NOT NULL AND likes_json != '[]'")->fetchAll();
$countLikes = 0;
foreach ($posts as $post) {
    $likes = json_decode($post['likes_json'], true);
    if (!is_array($likes)) continue;
    foreach ($likes as $idClient) {
        if (!is_numeric($idClient)) continue;
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO like_post (id_poste, id_client) VALUES (?, ?)");
            $stmt->execute([$post['id_poste'], (int)$idClient]);
            $countLikes++;
        } catch (Exception $e) {
            // skip duplicates
        }
    }
}
echo "  $countLikes likes migres.\n";

// === 2. Migrate comments from poste.commentaires_json -> commentaire ===
echo "--- Commentaires ---\n";
$posts = $pdo->query("SELECT id_poste, commentaires_json FROM poste WHERE commentaires_json IS NOT NULL AND commentaires_json != '[]' AND commentaires_json != '{}'")->fetchAll();
$countComments = 0;

function migrateComment(PDO $pdo, array $json, int $idPoste, ?int $parentId = null): void {
    global $countComments;
    $idClient = (int)($json['id_client'] ?? 4);
    $contenu = $json['contenu'] ?? '';
    $dateStr = $json['date_commentaire'] ?? date('Y-m-d H:i:s');

    if (empty($contenu)) return;

    $stmt = $pdo->prepare("INSERT INTO commentaire (contenu, date_commentaire, id_poste, id_client, id_commentaire_parent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$contenu, $dateStr, $idPoste, $idClient, $parentId]);
    $newId = (int)$pdo->lastInsertId();
    $countComments++;

    $reponses = $json['reponses'] ?? [];
    if (is_array($reponses)) {
        foreach ($reponses as $reply) {
            migrateComment($pdo, $reply, $idPoste, $newId);
        }
    }
}

foreach ($posts as $post) {
    $comments = json_decode($post['commentaires_json'], true);
    if (!is_array($comments)) continue;
    foreach ($comments as $comment) {
        if (isset($comment['id_commentaire'])) {
            migrateComment($pdo, $comment, $post['id_poste']);
        }
    }
}
echo "  $countComments commentaires/reponses migres.\n";

// === 3. Migrate reviews from agence.avis_json -> avis_agence ===
echo "--- Avis agences ---\n";
$agences = $pdo->query("SELECT id_agence, avis_json FROM agence WHERE avis_json IS NOT NULL AND avis_json != '[]' AND avis_json != '{}'")->fetchAll();
$countAvis = 0;
foreach ($agences as $agence) {
    $avisList = json_decode($agence['avis_json'], true);
    if (!is_array($avisList)) continue;
    foreach ($avisList as $avis) {
        $idClient = (int)($avis['id_client'] ?? 4);
        $note = (int)($avis['note'] ?? 0);
        $commentaire = $avis['commentaire'] ?? '';
        $dateAvis = $avis['date_avis'] ?? date('Y-m-d H:i:s');

        if ($note < 1 || $note > 5 || empty($commentaire)) continue;

        try {
            $stmt = $pdo->prepare("INSERT INTO avis_agence (note, commentaire, date_avis, id_agence, id_client) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$note, $commentaire, $dateAvis, $agence['id_agence'], $idClient]);
            $countAvis++;
        } catch (Exception $e) {
            echo "    Erreur: " . $e->getMessage() . "\n";
        }
    }
}
echo "  $countAvis avis migres.\n";

// === 4. Update stats counters ===
echo "--- Stats ---\n";
$stmt = $pdo->query("UPDATE poste SET nb_likes = 0, nb_commentaires = 0");
$posts = $pdo->query("SELECT id_poste FROM poste")->fetchAll();
foreach ($posts as $post) {
    syncPostStats($pdo, $post['id_poste']);
}
echo "  Stats poste synchronisees.\n";

echo "\nMigration terminee !\n";
