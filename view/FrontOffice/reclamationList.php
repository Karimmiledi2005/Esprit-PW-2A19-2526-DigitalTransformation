<?php
require_once __DIR__ . '/../../controller/ReclamationController.php';
require_once __DIR__ . '/../../controller/ReponseController.php';

$reclamationC = new ReclamationController();
$reponseC     = new ReponseController();

// SUPPRESSION via GET
if (isset($_GET['delete'])) {
    $reclamationC->deleteReclamation($_GET['delete']);
    header('Location: reclamationList.php');
    exit();
}

// Recherche par objet (côté serveur) ou liste complète triée alphabétiquement
$searchObjet = trim($_GET['search_objet'] ?? '');
if ($searchObjet !== '') {
    $list = $reponseC->searchAllReclamationsByObjet($searchObjet);
} else {
    $list = $reponseC->listAllReclamations();
}
 
$total         = 0;
$openCount     = 0;
$closedCount   = 0;
$rejectedCount = 0;
$rows          = [];
 
foreach ($list as $row) {
    $rows[] = $row;
    $total++;
    if (($row['statut'] ?? '') === 'open')     $openCount++;
    if (($row['statut'] ?? '') === 'closed')   $closedCount++;
    if (($row['statut'] ?? '') === 'rejected') $rejectedCount++;
}
 
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
 
function badgeClass($statut) {
    switch ($statut) {
        case 'closed':   return 'badge-success';
        case 'pending':  return 'badge-info';
        case 'rejected': return 'badge-danger';
        default:         return 'badge-warning';
    }
}
 
function badgeLabel($statut) {
    switch ($statut) {
        case 'closed':   return 'Résolue';
        case 'pending':  return 'En attente';
        case 'rejected': return 'Rejetée';
        default:         return 'En cours';
    }
}
 
function cardClass($statut) {
    $allowed = ['open', 'closed', 'pending', 'rejected'];
    return in_array($statut, $allowed, true) ? $statut : 'open';
}
 
function iconWrapClass($type) {
    switch ($type) {
        case 'Santé':      return 'icon-sante';
        case 'Auto':       return 'icon-auto';
        case 'Habitation': return 'icon-habitat';
        default:           return 'icon-autre';
    }
}
 
function iconBiClass($type) {
    switch ($type) {
        case 'Santé':      return 'bi-heart-pulse';
        case 'Auto':       return 'bi-car-front';
        case 'Habitation': return 'bi-house';
        default:           return 'bi-three-dots';
    }
}
 
