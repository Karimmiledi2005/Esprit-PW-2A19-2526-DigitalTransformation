<?php
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../model/Categorie.php';

$categorieC = new CategorieController();
$errors = [];

if (!isset($_GET['id'])) {
    die("ID catégorie manquant.");
}

$id = (int)$_GET['id'];
$categorieData = $categorieC->showCategorie($id);

if (!$categorieData) {
    die("Catégorie introuvable.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom_categorie"] ?? "");
    $description = trim($_POST["description_categorie"] ?? "");

    if ($nom === "") {
        $errors[] = "Le nom de la catégorie est obligatoire.";
    } elseif (mb_strlen($nom) < 3) {
        $errors[] = "Le nom doit contenir au moins 3 caractères.";
    } elseif (mb_strlen($nom) > 100) {
        $errors[] = "Le nom ne doit pas dépasser 100 caractères.";
    } elseif (!preg_match('/^[A-Za-zÀ-ÿ\s\-]+$/u', $nom)) {
        $errors[] = "Le nom doit contenir uniquement des lettres, espaces ou tirets.";
    }

    if ($description !== "") {
        if (mb_strlen($description) < 10) {
            $errors[] = "La description doit contenir au moins 10 caractères.";
        } elseif (mb_strlen($description) > 500) {
            $errors[] = "La description ne doit pas dépasser 500 caractères.";
        }
    }

    if (empty($errors)) {
        $categorie = new Categorie($nom, $description);
        $categorieC->updateCategorie($id, $categorie);

        header("Location: categories_back.php");
        exit;
    }
}

$currentNom = $_POST['nom_categorie'] ?? $categorieData['nom_categorie'];
$currentDescription = $_POST['description_categorie'] ?? ($categorieData['description_categorie'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier catégorie</title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/admin-users.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
    <link rel="stylesheet" href="assets/css/forms.css">
    <style>
        .field-error {
            color: #ff8f8f;
            font-size: 13px;
            margin-top: 6px;
            min-height: 18px;
        }

        .input-invalid {
            border-color: #ff6b6b !important;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.12) !important;
        }

        .input-valid {
            border-color: #22c55e !important;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.10) !important;
        }

        .error-box {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(255,99,99,0.12);
            border: 1px solid rgba(255,99,99,0.35);
            color: #ffd6d6;
        }
    </style>
</head>
<body>
<div class="content" style="padding:40px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Modifier la catégorie</div>
        </div>

        <form method="POST" id="categorieForm" style="padding:24px;" onsubmit="return validateCategorieForm()">
            <?php if (!empty($errors)) { ?>
                <div class="error-box">
                    <?php foreach ($errors as $error) { ?>
                        <div>• <?= htmlspecialchars($error) ?></div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="form-group">
                <label for="nom_categorie">Nom catégorie <span style="color:red;">*</span></label>
                <input type="text"
                       id="nom_categorie"
                       class="form-control"
                       name="nom_categorie"
                       minlength="3"
                       maxlength="100"
                       value="<?= htmlspecialchars($currentNom) ?>"
                       required>
                <div class="field-error" id="error_nom_categorie"></div>
            </div>

            <div class="form-group">
                <label for="description_categorie">Description</label>
                <textarea id="description_categorie"
                          class="form-control"
                          name="description_categorie"
                          rows="4"
                          maxlength="500"><?= htmlspecialchars($currentDescription) ?></textarea>
                <div class="field-error" id="error_description_categorie"></div>
            </div>

            <div class="modal-footer" style="padding:0; border-top:none;">
                <a href="categories_back.php" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Modifier</button>
            </div>
        </form>
    </div>
</div>

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

function blockNumbers(e) {
    if (/\d/.test(e.key)) {
        e.preventDefault();
    }
}


function blockPasteNumbers(e) {
    const paste = (e.clipboardData || window.clipboardData).getData('text');
    if (/\d/.test(paste)) {
        e.preventDefault();
    }
}

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

    // ✅ validation realtime
    nom.addEventListener('input', validateNom);
    desc.addEventListener('input', validateDescription);

    // ✅ submit
    form.addEventListener('submit', function(e) {
        if (!validateNom() || !validateDescription()) {
            e.preventDefault();
        }
    });

});
</script>
</body>
</html>
