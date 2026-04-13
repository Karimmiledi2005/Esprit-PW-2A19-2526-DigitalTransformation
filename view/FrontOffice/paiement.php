<?php
/* =============================================
   paiement.php — Protex FrontOffice
   Adapté au style du projet de l'équipe
   ============================================= */

$offres = [
    1 => [
        'nom'         => 'Auto Premium',
        'icon'        => 'bi-car-front',
        'color'       => 'auto',
        'prix_mensuel'=> 85,
        'prix_annuel' => 950,
        'type'        => 'Tous risques',
        'duree'       => '1 mois',
        'ref'         => 'POL-0001',
        'description' => 'Protection automobile complète avec couverture tous risques et assistance 24/7.',
    ],
    2 => [
        'nom'         => 'Santé Premium',
        'icon'        => 'bi-heart-pulse',
        'color'       => 'sante',
        'prix_mensuel'=> 120,
        'prix_annuel' => 1350,
        'type'        => 'Couverture médicale',
        'duree'       => '12 mois',
        'ref'         => 'POL-0002',
        'description' => 'Couverture médicale élargie hospitalisation, consultations et médicaments.',
    ],
    3 => [
        'nom'         => 'Habitation Eco',
        'icon'        => 'bi-house-check',
        'color'       => 'maison',
        'prix_mensuel'=> 45,
        'prix_annuel' => 500,
        'type'        => 'Protection habitation',
        'duree'       => '12 mois',
        'ref'         => 'POL-0003',
        'description' => 'Protection complète de votre logement à un tarif accessible et transparent.',
    ],
    4 => [
        'nom'         => 'Vie Sérénité',
        'icon'        => 'bi-shield-heart',
        'color'       => 'vie',
        'prix_mensuel'=> 25,
        'prix_annuel' => 290,
        'type'        => 'Assurance vie',
        'duree'       => '24 mois',
        'ref'         => 'POL-0004',
        'description' => 'Assurez l\'avenir de vos proches avec capital décès et invalidité.',
    ],
];

$offreId = isset($_GET['offre']) && isset($offres[$_GET['offre']])
    ? (int)$_GET['offre'] : 1;
$offre = $offres[$offreId];

