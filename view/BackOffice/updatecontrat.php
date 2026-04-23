<?php
require_once __DIR__ . '/../../controller/ContratController.php';
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../model/Contrat.php';

if (!isset($_GET['id'])) {
    die("ID contrat manquant.");
}

$id = (int)$_GET['id'];
$contratC = new ContratController();
$categorieC = new CategorieController();

$contratData = $contratC->getById($id);
$categories = $categorieC->listCategories();

if (!$contratData) {
    die("Contrat introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = trim($_POST['numero_contrat'] ?? '');
    $type = trim($_POST['type_contrat'] ?? '');
    $client = (int)($_POST['id_client'] ?? 0);
    $categorie = (int)($_POST['id_categorie'] ?? 0);
    $prime = (float)($_POST['prime_contrat'] ?? 0);
    $franchise = (float)($_POST['franchise_contrat'] ?? 0);
    $dateDebut = trim($_POST['date_debut_contrat'] ?? '');
    $dateFin = trim($_POST['date_fin_contrat'] ?? '');
    $statut = trim($_POST['statut_contrat'] ?? 'en attente');

    if ($numero && $type && $client > 0 && $categorie > 0) {
        $contrat = new Contrat(
            $numero,
            $type,
            $client,
            $categorie,
            $prime,
            $franchise,
            $dateDebut,
            $dateFin,
            $statut
        );

        if ($contratC->updateContrat($id, $contrat)) {
            header('Location: showContrat.php?id=' . $id . '&success=1');
            exit();
        } else {
            $error = 'Erreur lors de la modification du contrat.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier contrat</title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/admin-users.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
    <link rel="stylesheet" href="assets/css/forms.css">
</head>
<body>
<div class="content" style="padding:40px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Modifier le contrat #<?= (int)$contratData['id_contrat'] ?></div>
        </div>

        <?php if (isset($error)): ?>
            <div style="padding:20px; background:#ffebee; color:#c62828; margin:10px 0; border-radius:4px;">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="padding:24px;" onsubmit="return validateContratForm(this)">
            <div class="form-group">
                <label>Numéro contrat <span style="color:red;">*</span></label>
                <input type="text" 
                       class="form-control" 
                       name="numero_contrat"
                       pattern="^[A-Z0-9\-]{5,20}$"
                       value="<?= htmlspecialchars($contratData['numero_contrat']) ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Type de contrat <span style="color:red;">*</span></label>
                <select class="form-control" name="type_contrat" required>
                    <option value="">-- Sélectionner un type --</option>
                    <option value="Auto" <?= $contratData['type_contrat'] === 'Auto' ? 'selected' : '' ?>>Auto</option>
                    <option value="Habitation" <?= $contratData['type_contrat'] === 'Habitation' ? 'selected' : '' ?>>Habitation</option>
                    <option value="Santé" <?= $contratData['type_contrat'] === 'Santé' ? 'selected' : '' ?>>Santé</option>
                    <option value="Protection" <?= $contratData['type_contrat'] === 'Protection' ? 'selected' : '' ?>>Protection</option>
                    <option value="Voyages" <?= $contratData['type_contrat'] === 'Voyages' ? 'selected' : '' ?>>Voyages</option>
                </select>
            </div>

            <div class="form-group">
                <label>Client <span style="color:red;">*</span></label>
                <input type="number" 
                       class="form-control" 
                       name="id_client"
                       min="1"
                       value="<?= (int)$contratData['id_client'] ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Catégorie <span style="color:red;">*</span></label>
                <select class="form-control" name="id_categorie" required>
                    <option value="">-- Sélectionner une catégorie --</option>
                    <?php
                    foreach ($categories as $cat) {
                        $selected = ((int)$cat['id_categorie'] === (int)$contratData['id_categorie']) ? 'selected' : '';
                        echo '<option value="' . (int)$cat['id_categorie'] . '" ' . $selected . '>';
                        echo htmlspecialchars($cat['nom_categorie']);
                        echo '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex:1;">
                    <label>Prime <span style="color:red;">*</span></label>
                    <input type="number" 
                           step="0.01"
                           min="0"
                           max="999999.99"
                           class="form-control" 
                           name="prime_contrat"
                           value="<?= htmlspecialchars($contratData['prime_contrat']) ?>"
                           required>
                </div>

                <div class="form-group" style="flex:1; margin-left:10px;">
                    <label>Franchise <span style="color:red;">*</span></label>
                    <input type="number" 
                           step="0.01"
                           min="0"
                           max="999999.99"
                           class="form-control" 
                           name="franchise_contrat"
                           value="<?= htmlspecialchars($contratData['franchise_contrat']) ?>"
                           required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex:1;">
                    <label>Date de début <span style="color:red;">*</span></label>
                    <input type="date" 
                           class="form-control" 
                           name="date_debut_contrat"
                           value="<?= htmlspecialchars($contratData['date_debut_contrat']) ?>"
                           required>
                </div>

                <div class="form-group" style="flex:1; margin-left:10px;">
                    <label>Date de fin <span style="color:red;">*</span></label>
                    <input type="date" 
                           class="form-control" 
                           name="date_fin_contrat"
                           value="<?= htmlspecialchars($contratData['date_fin_contrat']) ?>"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label>Statut <span style="color:red;">*</span></label>
                <select class="form-control" name="statut_contrat" required>
                    <option value="en attente" <?= $contratData['statut_contrat'] === 'en attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="actif" <?= $contratData['statut_contrat'] === 'actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="expiré" <?= $contratData['statut_contrat'] === 'expiré' ? 'selected' : '' ?>>Expiré</option>
                    <option value="résilié" <?= $contratData['statut_contrat'] === 'résilié' ? 'selected' : '' ?>>Résilié</option>
                </select>
            </div>

            <div class="modal-footer" style="padding:0; border-top:none;">
                <a href="showContrat.php?id=<?= $id ?>" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Modifier</button>
            </div>
        </form>
    </div>
</div>

<style>
.form-row {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.form-row .form-group {
    flex: 1;
    margin-bottom: 0;
}
</style>

<script src="assets/js/validation-forms.js"></script>
</body>
</html>
