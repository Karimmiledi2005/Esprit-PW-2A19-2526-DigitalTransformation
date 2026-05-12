<?php
require_once __DIR__ . '/db.php';

$data = getJsonInput();

$idAgence = isset($data['id_agence']) ? (int)$data['id_agence'] : 0;
$nomAgence = trim($data['nom_agence'] ?? '');
$pays = trim($data['pays'] ?? '');
$tel = trim($data['tel'] ?? '');
$email = trim($data['email'] ?? '');

$errors = [];

if ($nomAgence === '') {
    $errors['nom_agence'] = 'Veuillez remplir le nom de l\'agence.';
}

if ($pays === '') {
    $errors['pays'] = 'Veuillez remplir le pays.';
}

if ($tel !== '' && !preg_match('/^\d{8}$/', $tel)) {
    $errors['tel'] = 'Le numéro de téléphone doit contenir exactement 8 chiffres.';
}

if ($email === '') {
    $errors['email'] = 'Veuillez remplir l\'email.';
} elseif (strpos($email, '@protex.tn') === false) {
    $errors['email'] = 'L\'email doit contenir "@protex.tn".';
}

if (!empty($errors)) {
    jsonResponse([
        'success' => false,
        'errors' => $errors
    ], 422);
}

try {
    if ($idAgence > 0) {
        $stmt = $pdo->prepare("
            UPDATE agence
            SET nom_agence = ?, pays = ?, tel = ?, email = ?
            WHERE id_agence = ?
        ");
        $stmt->execute([$nomAgence, $pays, $tel, $email, $idAgence]);

        jsonResponse([
            'success' => true,
            'message' => 'Agence modifiée avec succès.'
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO agence (nom_agence, pays, tel, email)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$nomAgence, $pays, $tel, $email]);

        jsonResponse([
            'success' => true,
            'message' => 'Agence ajoutée avec succès.'
        ]);
    }
} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Erreur SQL : ' . $e->getMessage()
    ], 500);
}