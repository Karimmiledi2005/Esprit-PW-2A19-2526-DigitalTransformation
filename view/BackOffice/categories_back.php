<?php
require_once __DIR__ . '/../../controller/CategorieController.php';

$categorieC = new CategorieController();
$list = $categorieC->listCategories();

$totalCategories = $categorieC->countCategories();
$totalGaranties = $categorieC->countGarantiesLiees();
$totalContrats = $categorieC->countContratsLiees();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catégories — Protex Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="user/css/assets_contrats/css/variables.css">
    <link rel="stylesheet" href="user/css/assets_contrats/css/base.css">
    <link rel="stylesheet" href="user/css/assets_contrats/css/layout.css">
    <link rel="stylesheet" href="user/css/assets_contrats/css/contrats.css">

    <link rel="stylesheet" href="user/css/variables.css">
    <link rel="stylesheet" href="user/css/base.css">
    <link rel="stylesheet" href="user/css/layout.css">
    <link rel="stylesheet" href="user/css/admin-users.css">
    <link rel="stylesheet" href="user/css/validation.css">
    <link rel="stylesheet" href="user/css/animations.css">
  <script src="assets/js/validation.js"></script>

<style>
.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 16px 24px;
    border-top: 1px solid var(--glass-border);
}
.pagination-info {
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 600;
}
.pagination-btns {
    display: flex;
    align-items: center;
    gap: 8px;
}
.page-btn {
    min-width: 38px;
    height: 38px;
    border-radius: 12px;
    border: 1px solid var(--glass-border);
    background: rgba(255,255,255,.06);
    color: var(--text-primary);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}
.page-btn.active {
    border-color: var(--accent);
    color: var(--accent);
    background: rgba(255, 107, 26, .13);
}
.page-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}
@media (max-width: 700px) {
    .pagination { flex-direction: column; align-items: stretch; }
    .pagination-btns { justify-content: center; flex-wrap: wrap; }
}
</style>
</head>
<body>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">

    <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
            <img src="../FrontOffice/logo.png" alt="logo" width="40" height="40">
            <div>
                <div class="logo-text">Protex</div>
                <div class="logo-sub">Back-Office</div>
            </div>
        </div>

    <div class="sidebar-user">
    <div class="user-avatar">KM</div>
    <div>
        <div class="user-name">Karim Miledi</div>
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

      <a class="nav-item" href="contrats_back.php">
        <i class="bi bi-file-earmark-text"></i> Contrats
      </a>

      <a class="nav-item active" href="categories_back.php">
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

      <a class="nav-item" href="reponse.html">
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
                <div class="topbar-title">Gestion des catégories</div>
                <div class="topbar-sub" id="topbarDate"></div>
            </div>
        </div>

        <div class="content">

           <div class="page-header-bar">
    <div>
        <div class="page-title">Catégories</div>
        <div class="page-breadcrumb">
            <i class="bi bi-house"></i>
            <a href="#">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:10px;"></i>
            <span>Catégories</span>
        </div>
    </div>
    <div>
        <a href="addCategorie.php" class="btn btn-primary">
            <i class="bi bi-plus"></i> Ajouter une catégorie
        </a>
    </div>
</div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="bi bi-grid-3x3-gap"></i></div>
                    <div class="stat-value"><?= $totalCategories ?></div>
                    <div class="stat-label">Total catégories</div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="stat-value"><?= $totalContrats ?></div>
                    <div class="stat-label">Contrats liés</div>
                </div>

                <div class="stat-card gold">
                    <div class="stat-icon"><i class="bi bi-shield-check"></i></div>
                    <div class="stat-value"><?= $totalGaranties ?></div>
                    <div class="stat-label">Garanties liées</div>
                </div>

                <div class="stat-card red">
                    <div class="stat-icon"><i class="bi bi-folder2-open"></i></div>
                    <div class="stat-value"><?= $totalCategories ?></div>
                    <div class="stat-label">Structures disponibles</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="bi bi-table"></i> Liste des catégories</div>
                </div>

                <div class="toolbar-inner">
                    <div class="toolbar" style="margin-bottom:0;">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchInput" placeholder="Rechercher par nom ou description...">
                        </div>
                    </div>
                </div>

                <div class="table-wrap">
                    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Contrats liés</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody id="categoriesTable">

            <?php if (!empty($list)) { ?>

                <?php foreach ($list as $cat) { ?>
                    <tr data-search="<?= htmlspecialchars(strtolower(($cat['nom_categorie'] ?? '') . ' ' . ($cat['description_categorie'] ?? ''))) ?>">

                        <td>#<?= (int)$cat['id_categorie'] ?></td>

                        <td class="category-name">
                            <?= htmlspecialchars($cat['nom_categorie'] ?? '—') ?>
                        </td>

                        <td class="category-desc">
                            <?= htmlspecialchars($cat['description_categorie'] ?? '—') ?>
                        </td>

                        <td>
                            <?= (int)($cat['nb_contrats'] ?? 0) ?>
                        </td>

                        <td>
                            <div class="actions">

                                <!-- VOIR -->
                                <a class="btn-soft"
                                   href="showCategorie.php?id=<?= (int)$cat['id_categorie'] ?>">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <!-- MODIFIER -->
                                <a class="btn-soft"
                                   href="updateCategorie.php?id=<?= (int)$cat['id_categorie'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- SUPPRIMER (bloqué si lié) -->
                                <?php if ((int)($cat['nb_contrats'] ?? 0) === 0) { ?>
                                    <a class="btn-soft danger"
                                       href="deleteCategorie.php?id=<?= (int)$cat['id_categorie'] ?>"
                                       onclick="return confirm('Supprimer cette catégorie ?');">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                <?php } else { ?>
                                    <span class="btn-soft disabled"
                                          title="Impossible : catégorie liée à des contrats">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                <?php } ?>

                            </div>
                        </td>

                    </tr>
                <?php } ?>

            <?php } else { ?>

                <!-- Aucun résultat -->
                <tr>
                    <td colspan="5" style="text-align:center; padding:20px;">
                        Aucune catégorie trouvée
                    </td>
                </tr>

            <?php } ?>

        </tbody>
    </table>
