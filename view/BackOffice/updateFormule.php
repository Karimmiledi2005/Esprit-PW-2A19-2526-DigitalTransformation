<?php
require_once '../../controller/FormuleController.php';
require_once '../../controller/CategorieController.php';
require_once '../../model/Formule.php';
require_once '../../config/database.php';

$formuleC = new FormuleController();
$categorieC = new CategorieController();
$db = config::getConnexion();

$errors = [];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID formule manquant.");
}

$id = (int)$_GET['id'];
$formuleData = $formuleC->showFormule($id);

if (!$formuleData) {
    die("Formule introuvable.");
}

$categories = $categorieC->listCategories();

$currentCategorie = (int)($formuleData['id_categorie'] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom_formule"] ?? "");
    $description = trim($_POST["description_formule"] ?? "");
    $prixRaw = trim($_POST["prix_formule"] ?? "");
    $prix = is_numeric($prixRaw) ? (float)$prixRaw : -1;
    $niveau = trim($_POST["niveau_formule"] ?? "");
    $currentCategorie = isset($_POST["id_categorie"]) ? (int)$_POST["id_categorie"] : $currentCategorie;

    $garantiesChoisies = $_POST['garanties'] ?? [];
    $niveauxChoisis = $_POST['niveau_garantie'] ?? [];

    if ($nom === '') {
        $errors[] = 'Le nom de la formule est obligatoire.';
    } elseif (preg_match('/\d/', $nom)) {
        $errors[] = 'Le nom de la formule ne doit pas contenir de chiffres.';
    }

    if ($description === '') {
        $errors[] = 'La description est obligatoire.';
    } elseif (preg_match('/\d/', $description)) {
        $errors[] = 'La description ne doit pas contenir de chiffres.';
    }

    if ($prixRaw === '') {
        $errors[] = 'Le prix est obligatoire.';
    } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $prixRaw)) {
        $errors[] = 'Le prix est invalide.';
    } elseif ($prix < 0) {
        $errors[] = 'Le prix doit être positif.';
    }

    if ($niveau === '') {
        $errors[] = 'Le niveau est obligatoire.';
    }

    if ($currentCategorie <= 0) {
        $errors[] = 'La catégorie est invalide.';
    }

    if (empty($garantiesChoisies) || !is_array($garantiesChoisies)) {
        $errors[] = 'Choisis au moins une garantie.';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $formule = new Formule($nom, $description, $prix, $niveau, $currentCategorie);

            if (method_exists($formuleC, 'updateFormule')) {
                $formuleC->updateFormule($id, $formule);
            } else {
                $formuleC->update($id, $formule);
            }

            $deleteStmt = $db->prepare("DELETE FROM garantie WHERE id_formule = :id_formule");
            $deleteStmt->execute(['id_formule' => $id]);

            $stmtSource = $db->prepare("
                SELECT nom_garantie, description_garantie, plafond_couvert_garantie, id_categorie
                FROM garantie
                WHERE id_garantie = :id_garantie
                  AND id_categorie = :id_categorie
                  AND id_formule IS NULL
                LIMIT 1
            ");

            $stmtInsertGarantie = $db->prepare("
                INSERT INTO garantie (
                    nom_garantie,
                    description_garantie,
                    plafond_couvert_garantie,
                    niveau_couvert_garantie,
                    id_formule,
                    id_categorie
                ) VALUES (
                    :nom_garantie,
                    :description_garantie,
                    :plafond_couvert_garantie,
                    :niveau_couvert_garantie,
                    :id_formule,
                    :id_categorie
                )
            ");

            foreach ($garantiesChoisies as $idGarantieSource) {
                $idGarantieSource = (int)$idGarantieSource;
                $niveauGarantie = trim($niveauxChoisis[$idGarantieSource] ?? 'basique');

                if (!in_array($niveauGarantie, ['basique', 'option', 'non disponible'], true)) {
                    $niveauGarantie = 'basique';
                }

                $stmtSource->execute([
                    'id_garantie' => $idGarantieSource,
                    'id_categorie' => $currentCategorie
                ]);
                $source = $stmtSource->fetch(PDO::FETCH_ASSOC);

                if ($source) {
                    $stmtInsertGarantie->execute([
                        'nom_garantie' => $source['nom_garantie'],
                        'description_garantie' => $source['description_garantie'],
                        'plafond_couvert_garantie' => $source['plafond_couvert_garantie'],
                        'niveau_couvert_garantie' => $niveauGarantie,
                        'id_formule' => $id,
                        'id_categorie' => $source['id_categorie']
                    ]);
                }
            }

            $db->commit();

            header("Location: showCategorie.php?id=" . $currentCategorie);
            exit;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $errors[] = 'Erreur lors de la modification : ' . $e->getMessage();
        }
    }
}

