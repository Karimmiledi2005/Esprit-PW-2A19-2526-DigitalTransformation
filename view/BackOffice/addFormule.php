<?php
require_once '../../controller/CategorieController.php';
require_once '../../config/database.php';

$idCategorie = isset($_GET['id_categorie']) ? (int)$_GET['id_categorie'] : 0;
$errors = [];

if ($idCategorie <= 0) {
    header('Location: categories_back.php');
    exit();
}

$categorieC = new CategorieController();
$categorie = $categorieC->showCategorie($idCategorie);

if (!$categorie) {
    header('Location: categories_back.php');
    exit();
}

$db = config::getConnexion();

$garantiesCatalogue = [];
try {
    $stmtCatalogue = $db->prepare("
        SELECT id_garantie, nom_garantie, description_garantie, plafond_couvert_garantie
        FROM garantie
        WHERE id_categorie = :id_categorie
        ORDER BY nom_garantie ASC
    ");
    $stmtCatalogue->execute(['id_categorie' => $idCategorie]);
    $garantiesCatalogue = $stmtCatalogue->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $garantiesCatalogue = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom  = trim($_POST['nom_formule'] ?? '');
    $desc = trim($_POST['description_formule'] ?? '');
    $prixRaw = trim($_POST['prix_formule'] ?? '');
    $prix = is_numeric($prixRaw) ? (float)$prixRaw : -1;
    $franchiseRaw = trim($_POST['franchise_formule'] ?? '');
    $franchise = is_numeric($franchiseRaw) ? (float)$franchiseRaw : -1;
    $niveau = trim($_POST['niveau_formule'] ?? '');
    $cat  = isset($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : 0;

    $garantiesChoisies = $_POST['garanties'] ?? [];
    $niveauxChoisis = $_POST['niveau_garantie'] ?? [];

    if ($nom === '') {
        $errors[] = 'Le nom de la formule est obligatoire.';
    } elseif (preg_match('/\d/', $nom)) {
        $errors[] = 'Le nom de la formule ne doit pas contenir de chiffres.';
    }

    if ($desc === '') {
        $errors[] = 'La description est obligatoire.';
    } elseif (preg_match('/\d/', $desc)) {
        $errors[] = 'La description ne doit pas contenir de chiffres.';
    }

    if ($prixRaw === '') {
        $errors[] = 'Le prix est obligatoire.';
    } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $prixRaw)) {
        $errors[] = 'Le prix est invalide.';
    } elseif ($prix <= 0) {
        $errors[] = 'Le prix doit être supérieur à 0.';
    }

    if ($franchiseRaw === '') {
        $errors[] = 'La franchise est obligatoire.';
    } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $franchiseRaw)) {
        $errors[] = 'La franchise est invalide.';
    } elseif ($franchise < 0) {
        $errors[] = 'La franchise doit être positive ou égale à 0.';
    }

    if ($niveau === '') {
        $errors[] = 'Le niveau de formule est obligatoire.';
    }

    if ($cat !== $idCategorie) {
        $errors[] = 'La catégorie est invalide.';
    }

    // Contrôle anti-doublon dans la même catégorie : nom, description ou prix déjà utilisé
    if (empty($errors)) {
        $checkDoublon = $db->prepare("
            SELECT nom_formule, description_formule, prix_formule
            FROM formule
            WHERE id_categorie = :id_categorie
              AND (LOWER(nom_formule) = LOWER(:nom_formule)
                   OR LOWER(description_formule) = LOWER(:description_formule)
                   OR prix_formule = :prix_formule)
            LIMIT 1
        ");
        $checkDoublon->execute([
            'id_categorie' => $cat,
            'nom_formule' => $nom,
            'description_formule' => $desc,
            'prix_formule' => $prix
        ]);
        $doublon = $checkDoublon->fetch(PDO::FETCH_ASSOC);

        if ($doublon) {
            if (mb_strtolower($doublon['nom_formule']) === mb_strtolower($nom)) {
                $errors[] = 'Une formule avec ce nom existe déjà dans cette catégorie.';
            }
            if (mb_strtolower($doublon['description_formule']) === mb_strtolower($desc)) {
                $errors[] = 'Une formule avec cette description existe déjà dans cette catégorie.';
            }
            if ((float)$doublon['prix_formule'] === (float)$prix) {
                $errors[] = 'Une formule avec ce prix existe déjà dans cette catégorie.';
            }
        }
    }

    if (empty($garantiesChoisies) || !is_array($garantiesChoisies)) {
        $errors[] = 'Choisis au moins une garantie.';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $stmtFormule = $db->prepare("
                INSERT INTO formule (
                    nom_formule,
                    description_formule,
                    prix_formule,
                    franchise_formule,
                    niveau_formule,
                    id_categorie
                ) VALUES (
                    :nom_formule,
                    :description_formule,
                    :prix_formule,
                    :franchise_formule,
                    :niveau_formule,
                    :id_categorie
                )
            ");

            $stmtFormule->execute([
                'nom_formule' => $nom,
                'description_formule' => $desc,
                'prix_formule' => $prix,
                'franchise_formule' => $franchise,
                'niveau_formule' => $niveau,
                'id_categorie' => $cat
            ]);

            $idFormule = (int)$db->lastInsertId();

            $stmtCheckGarantie = $db->prepare("
                SELECT COUNT(*)
                FROM garantie
                WHERE id_garantie = :id_garantie
                  AND id_categorie = :id_categorie
            ");

            $stmtLinkGarantie = $db->prepare("
                INSERT INTO formule_garantie (
                    id_formule,
                    id_garantie,
                    niveau_couvert_garantie
                ) VALUES (
                    :id_formule,
                    :id_garantie,
                    :niveau_couvert_garantie
                )
            ");

            foreach ($garantiesChoisies as $idGarantieSource) {
                $idGarantieSource = (int)$idGarantieSource;
                $niveauGarantie = trim($niveauxChoisis[$idGarantieSource] ?? 'basique');

                if (!in_array($niveauGarantie, ['basique', 'option', 'non disponible'], true)) {
                    $niveauGarantie = 'basique';
                }

                $stmtCheckGarantie->execute([
                    'id_garantie' => $idGarantieSource,
                    'id_categorie' => $cat
                ]);

                if ((int)$stmtCheckGarantie->fetchColumn() > 0) {
                    $stmtLinkGarantie->execute([
                        'id_formule' => $idFormule,
                        'id_garantie' => $idGarantieSource,
                        'niveau_couvert_garantie' => $niveauGarantie
                    ]);
                }
            }

            $db->commit();

            header('Location: showCategorie.php?id=' . $cat);
            exit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $errors[] = 'Erreur lors de l\'ajout : ' . $e->getMessage();
        }
    }
}

$selectedGaranties = $_POST['garanties'] ?? [];
$niveauxPost = $_POST['niveau_garantie'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter formule</title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/admin-users.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
    <link rel="stylesheet" href="assets/css/forms.css">
    <style>
        .field-error { color: red; font-size: 13px; margin-top: 5px; min-height: 18px; }
        .input-invalid { border-color: red !important; }
        .input-valid { border-color: green !important; }
        .garantie-checklist { display: grid; gap: 12px; }
        .garantie-item {
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 14px;
            padding: 14px 16px;
            background: rgba(255,255,255,0.03);
        }
        .garantie-top {
            display: flex;
            gap: 14px;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .garantie-label {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            color: #fff;
        }
        .garantie-desc {
            color: rgba(255,255,255,0.65);
            margin: 8px 0 10px 30px;
        }
        .garantie-meta {
            margin-left: 30px;
            color: #8bc3ff;
            font-size: 13px;
        }
        .garantie-level {
            min-width: 220px;
        }

        #prix_formule,
        input[name="prix_formule"],
        #franchise_formule,
        input[name="franchise_formule"] {
            background: rgba(255,255,255,.05) !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,.12) !important;
            box-shadow: none !important;
        }
        #prix_formule::placeholder,
        #franchise_formule::placeholder {
            color: rgba(255,255,255,.45) !important;
        }
        #prix_formule.input-invalid,
        #franchise_formule.input-invalid {
            border-color: red !important;
        }
        #prix_formule.input-valid,
        #franchise_formule.input-valid {
            border-color: green !important;
        }
    </style>
