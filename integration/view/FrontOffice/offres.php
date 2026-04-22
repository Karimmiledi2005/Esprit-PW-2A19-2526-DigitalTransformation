<?php
require_once __DIR__ . '/../../config.php';

$BASE_URL = defined('BASE_URL')
    ? BASE_URL
    : '/final/Esprit-PW-2A19-2526-DigitalTransformation-integration/integration';

$db = config::getConnexion();

$offres = [];
$totalOffres = 0;

try {
    $stmt = $db->prepare("
        SELECT *
        FROM offre
        WHERE statut = 'active'
        ORDER BY date_creation DESC
    ");
    $stmt->execute();
    $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalOffres = count($offres);
} catch (Exception $e) {
    $offres = [];
    $totalOffres = 0;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function typeLabel(string $type): string
{
    return match (strtolower($type)) {
        'auto'       => 'Auto',
        'sante'      => 'Santé',
        'habitation' => 'Habitation',
        'vie'        => 'Vie',
        default      => ucfirst($type),
    };
}

function bannerClass(string $type): string
{
    return match (strtolower($type)) {
        'auto'       => 'auto',
        'sante'      => 'sante',
        'habitation' => 'maison',
        'vie'        => 'vie',
        default      => 'auto',
    };
}

function typeIcon(string $type): string
{
    return match (strtolower($type)) {
        'auto'       => 'bi-car-front',
        'sante'      => 'bi-heart-pulse',
        'habitation' => 'bi-house-check',
        'vie'        => 'bi-shield-heart',
        default      => 'bi-stars',
    };
}

function badgeText(array $offre): string
{
    $type = strtolower((string)($offre['type_offre'] ?? ''));
    $prix = (float)($offre['prix_mensuel'] ?? 0);

    if ($type === 'sante') {
        return 'Recommandé';
    }
    if ($type === 'auto') {
        return 'Populaire';
    }
    if ($type === 'vie') {
        return 'Premium';
    }
    if ($prix > 0 && $prix <= 50) {
        return 'Économique';
    }

    return 'Disponible';
}

function isRecommended(array $offre): bool
{
    return strtolower((string)($offre['type_offre'] ?? '')) === 'sante';
}

function shortDescription(string $desc, int $limit = 130): string
{
    $desc = trim($desc);
    if (mb_strlen($desc) <= $limit) {
        return $desc;
    }
    return mb_substr($desc, 0, $limit) . '...';
}

function extractFeatures(string $couverture): array
{
    $raw = preg_split('/[,\n;\-•]+/u', $couverture) ?: [];
    $items = [];

    foreach ($raw as $item) {
        $item = trim($item);
        if ($item !== '') {
            $items[] = $item;
        }
    }

    if (empty($items)) {
        return ['Couverture incluse'];
    }

    return array_slice($items, 0, 4);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Nos Offres — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/user/css/variables.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/user/css/base.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/user/css/layout.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/user/css/client.css">
    <style>
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        @keyframes shimmerText {
            0%   { background-position: 0% center; }
            100% { background-position: 200% center; }
        }
        .page-header {
            padding: 24px 32px 0;
            display: flex; align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 8px; flex-wrap: wrap; gap: 12px;
        }
        .page-title-main {
            font-family: 'Sora', sans-serif;
            font-size: 24px; font-weight: 800; color: #15233C;
        }
        .page-breadcrumb {
            font-size: 12px; color: rgba(21,35,60,0.5);
            margin-top: 5px; display: flex; align-items: center; gap: 6px;
        }
        .page-breadcrumb span { color: #FF6B1A; font-weight: 600; }

        .offer-hero {
            background: linear-gradient(135deg, #1A3A7A 0%, #0f2456 100%);
            border-radius: 22px; padding: 32px 36px; margin-bottom: 28px;
            display: flex; align-items: stretch;
            justify-content: space-between; gap: 22px; flex-wrap: wrap;
            animation: fadeUp .45s ease both; position: relative; overflow: hidden;
        }
        .offer-hero::before {
            content: ''; position: absolute; top: -60px; right: -50px;
            width: 220px; height: 220px;
            background: rgba(255,107,26,0.12); border-radius: 50%;
        }
        .offer-hero-inner {
            display: flex; justify-content: space-between;
            align-items: flex-start; gap: 24px; flex-wrap: wrap;
            width: 100%; position: relative; z-index: 1;
        }
        .offer-hero-title {
            font-family: 'Sora', sans-serif; font-size: 30px; font-weight: 900;
            color: #fff; line-height: 1.15; margin-bottom: 10px;
        }
        .offer-hero-title span {
            background: linear-gradient(90deg, #FF6B1A, #ffd2b5, #FF6B1A);
            background-size: 200% auto;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; animation: shimmerText 4s linear infinite;
        }
        .offer-hero-sub {
            font-size: 14px; color: rgba(255,255,255,0.68);
            line-height: 1.7; max-width: 600px; margin-bottom: 18px;
        }
        .hero-chips { display: flex; gap: 10px; flex-wrap: wrap; }
        .hero-chip {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 7px 13px; border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.87); font-size: 12px; font-weight: 500;
        }
        .hero-chip i { color: #4ade80; font-size: 13px; }
        .hero-stats {
            display: grid; grid-template-columns: repeat(3,1fr);
            gap: 14px; min-width: 320px;
        }
        .hero-stat {
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);
            border-radius: 18px; padding: 18px 16px; text-align: center;
            transition: all .25s; backdrop-filter: blur(4px);
        }
        .hero-stat:hover { background: rgba(255,255,255,0.12); transform: translateY(-2px); }
        .hero-stat-value {
            font-family: 'Sora', sans-serif; font-size: 24px; font-weight: 900;
            color: #fff; margin-bottom: 4px;
        }
        .hero-stat-label { font-size: 11px; color: rgba(255,255,255,0.65); text-transform: uppercase; letter-spacing: .5px; }

        .filter-wrap {
            background: #fff; border: 1px solid rgba(26,58,122,0.08);
            border-radius: 18px; padding: 18px 20px;
            box-shadow: 0 4px 18px rgba(26,58,122,0.06);
            margin-bottom: 24px; animation: fadeUp .4s .08s ease both;
        }
        .filter-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .filter-label { font-size: 13px; color: rgba(21,35,60,0.6); font-weight: 700; margin-right: 2px; }
        .filter-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 16px; border-radius: 999px;
            border: 1px solid rgba(26,58,122,0.12);
            background: rgba(26,58,122,0.03); color: rgba(21,35,60,0.60);
            font-size: 13px; font-weight: 600; text-decoration: none; transition: all .22s;
            cursor: pointer;
        }
        .filter-btn:hover {
            background: rgba(26,58,122,0.05); border-color: rgba(255,107,26,0.28);
            color: #15233C; transform: translateY(-1px);
        }
        .filter-btn.active {
            background: linear-gradient(135deg, #FF6B1A, #e05a0f);
            border-color: #FF6B1A; color: #fff;
            box-shadow: 0 6px 18px rgba(255,107,26,0.25);
        }

        .section-header { margin-bottom: 18px; animation: fadeUp .4s .12s ease both; }
        .section-title { font-family: 'Sora', sans-serif; font-size: 22px; font-weight: 800; color: #15233C; margin-bottom: 4px; }
        .section-sub { font-size: 13px; color: rgba(21,35,60,0.55); }

        .offers-grid {
            display: grid; grid-template-columns: repeat(3,1fr);
            gap: 22px; margin-bottom: 28px; animation: fadeUp .45s .15s ease both;
        }
        .offer-card {
            background: #fff; border: 1px solid rgba(26,58,122,0.08);
            border-radius: 22px; overflow: hidden;
            display: flex; flex-direction: column; position: relative;
            box-shadow: 0 6px 24px rgba(26,58,122,0.07); transition: all .28s;
        }
        .offer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 38px rgba(26,58,122,0.13);
            border-color: rgba(255,107,26,0.20);
        }
        .offer-card.recommande { border-color: rgba(255,107,26,0.22); box-shadow: 0 10px 28px rgba(255,107,26,0.12); }
        .offer-card.recommande::before {
            content: '★ Recommandé'; position: absolute; top: 17px; right: -34px;
            background: linear-gradient(135deg, #FF6B1A, #e05a0f); color: #fff;
            font-size: 10px; font-weight: 700; padding: 6px 40px;
            transform: rotate(45deg); letter-spacing: .4px; z-index: 2;
            box-shadow: 0 6px 18px rgba(255,107,26,0.28);
        }
        .offer-banner {
            padding: 22px; display: flex;
            justify-content: space-between; align-items: flex-start; min-height: 110px;
        }
        .offer-banner.auto    { background: linear-gradient(135deg, rgba(26,58,122,0.95), rgba(45,92,196,0.78)); }
        .offer-banner.sante   { background: linear-gradient(135deg, rgba(5,150,105,0.95), rgba(52,211,153,0.78)); }
        .offer-banner.maison  { background: linear-gradient(135deg, rgba(255,107,26,0.95), rgba(255,154,92,0.80)); }
        .offer-banner.vie     { background: linear-gradient(135deg, rgba(124,58,237,0.95), rgba(167,139,250,0.82)); }
        .offer-icon {
            width: 56px; height: 56px; border-radius: 16px;
            display: grid; place-items: center;
            background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.18);
            color: #fff; font-size: 25px; box-shadow: inset 0 1px 0 rgba(255,255,255,0.18);
        }
        .offer-badge {
            padding: 6px 12px; border-radius: 999px; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .35px;
            background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.16);
            color: #fff; backdrop-filter: blur(4px);
        }
        .offer-body { padding: 22px; flex: 1; display: flex; flex-direction: column; }
        .offer-name { font-family: 'Sora', sans-serif; font-size: 20px; font-weight: 800; color: #15233C; margin-bottom: 6px; }
        .offer-desc { color: rgba(21,35,60,0.58); font-size: 13px; line-height: 1.65; margin-bottom: 16px; flex: 1; }
        .offer-price-box {
            background: linear-gradient(135deg, #f8faff, #fff5f0);
            border: 1px solid rgba(26,58,122,0.08); border-radius: 16px;
            padding: 16px 18px; margin-bottom: 16px;
        }
        .offer-price-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 5px; flex-wrap: wrap; }
        .offer-price { font-family: 'Sora', sans-serif; font-size: 31px; font-weight: 900; color: #15233C; line-height: 1; }
        .offer-price-note { font-size: 13px; color: rgba(21,35,60,0.50); }
        .offer-price-annual { font-size: 12px; color: #FF6B1A; font-weight: 600; }
        .offer-price-annual i { margin-right: 4px; }
        .offer-features { display: grid; gap: 9px; margin-bottom: 16px; }
        .offer-feature { display: flex; align-items: flex-start; gap: 9px; font-size: 13px; color: #15233C; line-height: 1.45; }
        .offer-feature i { color: #059669; font-size: 14px; margin-top: 2px; flex-shrink: 0; }
        .offer-meta {
            display: flex; justify-content: space-between; gap: 10px;
            padding: 14px; border-radius: 15px;
            border: 1px solid rgba(26,58,122,0.08); background: rgba(26,58,122,0.03);
            margin-bottom: 18px; flex-wrap: wrap;
        }
        .offer-meta-item { min-width: 80px; flex: 1; }
        .offer-meta-item span:first-child { display: block; font-size: 10px; color: rgba(21,35,60,0.45); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .55px; }
        .offer-meta-item span:last-child  { font-size: 12px; font-weight: 700; color: #15233C; }
        .offer-actions { display: flex; gap: 10px; margin-top: auto; }

        .empty-offers {
            background: #fff;
            border: 1px solid rgba(26,58,122,0.08);
            border-radius: 22px;
            padding: 40px 24px;
            text-align: center;
            color: rgba(21,35,60,0.55);
            box-shadow: 0 6px 24px rgba(26,58,122,0.07);
        }
        .empty-offers i {
            font-size: 42px;
            color: #FF6B1A;
            margin-bottom: 12px;
            display: block;
        }
        .empty-offers strong {
            display: block;
            color: #15233C;
            font-size: 20px;
            margin-bottom: 8px;
        }

        .cta-banner {
            background: linear-gradient(135deg, #f8faff 0%, #fff5f0 100%);
            border: 1px solid rgba(26,58,122,0.08); border-radius: 22px;
            padding: 32px 36px; display: flex; align-items: center;
            justify-content: space-between; gap: 20px; flex-wrap: wrap;
            animation: fadeUp .45s .25s ease both;
            box-shadow: 0 6px 22px rgba(26,58,122,0.05);
            position: relative; overflow: hidden;
        }
        .cta-title { font-family: 'Sora', sans-serif; font-size: 22px; font-weight: 800; color: #15233C; margin-bottom: 8px; position: relative; z-index: 1; }
        .cta-sub   { font-size: 14px; color: rgba(21,35,60,0.58); line-height: 1.65; max-width: 620px; position: relative; z-index: 1; }

        .btn {
            padding: 10px 20px; border-radius: 11px; font-size: 13px; font-weight: 700;
            font-family: 'DM Sans', sans-serif; border: none; cursor: pointer;
            display: inline-flex; align-items: center; gap: 7px;
            transition: all 0.2s; text-decoration: none; white-space: nowrap;
        }
        .btn-primary { background: linear-gradient(135deg, #FF6B1A, #e05a0f); color: #fff; box-shadow: 0 4px 14px rgba(255,107,26,0.24); }
        .btn-primary:hover { background: linear-gradient(135deg, #e05a0f, #cc4f00); box-shadow: 0 6px 20px rgba(255,107,26,0.34); transform: translateY(-1px); }
        .btn-outline { background: transparent; color: #1A3A7A; border: 1px solid rgba(26,58,122,0.20); }
        .btn-outline:hover { background: rgba(26,58,122,0.06); border-color: #1A3A7A; }
        .btn-lg { padding: 14px 28px; font-size: 15px; }

        @media (max-width: 1100px) { .offers-grid { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 700px)  { .offers-grid { grid-template-columns: 1fr; } }
        @media (max-width: 560px)  { .hero-stats { display: none; } }
    </style>
</head>
<body>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">

    <nav class="navbar">
        <a href="<?= $BASE_URL ?>/view/FrontOffice/client.html" class="navbar-brand">
            <img src="<?= $BASE_URL ?>/view/FrontOffice/logo.png" alt="logo" width="40" height="40" style="border-radius:10px;">
            <div>
                <div class="logo-text">Protex</div>
                <div class="logo-sub">Assurance Digitale</div>
            </div>
        </a>

        <div class="navbar-nav">
            <a class="nav-link" href="<?= $BASE_URL ?>/view/FrontOffice/client.html">
                <i class="bi bi-grid-1x2"></i>
                <span class="nav-label">Tableau de bord</span>
            </a>
            <a class="nav-link" href="<?= $BASE_URL ?>/view/FrontOffice/mes-contrats.html">
                <i class="bi bi-file-earmark-text"></i>
                <span class="nav-label">Contrats</span>
                <span class="nav-badge accent">3</span>
            </a>
            <a class="nav-link" href="<?= $BASE_URL ?>/view/FrontOffice/mes-sinistres.html">
                <i class="bi bi-shield-exclamation"></i>
                <span class="nav-label">Sinistres</span>
                <span class="nav-badge">1</span>
            </a>
            <a class="nav-link" href="<?= $BASE_URL ?>/view/FrontOffice/paiement.html">
                <i class="bi bi-credit-card"></i>
                <span class="nav-label">Paiements</span>
            </a>
            <div class="nav-separator"></div>
            <a class="nav-link" href="<?= $BASE_URL ?>/view/FrontOffice/reclamations.html">
                <i class="bi bi-chat-dots"></i>
                <span class="nav-label">Réclamations</span>
            </a>
            <a class="nav-link" href="<?= $BASE_URL ?>/view/FrontOffice/agences.html">
                <i class="bi bi-geo-alt"></i>
                <span class="nav-label">Agences</span>
            </a>
            <a class="nav-link active" href="<?= $BASE_URL ?>/view/FrontOffice/offres.php">
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
                <div class="avatar-btn" id="avatarBtn">KM</div>
                <div class="avatar-dropdown" id="avatarDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">KM</div>
                        <div class="dropdown-info">
                            <div class="dropdown-name">Karim Miledi</div>
                            <div class="dropdown-email">karim.miledi@email.com</div>
                            <span class="dropdown-role">Client Premium</span>
                        </div>
                    </div>
                    <a href="<?= $BASE_URL ?>/view/FrontOffice/monprofile.html" class="dropdown-item"><i class="bi bi-person-circle"></i> Mon profil</a>
                    <a href="<?= $BASE_URL ?>/view/FrontOffice/parametres.html" class="dropdown-item"><i class="bi bi-gear"></i> Paramètres</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= $BASE_URL ?>/view/FrontOffice/login.html" class="dropdown-item logout"><i class="bi bi-box-arrow-right"></i> Se déconnecter</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="main">
        <div class="page-header">
            <div>
                <div class="page-title-main">Nos offres d'assurance</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="<?= $BASE_URL ?>/view/FrontOffice/client.html" style="color:inherit;text-decoration:none;">Accueil</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <span>Nos offres</span>
                </div>
            </div>
        </div>

        <div class="content">

            <section class="offer-hero">
                <div class="offer-hero-inner">
                    <div>
                        <div class="offer-hero-title">
                            Choisissez votre<br><span>protection idéale</span>
                        </div>
                        <div class="offer-hero-sub">
                            Des formules modernes, transparentes et adaptées à chaque profil.
                            Comparez les couvertures, consultez les prix et souscrivez en quelques clics.
                        </div>
                        <div class="hero-chips">
                            <span class="hero-chip"><i class="bi bi-shield-check"></i> Protection fiable</span>
                            <span class="hero-chip"><i class="bi bi-lightning-charge"></i> Souscription rapide</span>
                            <span class="hero-chip"><i class="bi bi-lock"></i> Paiement sécurisé</span>
                            <span class="hero-chip"><i class="bi bi-headset"></i> Support 24/7</span>
                        </div>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-value"><?= $totalOffres ?></div>
                            <div class="hero-stat-label">Formules</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">24/7</div>
                            <div class="hero-stat-label">Assistance</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">100%</div>
                            <div class="hero-stat-label">Digital</div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="filter-wrap">
                <div class="filter-bar">
                    <span class="filter-label">Filtrer :</span>
                    <button class="filter-btn active" onclick="filterOffres('tous', this)">
                        <i class="bi bi-grid-3x3-gap"></i> Toutes
                    </button>
                    <button class="filter-btn" onclick="filterOffres('auto', this)">
                        <i class="bi bi-car-front"></i> Auto
                    </button>
                    <button class="filter-btn" onclick="filterOffres('sante', this)">
                        <i class="bi bi-heart-pulse"></i> Santé
                    </button>
                    <button class="filter-btn" onclick="filterOffres('habitation', this)">
                        <i class="bi bi-house-check"></i> Habitation
                    </button>
                    <button class="filter-btn" onclick="filterOffres('vie', this)">
                        <i class="bi bi-shield-heart"></i> Vie
                    </button>
                </div>
            </div>

            <div class="section-header">
                <div class="section-title">Nos formules</div>
                <div class="section-sub" id="offreCount"><?= $totalOffres ?> offre(s) disponible(s)</div>
            </div>

            <?php if (empty($offres)): ?>
                <div class="empty-offers">
                    <i class="bi bi-inbox"></i>
                    <strong>Aucune offre disponible</strong>
                    <p>Aucune offre active n'est disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="offers-grid" id="offresGrid">
                    <?php foreach ($offres as $index => $offre): ?>
                        <?php
                            $type = strtolower((string)($offre['type_offre'] ?? ''));
                            $features = extractFeatures((string)($offre['couverture'] ?? ''));
                            $mensuel = (float)($offre['prix_mensuel'] ?? 0);
                            $annuel = (float)($offre['prix_annuel'] ?? 0);
                            $economy = max(0, ($mensuel * 12) - $annuel);
                        ?>
                        <article
                            class="offer-card <?= isRecommended($offre) ? 'recommande' : '' ?>"
                            data-type="<?= e($type) ?>"
                            style="animation:fadeUp .4s <?= number_format($index * 0.08, 2) ?>s ease both;"
                        >
                            <div class="offer-banner <?= bannerClass($type) ?>">
                                <div class="offer-icon"><i class="bi <?= typeIcon($type) ?>"></i></div>
                                <span class="offer-badge"><?= e(badgeText($offre)) ?></span>
                            </div>

                            <div class="offer-body">
                                <div class="offer-name"><?= e($offre['nom_offre'] ?? 'Offre') ?></div>
                                <div class="offer-desc"><?= e(shortDescription((string)($offre['description'] ?? ''))) ?></div>

                                <div class="offer-price-box">
                                    <div class="offer-price-row">
                                        <div class="offer-price"><?= number_format($mensuel, 0) ?> TND</div>
                                        <div class="offer-price-note">/ mois</div>
                                    </div>
                                    <div class="offer-price-annual">
                                        <i class="bi bi-tag"></i>
                                        <?= number_format($annuel, 0) ?> TND/an
                                        <?php if ($economy > 0): ?>
                                            — économisez <?= number_format($economy, 0) ?> TND
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="offer-features">
                                    <?php foreach ($features as $feature): ?>
                                        <div class="offer-feature">
                                            <i class="bi bi-check-circle-fill"></i>
                                            <?= e($feature) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="offer-meta">
                                    <div class="offer-meta-item">
                                        <span>Type</span>
                                        <span><?= e(typeLabel($type)) ?></span>
                                    </div>
                                    <div class="offer-meta-item">
                                        <span>Plafond</span>
                                        <span>
                                            <?= !empty($offre['plafond']) ? number_format((float)$offre['plafond'], 0, '.', ' ') . ' TND' : '—' ?>
                                        </span>
                                    </div>
                                    <div class="offer-meta-item">
                                        <span>Durée min.</span>
                                        <span><?= e($offre['duree_min'] ?? '—') ?> mois</span>
                                    </div>
                                </div>

                                <div class="offer-actions">
                                    <a href="<?= $BASE_URL ?>/view/FrontOffice/paiement.html" class="btn btn-primary" style="flex:1;justify-content:center;">
                                        <i class="bi bi-check2-circle"></i> Souscrire
                                    </a>
                                    <a href="<?= $BASE_URL ?>/view/FrontOffice/paiement.html" class="btn btn-outline" title="Payer">
                                        <i class="bi bi-credit-card"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <section class="cta-banner">
                <div>
                    <div class="cta-title">Prêt à souscrire ?</div>
                    <div class="cta-sub">
                        Choisissez l'offre qui vous convient puis finalisez votre souscription
                        en ligne dans une interface claire, sécurisée et cohérente.
                    </div>
                </div>
                <a href="<?= $BASE_URL ?>/view/FrontOffice/paiement.html" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-right-circle"></i> Aller au paiement
                </a>
            </section>

        </div>
    </main>
</div>

<script src="<?= $BASE_URL ?>/user/js/main.js"></script>
<script>
    const avatarBtn = document.getElementById('avatarBtn');
    const avatarDropdown = document.getElementById('avatarDropdown');

    if (avatarBtn && avatarDropdown) {
        avatarBtn.addEventListener('click', e => {
            e.stopPropagation();
            avatarDropdown.classList.toggle('open');
        });
        document.addEventListener('click', () => avatarDropdown.classList.remove('open'));
    }

    function filterOffres(type, btn) {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const cards = document.querySelectorAll('.offer-card');
        let count = 0;

        cards.forEach(card => {
            if (type === 'tous' || card.dataset.type === type) {
                card.style.display = '';
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        const counter = document.getElementById('offreCount');
        if (counter) {
            counter.textContent = count + ' offre(s) disponible(s)';
        }
    }
</script>
</body>
</html>