<?php
$paiements = $paiements ?? [];
$stats     = $stats     ?? [];
$echeances = $echeances ?? [];
$message   = $message   ?? ($_GET['message'] ?? '');
$erreur    = $erreur    ?? ($_GET['erreur']  ?? '');
$BASE_URL  = '/projet_web/MVC TEMPLATE';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des paiements — Protex Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/variables.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/base.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/layout.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/client.css">
    <style>
        /* ═══════════════════════════════════════
           HERO
        ═══════════════════════════════════════ */
        .page-hero {
            position: relative;
            margin-bottom: 24px;
            padding: 26px 28px 22px;
            border-radius: 22px;
            background:
                radial-gradient(circle at top right,  rgba(0,180,216,.14), transparent 35%),
                radial-gradient(circle at bottom left, rgba(16,185,129,.08), transparent 40%),
                linear-gradient(135deg, rgba(255,255,255,.05), rgba(255,255,255,.03));
            border: 1px solid rgba(255,255,255,.08);
            overflow: hidden;
        }
        .page-hero::after {
            content: "";
            position: absolute; inset: 0;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(255,255,255,.03), transparent 35%);
        }
        .page-hero-head {
            position: relative; z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px; flex-wrap: wrap;
        }
        .page-hero-title {
            margin: 0; font-size: 26px;
            font-weight: 800; color: #fff; letter-spacing: -.3px;
        }
        .page-hero-sub {
            margin: 8px 0 0; color: var(--text-secondary);
            font-size: 14px; line-height: 1.6; max-width: 640px;
        }
        .page-hero-pills { display:flex; gap:10px; flex-wrap:wrap; margin-top:18px; }
        .page-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 7px 13px; border-radius: 999px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            color: #fff; font-size: 12px; font-weight: 600;
        }
        .page-pill i { color: var(--accent); }
        .hero-side { display:flex; gap:12px; flex-wrap:wrap; flex-shrink:0; }
        .hero-mini-card {
            min-width: 130px; padding: 14px 16px;
            border-radius: 16px; text-align: center;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            transition: .2s;
        }
        .hero-mini-card:hover { background: rgba(255,255,255,.08); }
        .hero-mini-card strong { display:block; color:#fff; font-size:22px; font-weight:800; margin-bottom:4px; }
        .hero-mini-card span  { color:var(--text-secondary); font-size:12px; }

        /* ═══════════════════════════════════════
           STATS
        ═══════════════════════════════════════ */
        .stats-grid-pay {
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-pay {
            position: relative; overflow: hidden;
            padding: 18px 20px 16px;
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,.07);
            background: rgba(255,255,255,.04);
            transition: transform .2s, box-shadow .2s;
        }
        .stat-pay:hover { transform:translateY(-3px); box-shadow:0 12px 28px rgba(0,0,0,.2); }
        .stat-pay::before {
            content: ""; position:absolute; top:0; left:0;
            width:100%; height:3px; opacity:.9;
        }
        .stat-pay.blue::before  { background:linear-gradient(90deg,#3b82f6,#60a5fa); }
        .stat-pay.green::before { background:linear-gradient(90deg,#10b981,#34d399); }
        .stat-pay.gold::before  { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
        .stat-pay.red::before   { background:linear-gradient(90deg,#ef4444,#f87171); }
        .stat-pay .ic {
            width:42px; height:42px; border-radius:13px;
            display:grid; place-items:center;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.08);
            color:#fff; font-size:18px; margin-bottom:12px;
        }
        .stat-pay .val { font-size:28px; font-weight:800; color:#fff; line-height:1; margin-bottom:5px; }
        .stat-pay .lbl { font-size:12px; color:var(--text-secondary); }
        .stat-pay .sub { font-size:11px; color:var(--accent); margin-top:4px; }

        /* ═══════════════════════════════════════
           CA BANNER
        ═══════════════════════════════════════ */
        .ca-banner {
            display: flex; justify-content:space-between;
            align-items: center; gap:16px; flex-wrap:wrap;
            padding: 18px 22px; border-radius:18px;
            background: linear-gradient(135deg, rgba(0,180,216,.1), rgba(16,185,129,.06));
            border: 1px solid rgba(0,180,216,.2);
            margin-bottom: 22px;
        }
        .ca-banner-left { display:flex; align-items:center; gap:14px; }
        .ca-icon {
            width:48px; height:48px; border-radius:14px;
            display:grid; place-items:center;
            background:rgba(0,180,216,.15);
            border:1px solid rgba(0,180,216,.25);
            color:var(--accent); font-size:20px; flex-shrink:0;
        }
        .ca-title { font-size:13px; color:var(--text-secondary); margin-bottom:5px; }
        .ca-value { font-family:var(--font-display); font-size:22px; font-weight:800; color:#fff; }
        .ca-right  { font-size:12px; color:var(--text-secondary); text-align:right; }
        .ca-right strong { display:block; color:#fff; font-size:14px; margin-bottom:3px; }

        /* ═══════════════════════════════════════
           ALERTES ECHÉANCES
        ═══════════════════════════════════════ */
        .echeance-alert {
            background: rgba(245,158,11,.07);
            border: 1px solid rgba(245,158,11,.2);
            border-radius: 16px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex; align-items:flex-start; gap:14px;
        }
        .echeance-alert i { color:#fbbf24; font-size:18px; flex-shrink:0; margin-top:2px; }
        .echeance-alert-title { font-size:13px; font-weight:700; color:#fff; margin-bottom:6px; }
        .echeance-list { display:flex; gap:10px; flex-wrap:wrap; }
        .echeance-chip {
            padding: 5px 12px; border-radius:999px;
            background: rgba(245,158,11,.12);
            border: 1px solid rgba(245,158,11,.2);
            color: #fcd34d; font-size:12px; font-weight:600;
        }

        /* ═══════════════════════════════════════
           FILTRES
        ═══════════════════════════════════════ */
        .filter-section {
            display: flex; gap:10px; flex-wrap:wrap;
            margin-bottom: 18px; align-items:center;
        }
        .filter-tabs { display:flex; gap:6px; flex-wrap:wrap; flex:1; }
        .filter-tab {
            padding: 8px 16px; border-radius:999px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.04);
            color: var(--text-secondary);
            font-size: 13px; font-weight:500;
            cursor: pointer; font-family:var(--font-body);
            transition: .2s ease;
            display: flex; align-items:center; gap:7px;
        }
        .filter-tab:hover { background:rgba(255,255,255,.08); color:#fff; }
        .filter-tab.active { background:var(--accent); border-color:var(--accent); color:#fff; box-shadow:0 4px 14px rgba(0,180,216,.3); }
        .filter-tab .count {
            padding: 2px 7px; border-radius:999px;
            background:rgba(255,255,255,.2);
            font-size:11px; font-weight:700;
        }
        .filter-search {
            position: relative; flex-shrink:0;
        }
        .filter-search i {
            position:absolute; left:13px; top:50%;
            transform:translateY(-50%);
            color:var(--text-secondary); font-size:14px;
        }
        .filter-search input {
            height:40px; padding:0 14px 0 38px;
            border-radius:13px;
            border:1px solid rgba(255,255,255,.08);
            background:rgba(255,255,255,.05);
            color:#fff; font-size:13px;
            font-family:var(--font-body);
            outline:none; width:240px;
            transition:.2s;
        }
        .filter-search input:focus {
            border-color:rgba(0,180,216,.35);
            box-shadow:0 0 0 3px rgba(0,180,216,.08);
        }
        .filter-search input::placeholder { color:var(--text-secondary); }

        /* ═══════════════════════════════════════
           TABLE CARD
        ═══════════════════════════════════════ */
        .table-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            backdrop-filter: blur(20px);
            overflow: hidden;
        }
        .table-card-head {
            padding: 18px 24px;
            border-bottom: 1px solid var(--glass-border);
            display: flex; justify-content:space-between;
            align-items: center; gap:16px; flex-wrap:wrap;
        }
        .table-card-title { font-family:var(--font-display); font-size:16px; font-weight:700; color:#fff; }
        .table-card-sub   { font-size:12px; color:var(--text-secondary); margin-top:3px; }
        .result-badge {
            padding:7px 14px; border-radius:999px;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.08);
            color:#fff; font-size:12px; font-weight:600;
        }

        /* ═══════════════════════════════════════
           TABLE
        ═══════════════════════════════════════ */
        .admin-table-wrap { overflow-x:auto; }
        .table-pay { width:100%; border-collapse:collapse; }
        .table-pay thead th {
            background:rgba(255,255,255,.03);
            color:#cdd6f4;
            font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:.5px;
            border-bottom:1px solid rgba(255,255,255,.07);
            white-space:nowrap;
            padding:13px 18px;
        }
        .table-pay tbody td {
            border-bottom:1px solid rgba(255,255,255,.05);
            vertical-align:middle;
            padding:13px 18px;
        }
        .table-pay tbody tr { transition:.18s ease; }
        .table-pay tbody tr:hover { background:rgba(255,255,255,.03); }
        .table-pay tbody tr:last-child td { border-bottom:none; }

        /* Référence */
        .ref-badge {
            display: inline-flex; align-items:center; gap:6px;
            padding:6px 11px; border-radius:8px;
            background:rgba(0,180,216,.1);
            border:1px solid rgba(0,180,216,.2);
            color:var(--accent);
            font-family:monospace; font-size:12px; font-weight:700;
            letter-spacing:.5px;
        }

        /* Offre cell */
        .offre-cell { display:flex; align-items:center; gap:11px; }
        .offre-dot {
            width:36px; height:36px; border-radius:11px;
            display:grid; place-items:center;
            font-size:15px; color:#fff; flex-shrink:0;
        }
        .offre-dot.auto       { background:rgba(59,130,246,.2); }
        .offre-dot.sante      { background:rgba(16,185,129,.2); }
        .offre-dot.habitation { background:rgba(245,158,11,.2); }
        .offre-dot.vie        { background:rgba(236,72,153,.2); }
        .offre-name { font-weight:600; color:#fff; font-size:13px; margin-bottom:2px; }
        .offre-type { font-size:11px; color:var(--text-secondary); }

        /* Montant */
        .montant-val { font-weight:700; color:#fff; font-size:14px; }
        .montant-per { font-size:11px; color:var(--text-secondary); margin-top:2px; }

        /* Badges */
        .badge-methode {
            display:inline-flex; align-items:center; gap:5px;
            padding:5px 10px; border-radius:999px;
            font-size:11px; font-weight:600;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.1);
            color:var(--text-primary);
        }

        .badge-statut {
            display:inline-flex; align-items:center; gap:6px;
            padding:6px 12px; border-radius:999px;
            font-size:11px; font-weight:700;
        }
        .badge-statut.en_attente { background:rgba(245,158,11,.14); color:#fcd34d; }
        .badge-statut.valide     { background:rgba(16,185,129,.14);  color:#86efac; }
        .badge-statut.refuse     { background:rgba(239,68,68,.14);   color:#fca5a5; }
        .badge-statut.rembourse  { background:rgba(0,180,216,.14);   color:#7dd3fc; }

        /* Echéance proche */
        .echeance-soon {
            display:inline-flex; align-items:center; gap:4px;
            font-size:11px; color:#fbbf24; margin-top:3px;
        }

        /* Actions */
        .action-wrap { display:flex; gap:6px; align-items:center; }
        .action-btn {
            width:34px; height:34px; border-radius:10px;
            display:inline-flex; align-items:center; justify-content:center;
            text-decoration:none; color:#fff;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.08);
            font-size:14px; transition:.2s;
        }
        .action-btn:hover { background:rgba(255,255,255,.1); transform:translateY(-1px); }
        .action-btn.validate { background:rgba(16,185,129,.12); color:#86efac; border-color:rgba(16,185,129,.2); }
        .action-btn.validate:hover { background:rgba(16,185,129,.22); }
        .action-btn.refuse   { background:rgba(239,68,68,.12);  color:#fca5a5; border-color:rgba(239,68,68,.2); }
        .action-btn.refuse:hover   { background:rgba(239,68,68,.22); }
        .action-btn.refund   { background:rgba(0,180,216,.12);  color:#7dd3fc; border-color:rgba(0,180,216,.2); }
        .action-btn.refund:hover   { background:rgba(0,180,216,.22); }

        /* Empty */
        .empty-box { text-align:center; padding:60px 20px; color:var(--text-secondary); }
        .empty-box i      { font-size:42px; display:block; margin-bottom:12px; opacity:.6; }
        .empty-box strong { display:block; color:#fff; margin-bottom:8px; font-size:18px; }

        /* No results */
        .no-results { display:none; text-align:center; padding:28px; color:var(--text-secondary); font-size:14px; }

        /* Alertes */
        .alert-ok  { background:rgba(16,185,129,.08);  border:1px solid rgba(16,185,129,.25); border-radius:14px; padding:13px 18px; color:#86efac; font-size:13px; display:flex; align-items:center; gap:10px; margin-bottom:20px; }
        .alert-err { background:rgba(239,68,68,.08);   border:1px solid rgba(239,68,68,.25);  border-radius:14px; padding:13px 18px; color:#fca5a5; font-size:13px; display:flex; align-items:center; gap:10px; margin-bottom:20px; }

        @media(max-width:1100px){ .stats-grid-pay{grid-template-columns:repeat(2,1fr);} }
        @media(max-width:768px) { .hero-side{display:none;} .filter-search input{width:180px;} }
        @media(max-width:600px) { .stats-grid-pay{grid-template-columns:1fr;} .filter-tabs{flex-direction:column;} }
    </style>
</head>
<body>
<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">

    <!-- ═══════════════════════════════════════
         SIDEBAR
    ═══════════════════════════════════════ -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="<?= $BASE_URL ?>/view/FrontOffice/logo.png" alt="logo" width="38" height="38"
                 style="border-radius:9px;flex-shrink:0;">
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
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/controller/OffreController.php">
                <i class="bi bi-tags"></i> Offres
            </a>
            <a class="nav-item active" href="<?= $BASE_URL ?>/controller/PaiementController.php">
                <i class="bi bi-credit-card"></i> Paiements
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-file-earmark-text"></i> Contrats
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-shield-exclamation"></i> Sinistres
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-chat-dots"></i> Réclamations
            </a>
            <div class="nav-section">Compte</div>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/adminprofile.html">
                <i class="bi bi-person-gear"></i> Mon profil
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= $BASE_URL ?>/view/FrontOffice/login.html" class="logout-btn">
                <i class="bi bi-box-arrow-left"></i> Se déconnecter
            </a>
        </div>
    </aside>

    <!-- ═══════════════════════════════════════
         MAIN
    ═══════════════════════════════════════ -->
    <main class="main">

        <!-- TOPBAR -->
        <div class="topbar">
            <div>
                <div class="topbar-title">Gestion des paiements</div>
                <div class="topbar-sub" id="topbarDate"></div>
            </div>
            <div class="topbar-actions">
                <a href="#" class="topbar-btn" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="notif-dot"></span>
                </a>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content">

            <!-- Breadcrumb -->
            <div class="page-breadcrumb" style="margin-bottom:24px;">
                <i class="bi bi-house"></i>
                <span>Admin</span>
                <i class="bi bi-chevron-right" style="font-size:10px"></i>
                <span>Paiements</span>
            </div>

            <!-- Alertes -->
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

            <!-- Hero -->
            <section class="page-hero">
                <div class="page-hero-head">
                    <div>
                        <h1 class="page-hero-title">Gestion des paiements</h1>
                        <p class="page-hero-sub">
                            Suivez en temps réel tous les paiements effectués par vos clients,
                            validez ou refusez les transactions en attente et consultez
                            les statistiques financières du mois.
                        </p>
                        <div class="page-hero-pills">
                            <span class="page-pill"><i class="bi bi-shield-check"></i> Transactions sécurisées</span>
                            <span class="page-pill"><i class="bi bi-graph-up"></i> Suivi en temps réel</span>
                            <span class="page-pill"><i class="bi bi-lightning-charge"></i> Actions rapides</span>
                        </div>
                    </div>
                    <div class="hero-side">
                        <div class="hero-mini-card">
                            <strong><?= (int)($stats['total'] ?? 0) ?></strong>
                            <span>Total paiements</span>
                        </div>
                        <div class="hero-mini-card">
                            <strong style="color:#fcd34d;"><?= (int)($stats['en_attente'] ?? 0) ?></strong>
                            <span>En attente</span>
                        </div>
                        <div class="hero-mini-card">
                            <strong style="color:#86efac;"><?= (int)($stats['valides'] ?? 0) ?></strong>
                            <span>Validés</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats -->
            <div class="stats-grid-pay">
                <div class="stat-pay blue">
                    <div class="ic"><i class="bi bi-credit-card"></i></div>
                    <div class="val"><?= (int)($stats['total']      ?? 0) ?></div>
                    <div class="lbl">Total paiements</div>
                </div>
                <div class="stat-pay gold">
                    <div class="ic"><i class="bi bi-hourglass-split"></i></div>
                    <div class="val"><?= (int)($stats['en_attente'] ?? 0) ?></div>
                    <div class="lbl">En attente de validation</div>
                    <?php if (($stats['en_attente'] ?? 0) > 0): ?>
                    <div class="sub"><i class="bi bi-exclamation-circle"></i> À traiter</div>
                    <?php endif; ?>
                </div>
                <div class="stat-pay green">
                    <div class="ic"><i class="bi bi-check-circle"></i></div>
                    <div class="val"><?= (int)($stats['valides']    ?? 0) ?></div>
                    <div class="lbl">Paiements validés</div>
                </div>
                <div class="stat-pay red">
                    <div class="ic"><i class="bi bi-x-circle"></i></div>
                    <div class="val"><?= (int)($stats['refuses']    ?? 0) ?></div>
                    <div class="lbl">Refusés / Remboursés</div>
                </div>
            </div>

            <!-- CA Banner -->
            <?php if (!empty($stats['chiffre_affaires'])): ?>
            <div class="ca-banner">
                <div class="ca-banner-left">
                    <div class="ca-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <div class="ca-title">Chiffre d'affaires total</div>
                        <div class="ca-value">
                            <?= number_format((float)($stats['chiffre_affaires']??0), 3) ?> TND
                        </div>
                    </div>
                </div>
                <div class="ca-right">
                    <strong>
                        <?= number_format((float)($stats['ca_ce_mois']??0), 3) ?> TND
                    </strong>
                    Ce mois-ci
                </div>
            </div>
            <?php endif; ?>

            <!-- Alertes échéances proches -->
            <?php if (!empty($echeances)): ?>
            <div class="echeance-alert">
                <i class="bi bi-alarm-fill"></i>
                <div>
                    <div class="echeance-alert-title">
                        <?= count($echeances) ?> échéance(s) dans les 3 prochains jours
                    </div>
                    <div class="echeance-list">
                        <?php foreach ($echeances as $e): ?>
                        <span class="echeance-chip">
                            <i class="bi bi-calendar-event"></i>
                            <?= htmlspecialchars($e['reference']) ?>
                            — <?= date('d/m/Y', strtotime($e['date_echeance'])) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filtres par statut -->
            <div class="filter-section">
                <div class="filter-tabs">
                    <button class="filter-tab active" id="tab-tous"
                            onclick="filtrerStatut('','tab-tous')">
                        <i class="bi bi-list-ul"></i> Tous
                        <span class="count"><?= count($paiements) ?></span>
                    </button>
                    <button class="filter-tab" id="tab-en_attente"
                            onclick="filtrerStatut('en_attente','tab-en_attente')">
                        <i class="bi bi-hourglass-split"></i> En attente
                        <span class="count"><?= (int)($stats['en_attente']??0) ?></span>
                    </button>
                    <button class="filter-tab" id="tab-valide"
                            onclick="filtrerStatut('valide','tab-valide')">
                        <i class="bi bi-check-circle"></i> Validés
                        <span class="count"><?= (int)($stats['valides']??0) ?></span>
                    </button>
                    <button class="filter-tab" id="tab-refuse"
                            onclick="filtrerStatut('refuse','tab-refuse')">
                        <i class="bi bi-x-circle"></i> Refusés
                        <span class="count"><?= (int)($stats['refuses']??0) ?></span>
                    </button>
                    <button class="filter-tab" id="tab-rembourse"
                            onclick="filtrerStatut('rembourse','tab-rembourse')">
                        <i class="bi bi-arrow-counterclockwise"></i> Remboursés
                        <span class="count"><?= (int)($stats['rembourses']??0) ?></span>
                    </button>
                </div>
                <div class="filter-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchPay"
                           placeholder="Référence, offre...">
                </div>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <div class="table-card-head">
                    <div>
                        <div class="table-card-title">Liste des paiements</div>
                        <div class="table-card-sub">
                            <span id="countVisible"><?= count($paiements) ?></span>
                            paiement(s) affiché(s)
                        </div>
                    </div>
                </div>

                <div class="admin-table-wrap">
                    <?php if (empty($paiements)): ?>
                    <div class="empty-box">
                        <i class="bi bi-inbox"></i>
                        <strong>Aucun paiement trouvé</strong>
                        <p>Les paiements effectués par les clients apparaîtront ici.</p>
                    </div>
                    <?php else: ?>

                    <table class="table-pay">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Offre souscrite</th>
                                <th>Montant</th>
                                <th>Méthode</th>
                                <th>Périodicité</th>
                                <th>Statut</th>
                                <th>Date paiement</th>
                                <th>Échéance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="payBody">
                        <?php foreach ($paiements as $p):
                            $statut  = strtolower($p['statut']     ?? '');
                            $type    = strtolower($p['type_offre'] ?? '');
                            $icons   = ['auto'=>'bi-car-front','sante'=>'bi-heart-pulse','habitation'=>'bi-house-door','vie'=>'bi-shield-check'];
                            $icon    = $icons[$type] ?? 'bi-tags';
                            $methIcons = ['carte'=>'bi-credit-card-2-front','virement'=>'bi-bank','mobile'=>'bi-phone'];
                            $methIcon  = $methIcons[strtolower($p['methode']??'')] ?? 'bi-credit-card';
                            $search  = mb_strtolower(($p['reference']??'').' '.($p['nom_offre']??'').' '.$type);

                            /* Vérifier si l'échéance est dans 3 jours */
                            $echeanceSoon = false;
                            if (!empty($p['date_echeance'])) {
                                $diff = (strtotime($p['date_echeance']) - time()) / 86400;
                                $echeanceSoon = ($diff >= 0 && $diff <= 3);
                            }
                        ?>
                        <tr class="pay-row"
                            data-statut="<?= htmlspecialchars($statut) ?>"
                            data-search="<?= htmlspecialchars($search) ?>">

                            <td>
                                <span class="ref-badge">
                                    <i class="bi bi-hash"></i>
                                    <?= htmlspecialchars($p['reference'] ?? '—') ?>
                                </span>
                            </td>

                            <td>
                                <div class="offre-cell">
                                    <div class="offre-dot <?= $type ?>">
                                        <i class="bi <?= $icon ?>"></i>
                                    </div>
                                    <div>
                                        <div class="offre-name">
                                            <?= htmlspecialchars($p['nom_offre'] ?? '—') ?>
                                        </div>
                                        <div class="offre-type"><?= ucfirst($type) ?></div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="montant-val">
                                    <?= number_format((float)($p['montant']??0),3) ?> TND
                                </div>
                                <div class="montant-per">
                                    <?= ucfirst($p['periodicite'] ?? '') ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge-methode">
                                    <i class="bi <?= $methIcon ?>"></i>
                                    <?= ucfirst($p['methode'] ?? '—') ?>
                                </span>
                            </td>

                            <td style="color:var(--text-secondary);font-size:13px;">
                                <?= ucfirst($p['periodicite'] ?? '—') ?>
                            </td>

                            <td>
                                <?php if ($statut === 'en_attente'): ?>
                                <span class="badge-statut en_attente">
                                    <i class="bi bi-hourglass-split"></i> En attente
                                </span>
                                <?php elseif ($statut === 'valide'): ?>
                                <span class="badge-statut valide">
                                    <i class="bi bi-check-circle-fill"></i> Validé
                                </span>
                                <?php elseif ($statut === 'refuse'): ?>
                                <span class="badge-statut refuse">
                                    <i class="bi bi-x-circle-fill"></i> Refusé
                                </span>
                                <?php else: ?>
                                <span class="badge-statut rembourse">
                                    <i class="bi bi-arrow-counterclockwise"></i> Remboursé
                                </span>
                                <?php endif; ?>
                            </td>

                            <td style="font-size:12px;color:var(--text-secondary);">
                                <?= !empty($p['date_paiement'])
                                    ? date('d/m/Y', strtotime($p['date_paiement']))
                                    : '—' ?>
                                <div style="font-size:11px;margin-top:2px;color:var(--text-secondary);opacity:.7;">
                                    <?= !empty($p['date_paiement'])
                                        ? date('H:i', strtotime($p['date_paiement']))
                                        : '' ?>
                                </div>
                            </td>

                            <td style="font-size:12px;color:var(--text-secondary);">
                                <?= !empty($p['date_echeance'])
                                    ? date('d/m/Y', strtotime($p['date_echeance']))
                                    : '—' ?>
                                <?php if ($echeanceSoon): ?>
                                <div class="echeance-soon">
                                    <i class="bi bi-alarm-fill"></i> Bientôt
                                </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="action-wrap">
                                    <!-- Détail -->
                                    <a href="<?= $BASE_URL ?>/controller/PaiementController.php?action=detail&id=<?= (int)($p['id_paiement']??0) ?>"
                                       class="action-btn" title="Voir le détail">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <?php if ($statut === 'en_attente'): ?>
                                    <!-- Valider -->
                                    <a href="<?= $BASE_URL ?>/controller/PaiementController.php?action=valider&id=<?= (int)($p['id_paiement']??0) ?>"
                                       class="action-btn validate" title="Valider"
                                       onclick="return confirm('Valider ce paiement ?')">
                                        <i class="bi bi-check2"></i>
                                    </a>
                                    <!-- Refuser -->
                                    <a href="<?= $BASE_URL ?>/controller/PaiementController.php?action=refuser&id=<?= (int)($p['id_paiement']??0) ?>"
                                       class="action-btn refuse" title="Refuser">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                    <?php elseif ($statut === 'valide'): ?>
                                    <!-- Rembourser -->
                                    <a href="<?= $BASE_URL ?>/controller/PaiementController.php?action=rembourser&id=<?= (int)($p['id_paiement']??0) ?>"
                                       class="action-btn refund" title="Rembourser">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="no-results" id="noResults">
                        <i class="bi bi-search"></i> Aucun paiement trouvé pour ce filtre.
                    </div>

                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="<?= $BASE_URL ?>/view/BackOffice/assets/js/main.js"></script>
<script>
    /* Date topbar */
    document.getElementById('topbarDate').textContent =
        new Date().toLocaleDateString('fr-FR', {
            weekday:'long', day:'numeric', month:'long', year:'numeric'
        });

    const rows    = document.querySelectorAll('.pay-row');
    const counter = document.getElementById('countVisible');
    const noRes   = document.getElementById('noResults');
    let activeStatut = '';

    /* ── Filtre par statut (tabs) ── */
    function filtrerStatut(statut, tabId) {
        activeStatut = statut;

        /* Reset tabs */
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');

        appliquerFiltres();
    }

    /* ── Appliquer tous les filtres ── */
    function appliquerFiltres() {
        const search = (document.getElementById('searchPay').value || '').toLowerCase().trim();
        let count = 0;

        rows.forEach(row => {
            const okStatut = !activeStatut || row.dataset.statut === activeStatut;
            const okSearch = !search || row.dataset.search.includes(search);
            const visible  = okStatut && okSearch;
            row.style.display = visible ? '' : 'none';
            if (visible) count++;
        });

        if (counter) counter.textContent = count;
        if (noRes)   noRes.style.display = (count === 0 && rows.length > 0) ? 'block' : 'none';
    }

    /* ── Recherche ── */
    document.getElementById('searchPay').addEventListener('input', appliquerFiltres);
</script>
</body>
</html>