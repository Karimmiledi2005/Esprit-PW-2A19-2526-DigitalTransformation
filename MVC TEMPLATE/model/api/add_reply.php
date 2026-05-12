<?php
require_once __DIR__ . '/db.php';

$data = getJsonInput();

$idPoste = (int)($data['id_poste'] ?? 0);
$idClient = (int)($data['id_client'] ?? 0);
$idCommentaireParent = (int)($data['id_commentaire_parent'] ?? 0);
$contenu = trim($data['contenu'] ?? '');

if ($idPoste <= 0 || $idClient <= 0 || $idCommentaireParent <= 0 || $contenu === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Réponse invalide.'
    ], 422);
}

$stmt = $pdo->prepare("
    INSERT INTO commentaire (contenu, id_poste, id_client, id_commentaire_parent)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$contenu, $idPoste, $idClient, $idCommentaireParent]);

syncPostStats($pdo, $idPoste);

addNotification($pdo, $idClient,
    'Nous vous remercions pour votre réponse. Votre participation contribue à enrichir nos échanges.',
    'thanks');

jsonResponse([
    'success' => true,
    'message' => 'Réponse ajoutée.'
]);