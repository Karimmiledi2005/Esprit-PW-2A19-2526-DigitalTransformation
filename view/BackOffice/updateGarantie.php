<?php
require_once '../../controller/GarantieController.php';
require_once '../../controller/CategorieController.php';
require_once '../../config/database.php';

$garantieC = new GarantieController();
$categorieC = new CategorieController();

$categories = $categorieC->listCategories();
$errors = [];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID garantie manquant.");
}

$id = (int)$_GET['id'];
$garantieData = $garantieC->showGarantie($id);

if (!$garantieData) {
    die("Garantie introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom_garantie'] ?? '');
    $description = trim($_POST['description_garantie'] ?? '');
    $plafondRaw = trim($_POST['plafond_couvert_garantie'] ?? '');
    $plafond = is_numeric($plafondRaw) ? (float)$plafondRaw : -1;
    $idCategorie = isset($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : 0;

    if ($nom === '') {
        $errors[] = 'Le nom de la garantie est obligatoire.';
    } elseif (preg_match('/\d/', $nom)) {
        $errors[] = 'Le nom de la garantie ne doit pas contenir de chiffres.';
    }

    if ($description === '') {
        $errors[] = 'La description est obligatoire.';
    } elseif (preg_match('/\d/', $description)) {
        $errors[] = 'La description ne doit pas contenir de chiffres.';
    }

    if ($plafondRaw === '') {
        $errors[] = 'Le plafond est obligatoire.';
    } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $plafondRaw)) {
        $errors[] = 'Le plafond est invalide.';
    } elseif ($plafond < 0) {
        $errors[] = 'Le plafond doit être positif ou nul.';
    }

    if ($idCategorie <= 0) {
        $errors[] = 'La catégorie est obligatoire.';
    }

    if (empty($errors)) {
        $db = config::getConnexion();

        $check = $db->prepare("
            SELECT COUNT(*)
            FROM garantie
            WHERE nom_garantie = :nom_garantie
              AND id_categorie = :id_categorie
              AND id_formule IS NULL
              AND id_garantie != :id_garantie
        ");
        $check->execute([
            'nom_garantie' => $nom,
            'id_categorie' => $idCategorie,
            'id_garantie' => $id
        ]);

        if ((int)$check->fetchColumn() > 0) {
            $errors[] = 'Cette garantie existe déjà dans cette catégorie.';
        } else {
            try {
                $sql = "
                    UPDATE garantie
                    SET
                        nom_garantie = :nom_garantie,
                        description_garantie = :description_garantie,
                        plafond_couvert_garantie = :plafond_couvert_garantie,
                        id_categorie = :id_categorie,
                        id_formule = NULL
                    WHERE id_garantie = :id_garantie
                ";

                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'nom_garantie' => $nom,
                    'description_garantie' => $description,
                    'plafond_couvert_garantie' => $plafond,
                    'id_categorie' => $idCategorie,
                    'id_garantie' => $id
                ]);

                header('Location: garanties_back.php');
                exit();
            } catch (Exception $e) {
                $errors[] = 'Erreur lors de la modification : ' . $e->getMessage();
            }
        }
    }
}

