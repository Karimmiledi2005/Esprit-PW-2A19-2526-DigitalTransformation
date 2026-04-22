<?php
$offre   = $offre ?? [];
$message = $message ?? ($_GET['message'] ?? '');
$erreur  = $erreur ?? ($_GET['erreur'] ?? '');

if (!defined('BASE_URL')) {
    define('BASE_URL', '/final/Esprit-PW-2A19-2526-DigitalTransformation-integration/integration');
}

$BASE_URL = BASE_URL;

if (empty($offre)) {
    header('Location: ' . $BASE_URL . '/controller/OffreController.php?action=index&erreur=' . urlencode('Offre introuvable'));
    exit;
}

$type   = strtolower(trim((string)($offre['type_offre'] ?? '')));
$statut = strtolower(trim((string)($offre['statut'] ?? '')));

$icons = [
    'auto'       => 'bi-car-front',
    'sante'      => 'bi-heart-pulse',
    'habitation' => 'bi-house-door',
    'vie'        => 'bi-shield-check'
];

$icon = $icons[$type] ?? 'bi-tags';

$statutAffiche = 'Non défini';
if ($statut === 'active') {
    $statutAffiche = 'Active';
} elseif ($statut === 'suspendue') {
    $statutAffiche = 'Suspendue';
} elseif ($statut === 'archivée' || $statut === 'archivee') {
    $statutAffiche = 'Archivée';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Supprimer une offre — Protex Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/variables.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/base.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/layout.css">

    <style>
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .confirm-wrap {
            max-width: 620px;
            margin: 0 auto;
            animation: fadeUp .4s ease both;
        }

        .danger-card {
            background: var(--glass-bg);
            border: 1px solid rgba(239,68,68,.30);
            border-radius: 22px;
            overflow: hidden;
            backdrop-filter: blur(20px);
            box-shadow: 0 18px 40px rgba(0,0,0,.18);
        }

        .danger-card-head {
            background: rgba(239,68,68,.07);
            border-bottom: 1px solid rgba(239,68,68,.20);
            padding: 22px 26px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .danger-icon-wrap {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: rgba(239,68,68,.15);
            border: 1px solid rgba(239,68,68,.25);
            display: grid;
            place-items: center;
            font-size: 22px;
            color: #fca5a5;
            flex-shrink: 0;
        }

        .danger-card-title {
            font-family: var(--font-display);
            font-size: 19px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }

        .danger-card-sub {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .danger-card-body {
            padding: 26px;
        }

        .offre-preview {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 20px;
        }

        .offre-preview-top {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }

        .offre-preview-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 20px;
            color: #fff;
            flex-shrink: 0;
        }

        .offre-preview-icon.auto       { background: rgba(59,130,246,.20); }
        .offre-preview-icon.sante      { background: rgba(16,185,129,.20); }
        .offre-preview-icon.habitation { background: rgba(245,158,11,.20); }
        .offre-preview-icon.vie        { background: rgba(236,72,153,.20); }

        .offre-preview-name {
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }

        .offre-preview-type {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .offre-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .offre-info-item {
            padding: 11px 12px;
            border-radius: 12px;
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
        }

        .offre-info-label {
            font-size: 10px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 5px;
        }

        .offre-info-value {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .warning-box {
            background: rgba(245,158,11,.07);
            border: 1px solid rgba(245,158,11,.20);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 22px;
            font-size: 13px;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .warning-box i {
            color: #fbbf24;
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .alert-ok,
        .alert-err {
            border-radius: 14px;
            padding: 13px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert-ok {
            background: rgba(16,185,129,.08);
            border: 1px solid rgba(16,185,129,.25);
            color: #86efac;
        }

        .alert-err {
            background: rgba(239,68,68,.08);
            border: 1px solid rgba(239,68,68,.25);
            color: #fca5a5;
        }

        .confirm-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .confirm-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .sidebar-footer .logout-btn,
        .sidebar-nav .nav-item {
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .offre-info-grid {
                grid-template-columns: 1fr;
            }

            .danger-card-head {
                align-items: flex-start;
            }

            .confirm-actions {
                flex-direction: column;
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
            <img src="<?= $BASE_URL ?>/view/FrontOffice/logo.png" alt="logo" width="38" height="38" style="border-radius:9px;flex-shrink:0;">
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
                <div class="topbar-title">Supprimer une offre</div>
                <div class="topbar-sub">Confirmation requise avant suppression</div>
            </div>
            <div class="topbar-actions">
                <a href="<?= $BASE_URL ?>/controller/OffreController.php?action=index" class="btn btn-outline btn-sm">
                    <i class="bi bi-arrow-left"></i> Retour
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

            <div class="page-breadcrumb" style="margin-bottom:24px;">
                <i class="bi bi-house"></i>
                <span>Admin</span>
                <i class="bi bi-chevron-right" style="font-size:10px"></i>
                <a href="<?= $BASE_URL ?>/controller/OffreController.php?action=index" style="color:inherit;text-decoration:none;">
                    Offres
                </a>
                <i class="bi bi-chevron-right" style="font-size:10px"></i>
                <span>Supprimer</span>
            </div>

            <div class="confirm-wrap">
                <div class="danger-card">
                    <div class="danger-card-head">
                        <div class="danger-icon-wrap">
                            <i class="bi bi-trash3"></i>
                        </div>
                        <div>
                            <div class="danger-card-title">Confirmer la suppression</div>
                            <div class="danger-card-sub">Cette action est sensible et peut être irréversible.</div>
                        </div>
                    </div>

                    <div class="danger-card-body">
                        <div class="offre-preview">
                            <div class="offre-preview-top">
                                <div class="offre-preview-icon <?= htmlspecialchars($type ?: 'auto') ?>">
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                                <div>
                                    <div class="offre-preview-name">
                                        <?= htmlspecialchars($offre['nom_offre'] ?? '—') ?>
                                    </div>
                                    <div class="offre-preview-type">
                                        Assurance <?= htmlspecialchars(ucfirst($type ?: 'Non définie')) ?>
                                        &nbsp;·&nbsp; ID #<?= (int)($offre['id_offre'] ?? 0) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="offre-info-grid">
                                <div class="offre-info-item">
                                    <div class="offre-info-label">Prix mensuel</div>
                                    <div class="offre-info-value">
                                        <?= number_format((float)($offre['prix_mensuel'] ?? 0), 3) ?> TND
                                    </div>
                                </div>

                                <div class="offre-info-item">
                                    <div class="offre-info-label">Prix annuel</div>
                                    <div class="offre-info-value">
                                        <?= number_format((float)($offre['prix_annuel'] ?? 0), 3) ?> TND
                                    </div>
                                </div>

                                <div class="offre-info-item">
                                    <div class="offre-info-label">Statut</div>
                                    <div class="offre-info-value"><?= htmlspecialchars($statutAffiche) ?></div>
                                </div>

                                <div class="offre-info-item">
                                    <div class="offre-info-label">Créée le</div>
                                    <div class="offre-info-value">
                                        <?= !empty($offre['date_creation']) ? date('d/m/Y', strtotime($offre['date_creation'])) : '—' ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="warning-box">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>
                                <strong style="color:#fbbf24;">Attention :</strong>
                                si des paiements sont liés à cette offre, il est préférable de
                                <strong>l’archiver</strong> au lieu de la supprimer physiquement,
                                afin de conserver l’historique et l’intégrité des données.
                            </div>
                        </div>

                        <form method="post" action="<?= $BASE_URL ?>/controller/OffreController.php?action=supprimer&id=<?= (int)($offre['id_offre'] ?? 0) ?>">
                            <div class="confirm-actions">
                                <a href="<?= $BASE_URL ?>/controller/OffreController.php?action=index" class="btn btn-outline">
                                    <i class="bi bi-x-lg"></i> Annuler
                                </a>

                                <button type="submit" class="btn btn-danger" onclick="return confirm('Confirmer la suppression de cette offre ?');">
                                    <i class="bi bi-trash3"></i> Supprimer définitivement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="<?= $BASE_URL ?>/view/BackOffice/assets/js/main.js"></script>
</body>
</html>