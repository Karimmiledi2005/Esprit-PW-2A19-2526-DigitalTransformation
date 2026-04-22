<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config.php';

$paiements = $paiements ?? [];
$stats     = $stats ?? [];
$echeances = $echeances ?? [];
$message   = $message ?? ($_GET['message'] ?? '');
$erreur    = $erreur ?? ($_GET['erreur'] ?? '');
$BASE_URL  = BASE_URL;

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function formatMoney($value): string
{
    return number_format((float)$value, 3, '.', ' ') . ' TND';
}

function formatDateFr(?string $date): string
{
    if (empty($date)) {
        return '—';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '—';
    }

    return date('d/m/Y', $timestamp);
}

function formatTimeFr(?string $date): string
{
    if (empty($date)) {
        return '';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '';
    }

    return date('H:i', $timestamp);
}

function ucfirstSafe(?string $value): string
{
    $value = (string)$value;
    if ($value === '') {
        return '—';
    }
    return ucfirst($value);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des paiements — Protex Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e($BASE_URL) ?>/view/BackOffice/assets/css/variables.css">
    <link rel="stylesheet" href="<?= e($BASE_URL) ?>/view/BackOffice/assets/css/base.css">
    <link rel="stylesheet" href="<?= e($BASE_URL) ?>/view/BackOffice/assets/css/layout.css">

    <style>
        .page-hero {
            position: relative;
            margin-bottom: 24px;
            padding: 26px 28px 22px;
            border-radius: 22px;
            background:
                radial-gradient(circle at top right, rgba(0,180,216,.14), transparent 35%),
                radial-gradient(circle at bottom left, rgba(16,185,129,.08), transparent 40%),
                linear-gradient(135deg, rgba(255,255,255,.05), rgba(255,255,255,.03));
            border: 1px solid rgba(255,255,255,.08);
            overflow: hidden;
        }

        .page-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(255,255,255,.03), transparent 35%);
        }

        .page-hero-head {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
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
            max-width: 640px;
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
            padding: 7px 13px;
            border-radius: 999px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        .page-pill i {
            color: var(--accent);
        }

        .hero-side {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .hero-mini-card {
            min-width: 130px;
            padding: 14px 16px;
            border-radius: 16px;
            text-align: center;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            transition: .2s;
        }

        .hero-mini-card:hover {
            background: rgba(255,255,255,.08);
        }

        .hero-mini-card strong {
            display: block;
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .hero-mini-card span {
            color: var(--text-secondary);
            font-size: 12px;
        }

        .stats-grid-pay {
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-pay {
            position: relative;
            overflow: hidden;
            padding: 18px 20px 16px;
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,.07);
            background: rgba(255,255,255,.04);
            transition: transform .2s, box-shadow .2s;
        }

        .stat-pay:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(0,0,0,.2);
        }

        .stat-pay::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            opacity: .9;
        }

        .stat-pay.blue::before  { background: linear-gradient(90deg,#3b82f6,#60a5fa); }
        .stat-pay.green::before { background: linear-gradient(90deg,#10b981,#34d399); }
        .stat-pay.gold::before  { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
        .stat-pay.red::before   { background: linear-gradient(90deg,#ef4444,#f87171); }

        .stat-pay .ic {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.08);
            color: #fff;
            font-size: 18px;
            margin-bottom: 12px;
        }

        .stat-pay .val {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-pay .lbl {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .stat-pay .sub {
            font-size: 11px;
            color: var(--accent);
            margin-top: 4px;
        }

        .ca-banner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            padding: 18px 22px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(0,180,216,.1), rgba(16,185,129,.06));
            border: 1px solid rgba(0,180,216,.2);
            margin-bottom: 22px;
        }

        .ca-banner-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ca-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(0,180,216,.15);
            border: 1px solid rgba(0,180,216,.25);
            color: var(--accent);
            font-size: 20px;
            flex-shrink: 0;
        }

        .ca-title {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .ca-value {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 800;
            color: #fff;
        }

        .ca-right {
            font-size: 12px;
            color: var(--text-secondary);
            text-align: right;
        }

        .ca-right strong {
            display: block;
            color: #fff;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .echeance-alert {
            background: rgba(245,158,11,.07);
            border: 1px solid rgba(245,158,11,.2);
            border-radius: 16px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .echeance-alert i {
            color: #fbbf24;
            font-size: 18px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .echeance-alert-title {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }

        .echeance-list {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .echeance-chip {
            padding: 5px 12px;
            border-radius: 999px;
            background: rgba(245,158,11,.12);
            border: 1px solid rgba(245,158,11,.2);
            color: #fcd34d;
            font-size: 12px;
            font-weight: 600;
        }

        .filter-section {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            align-items: center;
        }

        .filter-tabs {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            flex: 1;
        }

        .filter-tab {
            padding: 8px 16px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.04);
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            font-family: var(--font-body);
            transition: .2s ease;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .filter-tab:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }

        .filter-tab.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
            box-shadow: 0 4px 14px rgba(0,180,216,.3);
        }

        .filter-tab .count {
            padding: 2px 7px;
            border-radius: 999px;
            background: rgba(255,255,255,.2);
            font-size: 11px;
            font-weight: 700;
        }

        .filter-search {
            position: relative;
            flex-shrink: 0;
        }

        .filter-search i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 14px;
        }

        .filter-search input {
            height: 40px;
            padding: 0 14px 0 38px;
            border-radius: 13px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.05);
            color: #fff;
            font-size: 13px;
            font-family: var(--font-body);
            outline: none;
            width: 240px;
            transition: .2s;
        }

        .filter-search input:focus {
            border-color: rgba(0,180,216,.35);
            box-shadow: 0 0 0 3px rgba(0,180,216,.08);
        }

        .filter-search input::placeholder {
            color: var(--text-secondary);
        }

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
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .table-card-title {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        .table-card-sub {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 3px;
        }

        .admin-table-wrap {
            overflow-x: auto;
        }

        .table-pay {
            width: 100%;
            border-collapse: collapse;
        }

        .table-pay thead th {
            background: rgba(255,255,255,.03);
            color: #cdd6f4;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            white-space: nowrap;
            padding: 14px 16px;
            text-align: left;
        }

        .table-pay tbody td {
            border-bottom: 1px solid rgba(255,255,255,.05);
            vertical-align: middle;
            padding: 14px 16px;
            font-size: 13px;
        }

        .table-pay tbody tr:hover {
            background: rgba(255,255,255,.03);
        }

        .table-pay tbody tr:last-child td {
            border-bottom: none;
        }

        .ref-code {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 8px;
            background: rgba(0,180,216,.08);
            border: 1px solid rgba(0,180,216,.18);
            color: var(--accent);
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
        }

        .offre-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .offre-type-icon {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            color: var(--accent);
            font-size: 14px;
            flex-shrink: 0;
        }

        .offre-name {
            color: #fff;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .offre-type {
            color: var(--text-secondary);
            font-size: 11px;
        }

        .badge-methode {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            color: #fff;
        }

        .badge-statut {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-statut.en_attente { background: rgba(245,158,11,.14); color: #fcd34d; }
        .badge-statut.valide     { background: rgba(16,185,129,.14); color: #86efac; }
        .badge-statut.refuse     { background: rgba(239,68,68,.14); color: #fca5a5; }
        .badge-statut.rembourse  { background: rgba(0,180,216,.14); color: #7dd3fc; }

        .echeance-soon {
            margin-top: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            color: #fcd34d;
        }

        .action-wrap {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            color: #fff;
            transition: .2s;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            background: rgba(255,255,255,.1);
        }

        .action-btn.validate {
            background: rgba(16,185,129,.1);
            color: #86efac;
            border-color: rgba(16,185,129,.2);
        }

        .action-btn.refuse {
            background: rgba(239,68,68,.1);
            color: #fca5a5;
            border-color: rgba(239,68,68,.2);
        }

        .action-btn.refund {
            background: rgba(0,180,216,.1);
            color: #7dd3fc;
            border-color: rgba(0,180,216,.2);
        }

        .no-results,
        .empty-box {
            text-align: center;
            padding: 42px 20px;
            color: var(--text-secondary);
        }

        .no-results i,
        .empty-box i {
            font-size: 42px;
            display: block;
            margin-bottom: 10px;
            opacity: .7;
        }

        .no-results {
            display: none;
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

        .sidebar-footer .logout-btn,
        .sidebar-nav .nav-item {
            text-decoration: none;
        }

        @media (max-width: 1100px) {
            .stats-grid-pay {
                grid-template-columns: repeat(2,1fr);
            }
        }

        @media (max-width: 780px) {
            .stats-grid-pay {
                grid-template-columns: 1fr;
            }

            .filter-search input {
                width: 100%;
                min-width: 220px;
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
            <img src="<?= e($BASE_URL) ?>/view/FrontOffice/logo.png" alt="logo" width="40" height="40" style="border-radius:9px;">
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
            <a class="nav-item" href="<?= e($BASE_URL) ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-grid-1x2"></i> Tableau de bord
            </a>

            <div class="nav-section">Gestion</div>
            <a class="nav-item" href="<?= e($BASE_URL) ?>/view/BackOffice/sinsiter.html">
                <i class="bi bi-shield-exclamation"></i> Sinistres
            </a>
            <a class="nav-item" href="<?= e($BASE_URL) ?>/view/BackOffice/traitement.html">
                <i class="bi bi-file-earmark-text"></i> Traitements
            </a>
            <a class="nav-item" href="<?= e($BASE_URL) ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
            <a class="nav-item" href="<?= e($BASE_URL) ?>/view/BackOffice/admin-contrats.html">
                <i class="bi bi-file-earmark-text"></i> Contrats
            </a>
            <a class="nav-item active" href="<?= e($BASE_URL) ?>/controller/PaiementController.php?action=index">
                <i class="bi bi-credit-card"></i> Paiements
            </a>
            <a class="nav-item" href="<?= e($BASE_URL) ?>/controller/OffreController.php?action=index">
                <i class="bi bi-tags"></i> Offres
            </a>
            <a class="nav-item" href="<?= e($BASE_URL) ?>/view/BackOffice/admin-reclamations.html">
                <i class="bi bi-chat-dots"></i> Réclamations
            </a>
            <a class="nav-item" href="<?= e($BASE_URL) ?>/view/BackOffice/admin-agences.html">
                <i class="bi bi-geo-alt"></i> Agences
            </a>

            <div class="nav-section">Compte</div>
            <a class="nav-item" href="<?= e($BASE_URL) ?>/view/BackOffice/adminprofile.html">
                <i class="bi bi-person-gear"></i> Mon profil
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= e($BASE_URL) ?>/view/FrontOffice/connexion.html" class="logout-btn">
                <i class="bi bi-box-arrow-left"></i> Se déconnecter
            </a>
        </div>
    </aside>

    <main class="main">
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

        <div class="content">
            <div class="page-breadcrumb" style="margin-bottom:24px;">
                <i class="bi bi-house"></i>
                <span>Admin</span>
                <i class="bi bi-chevron-right" style="font-size:10px"></i>
                <span>Paiements</span>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert-ok">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong><?= e($message) ?></strong>
                </div>
            <?php endif; ?>

            <?php if ($erreur !== ''): ?>
                <div class="alert-err">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong><?= e($erreur) ?></strong>
                </div>
            <?php endif; ?>

            <section class="page-hero">
                <div class="page-hero-head">
                    <div>
                        <h1 class="page-hero-title">Gestion des paiements</h1>
                        <p class="page-hero-sub">
                            Suivez en temps réel tous les paiements effectués par vos clients,
                            validez ou refusez les transactions en attente et consultez
                            rapidement les statistiques du module.
                        </p>

                        <div class="page-hero-pills">
                            <span class="page-pill"><i class="bi bi-shield-check"></i> Transactions sécurisées</span>
                            <span class="page-pill"><i class="bi bi-clock-history"></i> Suivi des échéances</span>
                            <span class="page-pill"><i class="bi bi-bar-chart"></i> Vue synthétique</span>
                        </div>
                    </div>

                    <div class="hero-side">
                        <div class="hero-mini-card">
                            <strong><?= count($paiements) ?></strong>
                            <span>Total paiements</span>
                        </div>
                        <div class="hero-mini-card">
                            <strong><?= (int)($stats['en_attente'] ?? 0) ?></strong>
                            <span>En attente</span>
                        </div>
                        <div class="hero-mini-card">
                            <strong><?= (int)($stats['valides'] ?? 0) ?></strong>
                            <span>Validés</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="stats-grid-pay">
                <div class="stat-pay blue">
                    <div class="ic"><i class="bi bi-credit-card-2-front"></i></div>
                    <div class="val"><?= count($paiements) ?></div>
                    <div class="lbl">Paiements enregistrés</div>
                </div>

                <div class="stat-pay gold">
                    <div class="ic"><i class="bi bi-hourglass-split"></i></div>
                    <div class="val"><?= (int)($stats['en_attente'] ?? 0) ?></div>
                    <div class="lbl">Paiements en attente</div>
                </div>

                <div class="stat-pay green">
                    <div class="ic"><i class="bi bi-check2-circle"></i></div>
                    <div class="val"><?= (int)($stats['valides'] ?? 0) ?></div>
                    <div class="lbl">Paiements validés</div>
                </div>

                <div class="stat-pay red">
                    <div class="ic"><i class="bi bi-x-circle"></i></div>
                    <div class="val"><?= (int)($stats['refuses'] ?? 0) ?></div>
                    <div class="lbl">Paiements refusés</div>
                </div>
            </section>

            <section class="ca-banner">
                <div class="ca-banner-left">
                    <div class="ca-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <div class="ca-title">Montant total validé</div>
                        <div class="ca-value"><?= formatMoney($stats['montant_total_valide'] ?? 0) ?></div>
                    </div>
                </div>

                <div class="ca-right">
                    <strong><?= (int)($stats['rembourses'] ?? 0) ?> remboursement(s)</strong>
                    Suivi financier du module paiements
                </div>
            </section>

            <?php if (!empty($echeances)): ?>
                <div class="echeance-alert">
                    <i class="bi bi-alarm-fill"></i>
                    <div>
                        <div class="echeance-alert-title">Échéances proches à surveiller</div>
                        <div class="echeance-list">
                            <?php foreach ($echeances as $eItem): ?>
                                <span class="echeance-chip">
                                    <?= e($eItem['reference_paiement'] ?? $eItem['reference'] ?? 'Réf') ?>
                                    — <?= formatDateFr($eItem['date_echeance'] ?? null) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="filter-section">
                <div class="filter-tabs">
                    <button class="filter-tab active" id="tab-tous" onclick="filtrerStatut('', 'tab-tous')">
                        <i class="bi bi-list-ul"></i> Tous
                        <span class="count"><?= count($paiements) ?></span>
                    </button>

                    <button class="filter-tab" id="tab-en_attente" onclick="filtrerStatut('en_attente', 'tab-en_attente')">
                        <i class="bi bi-hourglass-split"></i> En attente
                        <span class="count"><?= (int)($stats['en_attente'] ?? 0) ?></span>
                    </button>

                    <button class="filter-tab" id="tab-valide" onclick="filtrerStatut('valide', 'tab-valide')">
                        <i class="bi bi-check-circle"></i> Validés
                        <span class="count"><?= (int)($stats['valides'] ?? 0) ?></span>
                    </button>

                    <button class="filter-tab" id="tab-refuse" onclick="filtrerStatut('refuse', 'tab-refuse')">
                        <i class="bi bi-x-circle"></i> Refusés
                        <span class="count"><?= (int)($stats['refuses'] ?? 0) ?></span>
                    </button>

                    <button class="filter-tab" id="tab-rembourse" onclick="filtrerStatut('rembourse', 'tab-rembourse')">
                        <i class="bi bi-arrow-counterclockwise"></i> Remboursés
                        <span class="count"><?= (int)($stats['rembourses'] ?? 0) ?></span>
                    </button>
                </div>

                <div class="filter-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchPay" placeholder="Référence, offre, méthode...">
                </div>
            </div>

            <div class="table-card">
                <div class="table-card-head">
                    <div>
                        <div class="table-card-title">Liste des paiements</div>
                        <div class="table-card-sub">
                            <span id="countVisible"><?= count($paiements) ?></span> paiement(s) affiché(s)
                        </div>
                    </div>
                </div>

                <div class="admin-table-wrap">
                    <?php if (empty($paiements)): ?>
                        <div class="empty-box">
                            <i class="bi bi-credit-card-2-front"></i>
                            <strong>Aucun paiement trouvé</strong>
                            <p>La liste des paiements est vide pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <table class="table-pay">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Offre</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                    <th>Date paiement</th>
                                    <th>Échéance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="paiementsBody">
                                <?php foreach ($paiements as $p): ?>
                                    <?php
                                        $statut = strtolower((string)($p['statut'] ?? ''));
                                        $type   = strtolower((string)($p['type_offre'] ?? ''));
                                        $methode = strtolower((string)($p['methode'] ?? ''));

                                        $typeIcons = [
                                            'auto'       => 'bi-car-front',
                                            'sante'      => 'bi-heart-pulse',
                                            'habitation' => 'bi-house-door',
                                            'vie'        => 'bi-shield-check'
                                        ];

                                        $methodeIcons = [
                                            'carte'    => 'bi-credit-card-2-front',
                                            'virement' => 'bi-bank',
                                            'mobile'   => 'bi-phone'
                                        ];

                                        $typeIcon = $typeIcons[$type] ?? 'bi-tags';
                                        $methodeIcon = $methodeIcons[$methode] ?? 'bi-credit-card';

                                        $echeanceSoon = false;
                                        if (!empty($p['date_echeance'])) {
                                            $timestamp = strtotime((string)$p['date_echeance']);
                                            if ($timestamp !== false) {
                                                $diff = ($timestamp - time()) / 86400;
                                                $echeanceSoon = ($diff >= 0 && $diff <= 3);
                                            }
                                        }

                                        $searchData = strtolower(
                                            trim(
                                                ($p['reference_paiement'] ?? $p['reference'] ?? '') . ' ' .
                                                ($p['nom_offre'] ?? $p['offre'] ?? '') . ' ' .
                                                ($p['type_offre'] ?? '') . ' ' .
                                                ($p['methode'] ?? '') . ' ' .
                                                ($p['statut'] ?? '')
                                            )
                                        );
                                    ?>
                                    <tr class="pay-row"
                                        data-statut="<?= e($statut) ?>"
                                        data-search="<?= e($searchData) ?>">
                                        <td>
                                            <span class="ref-code">
                                                <?= e($p['reference_paiement'] ?? $p['reference'] ?? ('PAY-' . (int)($p['id_paiement'] ?? 0))) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="offre-cell">
                                                <div class="offre-type-icon">
                                                    <i class="bi <?= e($typeIcon) ?>"></i>
                                                </div>
                                                <div>
                                                    <div class="offre-name"><?= e($p['nom_offre'] ?? $p['offre'] ?? '—') ?></div>
                                                    <div class="offre-type"><?= e(ucfirstSafe($type)) ?></div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <strong style="color:#fff;"><?= formatMoney($p['montant'] ?? 0) ?></strong>
                                            <div style="font-size:11px;color:var(--text-secondary);margin-top:3px;">
                                                <?= e(ucfirstSafe($p['periodicite'] ?? '')) ?>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="badge-methode">
                                                <i class="bi <?= e($methodeIcon) ?>"></i>
                                                <?= e(ucfirstSafe($methode)) ?>
                                            </span>
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
                                            <?= formatDateFr($p['date_paiement'] ?? null) ?>
                                            <div style="font-size:11px;margin-top:2px;color:var(--text-secondary);opacity:.7;">
                                                <?= e(formatTimeFr($p['date_paiement'] ?? null)) ?>
                                            </div>
                                        </td>

                                        <td style="font-size:12px;color:var(--text-secondary);">
                                            <?= formatDateFr($p['date_echeance'] ?? null) ?>
                                            <?php if ($echeanceSoon): ?>
                                                <div class="echeance-soon">
                                                    <i class="bi bi-alarm-fill"></i> Bientôt
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="action-wrap">
                                                <a href="<?= e($BASE_URL) ?>/controller/PaiementController.php?action=detail&id=<?= (int)($p['id_paiement'] ?? 0) ?>"
                                                   class="action-btn" title="Voir le détail">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <?php if ($statut === 'en_attente'): ?>
                                                    <a href="<?= e($BASE_URL) ?>/controller/PaiementController.php?action=valider&id=<?= (int)($p['id_paiement'] ?? 0) ?>"
                                                       class="action-btn validate" title="Valider"
                                                       onclick="return confirm('Valider ce paiement ?')">
                                                        <i class="bi bi-check2"></i>
                                                    </a>

                                                    <a href="<?= e($BASE_URL) ?>/controller/PaiementController.php?action=refuser&id=<?= (int)($p['id_paiement'] ?? 0) ?>"
                                                       class="action-btn refuse" title="Refuser">
                                                        <i class="bi bi-x-lg"></i>
                                                    </a>
                                                <?php elseif ($statut === 'valide'): ?>
                                                    <a href="<?= e($BASE_URL) ?>/controller/PaiementController.php?action=rembourser&id=<?= (int)($p['id_paiement'] ?? 0) ?>"
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
                            <i class="bi bi-search"></i>
                            Aucun paiement trouvé pour ce filtre.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="<?= e($BASE_URL) ?>/view/BackOffice/assets/js/main.js"></script>
<script>
    const topbarDate = document.getElementById('topbarDate');
    if (topbarDate) {
        topbarDate.textContent = new Date().toLocaleDateString('fr-FR', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }

    let currentStatut = '';
    const searchInput = document.getElementById('searchPay');
    const rows = Array.from(document.querySelectorAll('.pay-row'));
    const noResults = document.getElementById('noResults');
    const countVisible = document.getElementById('countVisible');

    function updateRows() {
        let visible = 0;
        const term = (searchInput?.value || '').toLowerCase().trim();

        rows.forEach(row => {
            const matchStatut = !currentStatut || row.dataset.statut === currentStatut;
            const matchSearch = !term || row.dataset.search.includes(term);

            if (matchStatut && matchSearch) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        if (countVisible) {
            countVisible.textContent = visible;
        }

        if (noResults) {
            noResults.style.display = visible === 0 ? 'block' : 'none';
        }
    }

    function filtrerStatut(statut, tabId) {
        currentStatut = statut;

        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        const activeTab = document.getElementById(tabId);
        if (activeTab) {
            activeTab.classList.add('active');
        }

        updateRows();
    }

    if (searchInput) {
        searchInput.addEventListener('input', updateRows);
    }

    updateRows();
</script>
</body>
</html>