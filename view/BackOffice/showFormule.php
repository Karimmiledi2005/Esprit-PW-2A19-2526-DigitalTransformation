<?php
require_once '../../controller/FormuleController.php';
require_once '../../config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID formule manquant.");
}

$id = (int)$_GET['id'];

$formuleC = new FormuleController();
$formule = $formuleC->showFormule($id);

if (!$formule) {
    die("Formule introuvable.");
}

$db = config::getConnexion();

$sql = "
    SELECT
        g.*,
        fg.niveau_couvert_garantie AS niveau_couvert_garantie
    FROM formule_garantie fg
    INNER JOIN garantie g ON g.id_garantie = fg.id_garantie
    WHERE fg.id_formule = :id_formule
    ORDER BY g.nom_garantie ASC
";
$stmt = $db->prepare($sql);
$stmt->execute(['id_formule' => $id]);
$garanties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail formule</title>

    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
</head>

<body>

<div class="content" style="padding:40px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2><?= htmlspecialchars($formule['nom_formule']) ?></h2>

        <a href="showCategorie.php?id=<?= $formule['id_categorie'] ?>" class="btn btn-outline">
            Retour
        </a>
    </div>

    <!-- INFOS -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <div class="card-title">Informations formule</div>
        </div>

        <div style="padding:20px;">
            <p><strong>Description :</strong> <?= htmlspecialchars($formule['description_formule']) ?></p>
            <p><strong>Prix :</strong> <?= number_format($formule['prix_formule'], 2) ?> DT</p>
            <p><strong>Franchise :</strong> <?= number_format((float)($formule['franchise_formule'] ?? 0), 2) ?> DT</p>
            <p><strong>Niveau :</strong> <?= htmlspecialchars($formule['niveau_formule']) ?></p>
        </div>
    </div>

    <!-- GARANTIES -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Garanties</div>
        </div>

        <div style="padding:20px;">

            <?php if (!empty($garanties)) { ?>
                <ul style="list-style:none; padding:0;">
                    <?php foreach ($garanties as $g) { ?>

                        <?php
                        $niveau = $g['niveau_couvert_garantie'];

                        if ($niveau === 'basique') {
                            $icon = '✔';
                            $color = 'green';
                        } elseif ($niveau === 'option') {
                            $icon = '+';
                            $color = 'orange';
                        } else {
                            $icon = '✖';
                            $color = 'gray';
                        }
                        ?>

                        <li style="margin-bottom:10px; color:<?= $color ?>;">
                            <?= $icon ?> <?= htmlspecialchars($g['nom_garantie']) ?>
                            (<?= htmlspecialchars($niveau) ?>)
                        </li>

                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>Aucune garantie.</p>
            <?php } ?>

        </div>
    </div>

</div>

</body>
</html>