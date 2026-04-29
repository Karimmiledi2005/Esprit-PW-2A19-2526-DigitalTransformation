<?php
declare(strict_types=1);

/* =============================================
   paiement.php — Protex FrontOffice
   Version professionnelle light mode
   Reliée à confirmer_paiement.php
   ============================================= */

/*
   CORRECTION PROBLEME PRIX :
   - Avant : la page utilisait un tableau PHP fixe $offres[] avec des anciens prix.
   - Maintenant : on récupère l'offre sélectionnée depuis la base avec id_offre.
   - La page accepte aussi l'ancien lien ?offre= pour ne pas casser tes anciens boutons.
*/

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function findProjectConfig(): ?string
{
    $paths = [
        __DIR__ . '/../../config.php',
        __DIR__ . '/../../../config.php',
        __DIR__ . '/config.php',
        dirname(__DIR__, 2) . '/config.php',
        dirname(__DIR__, 3) . '/config.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }

    return null;
}

$configPath = findProjectConfig();
if ($configPath !== null) {
    require_once $configPath;
}

/* Accepte les deux formats : paiement.php?id_offre=5 ET ancien paiement.php?offre=5 */
$offreId = 0;
if (isset($_GET['id_offre'])) {
    $offreId = (int)$_GET['id_offre'];
} elseif (isset($_GET['offre'])) {
    $offreId = (int)$_GET['offre'];
}

/* Fallback local si la base ou config.php n'est pas disponible.
   Tu peux le garder : il évite page blanche pendant les tests. */
$offresFallback = [
    1 => [
        'id_offre'     => 1,
        'nom'          => 'Auto Premium',
        'icon'         => 'bi-car-front',
        'color'        => 'auto',
        'prix_mensuel' => 85,
        'prix_annuel'  => 950,
        'type'         => 'Tous risques',
        'duree'        => '1 mois',
        'ref'          => 'POL-0001',
        'description'  => 'Protection automobile complète avec couverture tous risques et assistance 24/7.',
    ],
    2 => [
        'id_offre'     => 2,
        'nom'          => 'Santé Premium',
        'icon'         => 'bi-heart-pulse',
        'color'        => 'sante',
        'prix_mensuel' => 120,
        'prix_annuel'  => 1350,
        'type'         => 'Couverture médicale',
        'duree'        => '12 mois',
        'ref'          => 'POL-0002',
        'description'  => 'Couverture médicale élargie hospitalisation, consultations et médicaments.',
    ],
    3 => [
        'id_offre'     => 3,
        'nom'          => 'Habitation Eco',
        'icon'         => 'bi-house-check',
        'color'        => 'maison',
        'prix_mensuel' => 45,
        'prix_annuel'  => 500,
        'type'         => 'Protection habitation',
        'duree'        => '12 mois',
        'ref'          => 'POL-0003',
        'description'  => 'Protection complète de votre logement à un tarif accessible et transparent.',
    ],
    4 => [
        'id_offre'     => 4,
        'nom'          => 'Vie Sérénité',
        'icon'         => 'bi-shield-heart',
        'color'        => 'vie',
        'prix_mensuel' => 25,
        'prix_annuel'  => 290,
        'type'         => 'Assurance vie',
        'duree'        => '24 mois',
        'ref'          => 'POL-0004',
        'description'  => 'Assurez l\'avenir de vos proches avec capital décès et invalidité.',
    ],
];

function normalizeOffre(array $row, int $id): array
{
    $prixMensuel = $row['prix_mensuel'] ?? $row['prix'] ?? $row['montant'] ?? 0;
    $prixAnnuel  = $row['prix_annuel'] ?? ((float)$prixMensuel * 12);

    return [
        'id_offre'     => (int)($row['id_offre'] ?? $id),
        'nom'          => (string)($row['nom_offre'] ?? $row['nom'] ?? $row['titre'] ?? 'Offre Protex'),
        'icon'         => 'bi-shield-check',
        'color'        => strtolower((string)($row['type_offre'] ?? $row['type'] ?? 'auto')),
        'prix_mensuel' => (float)$prixMensuel,
        'prix_annuel'  => (float)$prixAnnuel,
        'type'         => (string)($row['type_offre'] ?? $row['type'] ?? 'Assurance'),
        'duree'        => (string)($row['duree_min'] ?? $row['duree'] ?? '12 mois'),
        'ref'          => 'OFF-' . (int)($row['id_offre'] ?? $id),
        'description'  => (string)($row['description'] ?? 'Offre sélectionnée depuis la base de données.'),
    ];
}

$offres = $offresFallback;
$offre = null;
$dbError = '';

if ($offreId <= 0) {
    $offreId = 1;
}

/* Récupération depuis la base : c'est la vraie correction du prix. */
try {
    if (class_exists('config')) {
        $db = config::getConnexion();

        /* Supporte les deux noms possibles : offres OU offre */
        $tableName = 'offres';
        try {
            $test = $db->query("SHOW TABLES LIKE 'offres'");
            if ($test && $test->rowCount() === 0) {
                $tableName = 'offre';
            }
        } catch (Throwable $ignore) {
            $tableName = 'offres';
        }

        $stmt = $db->prepare("SELECT * FROM {$tableName} WHERE id_offre = :id_offre LIMIT 1");
        $stmt->execute([':id_offre' => $offreId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $offre = normalizeOffre($row, $offreId);
        }
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

if ($offre === null) {
    $offre = $offresFallback[$offreId] ?? $offresFallback[1];
    $offreId = (int)$offre['id_offre'];
}

/* Pour les boutons de changement d'offre en haut */
$offres = $offresFallback;
if (!isset($offres[$offreId])) {
    $offres[$offreId] = $offre;
}

$showSuccess = isset($_GET['success']) && $_GET['success'] === '1';
$showError   = isset($_GET['error']) && $_GET['error'] === '1';
$reference   = $_GET['reference'] ?? '';

$old = [
    'fullname'   => e($_GET['fullname']   ?? ''),
    'email'      => e($_GET['email']      ?? ''),
    'phone'      => e($_GET['phone']      ?? ''),
    'cardnumber' => e($_GET['cardnumber'] ?? ''),
    'cardholder' => e($_GET['cardholder'] ?? ''),
    'expiry'     => e($_GET['expiry']     ?? ''),
    'cvv'        => e($_GET['cvv']        ?? ''),
    'address'    => e($_GET['address']    ?? ''),
    'periodicite'=> e($_GET['periodicite'] ?? 'mensuel'),
    'methode'    => e($_GET['methode'] ?? 'carte'),
];

if (!in_array($old['periodicite'], ['mensuel', 'annuel'], true)) {
    $old['periodicite'] = 'mensuel';
}
if (!in_array($old['methode'], ['carte', 'virement', 'mobile'], true)) {
    $old['methode'] = 'carte';
}

$defaultMontant = $old['periodicite'] === 'annuel'
    ? $offre['prix_annuel']
    : $offre['prix_mensuel'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Paiement — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <link rel="stylesheet" href="assets/css/light-theme.css">

    <style>
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }

        @keyframes pulse-green {
            0%,100% { box-shadow: 0 0 0 0 rgba(26,58,122,0.3); }
            50%     { box-shadow: 0 0 0 8px rgba(26,58,122,0); }
        }

        .page-header {
            padding: 24px 32px 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 8px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .page-title-main {
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #15233C;
        }

        .page-breadcrumb {
            font-size: 12px;
            color: rgba(21,35,60,0.5);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .page-breadcrumb span {
            color: #FF6B1A;
            font-weight: 500;
        }

        .pay-hero {
            background: linear-gradient(135deg, #1A3A7A 0%, #0f2456 100%);
            border-radius: 22px;
            padding: 30px 36px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            animation: fadeUp .4s ease both;
            position: relative;
            overflow: hidden;
        }

        .pay-hero::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 220px;
            height: 220px;
            background: rgba(255,107,26,0.12);
            border-radius: 50%;
        }

        .pay-hero::after {
            content: '';
            position: absolute;
            bottom: -60px;
            right: 150px;
            width: 160px;
            height: 160px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        .pay-hero-content {
            position: relative;
            z-index: 1;
        }

        .pay-hero-title {
            font-family: 'Sora', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }

        .pay-hero-sub {
            font-size: 14px;
            color: rgba(255,255,255,0.65);
            line-height: 1.65;
            max-width: 580px;
        }

        .pay-pills {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .pay-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.85);
            font-size: 12px;
            font-weight: 500;
        }

        .pay-pill i { color: #4ade80; }

        .pay-hero-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .pay-offre-btn {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.75);
            background: rgba(255,255,255,0.08);
        }

        .pay-offre-btn:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        .pay-offre-btn.active {
            background: #FF6B1A;
            border-color: #FF6B1A;
            color: #fff;
        }

        .alert-error {
            background: rgba(230,57,70,0.06);
            border: 1px solid rgba(230,57,70,0.20);
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 24px;
            animation: fadeUp .3s ease both;
        }

        .alert-error > i {
            color: #e63946;
            font-size: 18px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .alert-error strong {
            color: #e63946;
            font-size: 13px;
        }

        .alert-error p {
            margin: 6px 0 0;
            color: rgba(21,35,60,0.7);
            font-size: 13px;
            line-height: 1.8;
        }

        .alert-success {
            background: rgba(26,58,122,0.05);
            border: 1px solid rgba(26,58,122,0.14);
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 24px;
            animation: fadeUp .3s ease both;
        }

        .alert-success > i {
            color: #1A3A7A;
            font-size: 18px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .alert-success strong {
            color: #1A3A7A;
            font-size: 13px;
        }

        .alert-success p {
            margin: 6px 0 0;
            color: rgba(21,35,60,0.7);
            font-size: 13px;
            line-height: 1.8;
        }

        .pay-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 24px;
            animation: fadeUp .4s .1s ease both;
            margin-bottom: 28px;
        }

        .pay-panel {
            background: #fff;
            border: 1px solid rgba(26,58,122,0.08);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(26,58,122,0.07);
            transition: box-shadow 0.25s;
        }

        .pay-panel:hover {
            box-shadow: 0 8px 32px rgba(26,58,122,0.10);
        }

        .pay-panel-head {
            padding: 22px 26px 16px;
            border-bottom: 1px solid rgba(26,58,122,0.07);
            background: rgba(26,58,122,0.02);
        }

        .pay-panel-title {
            font-family: 'Sora', sans-serif;
            font-size: 17px;
            font-weight: 700;
            color: #15233C;
            margin-bottom: 3px;
        }

        .pay-panel-sub {
            font-size: 12px;
            color: rgba(21,35,60,0.5);
        }

        .pay-panel-body {
            padding: 24px 26px;
        }

        .summary-top {
            background: linear-gradient(135deg, #f8faff, #fff5f0);
            border: 1px solid rgba(26,58,122,0.08);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 18px;
        }

        .summary-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px;
            border-radius: 999px;
            border: 1px solid rgba(255,107,26,0.2);
            background: rgba(255,107,26,0.08);
            font-size: 11px;
            font-weight: 700;
            color: #FF6B1A;
            margin-bottom: 14px;
        }

        .summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            font-size: 24px;
            color: #fff;
            margin-bottom: 12px;
        }

        .summary-icon.auto   { background: linear-gradient(135deg,#1A3A7A,#2d5cc4); }
        .summary-icon.sante  { background: linear-gradient(135deg,#059669,#34d399); }
        .summary-icon.maison { background: linear-gradient(135deg,#FF6B1A,#ff9a5c); }
        .summary-icon.vie    { background: linear-gradient(135deg,#7c3aed,#a78bfa); }

        .summary-name {
            font-family: 'Sora', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #15233C;
            margin-bottom: 6px;
        }

        .summary-desc {
            color: rgba(21,35,60,0.55);
            font-size: 13px;
            line-height: 1.6;
        }

        .summary-amount {
            margin-top: 16px;
            display: flex;
            align-items: baseline;
            gap: 6px;
            padding: 12px 16px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid rgba(26,58,122,0.08);
        }

        .summary-amount strong {
            font-family: 'Sora', sans-serif;
            font-size: 34px;
            font-weight: 900;
            color: #15233C;
        }

        .summary-amount span {
            font-size: 14px;
            color: rgba(21,35,60,0.5);
        }

        .summary-rows {
            display: grid;
            gap: 8px;
            margin-top: 16px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid rgba(26,58,122,0.07);
            background: rgba(26,58,122,0.02);
            transition: background 0.2s;
        }

        .summary-row:hover {
            background: rgba(26,58,122,0.04);
        }

        .summary-row span:first-child {
            font-size: 12px;
            color: rgba(21,35,60,0.5);
        }

        .summary-row span:last-child {
            font-size: 13px;
            font-weight: 700;
            color: #15233C;
            text-align: right;
        }

        .summary-note {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid rgba(255,107,26,0.15);
            background: rgba(255,107,26,0.04);
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: 12px;
            color: rgba(21,35,60,0.7);
            line-height: 1.6;
        }

        .summary-note i {
            color: #FF6B1A;
            font-size: 15px;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .period-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
        }

        .period-btn {
            flex: 1;
            padding: 16px 14px;
            border-radius: 16px;
            border: 1px solid rgba(26,58,122,0.12);
            background: rgba(26,58,122,0.02);
            color: rgba(21,35,60,0.55);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
            text-align: center;
        }

        .period-btn:hover {
            border-color: rgba(255,107,26,0.3);
            color: #15233C;
        }

        .period-btn.active {
            border-color: #FF6B1A;
            background: rgba(255,107,26,0.06);
            color: #FF6B1A;
        }

        .period-btn .period-price {
            font-family: 'Sora', sans-serif;
            font-size: 22px;
            font-weight: 900;
            color: #15233C;
            display: block;
            margin-top: 5px;
        }

        .period-btn.active .period-price {
            color: #FF6B1A;
        }

        .period-btn .period-save {
            font-size: 11px;
            color: rgba(21,35,60,0.45);
            display: block;
            margin-top: 3px;
        }

        .period-btn.active .period-save {
            color: #FF6B1A;
        }

        .method-row {
            display: flex;
            gap: 8px;
            margin-bottom: 22px;
        }

        .method-card {
            flex: 1;
            padding: 13px 10px;
            border-radius: 13px;
            border: 1px solid rgba(26,58,122,0.12);
            background: rgba(26,58,122,0.02);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(21,35,60,0.55);
            cursor: pointer;
            transition: all .2s ease;
        }

        .method-card:hover {
            border-color: rgba(255,107,26,0.3);
            color: #15233C;
        }

        .method-card.active {
            border-color: #FF6B1A;
            background: rgba(255,107,26,0.06);
            color: #FF6B1A;
        }

        .method-card i {
            font-size: 17px;
        }

        .pay-form {
            display: grid;
            gap: 16px;
        }

        .form-group {
            display: grid;
            gap: 6px;
        }

        .form-label {
            font-size: 12px;
            color: rgba(21,35,60,0.6);
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 13px 16px;
            border-radius: 13px;
            border: 1px solid rgba(26,58,122,0.12);
            background: #fafbff;
            color: #15233C;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: all .2s ease;
            box-sizing: border-box;
        }

        .form-input::placeholder {
            color: rgba(21,35,60,0.35);
        }

        .form-input:focus,
        .form-select:focus {
            border-color: #FF6B1A;
            box-shadow: 0 0 0 3px rgba(255,107,26,0.10);
            background: #fff;
            transform: translateY(-1px);
        }

        .form-select option {
            background: #fff;
            color: #15233C;
        }

        .two-cols {
            display: grid;
            grid-template-columns: repeat(2,1fr);
            gap: 14px;
        }

        .three-cols {
            display: grid;
            grid-template-columns: 1.3fr .85fr .85fr;
            gap: 14px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s;
            text-decoration: none;
            white-space: nowrap;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF6B1A, #e05a0f);
            color: #fff;
            box-shadow: 0 4px 14px rgba(255,107,26,0.25);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #e05a0f, #cc4f00);
            box-shadow: 0 6px 20px rgba(255,107,26,0.35);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: #1A3A7A;
            border: 1px solid rgba(26,58,122,0.20);
        }

        .btn-outline:hover {
            background: rgba(26,58,122,0.06);
            border-color: #1A3A7A;
        }

        .btn-success {
            background: linear-gradient(135deg, #1A3A7A, #2d5cc4);
            color: #fff;
            box-shadow: 0 4px 14px rgba(26,58,122,0.25);
        }

        .btn-success:hover {
            box-shadow: 0 6px 20px rgba(26,58,122,0.35);
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 7px 14px;
            font-size: 12px;
        }

        .btn-lg {
            padding: 14px 28px;
            font-size: 15px;
        }

        .trust-row {
            display: grid;
            grid-template-columns: repeat(3,1fr);
            gap: 16px;
            margin-bottom: 28px;
            animation: fadeUp .4s .2s ease both;
        }

        .trust-card {
            background: #fff;
            border: 1px solid rgba(26,58,122,0.08);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 2px 12px rgba(26,58,122,0.05);
            transition: all 0.25s;
        }

        .trust-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(26,58,122,0.10);
            border-color: rgba(255,107,26,0.2);
        }

        .trust-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 14px;
        }

        .trust-card:nth-child(1) .trust-icon { background: rgba(26,58,122,0.08); color: #1A3A7A; }
        .trust-card:nth-child(2) .trust-icon { background: rgba(255,107,26,0.10); color: #FF6B1A; }
        .trust-card:nth-child(3) .trust-icon { background: rgba(5,150,105,0.10); color: #059669; }

        .trust-card h4 {
            font-family: 'Sora', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #15233C;
            margin-bottom: 8px;
        }

        .trust-card p {
            font-size: 13px;
            color: rgba(21,35,60,0.55);
            line-height: 1.65;
            margin: 0;
        }

        .success-wrap {
            background: linear-gradient(135deg, #f8faff 0%, #fff5f0 100%);
            border: 1px solid rgba(26,58,122,0.10);
            border-radius: 24px;
            padding: 56px 40px;
            text-align: center;
            margin-bottom: 24px;
            animation: fadeUp .5s ease both;
            box-shadow: 0 8px 40px rgba(26,58,122,0.08);
        }

        .success-icon-wrap {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1A3A7A, #2d5cc4);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 28px rgba(26,58,122,0.3);
            animation: pulse-green 2s infinite;
        }

        .success-icon-wrap i {
            font-size: 40px;
            color: #fff;
        }

        .success-title {
            font-family: 'Sora', sans-serif;
            font-size: 32px;
            font-weight: 900;
            color: #15233C;
            margin-bottom: 12px;
        }

        .success-sub {
            font-size: 15px;
            color: rgba(21,35,60,0.6);
            line-height: 1.7;
            max-width: 520px;
            margin: 0 auto 30px;
        }

        .success-ref {
            display: inline-block;
            background: rgba(26,58,122,0.06);
            border: 1px solid rgba(26,58,122,0.15);
            color: #1A3A7A;
            font-family: monospace;
            font-size: 18px;
            font-weight: 700;
            padding: 12px 28px;
            border-radius: 12px;
            letter-spacing: 2px;
            margin-bottom: 32px;
        }

        .success-grid {
            display: grid;
            grid-template-columns: repeat(2,1fr);
            gap: 12px;
            max-width: 500px;
            margin: 0 auto 36px;
            text-align: left;
        }

        .success-item {
            padding: 15px 18px;
            border-radius: 14px;
            border: 1px solid rgba(26,58,122,0.08);
            background: #fff;
            box-shadow: 0 2px 8px rgba(26,58,122,0.04);
        }

        .success-item .label {
            font-size: 10px;
            color: rgba(21,35,60,0.45);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .success-item .value {
            font-size: 14px;
            font-weight: 700;
            color: #15233C;
        }

        .success-actions {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width:1000px) {
            .pay-grid { grid-template-columns: 1fr; }
            .trust-row { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width:768px) {
            .trust-row { grid-template-columns: 1fr; }
            .pay-hero { padding: 22px 20px; }
            .pay-hero-title { font-size: 20px; }
            .page-header { padding: 16px 16px 0; }
        }

        @media (max-width:640px) {
            .two-cols,
            .three-cols {
                grid-template-columns: 1fr;
            }

            .success-grid {
                grid-template-columns: 1fr;
            }

            .pay-panel-body {
                padding: 18px;
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
 <?php include __DIR__ . '/assets/includes/navbar.php'; ?>

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

<?php if ($showSuccess): ?>

            <section class="success-wrap">
                <div class="success-icon-wrap">
                    <i class="bi bi-check-lg"></i>
                </div>
                <div class="success-title">Paiement enregistré !</div>
                <div class="success-sub">
                    Votre demande de paiement a bien été enregistrée.
                    Elle sera visible dans votre espace et traitée par l’administration.
                </div>
                <div class="success-ref"><?= e($reference !== '' ? $reference : 'Référence générée') ?></div>
                <div class="success-grid">
                    <div class="success-item">
                        <div class="label">Offre souscrite</div>
                        <div class="value"><?= e($offre['nom']) ?></div>
                    </div>
                    <div class="success-item">
                        <div class="label">Montant</div>
                        <div class="value" id="successAmount">
                            <?= $old['periodicite'] === 'annuel'
                                ? e((string)$offre['prix_annuel']) . ' TND / annuel'
                                : e((string)$offre['prix_mensuel']) . ' TND / mensuel' ?>
                        </div>
                    </div>
                    <div class="success-item">
                        <div class="label">Date</div>
                        <div class="value"><?= date('d/m/Y à H:i') ?></div>
                    </div>
                    <div class="success-item">
                        <div class="label">Statut</div>
                        <div class="value" style="color:#FF6B1A;">
                            <i class="bi bi-hourglass-split"></i> En attente
                        </div>
                    </div>
                </div>
                <div class="success-actions">
                    <a href="client.html" class="btn btn-success btn-lg">
                        <i class="bi bi-grid-1x2"></i> Tableau de bord
                    </a>
                    <a href="offres.php" class="btn btn-outline btn-lg">
                        <i class="bi bi-stars"></i> Voir d'autres offres
                    </a>
                </div>
            </section>

<?php else: ?>

            <section class="pay-hero">
                <div class="pay-hero-content">
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
                <div class="pay-hero-actions">
                    <?php foreach ([1,2,3,4] as $id): ?>
                        <a href="paiement.php?id_offre=<?= $id ?>"
                           class="pay-offre-btn <?= $id === $offreId ? 'active' : '' ?>">
                            <?= e($offres[$id]['nom']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <?php if ($showError): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Le paiement n’a pas pu être enregistré.</strong>
                        <p>Merci de vérifier les informations saisies puis de réessayer.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="pay-grid">
                <div class="pay-panel">
                    <div class="pay-panel-head">
                        <div class="pay-panel-title">
                            <i class="bi bi-file-earmark-text" style="color:#FF6B1A;margin-right:8px;"></i>
                            Résumé de l'offre
                        </div>
                        <div class="pay-panel-sub">Détails avant confirmation finale</div>
                    </div>
                    <div class="pay-panel-body">
                        <div class="summary-top">
                            <div class="summary-tag">
                                <i class="bi bi-stars"></i> Offre sélectionnée
                            </div>
                            <div class="summary-icon <?= e($offre['color']) ?>">
                                <i class="bi <?= e($offre['icon']) ?>"></i>
                            </div>
                            <div class="summary-name"><?= e($offre['nom']) ?></div>
                            <div class="summary-desc"><?= e($offre['description']) ?></div>
                            <div class="summary-amount" id="displayAmount">
                                <strong><?= $defaultMontant ?></strong>
                                <span>TND / <?= $old['periodicite'] === 'annuel' ? 'an' : 'mois' ?></span>
                            </div>
                        </div>

                        <div class="summary-rows">
                            <div class="summary-row">
                                <span><i class="bi bi-hash" style="margin-right:6px;color:#FF6B1A;"></i>Référence</span>
                                <span><?= e($offre['ref']) ?></span>
                            </div>
                            <div class="summary-row">
                                <span><i class="bi bi-tag" style="margin-right:6px;color:#FF6B1A;"></i>Type</span>
                                <span><?= e($offre['type']) ?></span>
                            </div>
                            <div class="summary-row">
                                <span><i class="bi bi-calendar3" style="margin-right:6px;color:#FF6B1A;"></i>Durée minimale</span>
                                <span><?= e($offre['duree']) ?></span>
                            </div>
                            <div class="summary-row">
                                <span><i class="bi bi-circle-half" style="margin-right:6px;color:#FF6B1A;"></i>Statut</span>
                                <span style="color:#FF6B1A;font-weight:700;">
                                    <i class="bi bi-hourglass-split"></i> En attente
                                </span>
                            </div>
                            <div class="summary-row">
                                <span><i class="bi bi-phone" style="margin-right:6px;color:#FF6B1A;"></i>Gestion</span>
                                <span>100% digitale</span>
                            </div>
                        </div>

                        <div class="summary-note">
                            <i class="bi bi-info-circle-fill"></i>
                            <div>Après soumission, votre paiement sera enregistré et visible dans votre espace d’administration pour traitement.</div>
                        </div>
                    </div>
                </div>

                <div class="pay-panel">
                    <div class="pay-panel-head">
                        <div class="pay-panel-title">
                            <i class="bi bi-credit-card" style="color:#1A3A7A;margin-right:8px;"></i>
                            Formulaire de paiement
                        </div>
                        <div class="pay-panel-sub">Tous les champs * sont obligatoires</div>
                    </div>

                    <div class="pay-panel-body">
                        <div class="period-toggle">
                            <div class="period-btn <?= $old['periodicite'] === 'mensuel' ? 'active' : '' ?>" id="btn-mensuel" onclick="setPeriod('mensuel')">
                                <i class="bi bi-calendar3"></i> Mensuel
                                <span class="period-price"><?= $offre['prix_mensuel'] ?> TND</span>
                                <span class="period-save">par mois</span>
                            </div>
                            <div class="period-btn <?= $old['periodicite'] === 'annuel' ? 'active' : '' ?>" id="btn-annuel" onclick="setPeriod('annuel')">
                                <i class="bi bi-calendar-check"></i> Annuel
                                <span class="period-price"><?= $offre['prix_annuel'] ?> TND</span>
                                <span class="period-save">économisez <?= ($offre['prix_mensuel'] * 12 - $offre['prix_annuel']) ?> TND</span>
                            </div>
                        </div>

                        <div class="method-row">
                            <div class="method-card <?= $old['methode'] === 'carte' ? 'active' : '' ?>" onclick="setMethod(this,'carte')">
                                <i class="bi bi-credit-card-2-front"></i> Carte
                            </div>
                            <div class="method-card <?= $old['methode'] === 'virement' ? 'active' : '' ?>" onclick="setMethod(this,'virement')">
                                <i class="bi bi-bank"></i> Virement
                            </div>
                            <div class="method-card <?= $old['methode'] === 'mobile' ? 'active' : '' ?>" onclick="setMethod(this,'mobile')">
                                <i class="bi bi-phone"></i> Mobile
                            </div>
                        </div>

                        <form class="pay-form" method="post" action="confirmer_paiement.php" novalidate>
                            <input type="hidden" name="id_offre" value="<?= $offreId ?>">
                            <input type="hidden" name="offre_id" value="<?= $offreId ?>">
                            <input type="hidden" name="periodicite" id="inputPeriodicite" value="<?= e($old['periodicite']) ?>">
                            <input type="hidden" name="methode" id="inputMethode" value="<?= e($old['methode']) ?>">
                            <input type="hidden" name="montant" id="hiddenMontant" value="<?= $defaultMontant ?>">

                            <div class="form-group">
                                <label class="form-label">Nom complet *</label>
                                <input class="form-input" type="text" name="fullname" placeholder="Votre nom complet" value="<?= $old['fullname'] ?>">
                            </div>

                            <div class="two-cols">
                                <div class="form-group">
                                    <label class="form-label">E-mail *</label>
                                    <input class="form-input" type="email" name="email" placeholder="exemple@email.com" value="<?= $old['email'] ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Téléphone *</label>
                                    <input class="form-input" type="tel" name="phone" placeholder="+216 XX XXX XXX" value="<?= $old['phone'] ?>">
                                </div>
                            </div>

                            <div class="form-group" id="card-fields">
                                <label class="form-label">Numéro de carte *</label>
                                <input class="form-input" type="text" name="cardnumber" id="cardnumber" placeholder="1234 5678 9012 3456" maxlength="19" value="<?= $old['cardnumber'] ?>">
                            </div>

                            <div class="three-cols">
                                <div class="form-group">
                                    <label class="form-label">Titulaire *</label>
                                    <input class="form-input" type="text" name="cardholder" placeholder="Nom sur la carte" value="<?= $old['cardholder'] ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Expiration *</label>
                                    <input class="form-input" type="text" name="expiry" id="expiry" placeholder="MM/AA" maxlength="5" value="<?= $old['expiry'] ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">CVV *</label>
                                    <input class="form-input" type="text" name="cvv" placeholder="123" maxlength="4" value="<?= $old['cvv'] ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Adresse de facturation *</label>
                                <input class="form-input" type="text" name="address" placeholder="Votre adresse complète" value="<?= $old['address'] ?>">
                            </div>

                            <div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap;">
                                <button type="submit" class="btn btn-primary btn-lg" style="flex:1;">
                                    <i class="bi bi-credit-card"></i>
                                    Payer — <span id="btnAmount"><?= $defaultMontant ?> TND</span>
                                </button>
                                <a href="offres.php" class="btn btn-outline">
                                    <i class="bi bi-arrow-left"></i> Changer
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="trust-row">
                <div class="trust-card">
                    <div class="trust-icon"><i class="bi bi-lock-fill"></i></div>
                    <h4>Sécurité maximale</h4>
                    <p>Vos données de paiement sont chiffrées SSL. Le numéro de carte n'est jamais stocké en clair sur nos serveurs.</p>
                </div>
                <div class="trust-card">
                    <div class="trust-icon"><i class="bi bi-speedometer2"></i></div>
                    <h4>Validation rapide</h4>
                    <p>Le processus est optimisé pour être rapide, simple et sans friction. Votre paiement est enregistré en quelques instants.</p>
                </div>
                <div class="trust-card">
                    <div class="trust-icon"><i class="bi bi-check2-square"></i></div>
                    <h4>Transparence totale</h4>
                    <p>Le montant et les détails de votre offre sont toujours visibles avant de confirmer. Aucune surprise cachée.</p>
                </div>
            </div>

<?php endif; ?>

        </div>
    </main>
</div>

<script src="assets/js/main.js"></script>
<script>
    const avatarBtn = document.getElementById('avatarBtn');
    const avatarDropdown = document.getElementById('avatarDropdown');

    if (avatarBtn && avatarDropdown) {
        avatarBtn.addEventListener('click', e => {
            e.stopPropagation();
            avatarDropdown.classList.toggle('open');
        });

        document.addEventListener('click', () => {
            avatarDropdown.classList.remove('open');
        });
    }

    document.getElementById('cardnumber')?.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '').substring(0, 16);
        this.value = v.replace(/(.{4})/g, '$1 ').trim();
    });

    document.getElementById('expiry')?.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 3) {
            v = v.substring(0, 2) + '/' + v.substring(2);
        }
        this.value = v;
    });

    const prixMensuel = <?= (float)$offre['prix_mensuel'] ?>;
    const prixAnnuel  = <?= (float)$offre['prix_annuel'] ?>;

    function setPeriod(p) {
        document.getElementById('inputPeriodicite').value = p;
        document.getElementById('btn-mensuel').classList.toggle('active', p === 'mensuel');
        document.getElementById('btn-annuel').classList.toggle('active', p === 'annuel');

        const montant = p === 'annuel' ? prixAnnuel : prixMensuel;
        const periode = p === 'annuel' ? 'an' : 'mois';

        document.getElementById('btnAmount').textContent = montant + ' TND';
        document.getElementById('displayAmount').innerHTML =
            '<strong>' + montant + '</strong><span>TND / ' + periode + '</span>';

        document.getElementById('hiddenMontant').value = montant;
    }

    function setMethod(el, m) {
        document.querySelectorAll('.method-card').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('inputMethode').value = m;

        const cf = document.getElementById('card-fields');
        if (cf) {
            cf.style.display = (m === 'carte') ? '' : 'none';
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        setPeriod(document.getElementById('inputPeriodicite').value || 'mensuel');

        const currentMethod = document.getElementById('inputMethode').value || 'carte';
        const methodCard = Array.from(document.querySelectorAll('.method-card')).find(card =>
            card.textContent.toLowerCase().includes(currentMethod)
        );

        if (methodCard) {
            setMethod(methodCard, currentMethod);
        }
    });
</script>
</body>
</html>