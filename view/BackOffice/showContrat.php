<?php
require_once __DIR__ . '/../../controller/ContratController.php';

if (!isset($_GET['id'])) {
    die("ID contrat manquant.");
}

$id = (int)$_GET['id'];
$contratC = new ContratController();
$contratData = $contratC->getById($id);

if (!$contratData) {
    die("Contrat introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail contrat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/admin-users.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
    <link rel="stylesheet" href="assets/css/forms.css">
</head>
<body>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">🛡️</div>
            <div>
                <div class="logo-text">Protex</div>
                <div class="logo-sub">Back-Office</div>
            </div>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">AD</div>
            <div>
                <div class="user-name">Agent Admin</div>
                <span class="user-role">Administrateur</span>
            </div>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Détail du contrat</div>
                <div class="topbar-sub" id="topbarDate"></div>
            </div>
        </div>

        <div class="content">
            <div class="page-header-bar">
                <div>
                    <div class="page-title">Contrat #<?= (int)$contratData['id_contrat'] ?></div>
                    <div class="page-breadcrumb">
                        <i class="bi bi-house"></i>
                        <a href="#">Accueil</a>
                        <i class="bi bi-chevron-right" style="font-size:10px;"></i>
                        <a href="contrats_back.php">Contrats</a>
                        <i class="bi bi-chevron-right" style="font-size:10px;"></i>
                        <span>Détail #<?= (int)$contratData['id_contrat'] ?></span>
                    </div>
                </div>
                <div>
                    <a href="updateContrat.php?id=<?= (int)$contratData['id_contrat'] ?>" class="btn btn-secondary">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                    <a href="deleteContrat.php?id=<?= (int)$contratData['id_contrat'] ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contrat ?');">
                        <i class="bi bi-trash3"></i> Supprimer
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Informations générales</div>
                </div>

                <div style="padding:24px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">NUMÉRO DE CONTRAT</div>
                            <div style="font-weight:600; font-size:16px;">
                                <?= htmlspecialchars($contratData['numero_contrat']) ?>
                            </div>
                        </div>

                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">TYPE DE CONTRAT</div>
                            <div style="font-weight:600; font-size:16px;">
                                <?= htmlspecialchars($contratData['type_contrat']) ?>
                            </div>
                        </div>

                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">CATÉGORIE</div>
                            <div style="font-weight:600; font-size:16px;">
                                <?= htmlspecialchars($contratData['nom_categorie'] ?? '—') ?>
                            </div>
                        </div>

                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">STATUT</div>
                            <div style="font-weight:600; font-size:16px;">
                                <span style="display:inline-block; padding:4px 12px; border-radius:4px; 
                                           background:<?= $contratData['statut_contrat'] === 'actif' ? '#4CAF50' : ($contratData['statut_contrat'] === 'en attente' ? '#FF9800' : '#f44336') ?>;
                                           color:white; font-size:12px;">
                                    <?= htmlspecialchars($contratData['statut_contrat']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Informations financières</div>
                </div>

                <div style="padding:24px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">PRIME</div>
                            <div style="font-weight:600; font-size:16px;">
                                <?= number_format((float)$contratData['prime_contrat'], 2, ',', ' ') ?> €
                            </div>
                        </div>

                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">FRANCHISE</div>
                            <div style="font-weight:600; font-size:16px;">
                                <?= number_format((float)$contratData['franchise_contrat'], 2, ',', ' ') ?> €
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Périodes de couverture</div>
                </div>

                <div style="padding:24px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">DATE DE DÉBUT</div>
                            <div style="font-weight:600; font-size:16px;">
                                <?= date('d/m/Y', strtotime($contratData['date_debut_contrat'])) ?>
                            </div>
                        </div>

                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">DATE DE FIN</div>
                            <div style="font-weight:600; font-size:16px;">
                                <?= date('d/m/Y', strtotime($contratData['date_fin_contrat'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Informations client</div>
                </div>

                <div style="padding:24px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">ID CLIENT</div>
                            <div style="font-weight:600; font-size:16px;">
                                <?= (int)$contratData['id_client'] ?>
                            </div>
                        </div>

                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">NOM COMPLET</div>
                            <div style="font-weight:600; font-size:16px;">
                                <?= htmlspecialchars(($contratData['prenom'] ?? '') . ' ' . ($contratData['nom'] ?? '')) ?: '—' ?>
                            </div>
                        </div>

                        <div>
                            <div style="color:#999; font-size:12px; margin-bottom:5px;">EMAIL</div>
                            <div style="font-weight:600; font-size:16px;">
                                <?= htmlspecialchars($contratData['email'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top:20px;">
                <a href="contrats_back.php" class="btn btn-outline">← Retour</a>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('topbarDate').textContent = new Date().toLocaleDateString('fr-FR', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
});
</script>
</body>
</html>
