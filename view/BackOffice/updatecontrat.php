<?php
require_once __DIR__ . '/../../controller/ContratController.php';
require_once __DIR__ . '/../../model/Contrat.php';

$id = (int)($_GET['id'] ?? 0);
$contratC = new ContratController();
$contratData = $contratC->getById($id);
$formules = $contratC->getAllFormules();

if (!$contratData) die('Contrat introuvable.');

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function labelDetail($key) {
    $labels = [
        'telephone' => 'N° de téléphone',
        'date_naissance' => 'Date de naissance',
        'nationalite' => 'Nationalité',
        'situation_professionnelle' => 'Situation professionnelle',
        'adresse_personnelle' => 'Adresse personnelle principale',
        'situation_matrimoniale' => 'Situation matrimoniale',
        'revenu_annuel' => 'Niveau de revenu annuel brut en Dinars',
        'immatriculation' => 'Immatriculation du véhicule',
        'marque' => 'Marque du véhicule',
        'usage_vehicule' => 'Usage du véhicule',
        'kilometrage' => 'Kilométrage du véhicule',
        'puissance' => 'Puissance',
        'date_circulation' => 'Date de circulation',
        'valeur_venale' => 'Valeur vénale',
        'financement' => 'Financement',
        'identite' => 'Identité',
        'email' => 'Email',
        'type_logement' => 'Type de logement',
        'statut_occupation' => 'Statut d’occupation',
        'adresse_logement' => 'Adresse du logement',
        'surface' => 'Surface (m²)',
        'nombre_pieces' => 'Nombre de pièces',
        'valeur_bien' => 'Valeur du bien',
        'type_construction' => 'Type de construction',
        'systeme_securite' => 'Système de sécurité',
        'age' => 'Âge',
        'profession' => 'Profession',
        'antecedents' => 'Antécédents médicaux',
        'nombre_personnes' => 'Nombre de personnes à assurer',
        'objet_protege' => 'Objet protégé',
        'valeur_objet' => 'Valeur objet',
        'description' => 'Description'
    ];
    return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
}

function detailValue($value) {
    if (is_array($value)) return implode(', ', array_map('strval', $value));
    return (string)$value;
}

