<?php
require_once __DIR__ . '/../../controller/GarantieController.php';

$controller = new GarantieController();
$garanties = $controller->listGaranties();

$totalGaranties = count($garanties);
$categoriesListe = [];

foreach ($garanties as $g) {
    $cat = $g->getNomCategorie();
    if ($cat) {
        $categoriesListe[] = $cat;
    }
}

$categoriesLiees = count(array_unique($categoriesListe));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Garanties — Protex Admin</title>
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
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="../FrontOffice/logo.png" alt="logo" width="40" height="40">
            <div>
                <div class="logo-text">Protex</div>
                <div class="logo-sub">Back-Office</div>
            </div>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">AD</div>
            <div>
                <div class="user-name">Admin Protex</div>
                <span class="user-role">Administrateur</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">Principal</div>
            <a class="nav-item" href="admin.html"><i class="bi bi-grid-1x2"></i> Tableau de bord</a>

            <div class="nav-section">Gestion</div>
            <a class="nav-item" href="sinsiter.html"><i class="bi bi-shield-exclamation"></i> Sinistres</a>
            <a class="nav-item" href="traitement.html"><i class="bi bi-file-earmark-text"></i> Traitements</a>
            <a class="nav-item" href="admin-users.html"><i class="bi bi-people"></i> Utilisateurs</a>
            <a class="nav-item" href="contrats_back.php"><i class="bi bi-file-earmark-text"></i> Contrats</a>
            <a class="nav-item" href="categories_back.php"><i class="bi bi-grid-3x3-gap"></i> Catégories</a>
            <a class="nav-item active" href="garanties_back.php"><i class="bi bi-shield-check"></i> Garanties</a>

            <div class="nav-section">Compte</div>
            <a class="nav-item" href="adminprofile.html"><i class="bi bi-person-gear"></i> Mon profil</a>
        </nav>

        <div class="sidebar-footer">
            <a href="connexion.html" class="logout-btn"><i class="bi bi-box-arrow-left"></i> Se déconnecter</a>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Gestion des garanties</div>
                <div class="topbar-sub" id="topbarDate"></div>
            </div>
        </div>

        <div class="content">
            <div class="page-header-bar">
                <div>
                    <div class="page-title">Garanties</div>
                    <div class="page-breadcrumb">
                        <i class="bi bi-house"></i>
                        <a href="admin.html">Accueil</a>
                        <i class="bi bi-chevron-right" style="font-size:10px;"></i>
                        <span>Garanties</span>
                    </div>
                </div>
                <a href="addGarantie.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Ajouter une garantie
                </a>
            </div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="bi bi-shield-check"></i></div>
                    <div class="stat-value"><?= $totalGaranties ?></div>
                    <div class="stat-label">Total garanties</div>
                </div>

                <div class="stat-card gold">
                    <div class="stat-icon"><i class="bi bi-grid-3x3-gap"></i></div>
                    <div class="stat-value"><?= $categoriesLiees ?></div>
                    <div class="stat-label">Catégories liées</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="bi bi-table"></i> Liste des garanties</div>
                </div>

                <div style="padding:16px 24px; border-bottom:1px solid var(--glass-border);">
                    <div class="toolbar">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchInput" placeholder="Rechercher une garantie...">
                        </div>

                        <select class="filter-select" id="filterCategory">
                            <option value="">Toutes les catégories</option>
                            <option value="Auto">Auto</option>
                            <option value="Santé">Santé</option>
                            <option value="Habitation">Habitation</option>
                            <option value="Protection">Protection</option>
                        </select>

                        <button class="btn btn-outline btn-sm" onclick="resetFilters()">
                            <i class="bi bi-x-circle"></i> Réinitialiser
                        </button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table id="garantiesTable">
                        <thead>
                            <tr>
                                <th>Garantie</th>
                                <th>Catégorie</th>
                                <th>Plafond</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="garantiesBody">
                            <?php foreach ($garanties as $garantie): ?>
                                <?php
                                    $nom = $garantie->getNomGarantie();
                                    $description = $garantie->getDescriptionGarantie();
                                    $categorie = $garantie->getNomCategorie() ?? '—';
                                    $plafond = $garantie->getPlafondCouvertGarantie();
                                ?>
                                <tr data-name="<?= strtolower(htmlspecialchars($nom)) ?>"
                                    data-category="<?= htmlspecialchars($categorie) ?>">
                                    <td>
                                        <div class="garantie-name"><?= htmlspecialchars($nom) ?></div>
                                        <div class="garantie-desc"><?= htmlspecialchars($description) ?></div>
                                    </td>
                                    <td>
                                        <span class="cat-badge">
                                            <i class="bi bi-folder2-open"></i>
                                            <?= htmlspecialchars($categorie) ?>
                                        </span>
                                    </td>
                                    <td style="color:#fff; font-weight:600;"><?= number_format((float)$plafond, 2, '.', ' ') ?> DT</td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn-soft" title="Voir" href="showGarantie.php?id=<?= $garantie->getIdGarantie() ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a class="btn-soft" title="Modifier" href="updateGarantie.php?id=<?= $garantie->getIdGarantie() ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a class="btn-soft danger" title="Supprimer" href="deleteGarantie.php?id=<?= $garantie->getIdGarantie() ?>" onclick="return confirm('Supprimer cette garantie ?')">
                                                <i class="bi bi-trash3"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterCategory').value = '';
    filterGaranties();
}

function filterGaranties() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('filterCategory').value;
    const rows = document.querySelectorAll('#garantiesBody tr');

    rows.forEach(row => {
        const name = row.dataset.name || '';
        const rowCategory = row.dataset.category || '';

        const matchSearch = !search || name.includes(search);
        const matchCategory = !category || rowCategory === category;

        row.style.display = (matchSearch && matchCategory) ? '' : 'none';
    });
}

document.getElementById('searchInput').addEventListener('input', filterGaranties);
document.getElementById('filterCategory').addEventListener('change', filterGaranties);
</script>

</body>
</html>
