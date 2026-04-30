<?php
require_once __DIR__ . '/../../controller/ContratController.php';

$contratController = new ContratController();
$contrats = $contratController->getAll();

$totalContrats = count($contrats);
$totalActifs = 0;
$totalAttente = 0;
$totalExpires = 0;

foreach ($contrats as $contrat) {
    $statut = strtolower(trim($contrat->getStatutContrat()));

    if ($statut === 'actif') {
        $totalActifs++;
    } elseif ($statut === 'en attente' || $statut === 'en_attente') {
        $totalAttente++;
    } else {
        $totalExpires++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contrats — Protex Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/variables.css">
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/layout.css">
  <link rel="stylesheet" href="assets/css/admin-users.css">
  <link rel="stylesheet" href="assets/css/contrats.css">
  <script src="assets/js/validation.js"></script>
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
        <div class="topbar-title">Gestion des contrats</div>
        <div class="topbar-sub" id="topbarDate"></div>
      </div>
      <div class="topbar-actions">
        <a href="#" class="topbar-btn" title="Notifications">
          <i class="bi bi-bell"></i>
          <span class="notif-dot"></span>
        </a>
        <a href="#" class="topbar-btn" title="Aide">
          <i class="bi bi-question-circle"></i>
        </a>
      </div>
    </div>

    <div class="content">

      <div class="page-header-bar">
        <div>
          <div class="page-title">Contrats</div>
          <div class="page-breadcrumb">
            <i class="bi bi-house"></i>
            <a href="#">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:10px;"></i>
            <span>Contrats</span>
          </div>
        </div>
        <div>
          <a href="addContrat.php" class="btn btn-primary">
            <i class="bi bi-plus"></i> Ajouter un contrat
          </a>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card blue">
          <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
          <div class="stat-value"><?= $totalContrats ?></div>
          <div class="stat-label">Total contrats</div>
          <div class="stat-trend trend-up"><i class="bi bi-arrow-up"></i> Portefeuille</div>
        </div>

        <div class="stat-card gold">
          <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
          <div class="stat-value"><?= $totalAttente ?></div>
          <div class="stat-label">En attente</div>
          <div class="stat-trend trend-warn"><i class="bi bi-clock"></i> À valider</div>
        </div>

        <div class="stat-card green">
          <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
          <div class="stat-value"><?= $totalActifs ?></div>
          <div class="stat-label">Actifs</div>
          <div class="stat-trend trend-up"><i class="bi bi-arrow-up"></i> En cours</div>
        </div>

        <div class="stat-card red">
          <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
          <div class="stat-value"><?= $totalExpires ?></div>
          <div class="stat-label">Expirés / résiliés</div>
          <div class="stat-trend trend-down"><i class="bi bi-exclamation-triangle"></i> À suivre</div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <i class="bi bi-table"></i> Liste des contrats
          </div>
          <button class="btn btn-outline btn-sm" onclick="exportCSV()">
            <i class="bi bi-download"></i> Exporter
          </button>
        </div>

        <div class="toolbar-inner">
          <div class="toolbar" style="margin-bottom:0;">
            <div class="search-box">
              <i class="bi bi-search"></i>
              <input type="text" id="searchInput" placeholder="Rechercher par numéro, type, catégorie...">
            </div>

            <select class="filter-select" id="filterStatut">
              <option value="">Tous les statuts</option>
              <option value="actif">Actif</option>
              <option value="en attente">En attente</option>
              <option value="expire">Expiré</option>
              <option value="resilie">Résilié</option>
            </select>

            <select class="filter-select" id="filterType">
              <option value="">Tous les types</option>
              <option value="auto">Auto</option>
              <option value="sante">Santé</option>
              <option value="habitation">Habitation</option>
              <option value="protection">Protection</option>
            </select>

            <input type="date" class="filter-select" id="filterDate" style="padding-right:10px;">

            <button class="btn btn-outline btn-sm" onclick="resetFilters()">
              <i class="bi bi-x-circle"></i> Réinitialiser
            </button>
          </div>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>N° Contrat</th>
                <th>Type</th>
                <th>Catégorie</th>
                <th>Prime</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="contratBody">
              <?php foreach ($contrats as $contrat): ?>
                <?php
                  $type = $contrat->getTypeContrat();
                  $statutRaw = strtolower(trim($contrat->getStatutContrat()));
                  $statutClass = str_replace([' ', 'é'], ['_', 'e'], $statutRaw);

                  $icon = 'bi-file-earmark';
                  if ($type === 'Auto') $icon = 'bi-car-front';
                  elseif ($type === 'Santé' || $type === 'Sante') $icon = 'bi-heart-pulse';
                  elseif ($type === 'Habitation') $icon = 'bi-house-door';
                ?>
                <tr
                  data-search="<?= htmlspecialchars(strtolower(
                    $contrat->getNumeroContrat() . ' ' .
                    $contrat->getTypeContrat() . ' ' .
                    $contrat->getNomCategorie()
                  )) ?>"
                  data-statut="<?= htmlspecialchars(str_replace(['é', '_'], ['e', ' '], $statutRaw)) ?>"
                  data-type="<?= htmlspecialchars(strtolower(str_replace(['é', 'É'], ['e', 'E'], $contrat->getTypeContrat()))) ?>"
                  data-date-debut="<?= htmlspecialchars($contrat->getDateDebutContrat()) ?>"
                  data-date-fin="<?= htmlspecialchars($contrat->getDateFinContrat()) ?>"
                >
                  <td style="color:var(--accent);font-weight:700;">
                    <?= htmlspecialchars($contrat->getNumeroContrat()) ?>
                  </td>
                  <td>
                    <div class="type-cell">
                      <div class="type-icon">
                        <i class="bi <?= $icon ?>"></i>
                      </div>
                      <span><?= htmlspecialchars($contrat->getTypeContrat()) ?></span>
                    </div>
                  </td>
                  <td style="color:#fff;font-weight:600;">
                    <?= htmlspecialchars($contrat->getNomCategorie() ?: '—') ?>
                  </td>
                  <td>
                    <span class="prime-badge">
                      <i class="bi bi-cash-stack"></i>
                      <?= htmlspecialchars($contrat->getPrimeContrat()) ?> DT
                    </span>
                  </td>
                  <td><?= htmlspecialchars($contrat->getDateDebutContrat()) ?></td>
                  <td><?= htmlspecialchars($contrat->getDateFinContrat()) ?></td>
                  <td>
                    <span class="status-select <?= $statutClass ?>">
                      <?= htmlspecialchars($contrat->getStatutContrat()) ?>
                    </span>
                  </td>
                  <td>
                    <div style="display:flex;gap:8px;">
                      <a href="showContrat.php?id=<?= (int)$contrat->getIdContrat() ?>"
                         class="btn btn-soft btn-sm"
                         title="Voir détails">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="updateContrat.php?id=<?= (int)$contrat->getIdContrat() ?>"
                         class="btn btn-soft btn-sm"
                         title="Modifier">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <a href="deleteContrat.php?id=<?= (int)$contrat->getIdContrat() ?>"
                         class="btn btn-soft danger btn-sm"
                         title="Supprimer"
                         onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contrat ?');">
                        <i class="bi bi-trash3"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <div id="emptyState" style="display:none;text-align:center;padding:48px 20px;color:var(--text-secondary);">
            <i class="bi bi-folder-x" style="font-size:36px;display:block;margin-bottom:10px;opacity:0.3;"></i>
            <p style="font-size:14px;">Aucun contrat trouvé</p>
          </div>
        </div>

        <div class="pagination">
          <div class="pagination-info" id="paginationInfo"></div>
          <div class="pagination-btns">
            <button class="page-btn" disabled><i class="bi bi-chevron-left"></i></button>
            <button class="page-btn active">1</button>
            <button class="page-btn" disabled><i class="bi bi-chevron-right"></i></button>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<div class="modal-overlay" id="modalDetail">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title"><i class="bi bi-file-earmark-text"></i> Détails du contrat</div>
      <button class="modal-close" onclick="closeModal('modalDetail')"><i class="bi bi-x"></i></button>
    </div>
    <div id="modalDetailBody"></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('modalDetail')">Fermer</button>
    </div>
  </div>
