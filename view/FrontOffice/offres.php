<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../model/OffreModel.php';

$filtre          = $_GET['type'] ?? 'tous';
$model           = new OffreModel();
$toutesLesOffres = array_values(array_filter(
    $model->getAll(),
    fn($o) => $o['statut'] === 'active'
));

$typeIcons  = [
    'auto'       => 'bi-car-front',
    'sante'      => 'bi-heart-pulse',
    'habitation' => 'bi-house-check',
    'vie'        => 'bi-shield-heart',
];
$typeBadges = [
    'auto'       => 'Populaire',
    'sante'      => 'Recommandé',
    'habitation' => 'Économique',
    'vie'        => 'Premium',
];
$typeColors = [
    'auto'       => 'auto',
    'sante'      => 'sante',
    'habitation' => 'maison',
    'vie'        => 'vie',
];

$offres_filtrees = ($filtre === 'tous')
    ? $toutesLesOffres
    : array_values(array_filter(
        $toutesLesOffres,
        fn($o) => $o['type_offre'] === $filtre
    ));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Nos Offres — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <style>
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        @keyframes shimmer {
            0%   { background-position:-200% center; }
            100% { background-position: 200% center; }
        }

        .offer-hero {
            background:
                radial-gradient(ellipse at top right,   rgba(0,180,216,.18), transparent 55%),
                radial-gradient(ellipse at bottom left,  rgba(255,140,0,.12), transparent 50%),
                rgba(255,255,255,.03);
            border:1px solid var(--glass-border);
            border-radius:24px; padding:36px 40px;
            margin-bottom:28px; backdrop-filter:blur(16px);
            animation:fadeUp .5s ease both;
        }
        .offer-hero-inner { display:flex; justify-content:space-between; align-items:flex-start; gap:24px; flex-wrap:wrap; }
        .offer-hero-title { font-family:var(--font-display); font-size:30px; font-weight:800; color:#fff; line-height:1.15; margin-bottom:10px; }
        .offer-hero-title span {
            background:linear-gradient(90deg,var(--accent),#7eb8ff,var(--accent));
            background-size:200% auto;
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            background-clip:text; animation:shimmer 3s linear infinite;
        }
        .offer-hero-sub { color:var(--text-secondary); font-size:14px; line-height:1.7; max-width:580px; margin-bottom:20px; }
        .hero-chips { display:flex; gap:10px; flex-wrap:wrap; }
        .hero-chip { display:inline-flex; align-items:center; gap:7px; padding:7px 13px; border-radius:999px; border:1px solid var(--glass-border); background:rgba(255,255,255,.05); color:var(--text-primary); font-size:12px; font-weight:500; }
        .hero-chip i { color:var(--accent); font-size:13px; }
        .hero-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; min-width:300px; }
        .hero-stat { background:rgba(255,255,255,.05); border:1px solid var(--glass-border); border-radius:16px; padding:16px; text-align:center; transition:border-color .2s; }
        .hero-stat:hover { border-color:rgba(0,180,216,.3); }
        .hero-stat-value { font-family:var(--font-display); font-size:22px; font-weight:800; color:#fff; margin-bottom:4px; }
        .hero-stat-label { font-size:11px; color:var(--text-secondary); }

        .filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:24px; animation:fadeUp .4s .1s ease both; }
        .filter-label { font-size:13px; color:var(--text-secondary); margin-right:4px; }
        .filter-btn { display:inline-flex; align-items:center; gap:7px; padding:8px 16px; border-radius:999px; border:1px solid var(--glass-border); background:var(--glass-bg); color:var(--text-secondary); font-size:13px; text-decoration:none; transition:all .2s ease; }
        .filter-btn:hover { color:var(--text-primary); border-color:rgba(255,255,255,.2); background:rgba(255,255,255,.07); }
        .filter-btn.active { background:var(--accent); border-color:var(--accent); color:#fff; box-shadow:0 4px 14px rgba(0,180,216,.35); }

        .offers-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:22px; margin-bottom:28px; animation:fadeUp .45s .15s ease both; }

        .offer-card {
            background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.02)),rgba(8,18,38,.95);
            border:1px solid var(--glass-border); border-radius:22px;
            overflow:hidden; display:flex; flex-direction:column; position:relative;
            transition:transform .3s ease, box-shadow .3s ease, border-color .3s ease;
            box-shadow:0 8px 24px rgba(0,0,0,.2);
        }
        .offer-card:hover { transform:translateY(-8px); box-shadow:0 24px 50px rgba(0,0,0,.32); border-color:rgba(0,180,216,.35); }
        .offer-card.recommande { border-color:rgba(0,180,216,.4); box-shadow:0 8px 30px rgba(0,180,216,.12); }
        .offer-card.recommande::before {
            content:'★ Recommandé'; position:absolute; top:16px; right:-32px;
            background:var(--accent); color:#fff; font-size:10px; font-weight:700;
            padding:5px 40px; transform:rotate(45deg); letter-spacing:.5px; z-index:2;
        }

        .offer-banner { padding:22px; display:flex; justify-content:space-between; align-items:flex-start; }
        .offer-banner.auto    { background:linear-gradient(135deg,rgba(56,109,255,.30),rgba(56,109,255,.06)); }
        .offer-banner.sante   { background:linear-gradient(135deg,rgba(34,197,94,.26),rgba(34,197,94,.05)); }
        .offer-banner.maison  { background:linear-gradient(135deg,rgba(245,158,11,.24),rgba(245,158,11,.05)); }
        .offer-banner.vie     { background:linear-gradient(135deg,rgba(168,85,247,.24),rgba(168,85,247,.05)); }

        .offer-icon { width:54px; height:54px; border-radius:15px; display:grid; place-items:center; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.12); color:#fff; font-size:24px; }
        .offer-badge { padding:6px 12px; border-radius:999px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.3px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); color:#fff; }

        .offer-body { padding:22px; flex:1; display:flex; flex-direction:column; }
        .offer-name { font-family:var(--font-display); font-size:20px; font-weight:800; color:#fff; margin-bottom:6px; }
        .offer-desc { color:var(--text-secondary); font-size:13px; line-height:1.65; margin-bottom:16px; flex:1; }

        .offer-price-row { display:flex; align-items:baseline; gap:6px; margin-bottom:8px; }
        .offer-price { font-family:var(--font-display); font-size:30px; font-weight:900; color:#fff; }
        .offer-price-note { font-size:13px; color:var(--text-secondary); }
        .offer-price-annual { font-size:12px; color:var(--accent); margin-bottom:16px; }

        .offer-features { display:grid; gap:8px; margin-bottom:16px; }
        .offer-feature { display:flex; align-items:center; gap:9px; font-size:13px; color:var(--text-primary); }
        .offer-feature i { color:var(--success); font-size:14px; }

        .offer-meta { display:flex; justify-content:space-between; padding:12px 14px; border-radius:14px; border:1px solid var(--glass-border); background:rgba(255,255,255,.03); margin-bottom:18px; }
        .offer-meta-item span:first-child { display:block; font-size:10px; color:var(--text-secondary); margin-bottom:3px; text-transform:uppercase; letter-spacing:.5px; }
        .offer-meta-item span:last-child  { font-size:12px; font-weight:700; color:var(--text-primary); }

        .offer-actions { display:flex; gap:10px; margin-top:auto; }

        .empty-offres { grid-column:1/-1; text-align:center; padding:60px 20px; color:var(--text-secondary); }
        .empty-offres i      { font-size:42px; display:block; margin-bottom:12px; opacity:.5; }
        .empty-offres strong { display:block; color:#fff; font-size:18px; margin-bottom:8px; }

        .compare-section { background:rgba(255,255,255,.03); border:1px solid var(--glass-border); border-radius:22px; padding:28px; margin-bottom:28px; animation:fadeUp .45s .2s ease both; }
        .compare-title { font-family:var(--font-display); font-size:20px; font-weight:700; color:#fff; margin-bottom:6px; }
        .compare-sub   { font-size:13px; color:var(--text-secondary); margin-bottom:22px; }
        .compare-wrap  { overflow-x:auto; }
        .compare-table { width:100%; border-collapse:collapse; min-width:640px; }
        .compare-table th { text-align:left; padding:12px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:var(--text-secondary); border-bottom:1px solid var(--glass-border); }
        .compare-table th:not(:first-child) { text-align:center; }
        .compare-table td { padding:13px 16px; font-size:13px; color:var(--text-primary); border-bottom:1px solid rgba(255,255,255,.04); }
        .compare-table td:not(:first-child) { text-align:center; }
        .compare-table tbody tr:hover { background:rgba(255,255,255,.02); }
        .compare-table td i.bi-check-circle-fill { color:var(--success); font-size:16px; }
        .compare-table td i.bi-dash-circle       { color:var(--text-secondary); opacity:.4; font-size:16px; }

        .cta-banner {
            background:
                radial-gradient(ellipse at top left,    rgba(0,180,216,.18),transparent 40%),
                radial-gradient(ellipse at bottom right, rgba(255,140,0,.12),transparent 40%),
                rgba(255,255,255,.03);
            border:1px solid var(--glass-border); border-radius:22px;
            padding:32px 36px; display:flex; align-items:center;
            justify-content:space-between; gap:20px; flex-wrap:wrap;
            animation:fadeUp .45s .25s ease both;
        }
        .cta-title { font-family:var(--font-display); font-size:22px; font-weight:700; color:#fff; margin-bottom:8px; }
        .cta-sub   { font-size:14px; color:var(--text-secondary); line-height:1.6; max-width:600px; }

        @media (max-width:1100px) { .offers-grid { grid-template-columns:repeat(2,1fr); } .hero-stats { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:700px)  { .offers-grid { grid-template-columns:1fr; } .offer-hero-title { font-size:24px; } .hero-stats { display:none; } .cta-banner { flex-direction:column; } }
    </style>
</head>
<body>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">

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
            <a class="nav-link" href="mes-contrats.html">
                <i class="bi bi-file-earmark-text"></i>
                <span class="nav-label">Contrats</span>
                <span class="nav-badge accent">3</span>
            </a>
            <a class="nav-link" href="mes-sinistres.html">
                <i class="bi bi-shield-exclamation"></i>
                <span class="nav-label">Sinistres</span>
                <span class="nav-badge">1</span>
            </a>
            <a class="nav-link" href="paiement.php">
                <i class="bi bi-credit-card"></i>
                <span class="nav-label">Paiements</span>
            </a>
            <div class="nav-separator"></div>
            <a class="nav-link" href="reclamations.html">
                <i class="bi bi-chat-dots"></i>
                <span class="nav-label">Réclamations</span>
            </a>
            <a class="nav-link" href="agences.html">
                <i class="bi bi-geo-alt"></i>
                <span class="nav-label">Agences</span>
            </a>
            <a class="nav-link active" href="offres.php">
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
                    <a href="monprofile.html" class="dropdown-item"><i class="bi bi-person-circle"></i> Mon profil</a>
                    <a href="parametres.html" class="dropdown-item"><i class="bi bi-gear"></i> Paramètres</a>
                    <div class="dropdown-divider"></div>
                    <a href="login.html" class="dropdown-item logout"><i class="bi bi-box-arrow-right"></i> Se déconnecter</a>
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
                    <a href="client.html" style="color:inherit;text-decoration:none;">Accueil</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <span>Nos offres</span>
                </div>
            </div>
        </div>

        <div class="content">

            <!-- Hero -->
            <section class="offer-hero">
                <div class="offer-hero-inner">
                    <div>
                        <div class="offer-hero-title">
                            Choisissez votre<br><span>protection idéale</span>
                        </div>
                        <div class="offer-hero-sub">
                            Des formules conçues pour chaque profil — comparez, choisissez
                            et souscrivez en quelques clics dans une interface 100% digitale.
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
                            <div class="hero-stat-value"><?= count($toutesLesOffres) ?></div>
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

            <!-- Filtres -->
            <div class="filter-bar">
                <span class="filter-label">Filtrer :</span>
                <a href="offres.php?type=tous"      class="filter-btn <?= $filtre==='tous'      ?'active':'' ?>"><i class="bi bi-grid-3x3-gap"></i> Toutes</a>
                <a href="offres.php?type=auto"       class="filter-btn <?= $filtre==='auto'       ?'active':'' ?>"><i class="bi bi-car-front"></i> Auto</a>
                <a href="offres.php?type=sante"      class="filter-btn <?= $filtre==='sante'      ?'active':'' ?>"><i class="bi bi-heart-pulse"></i> Santé</a>
                <a href="offres.php?type=habitation" class="filter-btn <?= $filtre==='habitation' ?'active':'' ?>"><i class="bi bi-house-check"></i> Habitation</a>
                <a href="offres.php?type=vie"        class="filter-btn <?= $filtre==='vie'        ?'active':'' ?>"><i class="bi bi-shield-heart"></i> Vie</a>
            </div>

            <!-- Section header -->
            <div class="section-header" id="offres-section">
                <div>
                    <div class="section-title">Nos formules</div>
                    <div class="section-sub"><?= count($offres_filtrees) ?> offre(s) disponible(s)</div>
                </div>
            </div>

            <!-- Grid offres depuis BDD -->
            <div class="offers-grid">
                <?php if (empty($offres_filtrees)): ?>
                <div class="empty-offres">
                    <i class="bi bi-inbox"></i>
                    <strong>Aucune offre disponible</strong>
                    <p>Revenez bientôt pour découvrir nos nouvelles formules.</p>
                </div>
                <?php else: ?>
                <?php foreach ($offres_filtrees as $i => $o):
                    $type     = strtolower($o['type_offre'] ?? '');
                    $icon     = $typeIcons[$type]  ?? 'bi-tags';
                    $badge    = $typeBadges[$type] ?? 'Offre';
                    $color    = $typeColors[$type] ?? 'auto';
                    $eco      = round(($o['prix_mensuel'] * 12) - $o['prix_annuel'], 0);
                    $features = !empty($o['couverture'])
                        ? array_slice(array_map('trim', explode(',', $o['couverture'])), 0, 4)
                        : [];
                ?>
                <article class="offer-card <?= $badge==='Recommandé' ? 'recommande' : '' ?>"
                         style="animation:fadeUp .4s <?= $i*0.1 ?>s ease both;">

                    <div class="offer-banner <?= $color ?>">
                        <div class="offer-icon"><i class="bi <?= $icon ?>"></i></div>
                        <span class="offer-badge"><?= htmlspecialchars($badge) ?></span>
                    </div>

                    <div class="offer-body">
                        <div class="offer-name"><?= htmlspecialchars($o['nom_offre']) ?></div>
                        <div class="offer-desc"><?= htmlspecialchars($o['description'] ?? '') ?></div>

                        <div class="offer-price-row">
                            <div class="offer-price"><?= number_format((float)$o['prix_mensuel'], 0) ?> TND</div>
                            <div class="offer-price-note">/ mois</div>
                        </div>
                        <div class="offer-price-annual">
                            <i class="bi bi-tag"></i>
                            <?= number_format((float)$o['prix_annuel'], 0) ?> TND/an
                            <?php if ($eco > 0): ?>
                            — économisez <?= $eco ?> TND
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($features)): ?>
                        <div class="offer-features">
                            <?php foreach ($features as $f): ?>
                            <div class="offer-feature">
                                <i class="bi bi-check-circle-fill"></i>
                                <?= htmlspecialchars($f) ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="offer-meta">
                            <div class="offer-meta-item">
                                <span>Type</span>
                                <span><?= ucfirst($type) ?></span>
                            </div>
                            <div class="offer-meta-item">
                                <span>Plafond</span>
                                <span><?= !empty($o['plafond']) ? number_format((float)$o['plafond'],0,'.',' ').' TND' : '—' ?></span>
                            </div>
                            <div class="offer-meta-item">
                                <span>Durée min.</span>
                                <span><?= (int)($o['duree_min'] ?? 1) ?> mois</span>
                            </div>
                        </div>

                        <div class="offer-actions">
                            <a href="paiement.php?offre=<?= (int)$o['id_offre'] ?>"
                               class="btn btn-primary" style="flex:1;justify-content:center;">
                                <i class="bi bi-check2-circle"></i> Souscrire
                            </a>
                            <a href="paiement.php?offre=<?= (int)$o['id_offre'] ?>"
                               class="btn btn-outline" title="Payer">
                                <i class="bi bi-credit-card"></i>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Comparatif -->
            <?php if ($filtre === 'tous' && !empty($toutesLesOffres)): ?>
            <section class="compare-section">
                <div class="compare-title">Comparatif rapide</div>
                <div class="compare-sub">Une lecture simple pour choisir la formule qui vous correspond le mieux.</div>
                <div class="compare-wrap">
                    <table class="compare-table">
                        <thead>
                            <tr>
                                <th>Critère</th>
                                <?php foreach ($toutesLesOffres as $o): ?>
                                <th><?= htmlspecialchars($o['nom_offre']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Prix mensuel</td>
                                <?php foreach ($toutesLesOffres as $o): ?>
                                <td><?= number_format((float)$o['prix_mensuel'],0) ?> TND</td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Prix annuel</td>
                                <?php foreach ($toutesLesOffres as $o): ?>
                                <td><?= number_format((float)$o['prix_annuel'],0) ?> TND</td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Plafond</td>
                                <?php foreach ($toutesLesOffres as $o): ?>
                                <td><?= !empty($o['plafond']) ? number_format((float)$o['plafond'],0,'.',' ').' TND' : '—' ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Durée minimale</td>
                                <?php foreach ($toutesLesOffres as $o): ?>
                                <td><?= (int)($o['duree_min']??1) ?> mois</td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Gestion digitale</td>
                                <?php foreach ($toutesLesOffres as $o): ?>
                                <td><i class="bi bi-check-circle-fill"></i></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Assistance 24/7</td>
                                <?php foreach ($toutesLesOffres as $o):
                                    $t = strtolower($o['type_offre']??'');
                                ?>
                                <td>
                                    <?php if (in_array($t,['auto','sante','vie'])): ?>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <?php else: ?>
                                    <i class="bi bi-dash-circle"></i>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td>Couverture famille</td>
                                <?php foreach ($toutesLesOffres as $o):
                                    $t = strtolower($o['type_offre']??'');
                                ?>
                                <td>
                                    <?php if (in_array($t,['sante','vie'])): ?>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <?php else: ?>
                                    <i class="bi bi-dash-circle"></i>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- CTA -->
            <section class="cta-banner">
                <div>
                    <div class="cta-title">Prêt à souscrire ?</div>
                    <div class="cta-sub">
                        Choisissez votre offre et finalisez votre souscription directement en ligne.
                        Interface sécurisée, validation rapide, contrat actif immédiatement.
                    </div>
                </div>
                <a href="paiement.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-right-circle"></i> Aller au paiement
                </a>
            </section>

        </div>
    </main>
</div>

<script src="assets/js/main.js"></script>
<script>
    const avatarBtn = document.getElementById('avatarBtn');
    const avatarDropdown = document.getElementById('avatarDropdown');
    if (avatarBtn && avatarDropdown) {
        avatarBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            avatarDropdown.classList.toggle('open');
        });
        document.addEventListener('click', () => avatarDropdown.classList.remove('open'));
    }
</script>
</body>
</html>