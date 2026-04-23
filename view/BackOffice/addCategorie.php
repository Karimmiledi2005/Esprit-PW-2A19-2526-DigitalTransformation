<?php
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../model/Categorie.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom_categorie"] ?? "");
    $description = trim($_POST["description_categorie"] ?? "");

    // sécurité serveur (important)
    if ($nom === "") {
        die("Erreur : le nom de la catégorie est obligatoire.");
    }

    if (preg_match('/\d/', $nom)) {
        die("Erreur : le nom ne doit pas contenir de chiffres.");
    }

    if ($description === "") {
        die("Erreur : la description est obligatoire.");
    }

    if (preg_match('/\d/', $description)) {
        die("Erreur : la description ne doit pas contenir de chiffres.");
    }

    $categorie = new Categorie($nom, $description);
    $categorieC = new CategorieController();
    $categorieC->addCategorie($categorie);

    header("Location: categories_back.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter catégorie</title>

<link rel="stylesheet" href="assets/css/variables.css">
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/layout.css">
<link rel="stylesheet" href="assets/css/admin-users.css">
<link rel="stylesheet" href="assets/css/contrats.css">
<link rel="stylesheet" href="assets/css/forms.css">

<style>
.input-invalid {
    border-color: red !important;
}
.input-valid {
    border-color: green !important;
}
.field-error {
    color: red;
    font-size: 13px;
    margin-top: 5px;
}
</style>

</head>

<body>

<div class="content" style="padding:40px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Ajouter une catégorie</div>
        </div>

        <form method="POST" id="categorieForm" style="padding:24px;">

            <!-- NOM -->
            <div class="form-group">
                <label>Nom catégorie <span style="color:red;">*</span></label>
                <input type="text" 
                       id="nom_categorie"
                       class="form-control" 
                       name="nom_categorie"
                       placeholder="Saisir le nom de la catégorie">
                <div id="error_nom_categorie" class="field-error"></div>
            </div>

            <!-- DESCRIPTION -->
            <div class="form-group">
                <label>Description <span style="color:red;">*</span></label>
                <textarea class="form-control" 
                          id="description_categorie"
                          name="description_categorie"
                          rows="4"
                          placeholder="Saisir la description de la catégorie"></textarea>
                <div id="error_description_categorie" class="field-error"></div>
            </div>

            <div class="modal-footer" style="padding:0; border-top:none;">
                <a href="categories_back.php" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>

        </form>
    </div>
</div>

<!-- ================= JS VALIDATION ================= -->
<script>
function setError(input, errorElement, message) {
    input.classList.remove('input-valid');
    input.classList.add('input-invalid');
    errorElement.textContent = message;
}

function setSuccess(input, errorElement) {
    input.classList.remove('input-invalid');
    input.classList.add('input-valid');
    errorElement.textContent = '';
}

// 🚫 bloc chiffres clavier
function blockNumbers(e) {
    if (/\d/.test(e.key)) {
        e.preventDefault();
    }
}

// 🚫 bloc paste chiffres
function blockPasteNumbers(e) {
    const paste = (e.clipboardData || window.clipboardData).getData('text');
    if (/\d/.test(paste)) {
        e.preventDefault();
    }
}

// VALID NOM
function validateNom() {
    const input = document.getElementById('nom_categorie');
    const error = document.getElementById('error_nom_categorie');
    const value = input.value.trim();

    if (value === '') {
        setError(input, error, 'Nom obligatoire');
        return false;
    }

    if (/\d/.test(value)) {
        setError(input, error, 'Les chiffres sont interdits');
        return false;
    }

    setSuccess(input, error);
    return true;
}

// VALID DESCRIPTION
function validateDescription() {
    const input = document.getElementById('description_categorie');
    const error = document.getElementById('error_description_categorie');
    const value = input.value.trim();

    if (value === '') {
        setError(input, error, 'Description obligatoire');
        return false;
    }

    if (/\d/.test(value)) {
        setError(input, error, 'Les chiffres sont interdits');
        return false;
    }

    setSuccess(input, error);
    return true;
}

document.addEventListener('DOMContentLoaded', function () {

    const nom = document.getElementById('nom_categorie');
    const desc = document.getElementById('description_categorie');
    const form = document.getElementById('categorieForm');

    nom.addEventListener('keypress', blockNumbers);
    desc.addEventListener('keypress', blockNumbers);

    nom.addEventListener('paste', blockPasteNumbers);
    desc.addEventListener('paste', blockPasteNumbers);

    // validation realtime
    nom.addEventListener('input', validateNom);
    desc.addEventListener('input', validateDescription);

    // submit
    form.addEventListener('submit', function(e) {
        if (!validateNom() || !validateDescription()) {
            e.preventDefault();
        }
    });

});
</script>

</body>
</html>