function formatDateFr($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    if ($timestamp === false) return $date;
    $months = [
        1=>'Janvier',  2=>'Février',   3=>'Mars',      4=>'Avril',
        5=>'Mai',      6=>'Juin',      7=>'Juillet',   8=>'Août',
        9=>'Septembre',10=>'Octobre',  11=>'Novembre', 12=>'Décembre'
    ];
    return date('d', $timestamp) . ' ' . $months[(int)date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Réclamations — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/reclamation.css">
</head>
<body>
 
<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
 
<div class="layout">
 
    <!-- ===== NAVBAR ===== -->
    <!-- ===== NAVBAR ===== -->
    <nav class="navbar">
        <a href="client.html" class="navbar-brand">
           <img src="logo.png" alt="logo" width="40" height="40">
            <div>
                <div class="logo-text">Protex</div>
                <div class="logo-sub">Assurance Digitale</div>
            </div>
        </a>

        <div class="navbar-nav">
            <a class="nav-link" href="client.html">
                <i class="bi bi-grid-1x2"></i>
                <span class="nav-label">Tableau de bord</span>
            </a>
            <a class="nav-link" href="contrat.html">
                <i class="bi bi-file-earmark-text"></i>
                <span class="nav-label">Contrats</span>
                <span class="nav-badge accent">3</span>
            <a class="nav-link" href="mes-sinistres.html">
               <i class="bi bi-shield-exclamation"></i>
                <span class="nav-label">Sinistres</span>
                <span class="nav-badge">1</span>
            </a>
            <a class="nav-link" href="paiement.html">
                <i class="bi bi-credit-card"></i>
                <span class="nav-label">Paiements</span>
            </a>
            <div class="nav-separator"></div>
            <a class="nav-link" href="reclamationList.php">
                <i class="bi bi-chat-dots"></i>
                <span class="nav-label">Réclamations</span>
            </a>
            <a class="nav-link" href="postes.html">
                <i class="bi bi-megaphone"></i>
                <span class="nav-label">Postes</span>
            </a>
            <a class="nav-link" href="agences.html">
                <i class="bi bi-geo-alt"></i>
                <span class="nav-label">Agences</span>
            </a>
            <a class="nav-link" href="offres.html">
                <i class="bi bi-stars"></i>
                <span class="nav-label">Nos offres</span>
            </a>
        </div>

        <div class="navbar-right">
            <a href="#" class="nav-btn" title="Notifications">
                <i class="bi bi-bell"></i>
                <span class="notif-dot"></span>
            </a>
            <a href="#" class="nav-btn" title="Aide">
                <i class="bi bi-question-circle"></i>
            </a>
            <div class="avatar-wrap">
                <div class="avatar-btn" id="avatarBtn" title="Mon compte">KM</div>
                <div class="avatar-dropdown" id="avatarDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">KM</div>
                        <div class="dropdown-info">
                            <div class="dropdown-name">Karim Miledi</div>
                            <div class="dropdown-email">karim.miledi@email.com</div>
                            <span class="dropdown-role">Client Premium</span>
                        </div>
                    </div>
                    <a href="monprofile.html" class="dropdown-item">
                        <i class="bi bi-person-circle"></i> Mon profil
                    </a>
                    <a href="parametres.html" class="dropdown-item">
                        <i class="bi bi-gear"></i> Paramètres
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="login.html" class="dropdown-item logout">
                        <i class="bi bi-box-arrow-right"></i> Se déconnecter
                    </a>
                </div>
            </div>
        </div>
    </nav>
 
    <!-- ======== MAIN CONTENT ======== -->
    <main class="main">
 
        <!-- ===== PAGE HEADER ===== -->
        <div class="page-header">
            <div>
                <div class="page-title-main">
                    Mes réclamations &nbsp;<i class="bi bi-chat-dots" style="color:var(--accent);font-size:22px;vertical-align:middle;"></i>
                </div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.html">Accueil</a>
                    <i class="bi bi-chevron-right"></i>
                    <span>Réclamations</span>
                    &nbsp;·&nbsp;
                    <span id="currentDate"></span>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <a href="addReclamation.php" class="btn-new">
                    <i class="bi bi-plus-lg"></i> Nouvelle réclamation
                </a>
                <button class="btn-ai-chat" id="btnOpenChat" title="Assistant IA Assurance">
                    <span class="btn-ai-icon"><i class="bi bi-stars"></i></span>
                    <span class="btn-ai-label">Assistant IA</span>
                    <span class="btn-ai-pulse"></span>
                </button>
            </div>
        </div>

        <style>
        .btn-ai-chat {
            position:relative; display:inline-flex; align-items:center; gap:8px;
            padding:10px 18px; border-radius:12px; border:none;
            background:linear-gradient(135deg,#23458f 0%,#1d3c82 100%);
            color:#fff; font-size:13.5px; font-weight:700; cursor:pointer;
            box-shadow:0 4px 16px rgba(35,69,143,.35);
            transition:transform .2s, box-shadow .2s; overflow:hidden;
        }
        .btn-ai-chat:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(35,69,143,.45); }
        .btn-ai-chat:active { transform:translateY(0); }
        .btn-ai-icon {
            width:26px; height:26px; background:rgba(255,255,255,.15);
            border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px;
        }
        .btn-ai-label { position:relative; z-index:1; }
        .btn-ai-pulse {
            position:absolute; top:8px; right:10px;
            width:8px; height:8px; border-radius:50%; background:#22c55e;
            animation:aiPulse 2s ease-in-out infinite;
        }
        @keyframes aiPulse {
            0%   { box-shadow:0 0 0 0 rgba(34,197,94,.6); }
            70%  { box-shadow:0 0 0 7px rgba(34,197,94,0); }
            100% { box-shadow:0 0 0 0 rgba(34,197,94,0); }
        }
        </style>
 
        <!-- ===== STATS ROW ===== -->
        <div class="stats-row">
            <div class="stat-pill sp-blue">
                <div class="stat-pill-icon"><i class="bi bi-chat-dots"></i></div>
                <div>
                    <div class="stat-pill-val"><?php echo $total; ?></div>
                    <div class="stat-pill-lbl">Total réclamations</div>
                </div>
            </div>
            <div class="stat-pill sp-warn">
                <div class="stat-pill-icon"><i class="bi bi-clock"></i></div>
                <div>
                    <div class="stat-pill-val"><?php echo $openCount; ?></div>
                    <div class="stat-pill-lbl">En cours</div>
                </div>
            </div>
            <div class="stat-pill sp-green">
                <div class="stat-pill-icon"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="stat-pill-val"><?php echo $closedCount; ?></div>
                    <div class="stat-pill-lbl">Résolues</div>
                </div>
            </div>
            <div class="stat-pill sp-red">
                <div class="stat-pill-icon"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="stat-pill-val"><?php echo $rejectedCount; ?></div>
                    <div class="stat-pill-lbl">Rejetées</div>
                </div>
            </div>
        </div>
 
        <!-- ===== FILTRES ===== -->
        <form method="GET" action="reclamationList.php" class="filters-bar" id="searchForm">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" class="search-input" id="searchObjetInput" name="search_objet"
                       placeholder="Rechercher par objet..."
                       value="<?php echo htmlspecialchars($searchObjet, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn-search-objet" title="Rechercher">
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </button>
                <?php if ($searchObjet !== ''): ?>
                <a href="reclamationList.php" class="btn-search-clear" title="Effacer la recherche">
                    <i class="bi bi-x-circle"></i>
                </a>
                <?php endif; ?>
            </div>
            <select class="filter-select" id="filterStatut">
                <option value="">Tous les statuts</option>
                <option value="open">En cours</option>
                <option value="closed">Résolue</option>
                <option value="pending">En attente</option>
                <option value="rejected">Rejetée</option>
            </select>
            <select class="filter-select" id="filterType">
                <option value="">Tous les types</option>
                <option value="Santé">Santé</option>
                <option value="Auto">Auto</option>
                <option value="Habitation">Habitation</option>
                <option value="Autre">Autre</option>
            </select>
            <button type="button" class="btn-sort-rec" id="btnSortRec" onclick="toggleSortRec()">
                <i class="bi bi-sort-alpha-down" id="sortIconRec"></i>
                <span id="sortLabelRec">Trier A→Z</span>
            </button>
        </form>
        <?php if ($searchObjet !== ''): ?>
        <div class="search-info">
            <i class="bi bi-funnel"></i>
            Résultats pour l'objet : <strong><?php echo htmlspecialchars($searchObjet, ENT_QUOTES, 'UTF-8'); ?></strong>
            &nbsp;— <?php echo count($rows); ?> réclamation(s) trouvée(s) &nbsp;
            <a href="reclamationList.php"><i class="bi bi-x"></i> Effacer</a>
        </div>
        <?php endif; ?>
        <style>
        .btn-search-objet {
            background: none; border: none; cursor: pointer;
            color: var(--accent, #23458f); font-size: 18px;
            padding: 0 4px; display: flex; align-items: center;
            transition: color .2s;
        }
        .btn-search-objet:hover { color: #1d3c82; }
        .btn-search-clear {
            color: #e74c3c; font-size: 16px;
            display: flex; align-items: center;
            text-decoration: none; padding: 0 4px;
            transition: color .2s;
        }
        .btn-search-clear:hover { color: #c0392b; }
        .search-info {
            margin: -8px 0 14px 0;
            font-size: 13px; color: #555;
            background: #edf3ff; border-radius: 8px;
            padding: 8px 14px; display: flex; align-items: center; gap: 6px;
        }
        .search-info a { color: #e74c3c; text-decoration: none; margin-left: 4px; }
        .search-info strong { color: #23458f; }
        /* ── Bouton tri ── */
        .btn-sort-rec {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 16px; border-radius: 10px;
            background: rgba(35,69,143,.08);
            border: 1.5px solid rgba(35,69,143,.25);
            color: #23458f; font-size: 13px; font-weight: 700;
            cursor: pointer; transition: all .2s; white-space: nowrap;
        }
        .btn-sort-rec:hover { background: rgba(35,69,143,.16); transform: translateY(-1px); }
        .btn-sort-rec.active {
            background: rgba(35,69,143,.18);
            border-color: #23458f;
            box-shadow: 0 0 0 3px rgba(35,69,143,.12);
        }
        </style>
 
        <!-- ===== LISTE DES RÉCLAMATIONS ===== -->
        <div class="reclamations-list" id="reclamationsList">
 
           <?php if (!empty($rows)) { ?>
 
                <?php foreach ($rows as $reclamation) { ?>
                    <div class="rec-card <?php echo cardClass($reclamation['statut'] ?? 'open'); ?>"
                         data-statut="<?php echo h($reclamation['statut'] ?? 'open'); ?>"
                         data-type="<?php echo h($reclamation['type'] ?? ''); ?>"
                         data-search="<?php echo h(strtolower(($reclamation['objet'] ?? '') . ' ' . ($reclamation['rec_ref'] ?? '') . ' ' . ($reclamation['ref_contrat'] ?? '') . ' ' . ($reclamation['description'] ?? ''))); ?>">
 
                        <div class="rec-header">
                            <div class="rec-title-group">
                                <div class="rec-icon <?php echo iconWrapClass($reclamation['type'] ?? 'Autre'); ?>">
                                    <i class="bi <?php echo iconBiClass($reclamation['type'] ?? 'Autre'); ?>"></i>
                                </div>
                                <div>
                                    <div class="rec-name"><?php echo h($reclamation['objet'] ?? ''); ?></div>
                                    <div class="rec-ref">
                                        <?php echo h($reclamation['rec_ref'] ?? ''); ?>
                                        &nbsp;·&nbsp;
                                        Contrat : <?php echo h($reclamation['ref_contrat'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>
 
                            <div class="rec-actions">
                                <!-- Modifier -->
                                <a href="updateReclamation.php?id=<?php echo (int)($reclamation['id'] ?? 0); ?>" class="btn-action edit">
                                    <i class="bi bi-pencil"></i> Modifier
                                </a>
                                <!-- Voir le détail + réponse (page dédiée) -->
                                <a href="detailReclamation.php?id=<?php echo (int)($reclamation['id'] ?? 0); ?>" class="btn-action" style="background:var(--navy-soft,#edf3ff);color:var(--navy,#23458f);border-color:var(--navy,#23458f)">
                                    <i class="bi bi-eye"></i> Voir détail
                                </a>
                                <!-- Supprimer -->
                                <a href="reclamationList.php?delete=<?php echo (int)($reclamation['id'] ?? 0); ?>"
                                   class="btn-action del"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réclamation ?')">
                                    <i class="bi bi-trash3"></i> Supprimer
                                </a>
                            </div>
                        </div>
 
                        <div class="rec-body">
                            <div class="rec-meta-item">
                                <label>Type</label>
                                <span><?php echo h($reclamation['type'] ?? '—'); ?></span>
                            </div>
                            <div class="rec-meta-item">
                                <label>Date de dépôt</label>
                                <span><?php echo h(formatDateFr($reclamation['date_depot'] ?? '')); ?></span>
                            </div>
                            <div class="rec-meta-item">
                                <label>Priorité</label>
                                <span><?php echo h(ucfirst($reclamation['priorite'] ?? '—')); ?></span>
                            </div>
                            <div class="rec-meta-item">
                                <label>Statut</label>
                                <span class="badge <?php echo badgeClass($reclamation['statut'] ?? 'open'); ?>">
                                    <?php echo badgeLabel($reclamation['statut'] ?? 'open'); ?>
                                </span>
                            </div>
                        </div>
 
                        <?php if (!empty($reclamation['description'])): ?>
                        <div class="rec-desc"><?php echo h($reclamation['description']); ?></div>
                        <?php endif; ?>

 
                    </div>
                <?php } ?>
 
            <?php } else { ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Aucune réclamation trouvée</p>
                </div>
            <?php } ?>
 
        </div><!-- /reclamations-list -->
 
    </main>
</div><!-- /layout -->
 
<script>
    // Date du jour en français
    const months = ['Janvier','Février','Mars','Avril','Mai','Juin',
                    'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    const now = new Date();
    const el = document.getElementById('currentDate');
    if (el) el.textContent = now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();

    // Filtres live (statut + type) — la recherche par objet est gérée côté serveur
    const filterStatut = document.getElementById('filterStatut');
    const filterType   = document.getElementById('filterType');
    const list         = document.getElementById('reclamationsList');
    let   sortState    = 0; // 0 = aucun, 1 = A→Z, 2 = Z→A

    function getCards() {
        return Array.from(list.querySelectorAll('.rec-card'));
    }

    function applyFilters() {
        const stat = filterStatut.value;
        const type = filterType.value;
        getCards().forEach(card => {
            const matchStat = !stat || card.dataset.statut === stat;
            const matchType = !type || card.dataset.type   === type;
            card.style.display = (matchStat && matchType) ? '' : 'none';
        });
    }

    function toggleSortRec() {
        const btn  = document.getElementById('btnSortRec');
        const icon = document.getElementById('sortIconRec');
        const lbl  = document.getElementById('sortLabelRec');

        sortState = (sortState + 1) % 3; // 0→1→2→0

        const cards = getCards();

        if (sortState === 1) {
            // A→Z
            icon.className = 'bi bi-sort-alpha-down';
            lbl.textContent = 'Trier A→Z';
            btn.classList.add('active');
            cards.sort((a, b) =>
                (a.querySelector('.rec-name')?.textContent || '')
                .localeCompare(b.querySelector('.rec-name')?.textContent || '', 'fr', {sensitivity:'base'})
            );
        } else if (sortState === 2) {
            // Z→A
            icon.className = 'bi bi-sort-alpha-up';
            lbl.textContent = 'Trier Z→A';
            btn.classList.add('active');
            cards.sort((a, b) =>
                (b.querySelector('.rec-name')?.textContent || '')
                .localeCompare(a.querySelector('.rec-name')?.textContent || '', 'fr', {sensitivity:'base'})
            );
        } else {
            // Désactiver le tri
            icon.className = 'bi bi-sort-alpha-down';
            lbl.textContent = 'Trier A→Z';
            btn.classList.remove('active');
        }

        // Remettre les cartes dans le bon ordre dans le DOM
        cards.forEach(card => list.appendChild(card));
        // Ré-appliquer les filtres
        applyFilters();
    }

    filterStatut.addEventListener('change', applyFilters);
    filterType  .addEventListener('change', applyFilters);
</script>
 

<!-- ═══ CHATBOT ASSURANCE PROTEX ═══ -->
<script>window.PROTEX_EMAIL = "<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'client@protex.tn'; ?>";</script>
<script src="assets/js/chatbot-assurance.js"></script>
</body>
</html>