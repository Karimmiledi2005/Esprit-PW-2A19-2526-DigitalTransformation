<?php
require_once __DIR__ . '/db.php';

$data = getJsonInput();

$idClient = (int)($data['id_client'] ?? 0);
$idAgence = (int)($data['id_agence'] ?? 0);
$note = (int)($data['note'] ?? 0);
$commentaire = trim($data['commentaire'] ?? '');

$errors = [];

if ($idClient <= 0) {
    $errors['id_client'] = 'Client invalide.';
}

if ($idAgence <= 0) {
    $errors['id_agence'] = 'Veuillez choisir une agence.';
}

if ($note < 1 || $note > 5) {
    $errors['note'] = 'Veuillez choisir une note entre 1 et 5.';
}

if ($commentaire === '') {
    $errors['commentaire'] = 'Veuillez écrire un commentaire.';
}

if (!empty($errors)) {
    jsonResponse([
        'success' => false,
        'errors' => $errors
    ], 422);
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO avis_agence (note, commentaire, date_avis, id_client, id_agence)
        VALUES (?, ?, NOW(), ?, ?)
    ");
    $stmt->execute([$note, $commentaire, $idClient, $idAgence]);

    addNotification($pdo, $idClient,
        'Nous vous remercions pour votre avis. Votre retour est essentiel pour l\'amélioration continue de la qualité de nos services.',
        'thanks');

    jsonResponse([
        'success' => true,
        'message' => 'Avis envoyé avec succès.'
    ]);
} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Erreur SQL : ' . $e->getMessage()
    ], 500);
}