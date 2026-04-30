<?php
require_once __DIR__ . '/../../controller/ContratController.php';
require_once __DIR__ . '/../../model/Contrat.php';

$contratC = new ContratController();
$formules = $contratC->getAllFormules();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client = (int)($_POST['id_client'] ?? 0);
    $idFormule = (int)($_POST['id_formule'] ?? 0);
    $dateDebut = trim($_POST['date_debut_contrat'] ?? date('Y-m-d'));
    $dateFin = trim($_POST['date_fin_contrat'] ?? date('Y-m-d', strtotime('+1 year')));
    $statut = trim($_POST['statut_contrat'] ?? 'en attente');

    $formule = $contratC->getFormuleById($idFormule);

    if ($client <= 0 || !$formule) {
        $error = 'Veuillez choisir un client et une formule valide.';
    } elseif ($dateFin <= $dateDebut) {
        $error = 'La date de fin doit être supérieure à la date de début.';
    } else {
        $type = $formule['nom_categorie'] ?? 'Contrat';
        $contrat = new Contrat(
            $contratC->generateNumero(),
            $type,
            $client,
            (int)$formule['id_categorie'],
            (float)$formule['prix_formule'],
            (float)$formule['franchise_formule'],
            $dateDebut,
            $dateFin,
            $statut,
            (int)$formule['id_formule'],
            $formule['nom_formule'],
            json_encode(['source' => 'Ajout back-office'], JSON_UNESCAPED_UNICODE)
        );

        if ($contratC->addContrat($contrat)) {
            header('Location: contrats_back.php?success=1');
            exit();
        }
        $error = 'Erreur lors de l\'ajout du contrat.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter contrat</title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/admin-users.css">
    <link rel="stylesheet" href="assets/css/contrats.css">
    <link rel="stylesheet" href="assets/css/forms.css">
</head>
<body>
<div class="content" style="padding:40px;">
    <div class="card">
        <div class="card-header"><div class="card-title">Ajouter un contrat</div></div>

        <?php if (isset($error)): ?>
            <div style="padding:20px; background:#ffebee; color:#c62828; margin:10px 0; border-radius:4px;">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate style="padding:24px;" onsubmit="return validateContratBO()">
            <div class="form-group">
                <label>ID Client <span style="color:red;">*</span></label>
                <input type="number" class="form-control" name="id_client" id="id_client" placeholder="Ex: 1">
                <small class="error-message" id="error_id_client"></small>
            </div>

            <div class="form-group">
                <label>Formule <span style="color:red;">*</span></label>
                <select class="form-control" name="id_formule" id="id_formule" onchange="syncPrixFranchise()">
                    <option value="">-- Sélectionner une formule --</option>
                    <?php foreach ($formules as $f): ?>
                        <option value="<?= (int)$f['id_formule'] ?>"
                                data-prix="<?= htmlspecialchars($f['prix_formule']) ?>"
                                data-franchise="<?= htmlspecialchars($f['franchise_formule']) ?>"
                                data-categorie="<?= htmlspecialchars($f['nom_categorie'] ?? '') ?>">
                            <?= htmlspecialchars(($f['nom_categorie'] ?? 'Catégorie') . ' — ' . $f['nom_formule']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="error-message" id="error_id_formule"></small>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex:1;">
                    <label>Prime automatique</label>
                    <input type="text" class="form-control" id="prime_preview" readonly placeholder="Depuis formule">
                </div>
                <div class="form-group" style="flex:1; margin-left:10px;">
                    <label>Franchise automatique</label>
                    <input type="text" class="form-control" id="franchise_preview" readonly placeholder="Depuis formule">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex:1;">
                    <label>Date début <span style="color:red;">*</span></label>
                    <input type="date" class="form-control" name="date_debut_contrat" id="date_debut" value="<?= date('Y-m-d') ?>">
                    <small class="error-message" id="error_date_debut"></small>
                </div>
                <div class="form-group" style="flex:1; margin-left:10px;">
                    <label>Date fin <span style="color:red;">*</span></label>
                    <input type="date" class="form-control" name="date_fin_contrat" id="date_fin" value="<?= date('Y-m-d', strtotime('+1 year')) ?>">
                    <small class="error-message" id="error_date_fin"></small>
                </div>
            </div>

            <div class="form-group">
                <label>Statut</label>
                <select class="form-control" name="statut_contrat">
                    <option value="en attente">En attente</option>
                    <option value="actif">Actif</option>
                    <option value="expiré">Expiré</option>
                    <option value="résilié">Résilié</option>
                    <option value="refusé">Refusé</option>
                </select>
            </div>

            <div class="modal-footer" style="padding:0; border-top:none;">
                <a href="contrats_back.php" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>
<style>.form-row{display:flex;gap:10px;margin-bottom:15px}.error-message{color:#ff4d4d;font-size:12px}</style>
<script>
function setError(id,msg){const e=document.getElementById(id); if(e)e.textContent=msg||'';}
function syncPrixFranchise(){
    const s=document.getElementById('id_formule'); const opt=s.options[s.selectedIndex];
    document.getElementById('prime_preview').value=opt && opt.dataset.prix ? opt.dataset.prix+' DT' : '';
    document.getElementById('franchise_preview').value=opt && opt.dataset.franchise ? opt.dataset.franchise+' DT' : '';
}
function validateContratBO(){
    let ok=true;
    const client=document.getElementById('id_client');
    const formule=document.getElementById('id_formule');
    const debut=document.getElementById('date_debut');
    const fin=document.getElementById('date_fin');
    setError('error_id_client',''); setError('error_id_formule',''); setError('error_date_debut',''); setError('error_date_fin','');
    if(!client.value || parseInt(client.value)<=0){setError('error_id_client','ID client obligatoire'); ok=false;}
    if(!formule.value){setError('error_id_formule','Formule obligatoire'); ok=false;}
    if(!debut.value){setError('error_date_debut','Date début obligatoire'); ok=false;}
    if(!fin.value){setError('error_date_fin','Date fin obligatoire'); ok=false;}
    if(debut.value && fin.value && fin.value<=debut.value){setError('error_date_fin','Date fin doit être supérieure à date début'); ok=false;}
    return ok;
}
document.addEventListener('DOMContentLoaded', syncPrixFranchise);
</script>
<!-- Validation JS contrat BO sans HTML5 -->
<script>
function boTodayISO(){
    const d=new Date(); d.setHours(0,0,0,0);
    return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
}
function boErr(id,msg){const e=document.getElementById(id); if(e)e.textContent=msg||'';}
function boMark(el,ok){if(!el)return; el.style.borderColor=ok?'#22c55e':'#ef4444'; el.style.boxShadow=ok?'0 0 0 3px rgba(34,197,94,.10)':'0 0 0 3px rgba(239,68,68,.12)';}
function validateContratBO(){
    let ok=true;
    const client=document.getElementById('id_client');
    const formule=document.getElementById('id_formule');
    const debut=document.getElementById('date_debut');
    const fin=document.getElementById('date_fin');
    boErr('error_id_client',''); boErr('error_id_formule',''); boErr('error_date_debut',''); boErr('error_date_fin','');

    const idClient=(client?.value||'').trim();
    if(!/^\d+$/.test(idClient) || parseInt(idClient,10)<=0){boErr('error_id_client','ID client obligatoire et doit être un entier positif.'); boMark(client,false); ok=false;} else boMark(client,true);
    if(!formule || formule.value===''){boErr('error_id_formule','Formule obligatoire.'); boMark(formule,false); ok=false;} else boMark(formule,true);
    if(!debut.value){boErr('error_date_debut','Date début obligatoire.'); boMark(debut,false); ok=false;}
    else if(debut.value < boTodayISO()){boErr('error_date_debut','La date début ne doit pas être antérieure à aujourd’hui.'); boMark(debut,false); ok=false;}
    else boMark(debut,true);
    if(!fin.value){boErr('error_date_fin','Date fin obligatoire.'); boMark(fin,false); ok=false;}
    else if(debut.value && fin.value<=debut.value){boErr('error_date_fin','Date fin doit être supérieure à date début.'); boMark(fin,false); ok=false;}
    else boMark(fin,true);

    document.querySelectorAll('.detail-input').forEach(function(input){
        const error=input.parentElement.querySelector('.detail-error');
        const label=(input.parentElement.querySelector('label')?.textContent||'Champ').trim();
        if(input.value.trim()===''){
            if(error) error.textContent=label+' obligatoire.';
            boMark(input,false); ok=false;
        } else if(input.type==='email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())){
            if(error) error.textContent='Email invalide.';
            boMark(input,false); ok=false;
        } else {
            if(error) error.textContent='';
            boMark(input,true);
        }
    });
    return ok;
}
document.addEventListener('DOMContentLoaded',function(){
    const form=document.querySelector('form[method="POST"]');
    if(form){form.setAttribute('novalidate','novalidate'); form.querySelectorAll('[required],[min]').forEach(el=>{el.removeAttribute('required'); el.removeAttribute('min');});}
    ['id_client','id_formule','date_debut','date_fin'].forEach(function(id){const el=document.getElementById(id); if(el){el.addEventListener('input',validateContratBO); el.addEventListener('change',validateContratBO);}});
    document.querySelectorAll('.detail-input').forEach(function(el){el.addEventListener('input',validateContratBO);});
    if(typeof syncPrixFranchise==='function') syncPrixFranchise();
});
</script>
</body>
</html>