$currentNom = $_POST['nom_garantie'] ?? $garantieData['nom_garantie'];
$currentDescription = $_POST['description_garantie'] ?? $garantieData['description_garantie'];
$currentPlafond = $_POST['plafond_couvert_garantie'] ?? $garantieData['plafond_couvert_garantie'];
$currentCategorie = isset($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : (int)$garantieData['id_categorie'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier garantie</title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/admin-users.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
    <link rel="stylesheet" href="assets/css/forms.css">
    <style>
        .field-error {
            color: red;
            font-size: 13px;
            margin-top: 5px;
            min-height: 18px;
        }

        .input-invalid {
            border-color: red !important;
        }

        .input-valid {
            border-color: green !important;
        }
    </style>
</head>
<body>
<div class="content" style="padding:40px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Modifier la garantie</div>
        </div>

        <form method="POST" id="garantieForm" style="padding:24px;">
            <?php if (!empty($errors)) { ?>
                <div style="margin-bottom:18px; padding:14px 16px; border-radius:14px; background:rgba(255,99,99,0.12); border:1px solid rgba(255,99,99,0.35); color:#ffd6d6;">
                    <?php foreach ($errors as $error) { ?>
                        <div>• <?= htmlspecialchars($error) ?></div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="form-group">
                <label>NOM GARANTIE <span style="color:red;">*</span></label>
                <input type="text"
                       id="nom_garantie"
                       class="form-control"
                       name="nom_garantie"
                       value="<?= htmlspecialchars($currentNom) ?>">
                <div id="error_nom_garantie" class="field-error"></div>
            </div>

            <div class="form-group">
                <label>DESCRIPTION <span style="color:red;">*</span></label>
                <textarea class="form-control"
                          id="description_garantie"
                          name="description_garantie"
                          rows="4"><?= htmlspecialchars($currentDescription) ?></textarea>
                <div id="error_description_garantie" class="field-error"></div>
            </div>

            <div class="form-group">
                <label>PLAFOND DE COUVERTURE <span style="color:red;">*</span></label>
                <input type="text"
                       id="plafond_couvert_garantie"
                       class="form-control"
                       name="plafond_couvert_garantie"
                       value="<?= htmlspecialchars((string)$currentPlafond) ?>">
                <div id="error_plafond_couvert_garantie" class="field-error"></div>
            </div>

            <div class="form-group">
                <label>CATÉGORIE <span style="color:red;">*</span></label>
                <select class="form-control" id="id_categorie" name="id_categorie">
                    <option value="">-- Sélectionner une catégorie --</option>
                    <?php foreach ($categories as $cat) { ?>
                        <option value="<?= (int)$cat['id_categorie'] ?>"
                            <?= ((int)$cat['id_categorie'] === $currentCategorie) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nom_categorie']) ?>
                        </option>
                    <?php } ?>
                </select>
                <div id="error_id_categorie" class="field-error"></div>
            </div>

            <div class="modal-footer" style="padding:0;border-top:none;">
                <a href="garanties_back.php" class="btn btn-outline">Annuler</a>
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

function blockNumbersForText(e) {
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

function validateNomGarantie() {
    const input = document.getElementById('nom_garantie');
    const error = document.getElementById('error_nom_garantie');
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

function validateDescriptionGarantie() {
    const input = document.getElementById('description_garantie');
    const error = document.getElementById('error_description_garantie');
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

function validatePlafondGarantie() {
    const input = document.getElementById('plafond_couvert_garantie');
    const error = document.getElementById('error_plafond_couvert_garantie');
    const value = input.value.trim();

    if (value === '') {
        setError(input, error, 'Plafond obligatoire');
        return false;
    }

    if (!/^\d+(\.\d{1,2})?$/.test(value)) {
        setError(input, error, 'Plafond invalide');
        return false;
    }

    if (parseFloat(value) < 0) {
        setError(input, error, 'Le plafond doit être positif');
        return false;
    }

    setSuccess(input, error);
    return true;
}

function validateCategorieGarantie() {
    const input = document.getElementById('id_categorie');
    const error = document.getElementById('error_id_categorie');

    if (input.value.trim() === '') {
        setError(input, error, 'Catégorie obligatoire');
        return false;
    }

    setSuccess(input, error);
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('garantieForm');
    const nom = document.getElementById('nom_garantie');
    const description = document.getElementById('description_garantie');
    const plafond = document.getElementById('plafond_couvert_garantie');
    const categorie = document.getElementById('id_categorie');

    nom.addEventListener('keypress', blockNumbersForText);
    description.addEventListener('keypress', blockNumbersForText);

    nom.addEventListener('paste', blockPasteNumbers);
    description.addEventListener('paste', blockPasteNumbers);

    nom.addEventListener('input', validateNomGarantie);
    description.addEventListener('input', validateDescriptionGarantie);
    plafond.addEventListener('input', validatePlafondGarantie);
    categorie.addEventListener('change', validateCategorieGarantie);

    form.addEventListener('submit', function(e) {
        const ok =
            validateNomGarantie() &&
            validateDescriptionGarantie() &&
            validatePlafondGarantie() &&
            validateCategorieGarantie();

        if (!ok) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>