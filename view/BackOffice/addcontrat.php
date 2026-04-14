<?php
require_once '../../Controller/ContratController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = $_POST['numero_contrat'] ?? '';
    $type = $_POST['type_contrat'] ?? '';
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $prime = $_POST['montant_prime'] ?? '';
    $franchise = $_POST['franchise'] ?? '';

    $errors = [];

    if (trim($numero) === '') {
        $errors[] = "Numéro du contrat obligatoire.";
    }

    if (trim($type) === '') {
        $errors[] = "Type du contrat obligatoire.";
    }

    if (trim($date_debut) === '' || trim($date_fin) === '') {
        $errors[] = "Les dates sont obligatoires.";
    } elseif ($date_fin <= $date_debut) {
        $errors[] = "La date fin doit être supérieure à la date début.";
    }

    if ($prime === '' || !is_numeric($prime) || $prime <= 0) {
        $errors[] = "La prime doit être un nombre positif.";
    }

    if ($franchise === '' || !is_numeric($franchise) || $franchise < 0) {
        $errors[] = "La franchise doit être un nombre valide.";
    }

    if (empty($errors)) {
        $contratC = new ContratController();
        $contratC->addContrat($numero, $type, $date_debut, $date_fin, $prime, $franchise);

        header('Location: ../FrontOffice/client.php');
        exit;
    } else {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
    }
}
?>