$details = [];
if (!empty($contratData['details_contrat'])) {
    $decoded = json_decode($contratData['details_contrat'], true);
    if (is_array($decoded)) $details = $decoded;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client = (int)($_POST['id_client'] ?? 0);
    $idFormule = (int)($_POST['id_formule'] ?? 0);
    $dateDebut = trim($_POST['date_debut_contrat'] ?? '');
    $dateFin = trim($_POST['date_fin_contrat'] ?? '');
    $statut = trim($_POST['statut_contrat'] ?? 'en attente');
    $postedDetails = $_POST['details'] ?? [];

    $cleanDetails = [];
    if (is_array($postedDetails)) {
        foreach ($postedDetails as $key => $value) {
            $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$key);
            if ($safeKey === '') continue;
            if (is_array($value)) {
                $cleanDetails[$safeKey] = array_values(array_map('trim', array_map('strval', $value)));
            } else {
                $cleanDetails[$safeKey] = trim((string)$value);
            }
        }
    }

    $formule = $contratC->getFormuleById($idFormule);

    if ($client <= 0 || !$formule) {
        $error = 'Veuillez choisir un client et une formule valide.';
    } elseif ($dateDebut === '') {
        $error = 'La date début est obligatoire.';
    } elseif ($dateFin === '') {
        $error = 'La date fin est obligatoire.';
    } elseif ($dateFin <= $dateDebut) {
        $error = 'La date de fin doit être supérieure à la date de début.';
    } else {
        $detailsJson = json_encode($cleanDetails, JSON_UNESCAPED_UNICODE);

        $contrat = new Contrat(
            $contratData['numero_contrat'],
            $formule['nom_categorie'] ?? $contratData['type_contrat'],
            $client,
            (int)$formule['id_categorie'],
            (float)$formule['prix_formule'],
            (float)$formule['franchise_formule'],
            $dateDebut,
            $dateFin,
            $statut,
            (int)$formule['id_formule'],
            $formule['nom_formule'],
            $detailsJson
        );

        if ($contratC->updateContrat($id, $contrat)) {
            header('Location: showContrat.php?id=' . $id . '&success=1');
            exit();
        }
        $error = 'Erreur lors de la modification du contrat.';
    }

    $details = $cleanDetails;
    $contratData['id_client'] = $client;
    $contratData['id_formule'] = $idFormule;
    $contratData['date_debut_contrat'] = $dateDebut;
    $contratData['date_fin_contrat'] = $dateFin;
    $contratData['statut_contrat'] = $statut;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier contrat</title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/admin-users.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
    <link rel="stylesheet" href="assets/css/forms.css">
    <style>
        .form-row{display:flex;gap:20px;margin-bottom:18px;}
        .form-row .form-group{flex:1;margin-bottom:0;}
        .error-message{color:#ff6b6b;font-size:12px;margin-top:6px;display:block;}
        .details-title{font-size:20px;font-weight:800;color:#fff;margin:28px 0 16px;}
        .details-box{border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:22px;background:rgba(255,255,255,.03);margin-top:20px;}
        .readonly-input{opacity:.85;}
        @media(max-width:900px){.form-row{flex-direction:column;gap:14px;}}
    </style>
</head>
<body>
<div class="content" style="padding:40px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Modifier le contrat #<?= (int)$contratData['id_contrat'] ?></div>
        </div>

        <?php if (isset($error)): ?>
            <div style="padding:16px 20px; background:rgba(255,70,70,.13); color:#ff8a8a; margin:18px 24px 0; border-radius:12px; border:1px solid rgba(255,70,70,.25);">
                ⚠️ <?= h($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate style="padding:24px;" onsubmit="return validateContratBO()">
            <div class="form-group">
                <label>Numéro contrat</label>
                <input type="text" class="form-control readonly-input" value="<?= h($contratData['numero_contrat']) ?>" readonly>
            </div>

            <div class="form-group">
                <label>ID Client <span style="color:red;">*</span></label>
                <input type="text" class="form-control" name="id_client" id="id_client" value="<?= (int)$contratData['id_client'] ?>">
                <small class="error-message" id="error_id_client"></small>
            </div>

            <div class="form-group">
                <label>Formule <span style="color:red;">*</span></label>
                <select class="form-control" name="id_formule" id="id_formule" onchange="syncPrixFranchise()">
                    <option value="">-- Sélectionner une formule --</option>
                    <?php foreach ($formules as $f): ?>
                        <option value="<?= (int)$f['id_formule'] ?>"
                                data-prix="<?= h($f['prix_formule']) ?>"
                                data-franchise="<?= h($f['franchise_formule']) ?>"
                                <?= ((int)$f['id_formule'] === (int)($contratData['id_formule'] ?? 0)) ? 'selected' : '' ?>>
                            <?= h(($f['nom_categorie'] ?? 'Catégorie') . ' — ' . $f['nom_formule']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="error-message" id="error_id_formule"></small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Prime automatique</label>
                    <input type="text" class="form-control readonly-input" id="prime_preview" readonly>
                </div>
                <div class="form-group">
                    <label>Franchise automatique</label>
                    <input type="text" class="form-control readonly-input" id="franchise_preview" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date début <span style="color:red;">*</span></label>
                    <input type="date" class="form-control" name="date_debut_contrat" id="date_debut" value="<?= h($contratData['date_debut_contrat']) ?>">
                    <small class="error-message" id="error_date_debut"></small>
                </div>
                <div class="form-group">
                    <label>Date fin <span style="color:red;">*</span></label>
                    <input type="date" class="form-control" name="date_fin_contrat" id="date_fin" value="<?= h($contratData['date_fin_contrat']) ?>">
                    <small class="error-message" id="error_date_fin"></small>
                </div>
            </div>

            <div class="form-group">
                <label>Statut</label>
                <select class="form-control" name="statut_contrat">
                    <?php foreach (['en attente','actif','expiré','résilié','refusé'] as $st): ?>
                        <option value="<?= h($st) ?>" <?= ($contratData['statut_contrat'] ?? '') === $st ? 'selected' : '' ?>><?= h(ucfirst($st)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="details-box">
                <div class="details-title">Informations saisies par le client</div>

                <?php if (empty($details)): ?>
                    <div style="color:var(--text-secondary);padding:10px 0;">Aucune information spécifique enregistrée.</div>
                <?php else: ?>
                    <?php $i = 0; foreach ($details as $key => $value): ?>
                        <?php if ($i % 2 === 0): ?><div class="form-row"><?php endif; ?>
                            <div class="form-group">
                                <label><?= h(labelDetail($key)) ?></label>
                                <?php if (is_array($value)): ?>
                                    <textarea class="form-control detail-input" name="details[<?= h($key) ?>]" rows="2"><?= h(detailValue($value)) ?></textarea>
                                <?php else: ?>
                                    <input type="text" class="form-control detail-input" name="details[<?= h($key) ?>]" value="<?= h($value) ?>">
                                <?php endif; ?>
                                <small class="error-message detail-error"></small>
                            </div>
                        <?php if ($i % 2 === 1): ?></div><?php endif; ?>
                    <?php $i++; endforeach; ?>
                    <?php if ($i % 2 === 1): ?></div><?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="modal-footer" style="padding:24px 0 0; border-top:none;">
                <a href="showContrat.php?id=<?= (int)$id ?>" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Modifier</button>
            </div>
        </form>
    </div>
</div>
<script>
function boTodayISO(){ const d=new Date(); d.setHours(0,0,0,0); return d.toISOString().slice(0,10); }
function boYearsAgo(y){ const d=new Date(); d.setFullYear(d.getFullYear()-y); d.setHours(0,0,0,0); return d.toISOString().slice(0,10); }
const boRules = {
    email: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
    letters: /^[A-Za-zÀ-ÖØ-öø-ÿĀ-ſ\u0600-\u06FF]+(?:[ '\-][A-Za-zÀ-ÖØ-öø-ÿĀ-ſ\u0600-\u06FF]+)*$/u,
    address: /^[A-Za-zÀ-ÖØ-öø-ÿĀ-ſ\u0600-\u06FF0-9\s,.'°º\-\/]+$/u,
    immatTN: /^\d{1,4}\s*TUN\s*\d{1,4}$/i,
    immatAr: /^نت\s*\d{1,6}$/u,
    immatForeign: /^(?=.*[A-Za-z\u0600-\u06FF])(?=.*\d)[A-Za-z0-9\u0600-\u06FF\-\s]{3,15}$/u
};
function boNum(v){ return Number(String(v).replace(',', '.').replace(/\s/g,'')); }
function boErr(id,msg){ const e=document.getElementById(id); if(e) e.textContent=msg||''; }
function boMark(el,msg){ if(!el)return !msg; el.style.borderColor=msg?'#ef4444':'#22c55e'; el.style.boxShadow=msg?'0 0 0 3px rgba(239,68,68,.12)':'0 0 0 3px rgba(34,197,94,.10)'; return !msg; }
function syncPrixFranchise(){
    const s=document.getElementById('id_formule');
    const opt=s ? s.options[s.selectedIndex] : null;
    const prime=document.getElementById('prime_preview');
    const franchise=document.getElementById('franchise_preview');
    if (prime) prime.value=opt && opt.dataset.prix ? parseFloat(opt.dataset.prix).toFixed(2)+' DT' : '';
    if (franchise) franchise.value=opt && opt.dataset.franchise ? parseFloat(opt.dataset.franchise).toFixed(2)+' DT' : '';
}
function validateDetailBO(input){
    const error=input.parentElement.querySelector('.detail-error');
    const label=(input.parentElement.querySelector('label')?.textContent||'Champ').trim();
    const key=(input.name+' '+label).toLowerCase();
    const value=input.value.trim();
    let msg='';
    if(value==='') msg=label+' obligatoire.';
    else if(key.includes('email') && !boRules.email.test(value)) msg='Email invalide.';
    else if((key.includes('telephone')||key.includes('téléphone')) && !/^\d{8}$/.test(value)) msg='Téléphone invalide : exactement 8 chiffres.';
    else if((key.includes('nom')||key.includes('prenom')||key.includes('nationalite')||key.includes('nationalité')) && !(boRules.letters.test(value)&&value.length>=2)) msg=label+' doit contenir seulement des lettres.';
    else if(key.includes('adresse') && !(boRules.address.test(value)&&value.length>=5)) msg='Adresse invalide : lettres, chiffres et ponctuation simple seulement.';
    else if(key.includes('immatriculation')){ const compact=value.replace(/\s+/g,''); if(!(boRules.immatTN.test(value)||boRules.immatAr.test(compact)||boRules.immatForeign.test(value))) msg='Immatriculation invalide. Exemples : 123TUN4567, نت225444, AB-123-CD.'; }
    else if(key.includes('puissance')){ const n=boNum(value); if(!(Number.isFinite(n)&&n>=1&&n<=60)) msg='Puissance invalide : entre 1 et 45 CV.'; }
    else if(key.includes('valeur venale')||key.includes('valeur_venale')){ const n=boNum(value); if(!(Number.isFinite(n)&&n>=1000&&n<=1000000)) msg='Valeur vénale invalide : entre 1 000 et 1 000 000 DT.'; }
    else if(key.includes('date circulation')||key.includes('date_circulation')){ if(!(value<=boTodayISO()&&value>='1980-01-01')) msg='Date circulation invalide : pas future et pas avant 1980.'; }
    else if(key.includes('date naissance')||key.includes('date_naissance')){ if(!(value<=boYearsAgo(18)&&value>=boYearsAgo(100))) msg='Âge invalide : entre 18 et 100 ans.'; }
    if(error) error.textContent=msg;
    return boMark(input,msg);
}
function validateContratBO(){
    let ok=true, first=null;
    const client=document.getElementById('id_client');
    const formule=document.getElementById('id_formule');
    const debut=document.getElementById('date_debut');
    const fin=document.getElementById('date_fin');
    boErr('error_id_client',''); boErr('error_id_formule',''); boErr('error_date_debut',''); boErr('error_date_fin','');
    const idClient=(client?.value||'').trim();
    let msgClient = (!/^\d+$/.test(idClient) || parseInt(idClient,10)<=0) ? 'ID client obligatoire et doit être un entier positif.' : '';
    if(!boMark(client,msgClient)){ boErr('error_id_client',msgClient); ok=false; first=first||client; }
    let msgFormule = (!formule || formule.value==='') ? 'Formule obligatoire.' : '';
    if(!boMark(formule,msgFormule)){ boErr('error_id_formule',msgFormule); ok=false; first=first||formule; }
    let msgDebut = !debut.value ? 'Date début obligatoire.' : (debut.value < boTodayISO() ? 'La date début doit être aujourd’hui ou après.' : '');
    if(!boMark(debut,msgDebut)){ boErr('error_date_debut',msgDebut); ok=false; first=first||debut; }
    let msgFin = !fin.value ? 'Date fin obligatoire.' : (debut.value && fin.value<=debut.value ? 'Date fin doit être supérieure à date début.' : '');
    if(!boMark(fin,msgFin)){ boErr('error_date_fin',msgFin); ok=false; first=first||fin; }
    document.querySelectorAll('.detail-input').forEach(function(input){ if(!validateDetailBO(input)){ ok=false; first=first||input; } });
    if(!ok && first) first.focus();
    return ok;
}
document.addEventListener('DOMContentLoaded',function(){
    const form=document.querySelector('form[method="POST"]');
    if(form){ form.setAttribute('novalidate','novalidate'); form.querySelectorAll('[required],[min],[max],[pattern]').forEach(el=>{el.removeAttribute('required');el.removeAttribute('min');el.removeAttribute('max');el.removeAttribute('pattern');}); }
    ['id_client','id_formule','date_debut','date_fin'].forEach(function(id){ const el=document.getElementById(id); if(el){ el.addEventListener('input',validateContratBO); el.addEventListener('change',validateContratBO); } });
    document.querySelectorAll('.detail-input').forEach(function(el){ el.addEventListener('input',()=>validateDetailBO(el)); el.addEventListener('change',()=>validateDetailBO(el)); });
    syncPrixFranchise();
});
</script>
</body>
</html>