</div>

<div class="modal-overlay delete-modal" id="modalDelete">
  <div class="modal">
    <div class="delete-icon"><i class="bi bi-trash3"></i></div>
    <div class="delete-title">Supprimer ce contrat ?</div>
    <div class="delete-msg" id="deleteMsg">Cette action est irréversible.</div>
    <div class="modal-footer" style="justify-content:center;margin-top:28px;">
      <button class="btn btn-outline" onclick="closeModal('modalDelete')">Annuler</button>
      <button class="btn btn-danger">
        <i class="bi bi-trash3"></i> Supprimer définitivement
      </button>
    </div>
  </div>
</div>

<script>
  document.getElementById('topbarDate').textContent =
    new Date().toLocaleDateString('fr-FR', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });

  function openModal(id) {
    document.getElementById(id).classList.add('open');
  }

  function closeModal(id) {
    document.getElementById(id).classList.remove('open');
  }

  function showDetails(numero, type, categorie, prime, dateDebut, dateFin, franchise, statut, clientNom, clientEmail) {
    let icon = 'bi-file-earmark';
    if (type === 'Auto') icon = 'bi-car-front';
    else if (type === 'Santé' || type === 'Sante') icon = 'bi-heart-pulse';
    else if (type === 'Habitation') icon = 'bi-house-door';

    document.getElementById('modalDetailBody').innerHTML = `
      <div style="padding:24px;">
        <div class="contrat-modal-header">
          <div class="contrat-modal-icon">
            <i class="bi ${icon}"></i>
          </div>
          <div>
            <div class="contrat-modal-type">${type} — ${categorie}</div>
            <div class="contrat-modal-id">${numero}</div>
          </div>
        </div>

        <div class="detail-grid">
          <div class="detail-field">
            <div class="detail-field-label"><i class="bi bi-person"></i> Client</div>
            <div class="detail-field-value">${clientNom}</div>
          </div>

          <div class="detail-field">
            <div class="detail-field-label"><i class="bi bi-envelope"></i> Email</div>
            <div class="detail-field-value">${clientEmail}</div>
          </div>

          <div class="detail-field">
            <div class="detail-field-label"><i class="bi bi-cash-stack"></i> Prime</div>
            <div class="detail-field-value">${prime} DT</div>
          </div>

          <div class="detail-field">
            <div class="detail-field-label"><i class="bi bi-shield"></i> Franchise</div>
            <div class="detail-field-value">${franchise} DT</div>
          </div>

          <div class="detail-field">
            <div class="detail-field-label"><i class="bi bi-calendar-event"></i> Date début</div>
            <div class="detail-field-value">${dateDebut}</div>
          </div>

          <div class="detail-field">
            <div class="detail-field-label"><i class="bi bi-calendar-check"></i> Date fin</div>
            <div class="detail-field-value">${dateFin}</div>
          </div>

          <div class="detail-field">
            <div class="detail-field-label"><i class="bi bi-info-circle"></i> Statut</div>
            <div class="detail-field-value">${statut}</div>
          </div>

          <div class="detail-field">
            <div class="detail-field-label"><i class="bi bi-tags"></i> Catégorie</div>
            <div class="detail-field-value">${categorie}</div>
          </div>
        </div>
      </div>
    `;
    openModal('modalDetail');
  }

  function openDeleteModal(numero) {
    document.getElementById('deleteMsg').textContent =
      `Le contrat ${numero} sera supprimé définitivement.`;
    openModal('modalDelete');
  }

  function normalizeValue(value) {
    return (value || '')
      .toString()
      .toLowerCase()
      .trim()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/_/g, ' ');
  }

  function filterRows() {
    const search = normalizeValue(document.getElementById('searchInput').value);
    const statut = normalizeValue(document.getElementById('filterStatut').value);
    const type = normalizeValue(document.getElementById('filterType').value);
    const date = document.getElementById('filterDate').value;

    const rows = document.querySelectorAll('#contratBody tr');
    let visible = 0;

    rows.forEach(row => {
      const rowSearch = normalizeValue(row.dataset.search || '');
      const rowStatut = normalizeValue(row.dataset.statut || '');
      const rowType = normalizeValue(row.dataset.type || '');
      const rowDateDebut = row.dataset.dateDebut || '';
      const rowDateFin = row.dataset.dateFin || '';

      const okSearch = !search || rowSearch.includes(search);
      const okStatut = !statut || rowStatut === statut;
      const okType = !type || rowType === type;
      const okDate = !date || rowDateDebut === date || rowDateFin === date;

      if (okSearch && okStatut && okType && okDate) {
        row.style.display = '';
        visible++;
      } else {
        row.style.display = 'none';
      }
    });

    document.getElementById('emptyState').style.display = visible ? 'none' : 'block';
    document.getElementById('paginationInfo').textContent =
      visible > 0
        ? `Affichage 1–${visible} sur ${visible} contrat${visible > 1 ? 's' : ''}`
        : 'Affichage 0–0 sur 0 contrat';
  }

  function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterStatut').value = '';
    document.getElementById('filterType').value = '';
    document.getElementById('filterDate').value = '';
    filterRows();
  }

  function exportCSV() {
    let csv = 'Numero,Type,Categorie,Prime,Date debut,Date fin,Statut\n';
    const rows = document.querySelectorAll('#contratBody tr');

    rows.forEach(row => {
      if (row.style.display === 'none') return;
      const cols = row.querySelectorAll('td');
      if (cols.length >= 7) {
        csv += [
          cols[0].innerText.trim(),
          cols[1].innerText.trim(),
          cols[2].innerText.trim(),
          cols[3].innerText.trim(),
          cols[4].innerText.trim(),
          cols[5].innerText.trim(),
          cols[6].innerText.trim()
        ].join(',') + '\n';
      }
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'contrats.csv';
    link.click();
  }

  document.getElementById('searchInput').addEventListener('input', filterRows);
  document.getElementById('filterStatut').addEventListener('change', filterRows);
  document.getElementById('filterType').addEventListener('change', filterRows);
  document.getElementById('filterDate').addEventListener('change', filterRows);

  filterRows();
</script>

</body>
</html>