/* ── Validation POST ── */
$errors  = [];
$success = [];
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old = array_map(
        fn($v) => htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8'),
        $_POST
    );

    $periodicite = $_POST['periodicite'] ?? 'mensuel';
    $montant     = ($periodicite === 'annuel')
        ? $offre['prix_annuel']
        : $offre['prix_mensuel'];

    /* Champs obligatoires */
    $required = [
        'fullname'   => 'Nom complet',
        'email'      => 'Adresse e-mail',
        'phone'      => 'Téléphone',
        'cardnumber' => 'Numéro de carte',
        'cardholder' => 'Titulaire',
        'expiry'     => 'Expiration',
        'cvv'        => 'CVV',
        'address'    => 'Adresse de facturation',
    ];

    foreach ($required as $f => $l) {
        if (empty(trim($_POST[$f] ?? '')))
            $errors[$f] = "Le champ « {$l} » est obligatoire.";
    }

    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Adresse e-mail invalide.';

    if (!empty($_POST['cardnumber'])) {
        $card = preg_replace('/\s+/', '', $_POST['cardnumber']);
        if (!preg_match('/^\d{16}$/', $card))
            $errors['cardnumber'] = 'Le numéro de carte doit contenir 16 chiffres.';
    }

    if (!empty($_POST['expiry'])) {
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $_POST['expiry']))
            $errors['expiry'] = 'Format attendu : MM/AA.';
        else {
            [$m, $y] = explode('/', $_POST['expiry']);
            if (DateTime::createFromFormat('m/y', "{$m}/{$y}") < new DateTime())
                $errors['expiry'] = 'Cette carte est expirée.';
        }
    }

    if (!empty($_POST['cvv']) && !preg_match('/^\d{3,4}$/', $_POST['cvv']))
        $errors['cvv'] = 'Le CVV doit contenir 3 ou 4 chiffres.';

    if (!empty($_POST['phone'])) {
        $phone = preg_replace('/[\s\+\-\(\)]/', '', $_POST['phone']);
        if (!preg_match('/^\d{8,15}$/', $phone))
            $errors['phone'] = 'Numéro de téléphone invalide.';
    }

    if (empty($errors)) {
        $card_masque = '**** **** **** ' . substr(preg_replace('/\s+/', '', $_POST['cardnumber']), -4);
        $success = [
            'reference'  => 'PTX-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'offre'      => $offre['nom'],
            'montant'    => $montant . ' TND / ' . $periodicite,
            'date'       => date('d/m/Y à H:i'),
            'titulaire'  => htmlspecialchars(trim($_POST['fullname'])),
            'carte'      => $card_masque,
            'echeance'   => ($periodicite === 'annuel')
                            ? date('d/m/Y', strtotime('+1 year'))
                            : date('d/m/Y', strtotime('+1 month')),
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Paiement — Protex</title>
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

        /* ── Hero paiement ── */
        .pay-hero {
            background:
                radial-gradient(ellipse at top right,  rgba(0,180,216,.16), transparent 50%),
                radial-gradient(ellipse at bottom left, rgba(255,140,0,.10), transparent 50%),
                rgba(255,255,255,.03);
            border:1px solid var(--glass-border);
            border-radius:22px; padding:28px 36px;
            margin-bottom:28px;
            display:flex; align-items:center;
            justify-content:space-between; gap:20px; flex-wrap:wrap;
            animation:fadeUp .4s ease both;
        }
        .pay-hero-title { font-family:var(--font-display); font-size:24px; font-weight:800; color:#fff; margin-bottom:8px; }
        .pay-hero-sub   { font-size:13px; color:var(--text-secondary); line-height:1.65; max-width:600px; }
        .pay-pills { display:flex; gap:10px; flex-wrap:wrap; margin-top:14px; }
        .pay-pill  { display:inline-flex; align-items:center; gap:7px; padding:7px 13px; border-radius:999px; border:1px solid var(--glass-border); background:rgba(255,255,255,.05); color:var(--text-primary); font-size:12px; font-weight:500; }
        .pay-pill i { color:var(--success); }

        /* ── Periodicité toggle ── */
        .period-toggle {
            display:flex; gap:10px; margin-bottom:24px;
        }
        .period-btn {
            flex:1; padding:14px; border-radius:16px;
            border:1px solid var(--glass-border);
            background:rgba(255,255,255,.04);
            color:var(--text-secondary); font-size:13px; font-weight:600;
            cursor:pointer; transition:all .2s ease; text-align:center;
        }
        .period-btn:hover { border-color:rgba(0,180,216,.3); color:var(--text-primary); }
        .period-btn.active {
            border-color:var(--accent); background:rgba(0,180,216,.1);
            color:#fff;
        }
        .period-btn .period-price { font-size:20px; font-weight:900; color:#fff; display:block; margin-top:4px; }
        .period-btn .period-save  { font-size:11px; color:var(--accent); display:block; margin-top:3px; }

        /* ── Grid layout ── */
        .pay-grid {
            display:grid;
            grid-template-columns:1fr 1.4fr;
            gap:24px;
            animation:fadeUp .4s .1s ease both;
        }

        /* ── Panel ── */
        .pay-panel {
            background:linear-gradient(180deg,rgba(255,255,255,.05),rgba(255,255,255,.02)),rgba(8,18,38,.95);
            border:1px solid var(--glass-border);
            border-radius:20px; overflow:hidden;
        }
        .pay-panel-head {
            padding:20px 24px 14px;
            border-bottom:1px solid var(--glass-border);
        }
        .pay-panel-title { font-family:var(--font-display); font-size:17px; font-weight:700; color:#fff; margin-bottom:4px; }
        .pay-panel-sub   { font-size:12px; color:var(--text-secondary); }
        .pay-panel-body  { padding:24px; }

        /* ── Résumé offre ── */
        .summary-top {
            background:
                radial-gradient(circle at top right, rgba(0,180,216,.12), transparent 40%),
                rgba(255,255,255,.03);
            border:1px solid var(--glass-border);
            border-radius:16px; padding:18px;
            margin-bottom:18px;
        }
        .summary-tag { display:inline-flex; align-items:center; gap:7px; padding:5px 11px; border-radius:999px; border:1px solid var(--glass-border); background:rgba(255,255,255,.05); font-size:11px; font-weight:700; color:var(--text-primary); margin-bottom:12px; }
        .summary-icon { width:48px; height:48px; border-radius:13px; display:grid; place-items:center; font-size:22px; color:#fff; margin-bottom:10px; }
        .summary-icon.auto      { background:linear-gradient(135deg,rgba(56,109,255,.4),rgba(56,109,255,.15)); }
        .summary-icon.sante     { background:linear-gradient(135deg,rgba(34,197,94,.4), rgba(34,197,94,.15)); }
        .summary-icon.maison    { background:linear-gradient(135deg,rgba(245,158,11,.4),rgba(245,158,11,.15)); }
        .summary-icon.vie       { background:linear-gradient(135deg,rgba(168,85,247,.4),rgba(168,85,247,.15)); }
        .summary-name { font-family:var(--font-display); font-size:20px; font-weight:800; color:#fff; margin-bottom:5px; }
        .summary-desc { color:var(--text-secondary); font-size:13px; line-height:1.6; }
        .summary-amount { margin-top:14px; display:flex; align-items:baseline; gap:6px; }
        .summary-amount strong { font-family:var(--font-display); font-size:32px; font-weight:900; color:#fff; }
        .summary-amount span   { font-size:13px; color:var(--text-secondary); }

        .summary-rows { display:grid; gap:10px; margin-top:16px; }
        .summary-row  { display:flex; justify-content:space-between; align-items:center; padding:11px 14px; border-radius:12px; border:1px solid var(--glass-border); background:rgba(255,255,255,.02); }
        .summary-row span:first-child { font-size:12px; color:var(--text-secondary); }
        .summary-row span:last-child  { font-size:13px; font-weight:700; color:var(--text-primary); }

        .summary-note { margin-top:14px; padding:12px 14px; border-radius:12px; border:1px solid rgba(0,180,216,.2); background:rgba(0,180,216,.06); display:flex; gap:10px; align-items:flex-start; font-size:12px; color:var(--text-primary); }
        .summary-note i { color:var(--accent); font-size:15px; margin-top:1px; flex-shrink:0; }

        /* ── Méthodes paiement ── */
        .method-row { display:flex; gap:8px; margin-bottom:20px; }
        .method-card { flex:1; padding:12px; border-radius:14px; border:1px solid var(--glass-border); background:rgba(255,255,255,.03); display:flex; align-items:center; justify-content:center; gap:7px; font-size:13px; font-weight:600; color:var(--text-secondary); cursor:pointer; transition:all .2s ease; }
        .method-card:hover, .method-card.active { border-color:var(--accent); background:rgba(0,180,216,.1); color:#fff; }
        .method-card i { font-size:17px; }

        /* ── Formulaire ── */
        .pay-form { display:grid; gap:14px; }
        .form-group { display:grid; gap:5px; }
        .form-label { font-size:12px; color:var(--text-secondary); font-weight:600; letter-spacing:.3px; }
        .form-input, .form-select {
            width:100%; padding:13px 16px;
            border-radius:13px; border:1px solid var(--glass-border);
            background:rgba(255,255,255,.04); color:var(--text-primary);
            font-family:var(--font-body); font-size:14px; outline:none;
            transition:border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .form-input::placeholder { color:var(--text-secondary); }
        .form-input:focus, .form-select:focus {
            border-color:rgba(0,180,216,.5);
            box-shadow:0 0 0 3px rgba(0,180,216,.1);
            transform:translateY(-1px);
        }
        .form-input.error { border-color:var(--danger); box-shadow:0 0 0 3px rgba(230,57,70,.1); }
        .field-error { font-size:11px; color:var(--danger); display:flex; align-items:center; gap:5px; margin-top:3px; }
        .form-select option { background:var(--navy-mid); color:var(--text-primary); }
        .two-cols   { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; }
        .three-cols { display:grid; grid-template-columns:1.3fr .85fr .85fr; gap:14px; }

        /* ── Alerte erreurs ── */
        .alert-error {
            background:rgba(230,57,70,.08); border:1px solid rgba(230,57,70,.22);
            border-radius:14px; padding:14px 18px;
            display:flex; align-items:flex-start; gap:12px; margin-bottom:20px;
            animation:fadeUp .3s ease both;
        }
        .alert-error > i { color:var(--danger); font-size:18px; flex-shrink:0; margin-top:2px; }
        .alert-error ul  { margin:6px 0 0; padding-left:16px; color:var(--text-secondary); font-size:13px; line-height:1.8; }
        .alert-error strong { color:var(--danger); font-size:13px; }

        /* ── Trust cards ── */
        .trust-row { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-top:24px; animation:fadeUp .4s .2s ease both; }
        .trust-card { background:rgba(255,255,255,.03); border:1px solid var(--glass-border); border-radius:18px; padding:18px; }
        .trust-card i  { font-size:22px; color:var(--success); display:block; margin-bottom:10px; }
        .trust-card h4 { font-family:var(--font-display); font-size:14px; font-weight:600; color:#fff; margin-bottom:6px; }
        .trust-card p  { font-size:12px; color:var(--text-secondary); line-height:1.6; margin:0; }

        /* ── Page succès ── */
        .success-wrap {
            background:
                radial-gradient(ellipse at top left,    rgba(46,196,182,.14), transparent 45%),
                radial-gradient(ellipse at bottom right, rgba(0,180,216,.10),  transparent 45%),
                rgba(255,255,255,.03);
            border:1px solid rgba(46,196,182,.25);
            border-radius:24px; padding:52px 40px;
            text-align:center; margin-bottom:24px;
            animation:fadeUp .5s ease both;
        }
        .success-icon  { font-size:72px; color:var(--success); display:block; margin-bottom:20px; }
        .success-title { font-family:var(--font-display); font-size:30px; font-weight:800; color:#fff; margin-bottom:10px; }
        .success-sub   { font-size:14px; color:var(--text-secondary); line-height:1.7; max-width:520px; margin:0 auto 30px; }
        .success-ref   { display:inline-block; background:rgba(0,180,216,.1); border:1px solid rgba(0,180,216,.25); color:var(--accent); font-family:monospace; font-size:16px; font-weight:700; padding:10px 24px; border-radius:10px; letter-spacing:1px; margin-bottom:30px; }
        .success-grid  { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; max-width:480px; margin:0 auto 32px; text-align:left; }
        .success-item  { padding:13px 15px; border-radius:14px; border:1px solid var(--glass-border); background:rgba(255,255,255,.04); }
        .success-item .label { font-size:11px; color:var(--text-secondary); margin-bottom:4px; text-transform:uppercase; letter-spacing:.4px; }
        .success-item .value { font-size:13px; font-weight:700; color:var(--text-primary); }
        .success-actions { display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }

        /* ── Responsive ── */
        @media (max-width:1000px) { .pay-grid { grid-template-columns:1fr; } .trust-row { grid-template-columns:1fr; } }
        @media (max-width:640px)  { .two-cols,.three-cols { grid-template-columns:1fr; } .pay-hero-title { font-size:20px; } .success-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">

    <!-- ═══ NAVBAR — identique à client.html ═══ -->
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
            <a class="nav-link active" href="paiement.php">
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
            <a class="nav-link" href="offres.php">
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

    <!-- ═══ MAIN ═══ -->
    <main class="main">

        <div class="page-header">
            <div>
                <div class="page-title-main">Paiement sécurisé</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.html" style="color:inherit;text-decoration:none;">Accueil</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <a href="offres.php" style="color:inherit;text-decoration:none;">Nos offres</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <span>Paiement</span>
                </div>
            </div>
            <a href="offres.php" class="btn btn-outline btn-sm">
                <i class="bi bi-arrow-left"></i> Retour aux offres
            </a>
        </div>

        <div class="content">

<?php if (!empty($success)): ?>

            <!-- ═══ PAGE SUCCÈS ═══ -->
            <section class="success-wrap">
                <span class="success-icon"><i class="bi bi-patch-check-fill"></i></span>
                <div class="success-title">Paiement confirmé !</div>
                <div class="success-sub">
                    Votre souscription a bien été enregistrée.
                    Un e-mail de confirmation vous sera envoyé prochainement.
                    Votre contrat est désormais actif.
                </div>
                <div class="success-ref"><?= $success['reference'] ?></div>
                <div class="success-grid">
                    <div class="success-item">
                        <div class="label">Offre souscrite</div>
                        <div class="value"><?= $success['offre'] ?></div>
                    </div>
                    <div class="success-item">
                        <div class="label">Montant</div>
                        <div class="value"><?= $success['montant'] ?></div>
                    </div>
                    <div class="success-item">
                        <div class="label">Date</div>
                        <div class="value"><?= $success['date'] ?></div>
                    </div>
                    <div class="success-item">
                        <div class="label">Prochaine échéance</div>
                        <div class="value"><?= $success['echeance'] ?></div>
                    </div>
                    <div class="success-item">
                        <div class="label">Titulaire</div>
                        <div class="value"><?= $success['titulaire'] ?></div>
                    </div>
                    <div class="success-item">
                        <div class="label">Statut</div>
                        <div class="value" style="color:var(--success)">
                            <i class="bi bi-check-circle-fill"></i> Validé
                        </div>
                    </div>
                </div>
                <div class="success-actions">
                    <a href="client.html" class="btn btn-primary btn-lg">
                        <i class="bi bi-grid-1x2"></i> Tableau de bord
                    </a>
                    <a href="offres.php" class="btn btn-outline btn-lg">
                        <i class="bi bi-stars"></i> Voir d'autres offres
                    </a>
                </div>
            </section>

<?php else: ?>

            <!-- ═══ HERO ═══ -->
            <section class="pay-hero">
                <div>
                    <div class="pay-hero-title">Finalisez votre souscription</div>
                    <div class="pay-hero-sub">
                        Vérifiez le résumé de votre formule et complétez le formulaire
                        de paiement dans une interface sécurisée et professionnelle.
                    </div>
                    <div class="pay-pills">
                        <span class="pay-pill"><i class="bi bi-shield-lock"></i> Paiement protégé</span>
                        <span class="pay-pill"><i class="bi bi-patch-check"></i> Données sécurisées</span>
                        <span class="pay-pill"><i class="bi bi-lightning-charge"></i> Validation rapide</span>
                    </div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                    <?php foreach([1,2,3,4] as $id): ?>
                    <a href="paiement.php?offre=<?= $id ?>"
                       class="btn <?= $id===$offreId ? 'btn-primary' : 'btn-outline' ?> btn-sm"
                       style="font-size:12px;">
                        <?= $offres[$id]['nom'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Alerte erreurs -->
            <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Veuillez corriger les erreurs suivantes :</strong>
                    <ul>
                        <?php foreach ($errors as $e): ?>
                        <li><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- ═══ GRID ═══ -->
            <div class="pay-grid">

                <!-- ── Résumé offre ── -->
                <div class="pay-panel">
                    <div class="pay-panel-head">
                        <div class="pay-panel-title">Résumé de l'offre</div>
                        <div class="pay-panel-sub">Détails avant confirmation finale</div>
                    </div>
                    <div class="pay-panel-body">

                        <div class="summary-top">
                            <div class="summary-tag"><i class="bi bi-stars"></i> Offre sélectionnée</div>
                            <div class="summary-icon <?= $offre['color'] ?>">
                                <i class="bi <?= $offre['icon'] ?>"></i>
                            </div>
                            <div class="summary-name"><?= htmlspecialchars($offre['nom']) ?></div>
                            <div class="summary-desc"><?= htmlspecialchars($offre['description']) ?></div>
                            <div class="summary-amount" id="displayAmount">
                                <strong><?= $offre['prix_mensuel'] ?></strong>
                                <span>TND / mois</span>
                            </div>
                        </div>

                        <div class="summary-rows">
                            <div class="summary-row">
                                <span>Référence</span>
                                <span><?= $offre['ref'] ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Type</span>
                                <span><?= htmlspecialchars($offre['type']) ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Durée minimale</span>
                                <span><?= $offre['duree'] ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Statut</span>
                                <span style="color:var(--warning)">
                                    <i class="bi bi-hourglass-split"></i> En attente
                                </span>
                            </div>
                            <div class="summary-row">
                                <span>Gestion</span>
                                <span>100% digitale</span>
                            </div>
                        </div>

                        <div class="summary-note">
                            <i class="bi bi-info-circle-fill"></i>
                            <div>Après validation, votre contrat sera actif et visible dans votre espace client.</div>
                        </div>

                    </div>
                </div>

                <!-- ── Formulaire paiement ── -->
                <div class="pay-panel">
                    <div class="pay-panel-head">
                        <div class="pay-panel-title">Formulaire de paiement</div>
                        <div class="pay-panel-sub">Tous les champs * sont obligatoires</div>
                    </div>
                    <div class="pay-panel-body">

                        <!-- Périodicité -->
                        <div class="period-toggle">
                            <div class="period-btn active" id="btn-mensuel" onclick="setPeriod('mensuel')">
                                <i class="bi bi-calendar3"></i> Mensuel
                                <span class="period-price"><?= $offre['prix_mensuel'] ?> TND</span>
                                <span class="period-save">par mois</span>
                            </div>
                            <div class="period-btn" id="btn-annuel" onclick="setPeriod('annuel')">
                                <i class="bi bi-calendar-check"></i> Annuel
                                <span class="period-price"><?= $offre['prix_annuel'] ?> TND</span>
                                <span class="period-save">économisez <?= ($offre['prix_mensuel']*12 - $offre['prix_annuel']) ?> TND</span>
                            </div>
                        </div>

                        <!-- Méthodes -->
                        <div class="method-row">
                            <div class="method-card active" onclick="setMethod(this,'carte')">
                                <i class="bi bi-credit-card-2-front"></i> Carte
                            </div>
                            <div class="method-card" onclick="setMethod(this,'virement')">
                                <i class="bi bi-bank"></i> Virement
                            </div>
                            <div class="method-card" onclick="setMethod(this,'mobile')">
                                <i class="bi bi-phone"></i> Mobile
                            </div>
                        </div>

                        <form class="pay-form"
                              method="post"
                              action="paiement.php?offre=<?= $offreId ?>"
                              novalidate>

                            <input type="hidden" name="periodicite" id="inputPeriodicite" value="mensuel">
                            <input type="hidden" name="methode"     id="inputMethode"     value="carte">

                            <!-- Nom -->
                            <div class="form-group">
                                <label class="form-label">Nom complet *</label>
                                <input class="form-input <?= isset($errors['fullname'])?'error':'' ?>"
                                       type="text" name="fullname"
                                       placeholder="Votre nom complet"
                                       value="<?= $old['fullname'] ?? '' ?>">
                                <?php if(isset($errors['fullname'])): ?>
                                <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= $errors['fullname'] ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Email + Téléphone -->
                            <div class="two-cols">
                                <div class="form-group">
                                    <label class="form-label">E-mail *</label>
                                    <input class="form-input <?= isset($errors['email'])?'error':'' ?>"
                                           type="email" name="email"
                                           placeholder="exemple@email.com"
                                           value="<?= $old['email'] ?? '' ?>">
                                    <?php if(isset($errors['email'])): ?>
                                    <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= $errors['email'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Téléphone *</label>
                                    <input class="form-input <?= isset($errors['phone'])?'error':'' ?>"
                                           type="tel" name="phone"
                                           placeholder="+216 XX XXX XXX"
                                           value="<?= $old['phone'] ?? '' ?>">
                                    <?php if(isset($errors['phone'])): ?>
                                    <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= $errors['phone'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Numéro carte -->
                            <div class="form-group" id="card-fields">
                                <label class="form-label">Numéro de carte *</label>
                                <input class="form-input <?= isset($errors['cardnumber'])?'error':'' ?>"
                                       type="text" name="cardnumber" id="cardnumber"
                                       placeholder="1234 5678 9012 3456" maxlength="19"
                                       value="<?= $old['cardnumber'] ?? '' ?>">
                                <?php if(isset($errors['cardnumber'])): ?>
                                <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= $errors['cardnumber'] ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Titulaire + Expiry + CVV -->
                            <div class="three-cols">
                                <div class="form-group">
                                    <label class="form-label">Titulaire *</label>
                                    <input class="form-input <?= isset($errors['cardholder'])?'error':'' ?>"
                                           type="text" name="cardholder"
                                           placeholder="Nom sur la carte"
                                           value="<?= $old['cardholder'] ?? '' ?>">
                                    <?php if(isset($errors['cardholder'])): ?>
                                    <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= $errors['cardholder'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Expiration *</label>
                                    <input class="form-input <?= isset($errors['expiry'])?'error':'' ?>"
                                           type="text" name="expiry" id="expiry"
                                           placeholder="MM/AA" maxlength="5"
                                           value="<?= $old['expiry'] ?? '' ?>">
                                    <?php if(isset($errors['expiry'])): ?>
                                    <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= $errors['expiry'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">CVV *</label>
                                    <input class="form-input <?= isset($errors['cvv'])?'error':'' ?>"
                                           type="text" name="cvv"
                                           placeholder="123" maxlength="4"
                                           value="<?= $old['cvv'] ?? '' ?>">
                                    <?php if(isset($errors['cvv'])): ?>
                                    <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= $errors['cvv'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Adresse -->
                            <div class="form-group">
                                <label class="form-label">Adresse de facturation *</label>
                                <input class="form-input <?= isset($errors['address'])?'error':'' ?>"
                                       type="text" name="address"
                                       placeholder="Votre adresse complète"
                                       value="<?= $old['address'] ?? '' ?>">
                                <?php if(isset($errors['address'])): ?>
                                <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= $errors['address'] ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Boutons -->
                            <div style="display:flex;gap:10px;margin-top:4px;flex-wrap:wrap;">
                                <button type="submit" class="btn btn-primary btn-lg" style="flex:1;justify-content:center;">
                                    <i class="bi bi-credit-card"></i>
                                    Payer — <span id="btnAmount"><?= $offre['prix_mensuel'] ?> TND</span>
                                </button>
                                <a href="offres.php" class="btn btn-outline">
                                    <i class="bi bi-arrow-left"></i> Changer
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>

            <!-- Trust -->
            <div class="trust-row">
                <div class="trust-card">
                    <i class="bi bi-lock-fill"></i>
                    <h4>Sécurité maximale</h4>
                    <p>Vos données de paiement sont chiffrées. Le numéro de carte n'est jamais stocké en clair.</p>
                </div>
                <div class="trust-card">
                    <i class="bi bi-speedometer2"></i>
                    <h4>Validation rapide</h4>
                    <p>Le processus est optimisé pour être rapide, simple et sans friction pour vous.</p>
                </div>
                <div class="trust-card">
                    <i class="bi bi-check2-square"></i>
                    <h4>Transparence totale</h4>
                    <p>Le montant et les détails de votre offre sont toujours visibles avant de confirmer.</p>
                </div>
            </div>

<?php endif; ?>

        </div>
    </main>
</div>

<script src="assets/js/main.js"></script>
<script>
    /* Avatar dropdown */
    const avatarBtn = document.getElementById('avatarBtn');
    const avatarDropdown = document.getElementById('avatarDropdown');
    if (avatarBtn && avatarDropdown) {
        avatarBtn.addEventListener('click', e => { e.stopPropagation(); avatarDropdown.classList.toggle('open'); });
        document.addEventListener('click', () => avatarDropdown.classList.remove('open'));
    }

    /* Format carte 4×4 */
    document.getElementById('cardnumber')?.addEventListener('input', function() {
        let v = this.value.replace(/\D/g,'').substring(0,16);
        this.value = v.replace(/(.{4})/g,'$1 ').trim();
    });

    /* Format expiry MM/AA */
    document.getElementById('expiry')?.addEventListener('input', function() {
        let v = this.value.replace(/\D/g,'').substring(0,4);
        if (v.length >= 3) v = v.substring(0,2)+'/'+v.substring(2);
        this.value = v;
    });

    /* Périodicité */
    const prixMensuel = <?= $offre['prix_mensuel'] ?>;
    const prixAnnuel  = <?= $offre['prix_annuel'] ?>;

    function setPeriod(p) {
        document.getElementById('inputPeriodicite').value = p;
        document.getElementById('btn-mensuel').classList.toggle('active', p==='mensuel');
        document.getElementById('btn-annuel').classList.toggle('active',  p==='annuel');
        const montant = p==='annuel' ? prixAnnuel : prixMensuel;
        const periode = p==='annuel' ? 'an' : 'mois';
        document.getElementById('btnAmount').textContent    = montant + ' TND';
        document.getElementById('displayAmount').innerHTML  =
            '<strong>'+montant+'</strong><span>TND / '+periode+'</span>';
    }

    /* Méthode paiement */
    function setMethod(el, m) {
        document.querySelectorAll('.method-card').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('inputMethode').value = m;
        const cf = document.getElementById('card-fields');
        if (cf) cf.style.display = (m === 'carte') ? '' : 'none';
    }
</script>
</body>
</html>