</div>

                    <div id="emptyState" class="empty-box" style="display:none;">
                        <i class="bi bi-folder-x"></i>
                        <strong>Aucune catégorie trouvée</strong>
                    </div>
                </div>

                <div class="pagination">
                    <div class="pagination-info" id="paginationInfo"></div>
                    <div class="pagination-btns" id="paginationBtns"></div>
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

const categoriesPerPage = 8;
let categoriesCurrentPage = 1;
let categoriesFilteredRows = [];

function normalizeText(text) {
    return (text || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function applyCategoryPagination() {
    const rows = Array.from(document.querySelectorAll('#categoriesTable tr[data-search]'));
    const value = normalizeText(document.getElementById('searchInput').value.trim());
    const keywords = value.split(/\s+/).filter(Boolean);

    categoriesFilteredRows = rows.filter(row => {
        const search = normalizeText(row.dataset.search || '');
        return keywords.length === 0 || keywords.every(word => search.includes(word));
    });

    const total = categoriesFilteredRows.length;
    const totalPages = Math.max(1, Math.ceil(total / categoriesPerPage));
    if (categoriesCurrentPage > totalPages) categoriesCurrentPage = totalPages;

    rows.forEach(row => row.style.display = 'none');

    const start = (categoriesCurrentPage - 1) * categoriesPerPage;
    const end = start + categoriesPerPage;
    categoriesFilteredRows.slice(start, end).forEach(row => row.style.display = '');

    document.getElementById('emptyState').style.display = total ? 'none' : 'block';

    const paginationInfo = document.getElementById('paginationInfo');
    const paginationBtns = document.getElementById('paginationBtns');

    const shownStart = total === 0 ? 0 : start + 1;
    const shownEnd = Math.min(end, total);
    paginationInfo.textContent = `Affichage ${shownStart}–${shownEnd} sur ${total} catégorie${total > 1 ? 's' : ''}`;

    let html = `
        <button class="page-btn" onclick="goCategoryPage(${categoriesCurrentPage - 1})" ${categoriesCurrentPage <= 1 ? 'disabled' : ''}>
            <i class="bi bi-chevron-left"></i>
        </button>
    `;

    const maxButtons = 7;
    let startPage = Math.max(1, categoriesCurrentPage - 3);
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) startPage = Math.max(1, endPage - maxButtons + 1);

    for (let p = startPage; p <= endPage; p++) {
        html += `<button class="page-btn ${p === categoriesCurrentPage ? 'active' : ''}" onclick="goCategoryPage(${p})">${p}</button>`;
    }

    html += `
        <button class="page-btn" onclick="goCategoryPage(${categoriesCurrentPage + 1})" ${categoriesCurrentPage >= totalPages ? 'disabled' : ''}>
            <i class="bi bi-chevron-right"></i>
        </button>
    `;

    paginationBtns.innerHTML = html;
}

function goCategoryPage(page) {
    const totalPages = Math.max(1, Math.ceil(categoriesFilteredRows.length / categoriesPerPage));
    if (page < 1 || page > totalPages) return;
    categoriesCurrentPage = page;
    applyCategoryPagination();
}

document.getElementById('searchInput').addEventListener('input', function () {
    categoriesCurrentPage = 1;
    applyCategoryPagination();
});

applyCategoryPagination();
</script>
<script>
fetch("get_admin.php")
.then(res => res.json())
.then(data => {
    if (!data || data.error) {
        window.location.href = "../FrontOffice/login.html";
        return;
    }

    const initiales = ((data.nom || '').charAt(0) + (data.prenom || '').charAt(0)).toUpperCase();

    const avatarValue = data.avatar || '';
    const skipAvatar = !avatarValue || avatarValue === 'default.png' || avatarValue === 'default' || avatarValue.trim() === '';
    const avatarPath = skipAvatar ? '' : (avatarValue.includes('/') ? avatarValue : '../../uploads/avatars/' + avatarValue);

    document.querySelectorAll('.user-avatar').forEach(el => {
        if (avatarPath) {
            el.innerHTML = `<img src="${avatarPath}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.parentElement.textContent='${initiales}'">`;
        } else {
            el.textContent = initiales || 'AD';
        }
    });

    const nameEl = document.querySelector('.user-name');
    if (nameEl) {
        nameEl.textContent = (data.prenom || '') + ' ' + (data.nom || '');
    }

    const roleEl = document.querySelector('.user-role');
    if (roleEl) {
        let roleTxt = data.role || 'admin';
        roleTxt = roleTxt.charAt(0).toUpperCase() + roleTxt.slice(1);

        if (data.nom_agence) {
            roleTxt += ' (' + data.nom_agence + ')';
        }

        roleEl.textContent = roleTxt;
    }
})
.catch(() => {
    window.location.href = "../FrontOffice/login.html";
});
</script>

<script src="user/js/main.js"></script>
<script src="user/js/admin.js"></script>
</body>
</html>
