<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$db = config::getConnexion();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: contrat.php?error=id');
    exit();
}

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function labelize($key) {
    return mb_strtoupper(str_replace('_', ' ', (string)$key), 'UTF-8');
}

$stmt = $db->prepare("SELECT c.*, cat.nom_categorie, f.nom_formule
                      FROM contrat c
                      LEFT JOIN categorie cat ON c.id_categorie = cat.id_categorie
                      LEFT JOIN formule f ON c.id_formule = f.id_formule
                      WHERE c.id_contrat = :id
                      LIMIT 1");
$stmt->execute(['id' => $id]);
$contrat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contrat) {
    header('Location: contrat.php?error=introuvable');
    exit();
}

$details = [];
if (!empty($contrat['details_contrat'])) {
    $decoded = json_decode($contrat['details_contrat'], true);
    if (is_array($decoded)) {
        $details = $decoded;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newDetails = $_POST['details'] ?? [];
    if (!is_array($newDetails)) {
        $newDetails = [];
    }

    $clean = [];
    foreach ($newDetails as $key => $value) {
        $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$key);
        if ($safeKey === '') continue;

        if (is_array($value)) {
            $items = [];
            foreach ($value as $item) {
                $item = trim((string)$item);
                if ($item !== '') {
                    $items[] = $item;
                }
            }
            $clean[$safeKey] = $items;
        } else {
            $clean[$safeKey] = trim((string)$value);
        }
    }

    $update = $db->prepare("UPDATE contrat
                            SET details_contrat = :details,
                                statut_contrat = 'en attente'
                            WHERE id_contrat = :id");
    $update->execute([
        'details' => json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'id' => $id
    ]);

    header('Location: contratshow.php?id=' . $id . '&success=update');
    exit();
}

$status = strtolower($contrat['statut_contrat'] ?? 'en attente');
$canEdit = in_array($status, ['en attente', 'refusé', 'refuse'], true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier contrat — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <link rel="stylesheet" href="assets/css/contrat.css">
    <style>
        body { background: #f6f8ff; }
        .edit-card { max-width: 1050px; margin: 35px auto; padding: 28px; border-radius: 28px; border: 1px solid rgba(15,31,58,.10); background: rgba(255,255,255,.94); box-shadow: 0 24px 70px rgba(15,31,58,.10); }
        .edit-head { display:flex; justify-content:space-between; align-items:center; gap:18px; margin-bottom:22px; }
        .edit-head h1 { margin:0; font-size:30px; color:#0b1f3a; }
        .edit-head p { margin:6px 0 0; color:#71809a; }
        .status-chip { padding:10px 18px; border-radius:999px; background:#fff0e8; color:#ff5a1f; font-weight:800; }
        .readonly-grid, .details-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
        .details-grid { grid-template-columns:repeat(3,1fr); margin-top:16px; }
        .box { border:1px solid rgba(15,31,58,.10); border-radius:18px; padding:14px 16px; background:#f8faff; }
        .box span, label { display:block; font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:#7a879d; margin-bottom:8px; font-weight:700; }
        .box strong { color:#0b1f3a; font-size:16px; }
        .form-control { width:100%; border:1px solid rgba(15,31,58,.12); border-radius:18px; padding:15px 16px; background:#f8faff; color:#0b1f3a; font-weight:800; outline:none; }
        .form-control:focus { border-color:#ff6b1a; box-shadow:0 0 0 4px rgba(255,107,26,.12); }
        .section-title { margin:26px 0 10px; color:#0b1f3a; font-size:23px; }
        .actions { display:flex; justify-content:flex-end; gap:12px; margin-top:26px; }
        .btn-orange { border:0; border-radius:16px; padding:14px 26px; color:#fff; background:linear-gradient(135deg,#ff7a2f,#ff4b1f); font-weight:900; cursor:pointer; text-decoration:none; }
        .btn-light { border:1px solid rgba(15,31,58,.12); border-radius:16px; padding:14px 26px; color:#0b1f3a; background:#fff; font-weight:900; text-decoration:none; }
        .alert { padding:14px 18px; border-radius:16px; background:#fff7e8; color:#9a5a00; margin-bottom:18px; }
        .error-msg { color:#e53935; font-size:13px; margin-top:6px; }
        .garanties-box { grid-column: 1 / -1; border:1px solid rgba(15,31,58,.10); border-radius:18px; padding:16px; background:#f8faff; }
        .garanties-box span { display:block; font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:#7a879d; margin-bottom:10px; font-weight:800; }
        .garanties-list { display:flex; flex-wrap:wrap; gap:10px; }
        .garantie-pill { display:inline-flex; align-items:center; gap:7px; padding:8px 12px; border-radius:999px; background:rgba(24,160,88,.10); border:1px solid rgba(24,160,88,.25); color:#0b5d36; font-weight:800; font-size:13px; }
        .garantie-pill i { color:#18a058; }
        @media(max-width:900px){ .readonly-grid,.details-grid{grid-template-columns:1fr 1fr;} }
        @media(max-width:600px){ .readonly-grid,.details-grid{grid-template-columns:1fr;} .edit-head{flex-direction:column;align-items:flex-start;} }
    </style>
</head>
<body>
<main>
    <section class="edit-card">
        <div class="edit-head">
            <div>
                <h1>Modifier contrat <?= h($contrat['type_contrat']) ?></h1>
                <p>N° <?= h($contrat['numero_contrat']) ?></p>
            </div>
            <span class="status-chip"><?= h($contrat['statut_contrat']) ?></span>
        </div>

        <?php if (!$canEdit): ?>
            <div class="alert">Ce contrat est déjà traité. La modification client est disponible seulement avant validation ou après refus.</div>
        <?php endif; ?>

        <div class="readonly-grid">
            <div class="box"><span>Catégorie</span><strong><?= h($contrat['nom_categorie'] ?? $contrat['type_contrat']) ?></strong></div>
            <div class="box"><span>Formule</span><strong><?= h($contrat['nom_formule'] ?? $contrat['formule_contrat'] ?? '-') ?></strong></div>
            <div class="box"><span>Date début</span><strong><?= h($contrat['date_debut_contrat']) ?></strong></div>
            <div class="box"><span>Date fin</span><strong><?= h($contrat['date_fin_contrat']) ?></strong></div>
            <div class="box"><span>Prime</span><strong><?= number_format((float)$contrat['prime_contrat'], 2) ?> DT</strong></div>
            <div class="box"><span>Franchise</span><strong><?= number_format((float)$contrat['franchise_contrat'], 2) ?> DT</strong></div>
        </div>

        <h2 class="section-title">Informations du formulaire</h2>
        <form method="POST" novalidate onsubmit="return validateDetails()">
            <div class="details-grid">
                <?php if (!empty($details)): ?>
                    <?php foreach ($details as $key => $value): ?>
                        <?php if ($key === 'garanties' && is_array($value)): ?>
                            <div class="garanties-box">
                                <span>Garanties choisies</span>
                                <div class="garanties-list">
                                    <?php foreach ($value as $index => $garantie): ?>
                                        <span class="garantie-pill"><i class="bi bi-check-circle-fill"></i><?= h($garantie) ?></span>
                                        <input type="hidden" name="details[garanties][<?= (int)$index ?>]" value="<?= h($garantie) ?>">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php elseif (is_array($value)): ?>
                            <div class="garanties-box">
                                <span><?= h(labelize($key)) ?></span>
                                <div class="garanties-list">
                                    <?php foreach ($value as $index => $item): ?>
                                        <span class="garantie-pill"><i class="bi bi-check-circle-fill"></i><?= h($item) ?></span>
                                        <input type="hidden" name="details[<?= h($key) ?>][<?= (int)$index ?>]" value="<?= h($item) ?>">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div>
                                <label for="d_<?= h($key) ?>"><?= h(labelize($key)) ?></label>
                                <input class="form-control detail-input" id="d_<?= h($key) ?>" name="details[<?= h($key) ?>]" value="<?= h($value) ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                                <div class="error-msg"></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="box"><strong>Aucun détail enregistré pour ce contrat.</strong></div>
                <?php endif; ?>
            </div>

            <div class="actions">
                <a href="contrat.php" class="btn-light">Retour</a>
                <?php if ($canEdit): ?>
                    <button type="submit" class="btn-orange">Enregistrer</button>
                <?php endif; ?>
            </div>
        </form>
    </section>
</main>

<script>
function detailTodayISO(){ const d=new Date(); d.setHours(0,0,0,0); return d.toISOString().slice(0,10); }
function detailYearsAgo(y){ const d=new Date(); d.setFullYear(d.getFullYear()-y); d.setHours(0,0,0,0); return d.toISOString().slice(0,10); }
const detailRules = {
    email: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
    letters: /^[A-Za-zÀ-ÖØ-öø-ÿĀ-ſ\u0600-\u06FF]+(?:[ '\-][A-Za-zÀ-ÖØ-öø-ÿĀ-ſ\u0600-\u06FF]+)*$/u,
    address: /^[A-Za-zÀ-ÖØ-öø-ÿĀ-ſ\u0600-\u06FF0-9\s,.'°º\-\/]+$/u,
    immatTN: /^\d{1,4}\s*TUN\s*\d{1,4}$/i,
    immatAr: /^نت\s*\d{1,6}$/u,
    immatForeign: /^(?=.*[A-Za-z\u0600-\u06FF])(?=.*\d)[A-Za-z0-9\u0600-\u06FF\-\s]{3,15}$/u
};
function cleanNumber(v){ return Number(String(v).replace(',', '.').replace(/\s/g,'')); }
function setDetailState(input, msg){
    const err = input.parentElement.querySelector('.error-msg');
    if (err) err.textContent = msg || '';
    input.style.borderColor = msg ? '#e53935' : '#18a058';
    input.style.boxShadow = msg ? '0 0 0 3px rgba(229,57,53,.12)' : '0 0 0 3px rgba(24,160,88,.10)';
    return !msg;
}
function validateOneDetail(input){
    if (input.readOnly) return true;
    const label = (input.parentElement.querySelector('label')?.textContent || 'Champ').trim();
    const key = (input.id + ' ' + input.name + ' ' + label).toLowerCase();
    const value = input.value.trim();
    if (value === '') return setDetailState(input, label + ' obligatoire.');
    if (key.includes('email')) return setDetailState(input, detailRules.email.test(value) ? '' : 'Email invalide.');
    if (key.includes('telephone') || key.includes('téléphone')) return setDetailState(input, /^\d{8}$/.test(value) ? '' : 'Téléphone invalide : exactement 8 chiffres.');
    if (key.includes('nom') || key.includes('prenom') || key.includes('nationalite') || key.includes('nationalité')) return setDetailState(input, detailRules.letters.test(value) && value.length >= 2 ? '' : label + ' doit contenir seulement des lettres.');
    if (key.includes('adresse')) return setDetailState(input, detailRules.address.test(value) && value.length >= 5 ? '' : 'Adresse invalide : lettres, chiffres et ponctuation simple seulement.');
    if (key.includes('immatriculation')) { const compact = value.replace(/\s+/g,''); return setDetailState(input, (detailRules.immatTN.test(value) || detailRules.immatAr.test(compact) || detailRules.immatForeign.test(value)) ? '' : 'Immatriculation invalide. Exemples : 123TUN4567, نت225444, AB-123-CD.'); }
    if (key.includes('puissance')) { const n=cleanNumber(value); return setDetailState(input, Number.isFinite(n) && n>=1 && n<=60 ? '' : 'Puissance invalide : entre 1 et 60 CV.'); }
    if (key.includes('valeur venale') || key.includes('valeur_venale')) { const n=cleanNumber(value); return setDetailState(input, Number.isFinite(n) && n>=1000 && n<=1000000 ? '' : 'Valeur vénale invalide : entre 1 000 et 1 000 000 DT.'); }
    if (key.includes('date circulation') || key.includes('date_circulation')) return setDetailState(input, value <= detailTodayISO() && value >= '1980-01-01' ? '' : 'Date circulation invalide : pas future et pas avant 1980.');
    if (key.includes('date naissance') || key.includes('date_naissance')) return setDetailState(input, value <= detailYearsAgo(18) && value >= detailYearsAgo(100) ? '' : 'Âge invalide : entre 18 et 100 ans.');
    return setDetailState(input, '');
}
function validateDetails(){
    let ok = true, first = null;
    document.querySelectorAll('.detail-input').forEach(input => {
        if (!validateOneDetail(input)) { ok = false; if (!first) first = input; }
    });
    if (!ok && first) first.focus();
    return ok;
}
document.addEventListener('DOMContentLoaded', function(){
    const form = document.querySelector('form[method="POST"]');
    if (form) form.setAttribute('novalidate','novalidate');
    document.querySelectorAll('.detail-input').forEach(input => {
        input.setAttribute('type', input.type === 'email' || input.type === 'number' ? 'text' : input.type);
        input.addEventListener('input', () => validateOneDetail(input));
        input.addEventListener('change', () => validateOneDetail(input));
    });
});
</script>
</body>
</html>