</head>
<body>
<div class="content" style="padding:40px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Ajouter une formule — <?= htmlspecialchars($categorie['nom_categorie']) ?></div>
        </div>

        <form method="POST" id="formuleForm" style="padding:24px;">
            <input type="hidden" name="id_categorie" value="<?= (int)$idCategorie ?>">

            <?php if (!empty($errors)) { ?>
                <div style="margin-bottom:18px; padding:14px 16px; border-radius:14px; background:rgba(255,99,99,0.12); border:1px solid rgba(255,99,99,0.35); color:#ffd6d6;">
                    <?php foreach ($errors as $error) { ?>
                        <div>• <?= htmlspecialchars($error) ?></div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="form-group">
                <label>NOM FORMULE <span style="color:red;">*</span></label>
                <input type="text" id="nom_formule" class="form-control" name="nom_formule"
                       placeholder="Saisir le nom de la formule"
                       value="<?= htmlspecialchars($_POST['nom_formule'] ?? '') ?>">
                <div id="error_nom_formule" class="field-error"></div>
            </div>

            <div class="form-group">
                <label>DESCRIPTION <span style="color:red;">*</span></label>
                <textarea class="form-control" id="description_formule" name="description_formule" rows="4"
                          placeholder="Saisir la description de la formule"><?= htmlspecialchars($_POST['description_formule'] ?? '') ?></textarea>
                <div id="error_description_formule" class="field-error"></div>
            </div>

            <div class="form-group">
                <label>PRIX <span style="color:red;">*</span></label>
                <input type="text" inputmode="decimal" id="prix_formule" class="form-control" name="prix_formule"
                       value="<?= htmlspecialchars($_POST['prix_formule'] ?? '') ?>">
                <div id="error_prix_formule" class="field-error"></div>
            </div>

            <div class="form-group">
                <label>NIVEAU <span style="color:red;">*</span></label>
                <?php $niveauCourant = $_POST['niveau_formule'] ?? ''; ?>
                <select class="form-control" id="niveau_formule" name="niveau_formule">
                    <option value="">-- Sélectionner un niveau --</option>
                    <option value="Essentiel" <?= $niveauCourant === 'Essentiel' ? 'selected' : '' ?>>Essentiel</option>
                    <option value="Intermédiaire" <?= $niveauCourant === 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                    <option value="Premium" <?= $niveauCourant === 'Premium' ? 'selected' : '' ?>>Premium</option>
                </select>
                <div id="error_niveau_formule" class="field-error"></div>
            </div>

            <div class="form-group">
                <label>GARANTIES DE LA CATÉGORIE <span style="color:red;">*</span></label>
                <div id="error_garanties" class="field-error"></div>

                <?php if (empty($garantiesCatalogue)) { ?>
                    <div style="padding:14px 16px; border-radius:14px; background:rgba(255,193,7,0.10); border:1px solid rgba(255,193,7,0.25); color:#ffe4a3;">
                        Aucune garantie catalogue n'est encore créée pour cette catégorie.
                    </div>
                <?php } else { ?>
                    <div class="garantie-checklist">
                        <?php foreach ($garantiesCatalogue as $garantie): ?>
                            <?php
                                $idGarantie = (int)$garantie['id_garantie'];
                                $isChecked = in_array((string)$idGarantie, array_map('strval', $selectedGaranties), true);
                                $niveauSaved = $niveauxPost[$idGarantie] ?? 'basique';
                            ?>
                            <div class="garantie-item">
                                <div class="garantie-top">
                                    <label class="garantie-label">
                                        <input type="checkbox"
                                               class="garantie-checkbox"
                                               name="garanties[]"
                                               value="<?= $idGarantie ?>"
                                               <?= $isChecked ? 'checked' : '' ?>>
                                        <span><?= htmlspecialchars($garantie['nom_garantie']) ?></span>
                                    </label>

                                    <select class="form-control garantie-level"
                                            name="niveau_garantie[<?= $idGarantie ?>]">
                                        <option value="basique" <?= $niveauSaved === 'basique' ? 'selected' : '' ?>>basique</option>
                                        <option value="option" <?= $niveauSaved === 'option' ? 'selected' : '' ?>>option</option>
                                        <option value="non disponible" <?= $niveauSaved === 'non disponible' ? 'selected' : '' ?>>non disponible</option>
                                    </select>
                                </div>

                                <div class="garantie-desc"><?= htmlspecialchars($garantie['description_garantie']) ?></div>
                                <div class="garantie-meta">Plafond catalogue : <?= number_format((float)$garantie['plafond_couvert_garantie'], 2, '.', ' ') ?> DT</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php } ?>
            </div>

            <div class="form-group">
                <label>CATÉGORIE</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($categorie['nom_categorie']) ?>" readonly>
            </div>

            <div class="modal-footer" style="padding:0;border-top:none;">
                <a href="showCategorie.php?id=<?= (int)$idCategorie ?>" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Ajouter</button>
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
    if (/\d/.test(e.key)) e.preventDefault();
}
function blockPasteNumbers(e) {
    const paste = (e.clipboardData || window.clipboardData).getData('text');
    if (/\d/.test(paste)) e.preventDefault();
}
function validateNomFormule() {
    const input = document.getElementById('nom_formule');
    const error = document.getElementById('error_nom_formule');
    const value = input.value.trim();
    if (value === '') return setError(input, error, 'Nom obligatoire'), false;
    if (/\d/.test(value)) return setError(input, error, 'Les chiffres sont interdits'), false;
    return setSuccess(input, error), true;
}
function validateDescriptionFormule() {
    const input = document.getElementById('description_formule');
    const error = document.getElementById('error_description_formule');
    const value = input.value.trim();
    if (value === '') return setError(input, error, 'Description obligatoire'), false;
    if (/\d/.test(value)) return setError(input, error, 'Les chiffres sont interdits'), false;
    return setSuccess(input, error), true;
}
function validatePrixFormule() {
    const input = document.getElementById('prix_formule');
    const error = document.getElementById('error_prix_formule');
    const value = input.value.trim();
    if (value === '') return setError(input, error, 'Prix obligatoire'), false;
    if (!/^\d+(\.\d{1,2})?$/.test(value)) return setError(input, error, 'Prix invalide'), false;
    if (parseFloat(value) <= 0) return setError(input, error, 'Le prix doit être supérieur à 0'), false;
    return setSuccess(input, error), true;
}
function validateNiveauFormule() {
    const input = document.getElementById('niveau_formule');
    const error = document.getElementById('error_niveau_formule');
    if (input.value.trim() === '') return setError(input, error, 'Niveau obligatoire'), false;
    return setSuccess(input, error), true;
}
function validateGarantiesSelection() {
    const error = document.getElementById('error_garanties');
    const checked = document.querySelectorAll('.garantie-checkbox:checked').length;
    if (checked === 0) {
        error.textContent = 'Choisis au moins une garantie';
        return false;
    }
    error.textContent = '';
    return true;
}
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formuleForm');
    const nom = document.getElementById('nom_formule');
    const desc = document.getElementById('description_formule');
    const prix = document.getElementById('prix_formule');
    const niveau = document.getElementById('niveau_formule');

    nom.addEventListener('keypress', blockNumbersForText);
    desc.addEventListener('keypress', blockNumbersForText);
    nom.addEventListener('paste', blockPasteNumbers);
    desc.addEventListener('paste', blockPasteNumbers);

    nom.addEventListener('input', validateNomFormule);
    desc.addEventListener('input', validateDescriptionFormule);
    prix.addEventListener('input', validatePrixFormule);
    niveau.addEventListener('change', validateNiveauFormule);

    document.querySelectorAll('.garantie-checkbox').forEach(cb => {
        cb.addEventListener('change', validateGarantiesSelection);
    });

    form.addEventListener('submit', function(e) {
        const ok = validateNomFormule() &&
                   validateDescriptionFormule() &&
                   validatePrixFormule() &&
                   validateNiveauFormule() &&
                   validateGarantiesSelection();
        if (!ok) e.preventDefault();
    });
});
</script>
</body>
</html>
