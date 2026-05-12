<?php
require_once __DIR__ . '/db.php';

$data = getJsonInput();

$idPoste = isset($data['id_poste']) ? (int)$data['id_poste'] : 0;
$contenu = trim($data['contenu'] ?? '');
$auteur = trim($data['auteur'] ?? '');
$idAgence = (int)($data['id_agence'] ?? 0);
$datePublication = trim($data['date_publication'] ?? '');

$errors = [];

if ($contenu === '') {
    $errors['contenu'] = 'Veuillez remplir le contenu du poste.';
}

if ($auteur === '') {
    $errors['auteur'] = 'Veuillez remplir le nom de l\'auteur.';
} elseif (preg_match('/\d/', $auteur)) {
    $errors['auteur'] = 'Le nom de l\'auteur ne doit pas contenir de chiffres.';
}

if ($idAgence <= 0) {
    $errors['id_agence'] = 'Veuillez choisir une agence.';
}

if ($datePublication === '') {
    $errors['date_publication'] = 'Veuillez choisir une date valide.';
}

if (!empty($errors)) {
    jsonResponse([
        'success' => false,
        'errors' => $errors
    ], 422);
}

try {
    if ($idPoste > 0) {
        $stmt = $pdo->prepare("
            UPDATE poste
            SET contenu = ?, auteur = ?, id_agence = ?, date_publication = ?
            WHERE id_poste = ?
        ");
        $stmt->execute([$contenu, $auteur, $idAgence, $datePublication, $idPoste]);

        jsonResponse([
            'success' => true,
            'message' => 'Poste modifié avec succès.'
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO poste (contenu, date_publication, note, auteur, nb_likes, nb_commentaires, id_agence)
            VALUES (?, ?, NULL, ?, 0, 0, ?)
        ");
        $stmt->execute([$contenu, $datePublication, $auteur, $idAgence]);

        jsonResponse([
            'success' => true,
            'message' => 'Poste ajouté avec succès.'
        ]);
    }
} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Erreur SQL : ' . $e->getMessage()
    ], 500);
}