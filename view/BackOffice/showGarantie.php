<?php
require_once '../../controller/GarantieController.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID garantie manquant.");
}

$id = (int)$_GET['id'];

$controller = new GarantieController();
$garantie = $controller->showGarantie($id);

if (!$garantie) {
    die("Garantie introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail garantie</title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/admin-users.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
    <link rel="stylesheet" href="assets/css/forms.css">
    <style>
        .detail-grid{
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap:18px;
        }
        .detail-box{
            padding:16px;
            border-radius:14px;
            background:rgba(255,255,255,0.04);
            border:1px solid rgba(255,255,255,0.08);
        }
        .detail-box.full{
            grid-column:1 / -1;
        }
        .detail-label{
            color:rgba(255,255,255,0.65);
            font-size:13px;
            margin-bottom:8px;
            text-transform:uppercase;
            letter-spacing:.4px;
        }
        .detail-value{
            color:#fff;
            font-weight:600;
        }
        @media (max-width: 768px){
            .detail-grid{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body>
<div class="content" style="padding:40px;">
    <div class="card" style="max-width:900px;margin:auto;">
        <div class="card-header">
            <div class="card-title">Détail de la garantie</div>
        </div>

        <div style="padding:24px;">
            <div class="detail-grid">
                <div class="detail-box">
                    <div class="detail-label">ID Garantie</div>
                    <div class="detail-value">#<?= (int)$garantie['id_garantie'] ?></div>
                </div>

                <div class="detail-box">
                    <div class="detail-label">Nom</div>
                    <div class="detail-value"><?= htmlspecialchars($garantie['nom_garantie']) ?></div>
                </div>

                <div class="detail-box full">
                    <div class="detail-label">Description</div>
                    <div class="detail-value"><?= nl2br(htmlspecialchars($garantie['description_garantie'])) ?></div>
                </div>

                <div class="detail-box">
                    <div class="detail-label">Plafond de couverture</div>
                    <div class="detail-value"><?= number_format((float)$garantie['plafond_couvert_garantie'], 2, '.', ' ') ?> DT</div>
                </div>

                <div class="detail-box">
                    <div class="detail-label">Catégorie</div>
                    <div class="detail-value"><?= htmlspecialchars($garantie['nom_categorie'] ?? '—') ?></div>
                </div>


            </div>

            <div class="modal-footer" style="padding:24px 0 0 0;border-top:none;">
                <a href="garanties_back.php" class="btn btn-outline">Retour</a>
                <a href="updateGarantie.php?id=<?= (int)$garantie['id_garantie'] ?>" class="btn btn-primary">Modifier</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
