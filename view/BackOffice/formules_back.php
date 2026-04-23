<?php
require_once __DIR__ . '/../../controller/FormuleController.php';

$formuleC = new FormuleController();
$list = $formuleC->listFormules();
$totalFormules = $formuleC->countFormules();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formules — Protex Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/admin-users.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
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

    <nav class="sidebar-nav">
      <div class="nav-section">Principal</div>
      <a class="nav-item" href="#">
        <i class="bi bi-grid-1x2"></i> Tableau de bord
      </a>

      <div class="nav-section">Gestion</div>
      <a class="nav-item" href="admin-users.html">
        <i class="bi bi-people"></i> Utilisateurs
        <span class="nav-badge accent">24</span>
      </a>

      <a class="nav-item" href="sinsiter.html">
        <i class="bi bi-shield-exclamation"></i> Sinistres
      </a>

      <a class="nav-item" href="traitement.html">
        <i class="bi bi-file-earmark-text"></i> Traitements
      </a>

      <a class="nav-item active" href="contrats_back.php">
        <i class="bi bi-file-earmark-text"></i> Contrats
      </a>

      <a class="nav-item" href="categories_back.php">
        <i class="bi bi-grid-3x3-gap"></i> Catégories
      </a>

      <a class="nav-item" href="garanties_back.php">
        <i class="bi bi-shield-check"></i> Garanties
      </a>

      <a class="nav-item" href="paiements_back.html">
        <i class="bi bi-credit-card"></i> Paiements
      </a>

      <a class="nav-item" href="offres_back.html">
        <i class="bi bi-tags"></i> Offres
      </a>

      <a class="nav-item" href="admin-reclamations.html">
        <i class="bi bi-chat-dots"></i> Réclamations
      </a>

      <a class="nav-item" href="admin-agences.html">
        <i class="bi bi-geo-alt"></i> Agences
      </a>

      <div class="nav-section">Compte</div>
      <a class="nav-item" href="adminprofile.html">
        <i class="bi bi-person-gear"></i> Mon profil
      </a>
    </nav>

    <div class="sidebar-footer">
      <a href="#" class="logout-btn">
        <i class="bi bi-box-arrow-left"></i> Se déconnecter
      </a>
    </div>
  </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Gestion des formules</div>
                <div class="topbar-sub" id="topbarDate"></div>
            </div>
        </div>

        <div class="content">
            <div class="page-header-bar">
                <div>
                    <div class="page-title">Formules</div>
                    <div class="page-breadcrumb">
                        <i class="bi bi-house"></i>
                        <a href="#">Accueil</a>
                        <i class="bi bi-chevron-right" style="font-size:10px;"></i>
                        <span>Formules</span>
                    </div>
                </div>

                <a href="addFormule.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Ajouter une formule
                </a>
            </div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="bi bi-list-check"></i></div>
                    <div class="stat-value"><?= $totalFormules ?></div>
                    <div class="stat-label">Total formules</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="bi bi-table"></i> Liste des formules</div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
<th>Nom</th>
<th>Description</th>
<th>Prix</th>
<th>Niveau</th>
<th>Catégorie</th>
<th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $f) { ?>
                                <tr>
                                    <td>#<?= (int)$f['id_formule'] ?></td>
                                    <td><?= htmlspecialchars($f['nom_formule']) ?></td>
                                    <td><?= htmlspecialchars($f['description_formule'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($f['prix_formule'] ?? '0') ?> DT</td>
<td><?= htmlspecialchars($f['niveau_formule'] ?? '—') ?></td>
<td><?= htmlspecialchars($f['nom_categorie'] ?? '—') ?></td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn-soft" href="showFormule.php?id=<?= (int)$f['id_formule'] ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a class="btn-soft" href="updateFormule.php?id=<?= (int)$f['id_formule'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a class="btn-soft danger" href="deleteFormule.php?id=<?= (int)$f['id_formule'] ?>" onclick="return confirm('Supprimer cette formule ?');">
                                                <i class="bi bi-trash3"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('topbarDate').textContent =
    new Date().toLocaleDateString('fr-FR', {
        weekday:'long',
        day:'numeric',
        month:'long',
        year:'numeric'
    });
</script>

</body>
</html>