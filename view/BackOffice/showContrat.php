<?php
require_once __DIR__ . '/../../controller/ContratController.php';

$id = (int)($_GET['id'] ?? 0);
$contratC = new ContratController();
$contrat = $contratC->getById($id);
if (!$contrat) die('Contrat introuvable.');

$details = [];
if (!empty($contrat['details_contrat'])) {
    $decoded = json_decode($contrat['details_contrat'], true);
    if (is_array($decoded)) $details = $decoded;
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function valueDetails($v){ return is_array($v) ? implode(', ', array_map('strval', $v)) : (string)$v; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail contrat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/admin-users.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
</head>
<body>
<div class="content" style="padding:40px;">
    <div class="page-header-bar">
        <div>
            <div class="page-title">Contrat <?= h($contrat['numero_contrat']) ?></div>
            <div class="page-breadcrumb"><i class="bi bi-house"></i> Contrats <i class="bi bi-chevron-right" style="font-size:10px;"></i> Détail</div>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="contrats_back.php" class="btn btn-outline">← Retour</a>
            <a href="updateContrat.php?id=<?= (int)$contrat['id_contrat'] ?>" class="btn btn-primary"><i class="bi bi-pencil"></i> Modifier</a>
        </div>
    </div>

    <div class="card" style="padding:24px;margin-top:20px;">
        <div class="card-header"><div class="card-title"><i class="bi bi-file-earmark-text"></i> Informations contrat</div></div>
        <div class="detail-grid" style="padding:20px;">
            <div class="detail-field"><div class="detail-field-label">N° contrat</div><div class="detail-field-value"><?= h($contrat['numero_contrat']) ?></div></div>
            <div class="detail-field"><div class="detail-field-label">Catégorie</div><div class="detail-field-value"><?= h($contrat['nom_categorie'] ?? $contrat['type_contrat']) ?></div></div>
            <div class="detail-field"><div class="detail-field-label">Formule</div><div class="detail-field-value"><?= h($contrat['nom_formule'] ?? $contrat['formule_contrat'] ?? '—') ?></div></div>
            <div class="detail-field"><div class="detail-field-label">Prime</div><div class="detail-field-value"><?= h($contrat['prime_contrat']) ?> DT</div></div>
            <div class="detail-field"><div class="detail-field-label">Franchise</div><div class="detail-field-value"><?= h($contrat['franchise_contrat']) ?> DT</div></div>
            <div class="detail-field"><div class="detail-field-label">Statut</div><div class="detail-field-value"><?= h($contrat['statut_contrat']) ?></div></div>
            <div class="detail-field"><div class="detail-field-label">Date début</div><div class="detail-field-value"><?= h($contrat['date_debut_contrat']) ?></div></div>
            <div class="detail-field"><div class="detail-field-label">Date fin</div><div class="detail-field-value"><?= h($contrat['date_fin_contrat']) ?></div></div>
            <div class="detail-field"><div class="detail-field-label">Client</div><div class="detail-field-value"><?= h(trim(($contrat['prenom'] ?? '').' '.($contrat['nom'] ?? '')) ?: ('ID '.$contrat['id_client'])) ?></div></div>
            <div class="detail-field"><div class="detail-field-label">Email</div><div class="detail-field-value"><?= h($contrat['email'] ?? '—') ?></div></div>
        </div>
    </div>

    <div class="card" style="padding:24px;margin-top:20px;">
        <div class="card-header"><div class="card-title"><i class="bi bi-list-check"></i> Informations remplies par le client</div></div>
        <?php if (empty($details)): ?>
            <div style="padding:20px;color:var(--text-secondary);">Aucun détail spécifique enregistré.</div>
        <?php else: ?>
            <div class="detail-grid" style="padding:20px;">
                <?php foreach ($details as $key => $value): ?>
                    <div class="detail-field">
                        <div class="detail-field-label"><?= h(str_replace('_', ' ', ucfirst($key))) ?></div>
                        <div class="detail-field-value"><?= h(valueDetails($value)) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div style="display:flex;gap:10px;margin-top:20px;">
        <a href="statutContrat.php?id=<?= (int)$contrat['id_contrat'] ?>&statut=actif" class="btn btn-primary">Valider</a>
        <a href="statutContrat.php?id=<?= (int)$contrat['id_contrat'] ?>&statut=refusé" class="btn btn-outline">Refuser</a>
        <a href="statutContrat.php?id=<?= (int)$contrat['id_contrat'] ?>&statut=résilié" class="btn btn-outline">Résilier</a>
        <a href="deleteContrat.php?id=<?= (int)$contrat['id_contrat'] ?>" onclick="return confirm('Supprimer ce contrat ?')" class="btn btn-soft danger">Supprimer</a>
    </div>
</div>
</body>
</html>
