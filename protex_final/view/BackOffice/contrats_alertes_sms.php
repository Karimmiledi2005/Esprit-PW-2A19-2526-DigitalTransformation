<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../controller/ContratController.php';

$controller = new ContratController();
$days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
$days = max(1, min($days, 365));

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $days = isset($_POST['days']) ? (int)$_POST['days'] : 30;
    $days = max(1, min($days, 365));
    $result = $controller->envoyerAlertesSmsExpiration($days);
}

$contrats = $controller->getContratsExpirantBientot($days);
$alerts = $controller->getSmsAlerts();

function h($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function dateFr($date): string
{
    if (!$date) return '—';
    return date('d/m/Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Alertes SMS contrats — Protex</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root{--navy:#0A1931;--blue:#1A3A7A;--cyan:#00b4d8;--orange:#FF6B1A;--soft:#f4f7fb;--text:#10233f;--muted:#718096;--border:#dbe4f0;}
        *{box-sizing:border-box}body{margin:0;font-family:Arial,Helvetica,sans-serif;background:linear-gradient(135deg,#f7fbff,#fff7ef);color:var(--text)}
        .page{max-width:1180px;margin:0 auto;padding:32px}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;gap:16px}.brand{display:flex;align-items:center;gap:12px}.brand img{width:54px;height:54px;object-fit:contain}.brand h1{margin:0;font-size:28px}.brand p{margin:4px 0 0;color:var(--muted);font-weight:600}.btn{border:0;border-radius:14px;padding:13px 18px;font-weight:800;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px}.btn-primary{background:linear-gradient(135deg,var(--cyan),var(--blue));color:white}.btn-orange{background:linear-gradient(135deg,var(--orange),#ff8a3d);color:white}.btn-light{background:white;color:var(--text);border:1px solid var(--border)}
        .cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px}.card{background:white;border:1px solid var(--border);border-radius:22px;padding:22px;box-shadow:0 16px 35px rgba(10,25,49,.08)}.card .value{font-size:32px;font-weight:900;margin-top:12px}.card .label{color:var(--muted);font-weight:700}.card i{color:var(--cyan);font-size:24px}
        .panel{background:white;border:1px solid var(--border);border-radius:24px;box-shadow:0 16px 35px rgba(10,25,49,.08);overflow:hidden;margin-bottom:26px}.panel-head{background:var(--navy);color:white;padding:18px 22px;display:flex;justify-content:space-between;align-items:center;gap:12px}.panel-head h2{margin:0;font-size:20px}.panel-body{padding:20px}.tools{display:flex;gap:12px;align-items:center;flex-wrap:wrap}.tools input{padding:13px 16px;border:1px solid var(--border);border-radius:14px;font-weight:700;min-width:120px}.notice{padding:14px 16px;border-radius:16px;margin-bottom:18px;font-weight:700}.notice.ok{background:#e6fffa;color:#047857}.notice.warn{background:#fff7ed;color:#c2410c}
        table{width:100%;border-collapse:collapse}th,td{padding:14px 12px;border-bottom:1px solid var(--border);text-align:left;font-size:14px}th{color:#526173;text-transform:uppercase;font-size:12px;letter-spacing:.06em}.badge{display:inline-flex;padding:7px 10px;border-radius:999px;font-size:12px;font-weight:800}.badge-green{background:#dcfce7;color:#15803d}.badge-orange{background:#ffedd5;color:#c2410c}.badge-blue{background:#dbeafe;color:#1d4ed8}.message{max-width:420px;color:#44546a}.empty{text-align:center;color:var(--muted);padding:35px;font-weight:700}@media(max-width:900px){.cards{grid-template-columns:1fr 1fr}.top{align-items:flex-start;flex-direction:column}}@media(max-width:600px){.cards{grid-template-columns:1fr}.page{padding:18px}table{font-size:12px}}
    </style>
</head>
<body>
<div class="page">
    <div class="top">
        <div class="brand">
            <img src="../FrontOffice/logo.png" alt="Protex">
            <div>
                <h1>Alertes SMS contrats</h1>
                <p>Détection des contrats actifs proches de l’expiration</p>
            </div>
        </div>
        <a class="btn btn-light" href="contrats_back.php"><i class="bi bi-arrow-left"></i> Retour contrats</a>
    </div>

    <?php if ($result): ?>
        <div class="notice ok">
            Traitement terminé : <?= (int)$result['envoyes'] ?> alerte(s) SMS simulée(s),
            <?= (int)$result['deja_envoyes'] ?> déjà envoyée(s),
            <?= (int)$result['sans_telephone'] ?> sans téléphone.
        </div>
    <?php endif; ?>

    <div class="cards">
        <div class="card"><i class="bi bi-hourglass-split"></i><div class="value"><?= count($contrats) ?></div><div class="label">Contrats à surveiller</div></div>
        <div class="card"><i class="bi bi-chat-dots"></i><div class="value"><?= count($alerts) ?></div><div class="label">SMS simulés session</div></div>
        <div class="card"><i class="bi bi-calendar-event"></i><div class="value"><?= (int)$days ?></div><div class="label">Jours avant expiration</div></div>
        <div class="card"><i class="bi bi-phone-vibrate"></i><div class="value">SMS</div><div class="label">Canal d’alerte</div></div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2><i class="bi bi-bell"></i> Lancer les alertes</h2>
        </div>
        <div class="panel-body">
            <form method="POST" class="tools">
                <label for="days"><strong>Alerter les contrats qui expirent dans</strong></label>
                <input type="number" name="days" id="days" value="<?= (int)$days ?>" min="1" max="365">
                <button class="btn btn-orange" type="submit"><i class="bi bi-send"></i> Envoyer / simuler SMS</button>
                <span class="badge badge-blue">Anti-doublon activé</span>
            </form>
            <div class="notice warn" style="margin-top:16px;margin-bottom:0">
                Mode actuel : simulation sans nouvelle table. Les SMS sont gardés dans <strong>$_SESSION</strong> pendant la session. Pour un vrai envoi, il suffit de remplacer la partie simulation par l’API du fournisseur SMS.
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2><i class="bi bi-list-check"></i> Contrats actifs proches de l’expiration</h2></div>
        <div class="panel-body">
            <?php if (empty($contrats)): ?>
                <div class="empty">Aucun contrat actif proche de l’expiration.</div>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>N° contrat</th><th>Client</th><th>Téléphone</th><th>Catégorie</th><th>Date fin</th><th>Reste</th><th>Statut alerte</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($contrats as $c): ?>
                        <?php $sent = $controller->smsAlertAlreadySent((int)$c['id_contrat']); ?>
                        <tr>
                            <td><strong><?= h($c['numero_contrat']) ?></strong></td>
                            <td><?= h(trim(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? ''))) ?></td>
                            <td><?= h($c['telephone_final'] ?: '—') ?></td>
                            <td><?= h($c['nom_categorie'] ?? '—') ?></td>
                            <td><?= h(dateFr($c['date_fin_contrat'])) ?></td>
                            <td><span class="badge badge-orange"><?= (int)$c['jours_restants'] ?> jour(s)</span></td>
                            <td><?= $sent ? '<span class="badge badge-green">Déjà alerté</span>' : '<span class="badge badge-blue">À alerter</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2><i class="bi bi-clock-history"></i> Historique SMS de la session</h2></div>
        <div class="panel-body">
            <?php if (empty($alerts)): ?>
                <div class="empty">Aucune alerte SMS simulée pendant cette session.</div>
            <?php else: ?>
                <table>
                    <thead>
                    <tr><th>Date</th><th>N° contrat</th><th>Téléphone</th><th>Message</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($alerts as $a): ?>
                        <tr>
                            <td><?= h(date('d/m/Y H:i', strtotime($a['date_envoi']))) ?></td>
                            <td><strong><?= h($a['numero_contrat'] ?? ('#' . $a['id_contrat'])) ?></strong></td>
                            <td><?= h($a['telephone']) ?></td>
                            <td class="message"><?= h($a['message']) ?></td>
                            <td><span class="badge badge-green"><?= h($a['statut']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