$garantiesCatalogue = [];
try {
    $stmtCatalogue = $db->prepare("
        SELECT id_garantie, nom_garantie, description_garantie, plafond_couvert_garantie
        FROM garantie
        WHERE id_categorie = :id_categorie
          AND id_formule IS NULL
        ORDER BY nom_garantie ASC
    ");
    $stmtCatalogue->execute(['id_categorie' => $currentCategorie]);
    $garantiesCatalogue = $stmtCatalogue->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $garantiesCatalogue = [];
}

$selectedGaranties = [];
$selectedLevels = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $currentNom = $_POST['nom_formule'] ?? '';
    $currentDescription = $_POST['description_formule'] ?? '';
    $currentPrix = $_POST['prix_formule'] ?? '0';
    $currentNiveau = $_POST['niveau_formule'] ?? '';
    $selectedGaranties = array_map('strval', $_POST['garanties'] ?? []);
    $selectedLevels = $_POST['niveau_garantie'] ?? [];
} else {
    $currentNom = $formuleData['nom_formule'] ?? '';
    $currentDescription = $formuleData['description_formule'] ?? '';
    $currentPrix = $formuleData['prix_formule'] ?? '0';
    $currentNiveau = $formuleData['niveau_formule'] ?? '';

    $stmtLinked = $db->prepare("
        SELECT nom_garantie, niveau_couvert_garantie
        FROM garantie
        WHERE id_formule = :id_formule
    ");
    $stmtLinked->execute(['id_formule' => $id]);
    $linked = $stmtLinked->fetchAll(PDO::FETCH_ASSOC);

    $mapByName = [];
    foreach ($linked as $item) {
        $mapByName[$item['nom_garantie']] = $item['niveau_couvert_garantie'] ?? 'basique';
    }

    foreach ($garantiesCatalogue as $g) {
        if (isset($mapByName[$g['nom_garantie']])) {
            $selectedGaranties[] = (string)$g['id_garantie'];
            $selectedLevels[$g['id_garantie']] = $mapByName[$g['nom_garantie']];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier formule</title>
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
    </style>
</head>
<body>
<div class="content" style="padding:40px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Modifier la formule</div>
        </div>

        <form method="POST" id="formuleForm" style="padding:24px;">
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
                       value="<?= htmlspecialchars($currentNom) ?>">
                <div id="error_nom_formule" class="field-error"></div>
            </div>

            <div class="form-group">
                <label>DESCRIPTION <span style="color:red;">*</span></label>
                <textarea class="form-control" id="description_formule" name="description_formule"
                          rows="4"><?= htmlspecialchars($currentDescription) ?></textarea>
                <div id="error_description_formule" class="field-error"></div>
            </div>

            <div class="form-group">
                <label>PRIX <span style="color:red;">*</span></label>
                <input type="text" id="prix_formule" class="form-control" name="prix_formule"
                       value="<?= htmlspecialchars((string)$currentPrix) ?>">
                <div id="error_prix_formule" class="field-error"></div>
            </div>

            <div class="form-group">
                <label>NIVEAU <span style="color:red;">*</span></label>
                <select class="form-control" id="niveau_formule" name="niveau_formule">
                    <option value="">-- Sélectionner un niveau --</option>
                    <option value="Essentiel" <?= $currentNiveau === 'Essentiel' ? 'selected' : '' ?>>Essentiel</option>
                    <option value="Intermédiaire" <?= $currentNiveau === 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                    <option value="Premium" <?= $currentNiveau === 'Premium' ? 'selected' : '' ?>>Premium</option>
                </select>
                <div id="error_niveau_formule" class="field-error"></div>
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
                                $isChecked = in_array((string)$idGarantie, $selectedGaranties, true);
                                $niveauSaved = $selectedLevels[$idGarantie] ?? 'basique';
                            ?>
                            <div class="garantie-item" data-categorie="<?= (int)$currentCategorie ?>">
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

            <div class="modal-footer" style="padding:0;border-top:none;">
                <a href="showCategorie.php?id=<?= (int)$formuleData['id_categorie'] ?>" class="btn btn-outline">Annuler</a>
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
    if (parseFloat(value) < 0) return setError(input, error, 'Le prix doit être positif'), false;
    return setSuccess(input, error), true;
}
function validateNiveauFormule() {
    const input = document.getElementById('niveau_formule');
    const error = document.getElementById('error_niveau_formule');
    if (input.value.trim() === '') return setError(input, error, 'Niveau obligatoire'), false;
    return setSuccess(input, error), true;
}
function validateCategorieFormule() {
    const input = document.getElementById('id_categorie');
    const error = document.getElementById('error_id_categorie');
    if (input.value.trim() === '') return setError(input, error, 'Catégorie obligatoire'), false;
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
    const categorie = document.getElementById('id_categorie');

    nom.addEventListener('keypress', blockNumbersForText);
    desc.addEventListener('keypress', blockNumbersForText);
    nom.addEventListener('paste', blockPasteNumbers);
    desc.addEventListener('paste', blockPasteNumbers);

    nom.addEventListener('input', validateNomFormule);
    desc.addEventListener('input', validateDescriptionFormule);
    prix.addEventListener('input', validatePrixFormule);
    niveau.addEventListener('change', validateNiveauFormule);
    categorie.addEventListener('change', validateCategorieFormule);

    document.querySelectorAll('.garantie-checkbox').forEach(cb => {
        cb.addEventListener('change', validateGarantiesSelection);
    });

    form.addEventListener('submit', function(e) {
        const ok = validateNomFormule() &&
                   validateDescriptionFormule() &&
                   validatePrixFormule() &&
                   validateNiveauFormule() &&
                   validateCategorieFormule() &&
                   validateGarantiesSelection();
        if (!ok) e.preventDefault();
    });
});
</script>
</body>
</html>
