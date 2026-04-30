<?php
session_start();
require_once __DIR__ . '/../../controller/ContratController.php';
if (!class_exists('config') && file_exists(__DIR__ . '/../../config/database.php')) {
    require_once __DIR__ . '/../../config/database.php';
}

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function formatDateFr($date){ $t = strtotime((string)$date); return $t ? date('d/m/Y', $t) : h($date); }
function statusClass($statut){
    $s = strtolower(trim((string)$statut));
    return match($s){
        'actif','active' => 'active',
        'en attente','pending' => 'waiting',
        'expiré','expire','résilié','resilie','inactive' => 'expired',
        'refusé','refuse' => 'refused',
        default => 'waiting'
    };
}
function typeIcon($type){
    $t = strtolower(trim((string)$type));
    return match($t){
        'auto' => ['icon'=>'bi-car-front-fill','class'=>'auto'],
        'habitation' => ['icon'=>'bi-house-door-fill','class'=>'habitation'],
        'sante','santé' => ['icon'=>'bi-heart-pulse-fill','class'=>'sante'],
        'protection' => ['icon'=>'bi-shield-check','class'=>'protection'],
        default => ['icon'=>'bi-file-earmark-text','class'=>'default']
    };
}
function labelize($key){ return mb_convert_case(str_replace('_',' ',(string)$key), MB_CASE_TITLE, 'UTF-8'); }
function normTxt($v){ return mb_strtolower(trim((string)$v), 'UTF-8'); }
function getFormuleGaranties($idFormule){
    if (empty($idFormule) || !class_exists('config')) return [];
    try {
        $db = config::getConnexion();
        $sql = "SELECT g.nom_garantie, fg.niveau_couvert_garantie
                FROM formule_garantie fg
                INNER JOIN garantie g ON g.id_garantie = fg.id_garantie
                WHERE fg.id_formule = :id
                ORDER BY g.id_garantie ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => (int)$idFormule]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

$id = (int)($_GET['id'] ?? 0);
$controller = new ContratController();
$contrat = $controller->getById($id);
if (!$contrat) { header('Location: contrat.php?error=introuvable'); exit(); }

$details = [];
if (!empty($contrat['details_contrat'])) {
    $decoded = json_decode($contrat['details_contrat'], true);
    if (is_array($decoded)) $details = $decoded;
}

$selectedGaranties = [];
if (!empty($details['garanties']) && is_array($details['garanties'])) {
    $selectedGaranties = array_map('normTxt', $details['garanties']);
}
$garantiesFormule = getFormuleGaranties($contrat['id_formule'] ?? 0);
$typeData = typeIcon($contrat['type_contrat'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Détail contrat — Protex</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/variables.css">
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/layout.css">
<link rel="stylesheet" href="assets/css/client.css">
<link rel="stylesheet" href="assets/css/contrat.css">
<style>
.show-wrap{max-width:1180px;margin:30px auto;padding:0 22px}.show-card{background:var(--card-bg);border:1px solid var(--border);border-radius:28px;padding:28px;box-shadow:var(--shadow-lg)}.show-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:24px}.show-title{display:flex;align-items:center;gap:16px}.show-icon{width:78px;height:78px;border-radius:22px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:32px;background:linear-gradient(135deg,#ff8a3d,#ff4f1a);box-shadow:0 18px 35px rgba(255,107,26,.25)}.show-icon.sante{background:linear-gradient(135deg,#2ecc71,#17b86a)}.show-icon.habitation{background:linear-gradient(135deg,#f5b21b,#d99000)}.show-icon.protection{background:linear-gradient(135deg,#5578ff,#2f5bff)}.show-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:20px 0}.show-info{border:1px solid var(--border);border-radius:18px;padding:16px;background:rgba(255,255,255,.05)}.show-info span{display:block;color:var(--text-secondary);font-size:13px;margin-bottom:7px}.show-info strong{font-size:16px;color:var(--text-primary)}.details-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:12px}.detail-item{border:1px solid var(--border);border-radius:16px;padding:14px;background:rgba(255,255,255,.04)}.detail-item span{display:block;color:var(--text-secondary);font-size:12px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px}.detail-item strong{color:var(--text-primary);font-size:15px;word-break:break-word}.garanties-box{margin:10px 0 24px;border:1px solid var(--border);border-radius:20px;padding:18px;background:rgba(255,255,255,.05)}.garanties-title{display:flex;align-items:center;gap:8px;margin-bottom:14px;font-weight:900;color:var(--text-primary);font-size:18px}.garanties-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.garantie-line{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:14px;background:rgba(255,255,255,.06);font-weight:800;color:var(--text-primary)}.garantie-line i{font-size:18px}.garantie-line.basique i{color:#16a064}.garantie-line.option-selected i{color:#ff6b1a}.garantie-line.option-off{opacity:.58}.garantie-line.option-off i{color:#8894a8}.garantie-line.no{opacity:.7}.garantie-line.no i{color:#a1aabc}.garantie-line small{font-weight:900;color:#005b3a;margin-left:4px}.actions{display:flex;gap:10px;justify-content:flex-end;margin-top:24px}.empty-note{padding:16px;border:1px dashed var(--border);border-radius:16px;color:var(--text-secondary)}@media(max-width:900px){.show-grid,.details-grid,.garanties-list{grid-template-columns:1fr}.show-head{align-items:flex-start;flex-direction:column}.actions{justify-content:flex-start;flex-wrap:wrap}}
</style>
</head>
<body>
<div class="background"></div><div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div>
<div class="layout">
<nav class="navbar">
<a href="client.html" class="navbar-brand"><img src="logo.png" alt="logo" width="40" height="40"><div><div class="logo-text">Protex</div><div class="logo-sub">Assurance Digitale</div></div></a>
<div class="navbar-nav"><a class="nav-link" href="client.html"><i class="bi bi-grid-1x2"></i><span class="nav-label">Tableau de bord</span></a><a class="nav-link active" href="contrat.php"><i class="bi bi-file-earmark-text"></i><span class="nav-label">Contrats</span></a><a class="nav-link" href="mes-sinistres.html"><i class="bi bi-shield-exclamation"></i><span class="nav-label">Sinistres</span></a><a class="nav-link" href="paiement.html"><i class="bi bi-credit-card"></i><span class="nav-label">Paiements</span></a></div>
</nav>
<main class="main">
<div class="show-wrap"><div class="show-card">
<div class="show-head"><div class="show-title"><div class="show-icon <?= h($typeData['class']) ?>"><i class="bi <?= h($typeData['icon']) ?>"></i></div><div><h1>Contrat <?= h($contrat['type_contrat'] ?? '') ?></h1><p>N° <?= h($contrat['numero_contrat'] ?? '') ?></p></div></div><span class="status-badge <?= h(statusClass($contrat['statut_contrat'] ?? '')) ?>"><?= h($contrat['statut_contrat'] ?? '') ?></span></div>
<div class="show-grid">
<div class="show-info"><span>Catégorie</span><strong><?= h($contrat['nom_categorie'] ?? $contrat['type_contrat'] ?? '-') ?></strong></div>
<div class="show-info"><span>Formule</span><strong><?= h($contrat['nom_formule'] ?? $contrat['formule_contrat'] ?? '-') ?></strong></div>
<div class="show-info"><span>Date début</span><strong><?= formatDateFr($contrat['date_debut_contrat'] ?? '') ?></strong></div>
<div class="show-info"><span>Date fin</span><strong><?= formatDateFr($contrat['date_fin_contrat'] ?? '') ?></strong></div>
<div class="show-info"><span>Prime</span><strong><?= h(number_format((float)($contrat['prime_contrat'] ?? 0), 2)) ?> DT</strong></div>
<div class="show-info"><span>Franchise</span><strong><?= h(number_format((float)($contrat['franchise_contrat'] ?? 0), 2)) ?> DT</strong></div>
<div class="show-info"><span>Client</span><strong><?= h(trim(($contrat['prenom'] ?? '') . ' ' . ($contrat['nom'] ?? '')) ?: ('#' . ($contrat['id_client'] ?? '-'))) ?></strong></div>
<div class="show-info"><span>Email</span><strong><?= h($contrat['email'] ?? '-') ?></strong></div>
</div>

<?php if (!empty($garantiesFormule)): ?>
<div class="garanties-box">
    <div class="garanties-title"><i class="bi bi-shield-check"></i> Garanties de la formule</div>
    <div class="garanties-list">
        <?php foreach ($garantiesFormule as $g):
            $nom = (string)($g['nom_garantie'] ?? '');
            $niveau = normTxt($g['niveau_couvert_garantie'] ?? 'basique');
            $isSelected = in_array(normTxt($nom), $selectedGaranties, true);
            if ($niveau === 'basique') { $class='basique'; $icon='bi-check-circle'; $label='basique'; }
            elseif ($niveau === 'option') { $class=$isSelected ? 'option-selected' : 'option-off'; $icon=$isSelected ? 'bi-plus-circle-fill' : 'bi-circle'; $label=$isSelected ? 'option ajoutée' : 'option non choisie'; }
            else { $class='no'; $icon='bi-x-circle'; $label='non disponible'; }
        ?>
            <div class="garantie-line <?= h($class) ?>"><i class="bi <?= h($icon) ?>"></i><span><?= h($nom) ?> <small>(<?= h($label) ?>)</small></span></div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<h2>Informations saisies dans le formulaire</h2>
<?php if (!empty($details)): ?>
<div class="details-grid">
<?php foreach ($details as $key => $value): ?>
<?php if ($key === 'garanties') continue; ?>
<?php if (is_array($value)) $value = implode(', ', $value); ?>
<div class="detail-item"><span><?= h(labelize($key)) ?></span><strong><?= h($value) ?></strong></div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-note">Aucun détail spécifique enregistré.</div>
<?php endif; ?>
<div class="actions"><a href="contrat.php" class="btn-protex btn-light-protex">Retour</a><a href="contrat_update_client.php?id=<?= urlencode((string)$id) ?>" class="btn-protex btn-primary-protex">Modifier</a></div>
</div></div>
</main>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
