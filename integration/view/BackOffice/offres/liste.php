<?php
$offres   = $offres ?? [];
$stats    = $stats ?? [];
$message  = $message ?? ($_GET['message'] ?? '');
$erreur   = $erreur ?? ($_GET['erreur'] ?? '');

if (!defined('BASE_URL')) {
    define('BASE_URL', '/final/Esprit-PW-2A19-2526-DigitalTransformation-integration/integration');
}

$BASE_URL = BASE_URL;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Offres — Protex Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/variables.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/base.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/layout.css">

    <style>
        .page-hero {
            position: relative;
            margin-bottom: 24px;
            padding: 26px 26px 22px;
            border-radius: 22px;
            background:
                radial-gradient(circle at top right, rgba(0,180,216,.14), transparent 30%),
                linear-gradient(135deg, rgba(255,255,255,.05), rgba(255,255,255,.03));
            border: 1px solid rgba(255,255,255,0.08);
            overflow: hidden;
        }

        .page-hero-head {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
        }

        .page-hero-title {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.3px;
        }

        .page-hero-sub {
            margin: 8px 0 0;
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.6;
            max-width: 740px;
        }

        .page-hero-pills {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .page-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        .hero-side {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .hero-mini-card {
            min-width: 125px;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            text-align: center;
        }

        .hero-mini-card strong {
            display: block;
            color: #fff;
            font-size: 21px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .hero-mini-card span {
            color: var(--text-secondary);
            font-size: 12px;
        }

        .quick-info-bar {
            display: grid;
            grid-template-columns: repeat(4, minmax(0,1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .quick-info-card {
            position: relative;
            overflow: hidden;
            padding: 18px 18px 16px;
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            transition: transform .2s, box-shadow .2s;
        }

        .quick-info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(0,0,0,.2);
        }

        .quick-info-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            opacity: .9;
        }

        .quick-info-card.blue::before  { background: linear-gradient(90deg,#3b82f6,#60a5fa); }
        .quick-info-card.green::before { background: linear-gradient(90deg,#10b981,#34d399); }
        .quick-info-card.gold::before  { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
        .quick-info-card.red::before   { background: linear-gradient(90deg,#ef4444,#f87171); }

        .quick-info-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .quick-info-icon {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.08);
            color: #fff;
            font-size: 18px;
        }

        .quick-info-value {
            color:#fff;
            font-size:28px;
            font-weight:800;
            line-height:1;
            margin-bottom:6px;
        }

        .quick-info-label {
            color:var(--text-secondary);
            font-size:12px;
        }

        .admin-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 22px;
            padding: 20px 24px;
            border-radius: 18px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .admin-toolbar h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #fff;
        }

        .admin-toolbar p {
            margin: 6px 0 0;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .admin-toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .admin-filter-bar {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr auto;
            gap: 12px;
            margin-bottom: 18px;
        }

        .admin-filter-bar .input-group {
            position: relative;
        }

        .admin-filter-bar .input-group i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 14px;
        }

        .admin-filter-bar input,
        .admin-filter-bar select {
            width: 100%;
            height: 44px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.05);
            color: #fff;
            outline: none;
            font-size: 13px;
            font-family: var(--font-body);
        }

        .admin-filter-bar input {
            padding: 0 12px 0 38px;
        }

        .admin-filter-bar select {
            padding: 0 12px;
        }

        .admin-filter-bar select option {
            background: var(--navy-mid);
        }

        .btn-reset-filter {
            height: 44px;
            padding: 0 16px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.05);
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 13px;
            font-family: var(--font-body);
            transition: .2s;
            white-space: nowrap;
        }

        .btn-reset-filter:hover {
            background: rgba(255,255,255,0.09);
            color: #fff;
        }

        .admin-table-wrap {
            overflow-x: auto;
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,.06);
        }

        .table-protex {
            width: 100%;
            border-collapse: collapse;
        }

        .table-protex thead th {
            background: rgba(255,255,255,0.04);
            color: #cdd6f4;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            white-space: nowrap;
            padding: 14px 18px;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .table-protex tbody td {
            border-bottom: 1px solid rgba(255,255,255,0.05);
            vertical-align: middle;
            padding: 14px 18px;
        }

        .table-protex tbody tr {
            transition: .18s ease;
        }

        .table-protex tbody tr:hover {
            background: rgba(255,255,255,0.03);
        }

        .table-protex tbody tr:last-child td {
            border-bottom: none;
        }

        .badge-type {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .badge-type.auto       { background: rgba(59,130,246,.12);  color: #93c5fd; border-color: rgba(59,130,246,.24); }
        .badge-type.sante      { background: rgba(16,185,129,.12);  color: #86efac; border-color: rgba(16,185,129,.24); }
        .badge-type.habitation { background: rgba(245,158,11,.12);  color: #fcd34d; border-color: rgba(245,158,11,.24); }
        .badge-type.vie        { background: rgba(236,72,153,.12);  color: #f9a8d4; border-color: rgba(236,72,153,.24); }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-badge.active    { background: rgba(16,185,129,.14); color: #86efac; }
        .status-badge.suspendue { background: rgba(245,158,11,.14); color: #fcd34d; }
        .status-badge.archivee  { background: rgba(148,163,184,.14); color: #cbd5e1; }

        .action-group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-icon {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #fff;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            transition: .2s ease;
            cursor: pointer;
        }

        .action-icon:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .action-icon.delete {
            background: rgba(239,68,68,.12);
            color: #fca5a5;
            border-color: rgba(239,68,68,.2);
        }

        .action-icon.delete:hover {
            background: rgba(239,68,68,.22);
        }

        .offre-main {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .offre-avatar {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            color: #fff;
            font-size: 17px;
            flex-shrink: 0;
        }

        .offre-title {
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .offre-desc {
            font-size: 11px;
            color: var(--text-secondary);
            line-height: 1.4;
            max-width: 260px;
        }

        .price-block strong {
            color: #fff;
            display: block;
            font-size: 14px;
        }

        .price-block span {
            display: block;
            margin-top: 3px;
            color: var(--text-secondary);
            font-size: 11px;
        }

        .empty-box {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-box i {
            font-size: 42px;
            display: block;
            margin-bottom: 12px;
            opacity: .7;
        }

        .empty-box strong {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-size: 18px;
        }

        .alert-ok {
            background: rgba(16,185,129,.08);
            border: 1px solid rgba(16,185,129,.25);
            border-radius: 14px;
            padding: 13px 18px;
            color: #86efac;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .alert-err {
            background: rgba(239,68,68,.08);
            border: 1px solid rgba(239,68,68,.25);
            border-radius: 14px;
            padding: 13px 18px;
            color: #fca5a5;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .pagination-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .pagination-note {
            color: var(--text-secondary);
            font-size: 12px;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .page-btn {
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.05);
            color: #fff;
            cursor: pointer;
            font-size: 13px;
            transition: .2s;
        }

        .page-btn:hover:not(:disabled),
        .page-btn.active {
            background: var(--accent);
            border-color: var(--accent);
        }

        .page-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .toast-notif {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 99999;
            min-width: 280px;
            max-width: 420px;
            padding: 14px 16px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,.25);
            opacity: 0;
            transform: translateY(16px);
            transition: .25s ease;
        }

        .toast-notif.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-success { background: rgba(16,185,129,.95); }
        .toast-danger  { background: rgba(239,68,68,.95); }

        .sidebar-footer .logout-btn,
        .sidebar-nav .nav-item {
            text-decoration: none;
        }

        @media (max-width: 1100px) {
            .quick-info-bar {
                grid-template-columns: repeat(2, minmax(0,1fr));
            }
        }

        @media (max-width: 900px) {
            .admin-filter-bar {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 700px) {
            .page-hero-head {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (max-width: 600px) {
            .admin-filter-bar,
            .quick-info-bar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">

    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="<?= $BASE_URL ?>/view/FrontOffice/logo.png" alt="logo" width="40" height="40" style="border-radius:9px;">
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
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-grid-1x2"></i> Tableau de bord
            </a>

            <div class="nav-section">Gestion</div>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/sinsiter.html">
                <i class="bi bi-shield-exclamation"></i> Sinistres
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/traitement.html">
                <i class="bi bi-file-earmark-text"></i> Traitements
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-contrats.html">
                <i class="bi bi-file-earmark-text"></i> Contrats
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/controller/PaiementController.php?action=index">
                <i class="bi bi-credit-card"></i> Paiements
            </a>
            <a class="nav-item active" href="<?= $BASE_URL ?>/controller/OffreController.php?action=index">
                <i class="bi bi-tags"></i> Offres
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-reclamations.html">
                <i class="bi bi-chat-dots"></i> Réclamations
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-agences.html">
                <i class="bi bi-geo-alt"></i> Agences
            </a>

            <div class="nav-section">Compte</div>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/adminprofile.html">
                <i class="bi bi-person-gear"></i> Mon profil
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= $BASE_URL ?>/view/FrontOffice/connexion.html" class="logout-btn">
                <i class="bi bi-box-arrow-left"></i> Se déconnecter
            </a>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Gestion des offres</div>
                <div class="topbar-sub" id="topbarDate"></div>
            </div>
            <div class="topbar-actions">
                <a href="<?= $BASE_URL ?>/controller/OffreController.php?action=ajouter" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Ajouter une offre
                </a>
                <a href="#" class="topbar-btn">
                    <i class="bi bi-bell"></i>
                    <span class="notif-dot"></span>
                </a>
            </div>
        </div>

        <div class="content">

            <?php if (!empty($message)): ?>
                <div class="alert-ok">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong><?= htmlspecialchars($message) ?></strong>
                </div>
            <?php endif; ?>

            <?php if (!empty($erreur)): ?>
                <div class="alert-err">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong><?= htmlspecialchars($erreur) ?></strong>
                </div>
            <?php endif; ?>

            <section class="page-hero">
                <div class="page-hero-head">
                    <div>
                        <h1 class="page-hero-title">Catalogue des offres Protex</h1>
                        <p class="page-hero-sub">
                            Consultez, recherchez, filtrez et gérez les offres d’assurance depuis une interface claire et moderne.
                        </p>
                        <div class="page-hero-pills">
                            <span class="page-pill"><i class="bi bi-stars"></i> Vue centralisée</span>
                            <span class="page-pill"><i class="bi bi-funnel"></i> Filtres intelligents</span>
                            <span class="page-pill"><i class="bi bi-bar-chart"></i> Tri & pagination</span>
                        </div>
                    </div>
                    <div class="hero-side">
                        <div class="hero-mini-card">
                            <strong><?= (int)($stats['total'] ?? 0) ?></strong>
                            <span>Total</span>
                        </div>
                        <div class="hero-mini-card">
                            <strong><?= (int)($stats['actives'] ?? 0) ?></strong>
                            <span>Actives</span>
                        </div>
                    </div>
                </div>
            </section>

            <div class="admin-toolbar">
                <div>
                    <h2>Administration des offres</h2>
                    <p>Recherchez, triez, filtrez, consultez et gérez toutes les offres.</p>
                </div>
                <div class="admin-toolbar-right">
                    <a href="<?= $BASE_URL ?>/controller/OffreController.php?action=ajouter" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Nouvelle offre
                    </a>
                </div>
            </div>

            <section class="quick-info-bar">
                <div class="quick-info-card blue">
                    <div class="quick-info-top">
                        <div class="quick-info-icon"><i class="bi bi-tags"></i></div>
                    </div>
                    <div class="quick-info-value"><?= (int)($stats['total'] ?? 0) ?></div>
                    <div class="quick-info-label">Total des offres</div>
                </div>

                <div class="quick-info-card green">
                    <div class="quick-info-top">
                        <div class="quick-info-icon"><i class="bi bi-check-circle"></i></div>
                    </div>
                    <div class="quick-info-value"><?= (int)($stats['actives'] ?? 0) ?></div>
                    <div class="quick-info-label">Offres actives</div>
                </div>

                <div class="quick-info-card gold">
                    <div class="quick-info-top">
                        <div class="quick-info-icon"><i class="bi bi-pause-circle"></i></div>
                    </div>
                    <div class="quick-info-value"><?= (int)($stats['suspendues'] ?? 0) ?></div>
                    <div class="quick-info-label">Offres suspendues</div>
                </div>

                <div class="quick-info-card red">
                    <div class="quick-info-top">
                        <div class="quick-info-icon"><i class="bi bi-archive"></i></div>
                    </div>
                    <div class="quick-info-value"><?= (int)($stats['archivees'] ?? 0) ?></div>
                    <div class="quick-info-label">Offres archivées</div>
                </div>
            </section>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-table"></i> Liste des offres
                    </div>
                    <a href="<?= $BASE_URL ?>/controller/OffreController.php?action=ajouter" class="btn btn-outline btn-sm">
                        <i class="bi bi-plus-lg"></i> Ajouter
                    </a>
                </div>

                <div style="padding:16px 24px;border-bottom:1px solid var(--glass-border);">
                    <div class="admin-filter-bar">
                        <div class="input-group">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchInput" placeholder="Rechercher par nom, description ou ID...">
                        </div>

                        <select id="typeFilter">
                            <option value="">Tous les types</option>
                            <option value="auto">Auto</option>
                            <option value="sante">Santé</option>
                            <option value="habitation">Habitation</option>
                            <option value="vie">Vie</option>
                        </select>

                        <select id="statusFilter">
                            <option value="">Tous les statuts</option>
                            <option value="active">Active</option>
                            <option value="suspendue">Suspendue</option>
                            <option value="archivee">Archivée</option>
                        </select>

                        <select id="sortFilter">
                            <option value="recent">Plus récent</option>
                            <option value="ancien">Plus ancien</option>
                            <option value="nom_asc">Nom A → Z</option>
                            <option value="nom_desc">Nom Z → A</option>
                            <option value="prix_asc">Prix mensuel croissant</option>
                            <option value="prix_desc">Prix mensuel décroissant</option>
                        </select>

                        <button class="btn-reset-filter" onclick="resetFilters()" type="button">
                            <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                        </button>
                    </div>
                </div>

                <?php if (empty($offres)): ?>
                    <div class="empty-box">
                        <i class="bi bi-inbox"></i>
                        <strong>Aucune offre trouvée</strong>
                        <p>Ajoute une première offre pour commencer.</p>
                        <a href="<?= $BASE_URL ?>/controller/OffreController.php?action=ajouter" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Ajouter une offre
                        </a>
                    </div>
                <?php else: ?>
                    <div class="admin-table-wrap">
                        <table class="table-protex">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Offre</th>
                                    <th>Type</th>
                                    <th>Prix mensuel</th>
                                    <th>Prix annuel</th>
                                    <th>Plafond</th>
                                    <th>Statut</th>
                                    <th>Créée le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="offresBody">
                                <?php foreach ($offres as $o): ?>
                                    <?php
                                        $type = strtolower(trim((string)($o['type_offre'] ?? '')));
                                        $statut = strtolower(trim((string)($o['statut'] ?? '')));
                                        $desc = trim((string)($o['description'] ?? ''));
                                        $shortDesc = mb_strlen($desc) > 58 ? mb_substr($desc, 0, 58) . '...' : $desc;
                                        $dataStatus = ($statut === 'archivée') ? 'archivee' : $statut;

                                        $typeIcon = 'bi-tags';
                                        if ($type === 'auto') $typeIcon = 'bi-car-front';
                                        elseif ($type === 'sante') $typeIcon = 'bi-heart-pulse';
                                        elseif ($type === 'habitation') $typeIcon = 'bi-house-check';
                                        elseif ($type === 'vie') $typeIcon = 'bi-shield-heart';

                                        $dateSort = !empty($o['date_creation']) ? strtotime($o['date_creation']) : 0;
                                        $prixMensuelSort = (float)($o['prix_mensuel'] ?? 0);
                                        $nomSort = mb_strtolower((string)($o['nom_offre'] ?? ''));
                                    ?>
                                    <tr class="offre-row"
                                        data-id="<?= (int)($o['id_offre'] ?? 0) ?>"
                                        data-type="<?= htmlspecialchars($type) ?>"
                                        data-status="<?= htmlspecialchars($dataStatus) ?>"
                                        data-search="<?= htmlspecialchars(mb_strtolower(($o['id_offre'] ?? '') . ' ' . ($o['nom_offre'] ?? '') . ' ' . $desc)) ?>"
                                        data-date="<?= $dateSort ?>"
                                        data-prix="<?= $prixMensuelSort ?>"
                                        data-nom="<?= htmlspecialchars($nomSort) ?>">

                                        <td>
                                            <span style="font-family:monospace;color:var(--accent);font-size:12px;">
                                                #<?= (int)($o['id_offre'] ?? 0) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="offre-main">
                                                <div class="offre-avatar">
                                                    <i class="bi <?= $typeIcon ?>"></i>
                                                </div>
                                                <div>
                                                    <div class="offre-title"><?= htmlspecialchars($o['nom_offre'] ?? '—') ?></div>
                                                    <div class="offre-desc"><?= htmlspecialchars($shortDesc ?: 'Aucune description') ?></div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="badge-type <?= htmlspecialchars($type ?: 'auto') ?>">
                                                <i class="bi <?= $typeIcon ?>"></i>
                                                <?= htmlspecialchars(ucfirst($type ?: 'N/A')) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="price-block">
                                                <strong><?= number_format((float)($o['prix_mensuel'] ?? 0), 3) ?> TND</strong>
                                                <span>Mensuel</span>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="price-block">
                                                <strong><?= number_format((float)($o['prix_annuel'] ?? 0), 3) ?> TND</strong>
                                                <span>Annuel</span>
                                            </div>
                                        </td>

                                        <td style="color:var(--text-secondary);">
                                            <?= !empty($o['plafond']) ? number_format((float)$o['plafond'], 0, '.', ' ') . ' TND' : '—' ?>
                                        </td>

                                        <td>
                                            <?php if ($dataStatus === 'active'): ?>
                                                <span class="status-badge active">
                                                    <i class="bi bi-check-circle-fill"></i> Active
                                                </span>
                                            <?php elseif ($dataStatus === 'suspendue'): ?>
                                                <span class="status-badge suspendue">
                                                    <i class="bi bi-pause-circle-fill"></i> Suspendue
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge archivee">
                                                    <i class="bi bi-archive-fill"></i> Archivée
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td style="color:var(--text-secondary);font-size:12px;">
                                            <?= !empty($o['date_creation']) ? date('d/m/Y', strtotime($o['date_creation'])) : '—' ?>
                                        </td>

                                        <td>
                                            <div class="action-group">
                                                <a class="action-icon"
                                                   href="<?= $BASE_URL ?>/controller/OffreController.php?action=modifier&id=<?= (int)($o['id_offre'] ?? 0) ?>"
                                                   title="Modifier">
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                                <?php if ($dataStatus === 'active'): ?>
                                                    <a class="action-icon"
                                                       href="<?= $BASE_URL ?>/controller/OffreController.php?action=statut&id=<?= (int)($o['id_offre'] ?? 0) ?>&statut=suspendue"
                                                       title="Suspendre"
                                                       onclick="return confirm('Suspendre cette offre ?')">
                                                        <i class="bi bi-pause-circle"></i>
                                                    </a>
                                                <?php elseif ($dataStatus === 'suspendue'): ?>
                                                    <a class="action-icon"
                                                       href="<?= $BASE_URL ?>/controller/OffreController.php?action=statut&id=<?= (int)($o['id_offre'] ?? 0) ?>&statut=active"
                                                       title="Activer"
                                                       onclick="return confirm('Activer cette offre ?')">
                                                        <i class="bi bi-play-circle"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <a class="action-icon delete"
                                                   href="<?= $BASE_URL ?>/controller/OffreController.php?action=supprimer&id=<?= (int)($o['id_offre'] ?? 0) ?>"
                                                   title="Supprimer"
                                                   onclick="return confirm('Supprimer cette offre ?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="noResults" class="empty-box" style="display:none;">
                        <i class="bi bi-search"></i>
                        <strong>Aucun résultat</strong>
                        <p>Aucune offre ne correspond aux critères actuels.</p>
                    </div>

                    <div class="pagination-wrap">
                        <div class="pagination-note">
                            Total affiché : <span id="visibleCount">0</span> offre(s)
                        </div>

                        <div class="pagination-controls">
                            <button class="page-btn" id="prevPageBtn" type="button">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <span id="paginationNumbers" style="display:flex;gap:8px;flex-wrap:wrap;"></span>
                            <button class="page-btn" id="nextPageBtn" type="button">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<?php if (!empty($message)): ?>
    <div class="toast-notif toast-success" id="toastMessage">
        <i class="bi bi-check-circle-fill"></i>
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($erreur)): ?>
    <div class="toast-notif toast-danger" id="toastError">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span><?= htmlspecialchars($erreur) ?></span>
    </div>
<?php endif; ?>

<script>
    const topbarDate = document.getElementById('topbarDate');
    if (topbarDate) {
        topbarDate.textContent = new Date().toLocaleDateString('fr-FR', {
            weekday:'long',
            day:'numeric',
            month:'long',
            year:'numeric'
        });
    }

    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const sortFilter = document.getElementById('sortFilter');
    const tbody = document.getElementById('offresBody');
    const visibleCount = document.getElementById('visibleCount');
    const noResults = document.getElementById('noResults');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const paginationNumbers = document.getElementById('paginationNumbers');

    let allRows = tbody ? Array.from(tbody.querySelectorAll('.offre-row')) : [];
    let currentPage = 1;
    const rowsPerPage = 5;

    function getFilteredSortedRows() {
        const search = (searchInput?.value || '').toLowerCase().trim();
        const type = typeFilter?.value || '';
        const status = statusFilter?.value || '';
        const sort = sortFilter?.value || 'recent';

        let rows = allRows.filter(row => {
            const matchSearch = !search || row.dataset.search.includes(search);
            const matchType = !type || row.dataset.type === type;
            const matchStatus = !status || row.dataset.status === status;
            return matchSearch && matchType && matchStatus;
        });

        rows.sort((a, b) => {
            if (sort === 'ancien') return Number(a.dataset.date) - Number(b.dataset.date);
            if (sort === 'nom_asc') return a.dataset.nom.localeCompare(b.dataset.nom);
            if (sort === 'nom_desc') return b.dataset.nom.localeCompare(a.dataset.nom);
            if (sort === 'prix_asc') return Number(a.dataset.prix) - Number(b.dataset.prix);
            if (sort === 'prix_desc') return Number(b.dataset.prix) - Number(a.dataset.prix);
            return Number(b.dataset.date) - Number(a.dataset.date);
        });

        return rows;
    }

    function renderPagination(totalRows) {
        if (!paginationNumbers || !prevPageBtn || !nextPageBtn) return;

        const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));
        if (currentPage > totalPages) currentPage = totalPages;

        paginationNumbers.innerHTML = '';

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
            btn.type = 'button';
            btn.textContent = i;
            btn.addEventListener('click', () => {
                currentPage = i;
                applyFilters();
            });
            paginationNumbers.appendChild(btn);
        }

        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages;

        prevPageBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                applyFilters();
            }
        };

        nextPageBtn.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                applyFilters();
            }
        };
    }

    function applyFilters() {
        if (!tbody || !allRows.length) return;

        const rows = getFilteredSortedRows();
        const total = rows.length;
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const pageRows = rows.slice(start, end);

        tbody.innerHTML = '';
        pageRows.forEach(row => tbody.appendChild(row));

        if (visibleCount) visibleCount.textContent = total;
        if (noResults) noResults.style.display = total === 0 ? 'block' : 'none';

        renderPagination(total);
    }

    function resetFilters() {
        if (searchInput) searchInput.value = '';
        if (typeFilter) typeFilter.value = '';
        if (statusFilter) statusFilter.value = '';
        if (sortFilter) sortFilter.value = 'recent';
        currentPage = 1;
        applyFilters();
    }

    searchInput?.addEventListener('input', () => {
        currentPage = 1;
        applyFilters();
    });

    typeFilter?.addEventListener('change', () => {
        currentPage = 1;
        applyFilters();
    });

    statusFilter?.addEventListener('change', () => {
        currentPage = 1;
        applyFilters();
    });

    sortFilter?.addEventListener('change', () => {
        currentPage = 1;
        applyFilters();
    });

    applyFilters();

    function showToast(id) {
        const toast = document.getElementById(id);
        if (!toast) return;

        setTimeout(() => {
            toast.classList.add('show');
        }, 80);

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3500);
    }

    showToast('toastMessage');
    showToast('toastError');
</script>
</body>
</html>