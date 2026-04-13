<?php
require_once __DIR__ . '/../../Controller/ReponseController.php';

$reponseC = new ReponseController();
$error    = '';
$success  = '';

// AJOUT D'UNE RÉPONSE
if (
    isset($_POST['action']) && $_POST['action'] === 'repondre' &&
    isset($_POST['reclamation_id'], $_POST['contenu'])
) {
    try {
        $reponse = new Reponse(
            null,
            date('Y-m-d'),          // date_reponse (DATE)
            trim($_POST['contenu']),
            'envoyee',              // statut
            (int)$_POST['reclamation_id']
        );
        $reponseC->addReponse($reponse);
        header('Location: Reponse.php?success=1');
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// MODIFICATION D'UNE RÉPONSE
if (
    isset($_POST['action']) && $_POST['action'] === 'modifier_reponse' &&
    isset($_POST['rep_id'], $_POST['contenu'])
) {
    try {
        $reponseC->updateReponse((int)$_POST['rep_id'], trim($_POST['contenu']));
        header('Location: Reponse.php?success=2');
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// SUPPRESSION D'UNE RÉPONSE
if (isset($_GET['delete_reponse'])) {
    $reponseC->deleteReponse($_GET['delete_reponse']);
    header('Location: Reponse.php');
    exit();
}

if (isset($_GET['success'])) {
    if ($_GET['success'] == '2') $success = 'Réponse modifiée avec succès.';
    else                         $success = 'Réponse envoyée avec succès.';
}

// CHARGEMENT DES RÉCLAMATIONS AVEC LEURS RÉPONSES
$rows = $reponseC->listAllReclamations();

$total    = count($rows);
$answered = 0;
$pending  = 0;
$urgent   = 0;
foreach ($rows as $row) {
    if (!empty($row['reponse_contenu'])) $answered++;
    else                                 $pending++;
    if (($row['priorite'] ?? '') === 'Urgente') $urgent++;
}

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function badgeStatut($s) {
    switch ($s) {
        case 'closed':   return ['label'=>'Résolue',    'class'=>'st-closed'];
        case 'pending':  return ['label'=>'En attente', 'class'=>'st-pending'];
        case 'rejected': return ['label'=>'Rejetée',    'class'=>'st-rejected'];
        default:         return ['label'=>'En cours',   'class'=>'st-open'];
    }
}
function badgePriorite($p) {
    switch ($p) {
        case 'Urgente': return 'pr-urgente';
        case 'Faible':  return 'pr-faible';
        default:        return 'pr-normale';
    }
}
function badgeType($t) {
    switch ($t) {
        case 'Santé':      return 'tp-sante';
        case 'Auto':       return 'tp-auto';
        case 'Habitation': return 'tp-habitat';
        default:           return 'tp-autre';
    }
}
function initiales($email) {
    $parts = explode('@', $email);
    $name  = explode('.', $parts[0]);
    $init  = '';
    foreach ($name as $n) $init .= strtoupper(substr($n, 0, 1));
    return substr($init, 0, 2);
}
function formatDate($date) {
    if (empty($date)) return '—';
    $ts = strtotime($date);
    if (!$ts) return '—';
    $months = [1=>'janv.',2=>'févr.',3=>'mars',4=>'avr.',5=>'mai',6=>'juin',
               7=>'juil.',8=>'août',9=>'sept.',10=>'oct.',11=>'nov.',12=>'déc.'];
    return date('d',$ts).' '.$months[(int)date('n',$ts)].' '.date('Y',$ts);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Réclamations — Protex Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg:          #0b1628;
            --sidebar-bg:  #0d1e35;
            --accent:      #2dd4bf;
            --accent-dark: #059669;
            --orange:      #f97316;
            --gold:        #f59e0b;
            --danger:      #ef4444;
            --success:     #22c55e;
            --info:        #3b82f6;
            --border:      rgba(255,255,255,0.07);
            --glass:       rgba(255,255,255,0.04);
            --glass2:      rgba(255,255,255,0.07);
            --text-1:      #f1f5f9;
            --text-2:      rgba(241,245,249,0.60);
            --text-3:      rgba(241,245,249,0.35);
            --sidebar-w:   260px;
            --topbar-h:    64px;
            --radius:      14px;
        }
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
        html{scroll-behavior:smooth;}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--text-1);min-height:100vh;overflow-x:hidden;}
        a{text-decoration:none;color:inherit;}

        .bg-layer{position:fixed;inset:0;z-index:-2;background:linear-gradient(135deg,#0b1628 0%,#0d1f3c 50%,#1a0f00 100%);}
        .bg-layer::after{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 50% at 80% 20%,rgba(249,115,22,0.12) 0%,transparent 60%),radial-gradient(ellipse 50% 40% at 20% 70%,rgba(45,212,191,0.08) 0%,transparent 55%);}

        /* Sidebar */
        .sidebar{position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);background:var(--sidebar-bg);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:300;}
        .sidebar-logo{display:flex;align-items:center;gap:12px;padding:20px 20px 18px;border-bottom:1px solid var(--border);}
        .logo-icon{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent-dark));display:flex;align-items:center;justify-content:center;font-size:18px;color:#0b1628;font-weight:900;}
        .logo-name{font-size:16px;font-weight:800;letter-spacing:.3px;}
        .logo-sub{font-size:9px;color:var(--orange);letter-spacing:1.5px;font-weight:700;text-transform:uppercase;}
        .sidebar-user{display:flex;align-items:center;gap:11px;padding:16px 20px;border-bottom:1px solid var(--border);}
        .user-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent-dark));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#0b1628;flex-shrink:0;}
        .user-name{font-size:13.5px;font-weight:700;}
        .user-role{font-size:10.5px;color:var(--accent);background:rgba(45,212,191,.12);padding:2px 8px;border-radius:20px;display:inline-block;margin-top:2px;}
        .sidebar-section{padding:16px 20px 6px;font-size:9.5px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--text-3);}
        .sidebar-nav{flex:1;overflow-y:auto;padding-bottom:12px;}
        .nav-item{display:flex;align-items:center;gap:11px;padding:10px 20px;font-size:13.5px;color:var(--text-2);transition:all .2s;cursor:pointer;position:relative;}
        .nav-item:hover{background:var(--glass2);color:var(--text-1);}
        .nav-item.active{background:rgba(45,212,191,.10);color:var(--accent);border-right:3px solid var(--accent);}
        .nav-item i{font-size:16px;width:20px;text-align:center;}
        .nav-badge{margin-left:auto;background:var(--accent);color:#0b1628;font-size:10px;font-weight:800;padding:1px 7px;border-radius:20px;}
        .nav-badge.orange{background:var(--orange);color:#fff;}
        .sidebar-footer{padding:16px 20px;border-top:1px solid var(--border);}
        .nav-item.logout{color:var(--danger);}

        /* Topbar */
        .topbar{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--topbar-h);background:rgba(13,30,53,0.92);backdrop-filter:blur(18px);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 28px;z-index:200;}
        .topbar-title{font-size:18px;font-weight:800;}
        .topbar-date{font-size:12px;color:var(--text-3);}
        .topbar-right{display:flex;align-items:center;gap:10px;}
        .tb-btn{width:38px;height:38px;border-radius:10px;background:var(--glass);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-2);font-size:17px;cursor:pointer;position:relative;transition:all .2s;}
        .tb-btn:hover{background:var(--glass2);color:var(--text-1);}
        .notif-dot{position:absolute;top:7px;right:7px;width:7px;height:7px;background:var(--orange);border-radius:50%;box-shadow:0 0 6px var(--orange);}

        /* Main */
        .main{margin-left:var(--sidebar-w);padding:calc(var(--topbar-h) + 28px) 28px 40px;min-height:100vh;}

        /* Page header */
        .page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;}
        .page-title{font-size:24px;font-weight:800;margin-bottom:5px;}
        .breadcrumb{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-3);}
        .breadcrumb a{color:var(--accent);}
        .breadcrumb i{font-size:9px;}

        /* Stats */
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;}
        .stat-card{background:var(--glass);border:1px solid var(--border);border-radius:var(--radius);padding:22px 20px;backdrop-filter:blur(12px);position:relative;overflow:hidden;transition:all .25s;}
        .stat-card:hover{background:var(--glass2);transform:translateY(-2px);box-shadow:0 12px 32px rgba(0,0,0,.3);}
        .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.10),transparent);}
        .stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:14px;}
        .si-teal{background:rgba(45,212,191,.15);color:var(--accent);}
        .si-blue{background:rgba(59,130,246,.15);color:#60a5fa;}
        .si-orange{background:rgba(249,115,22,.15);color:var(--orange);}
        .si-red{background:rgba(239,68,68,.15);color:var(--danger);}
        .stat-val{font-size:28px;font-weight:900;line-height:1;margin-bottom:5px;}
        .stat-lbl{font-size:12px;color:var(--text-2);margin-bottom:8px;}
        .stat-sub{font-size:11px;color:var(--text-3);display:flex;align-items:center;gap:5px;}
        .stat-sub.up{color:var(--success);}
        .stat-sub.warn{color:var(--orange);}

        /* Table card */
        .table-card{background:var(--glass);border:1px solid var(--border);border-radius:var(--radius);backdrop-filter:blur(12px);overflow:hidden;}
        .table-header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border);}
        .table-header-left{display:flex;align-items:center;gap:10px;}
        .table-header-left i{font-size:18px;color:var(--accent);}
        .table-header-left span{font-size:15px;font-weight:700;}
        .btn-export{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:9px;background:var(--glass2);border:1px solid var(--border);color:var(--text-2);font-size:12.5px;font-weight:600;cursor:pointer;transition:all .2s;}
        .btn-export:hover{color:var(--text-1);}

        /* Filters */
        .filters-row{display:flex;align-items:center;gap:10px;padding:16px 24px;border-bottom:1px solid var(--border);flex-wrap:wrap;}
        .search-box{flex:1;min-width:240px;position:relative;}
        .search-box i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:14px;}
        .search-input{width:100%;padding:9px 14px 9px 38px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:9px;color:var(--text-1);font-size:13px;outline:none;transition:all .2s;}
        .search-input::placeholder{color:var(--text-3);}
        .search-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(45,212,191,.10);}
        .filter-sel{padding:9px 28px 9px 14px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:9px;color:var(--text-1);font-size:12.5px;outline:none;cursor:pointer;transition:all .2s;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 16 16'%3E%3Cpath fill='%2364748b' d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;}
        .filter-sel:focus{border-color:var(--accent);}
        .btn-reset{display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border-radius:9px;background:transparent;border:1px solid var(--border);color:var(--text-3);font-size:12.5px;cursor:pointer;transition:all .2s;}
        .btn-reset:hover{color:var(--text-1);border-color:var(--text-2);}

        /* Table */
        .rec-table{width:100%;border-collapse:collapse;}
        .rec-table thead tr{border-bottom:1px solid var(--border);}
        .rec-table th{padding:12px 16px;text-align:left;font-size:10.5px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--text-3);}
        .rec-table tbody tr{border-bottom:1px solid var(--border);transition:background .15s;}
        .rec-table tbody tr:last-child{border-bottom:none;}
        .rec-table tbody tr:hover{background:rgba(255,255,255,.03);}
        .rec-table td{padding:14px 16px;font-size:13px;vertical-align:middle;}

        /* Cells */
        .client-cell{display:flex;align-items:center;gap:10px;}
        .client-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent-dark));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#0b1628;flex-shrink:0;}
        .client-name{font-size:13px;font-weight:600;}
        .client-ref{font-size:11px;color:var(--text-3);}
        .desc-objet{font-size:13px;font-weight:600;color:var(--text-1);margin-bottom:2px;}
        .desc-text{max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-2);font-size:12px;}

        /* Badges */
        .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;}
        .badge::before{content:'';width:5px;height:5px;border-radius:50%;}
        .st-open{background:rgba(245,158,11,.12);color:#f59e0b;} .st-open::before{background:#f59e0b;box-shadow:0 0 5px #f59e0b;}
        .st-closed{background:rgba(34,197,94,.12);color:var(--success);} .st-closed::before{background:var(--success);box-shadow:0 0 5px var(--success);}
        .st-pending{background:rgba(59,130,246,.12);color:#60a5fa;} .st-pending::before{background:#60a5fa;}
        .st-rejected{background:rgba(239,68,68,.12);color:var(--danger);} .st-rejected::before{background:var(--danger);}
        .pr-urgente{background:rgba(239,68,68,.12);color:var(--danger);} .pr-urgente::before{background:var(--danger);}
        .pr-normale{background:rgba(59,130,246,.12);color:#60a5fa;} .pr-normale::before{background:#60a5fa;}
        .pr-faible{background:rgba(100,116,139,.15);color:#94a3b8;} .pr-faible::before{background:#94a3b8;}
        .tp-sante{background:rgba(45,212,191,.12);color:var(--accent);} .tp-sante::before{background:var(--accent);}
        .tp-auto{background:rgba(59,130,246,.12);color:#60a5fa;} .tp-auto::before{background:#60a5fa;}
        .tp-habitat{background:rgba(245,158,11,.12);color:#f59e0b;} .tp-habitat::before{background:#f59e0b;}
        .tp-autre{background:rgba(239,68,68,.12);color:var(--danger);} .tp-autre::before{background:var(--danger);}

        .rep-done{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;color:var(--success);font-weight:600;}
        .rep-none{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;color:var(--text-3);}

        /* Action buttons */
        .action-btns{display:flex;align-items:center;gap:6px;}
        .act-btn{width:32px;height:32px;border-radius:8px;background:var(--glass);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--text-2);cursor:pointer;transition:all .2s;}
        .act-btn:hover{background:var(--glass2);color:var(--text-1);}
        .act-btn.reply:hover{border-color:var(--accent);color:var(--accent);}
        .act-btn.view:hover{border-color:#60a5fa;color:#60a5fa;}
        .act-btn.edit:hover{border-color:#f59e0b;color:#f59e0b;}
        .act-btn.disabled{opacity:.35;cursor:not-allowed;}

        .empty-state{text-align:center;padding:60px 20px;color:var(--text-3);}
        .empty-state i{font-size:48px;display:block;margin-bottom:14px;opacity:.4;}

        /* Modal */
        .modal-overlay{display:none;position:fixed;inset:0;z-index:900;background:rgba(5,12,28,0.80);backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:20px;}
        .modal-box{background:#0d1e35;border:1px solid rgba(255,255,255,.09);border-radius:18px;padding:30px 28px 26px;width:100%;max-width:560px;box-shadow:0 30px 70px rgba(0,0,0,.6);animation:popIn .22s ease;}
        @keyframes popIn{from{opacity:0;transform:translateY(18px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
        .modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
        .modal-head-left{display:flex;align-items:center;gap:10px;}
        .modal-head-icon{width:38px;height:38px;border-radius:10px;background:rgba(45,212,191,.12);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:17px;}
        .modal-head-title{font-size:16px;font-weight:700;}
        .modal-head-sub{font-size:11.5px;color:var(--text-3);margin-top:1px;}
        .modal-close-btn{width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text-2);display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer;transition:all .2s;}
        .modal-close-btn:hover{background:rgba(255,255,255,.12);color:var(--text-1);}
        .rec-info-block{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:12px;padding:14px 16px;margin-bottom:20px;}
        .rec-info-row{display:flex;gap:8px;align-items:flex-start;margin-bottom:6px;}
        .rec-info-row:last-child{margin-bottom:0;}
        .rec-info-label{font-size:10.5px;color:var(--text-3);font-weight:700;text-transform:uppercase;letter-spacing:.5px;width:90px;flex-shrink:0;padding-top:1px;}
        .rec-info-val{font-size:12.5px;color:var(--text-2);flex:1;line-height:1.5;}
        .form-lbl{display:flex;align-items:center;gap:6px;font-size:10.5px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--text-1);margin-bottom:8px;}
        .form-lbl i{color:var(--accent);font-size:12px;}
        .form-textarea{width:100%;padding:12px 14px;min-height:120px;resize:vertical;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);border-radius:11px;color:var(--text-1);font-size:13px;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s;}
        .form-textarea::placeholder{color:rgba(241,245,249,.25);}
        .form-textarea:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(45,212,191,.10);}
        .modal-actions{display:flex;gap:10px;margin-top:20px;}
        .btn-annuler{flex:1;padding:12px;border-radius:11px;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text-1);font-size:13.5px;font-weight:600;cursor:pointer;transition:all .2s;}
        .btn-annuler:hover{background:rgba(255,255,255,.11);}
        .btn-envoyer{flex:1;padding:12px;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent-dark));border:none;color:#0b1628;font-size:13.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;box-shadow:0 6px 18px rgba(45,212,191,.25);}
        .btn-envoyer:hover{transform:translateY(-1px);box-shadow:0 10px 26px rgba(45,212,191,.35);}

        /* Toast */
        .toast{position:fixed;top:20px;right:24px;z-index:9999;display:flex;align-items:center;gap:10px;padding:14px 20px;border-radius:12px;font-size:13.5px;font-weight:600;box-shadow:0 10px 30px rgba(0,0,0,.4);animation:slideIn .3s ease;}
        .toast.ok{background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:var(--success);}
        .toast.err{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:var(--danger);}
        @keyframes slideIn{from{opacity:0;transform:translateX(60px)}to{opacity:1;transform:translateX(0)}}

        @media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){.sidebar{transform:translateX(-100%)}.main{margin-left:0}.topbar{left:0}}
    </style>
</head>
<body>
<div class="bg-layer"></div>

<?php if (!empty($success)) { ?>
<div class="toast ok" id="toast"><i class="bi bi-check-circle-fill"></i> <?php echo h($success); ?></div>
<script>setTimeout(()=>{const t=document.getElementById('toast');if(t)t.style.display='none';},3500);</script>
<?php } ?>
<?php if (!empty($error)) { ?>
<div class="toast err" id="toastErr"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo h($error); ?></div>
<script>setTimeout(()=>{const t=document.getElementById('toastErr');if(t)t.style.display='none';},4000);</script>
<?php } ?>

<!-- MODAL RÉPONDRE -->
<div class="modal-overlay" id="modalRepondre">
    <div class="modal-box">
        <div class="modal-head">
            <div class="modal-head-left">
                <div class="modal-head-icon"><i class="bi bi-reply-fill"></i></div>
                <div>
                    <div class="modal-head-title">Répondre à la réclamation</div>
                    <div class="modal-head-sub" id="modalRefLabel">—</div>
                </div>
            </div>
            <button class="modal-close-btn" onclick="closeRepondre()"><i class="bi bi-x"></i></button>
        </div>
        <div class="rec-info-block">
            <div class="rec-info-row"><span class="rec-info-label">Client</span><span class="rec-info-val" id="mClient">—</span></div>
            <div class="rec-info-row"><span class="rec-info-label">Objet</span><span class="rec-info-val" id="mObjet">—</span></div>
            <div class="rec-info-row"><span class="rec-info-label">Description</span><span class="rec-info-val" id="mDesc">—</span></div>
        </div>
        <form method="POST" action="Reponse.php">
            <input type="hidden" name="action"         value="repondre">
            <input type="hidden" name="reclamation_id" id="fRecId" value="">
            <div class="form-lbl"><i class="bi bi-chat-text"></i> VOTRE RÉPONSE *</div>
            <textarea class="form-textarea" name="contenu" placeholder="Rédigez votre réponse au client..." required></textarea>
            <div class="modal-actions">
                <button type="button" class="btn-annuler" onclick="closeRepondre()">Annuler</button>
                <button type="submit" class="btn-envoyer"><i class="bi bi-send"></i> Envoyer la réponse</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VOIR DÉTAILS -->
<div class="modal-overlay" id="modalVoir">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-head">
            <div class="modal-head-left">
                <div class="modal-head-icon" style="background:rgba(59,130,246,.12);color:#60a5fa;"><i class="bi bi-eye"></i></div>
                <div>
                    <div class="modal-head-title">Détail de la réclamation</div>
                    <div class="modal-head-sub" id="vRefLabel">—</div>
                </div>
            </div>
            <button class="modal-close-btn" onclick="closeVoir()"><i class="bi bi-x"></i></button>
        </div>
        <div class="rec-info-block" style="margin-bottom:14px;">
            <div class="rec-info-row"><span class="rec-info-label">Client</span><span class="rec-info-val" id="vClient">—</span></div>
            <div class="rec-info-row"><span class="rec-info-label">Objet</span><span class="rec-info-val" id="vObjet">—</span></div>
            <div class="rec-info-row"><span class="rec-info-label">Type</span><span class="rec-info-val" id="vType">—</span></div>
            <div class="rec-info-row"><span class="rec-info-label">Priorité</span><span class="rec-info-val" id="vPriorite">—</span></div>
            <div class="rec-info-row"><span class="rec-info-label">Date dépôt</span><span class="rec-info-val" id="vDate">—</span></div>
            <div class="rec-info-row"><span class="rec-info-label">Description</span><span class="rec-info-val" id="vDesc">—</span></div>
        </div>
        <div id="vReponseBlock" style="display:none;">
            <div class="form-lbl" style="margin-bottom:10px;"><i class="bi bi-check-circle" style="color:var(--success)"></i> RÉPONSE ENVOYÉE</div>
            <div class="rec-info-block" style="border-color:rgba(34,197,94,.2);background:rgba(34,197,94,.04);">
                <div class="rec-info-row"><span class="rec-info-label">Réponse</span><span class="rec-info-val" id="vReponse">—</span></div>
                <div class="rec-info-row"><span class="rec-info-label">Date</span><span class="rec-info-val" id="vDateRep">—</span></div>
            </div>
        </div>
        <div class="modal-actions" style="margin-top:18px;">
            <button type="button" class="btn-envoyer" style="flex:unset;padding:11px 24px;" onclick="closeVoir()">Fermer</button>
        </div>
    </div>
</div>

<!-- MODAL MODIFIER RÉPONSE -->
<div class="modal-overlay" id="modalModifier">
    <div class="modal-box">
        <div class="modal-head">
            <div class="modal-head-left">
                <div class="modal-head-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;"><i class="bi bi-pencil-fill"></i></div>
                <div>
                    <div class="modal-head-title">Modifier la réponse</div>
                    <div class="modal-head-sub" id="modRefLabel">—</div>
                </div>
            </div>
            <button class="modal-close-btn" onclick="closeModifier()"><i class="bi bi-x"></i></button>
        </div>
        <div class="rec-info-block">
            <div class="rec-info-row"><span class="rec-info-label">Client</span><span class="rec-info-val" id="modClient">—</span></div>
            <div class="rec-info-row"><span class="rec-info-label">Objet</span><span class="rec-info-val" id="modObjet">—</span></div>
        </div>
        <form method="POST" action="Reponse.php">
            <input type="hidden" name="action" value="modifier_reponse">
            <input type="hidden" name="rep_id" id="fRepId" value="">
            <div class="form-lbl" style="margin-bottom:8px;"><i class="bi bi-pencil" style="color:#f59e0b;"></i> MODIFIER LA RÉPONSE *</div>
            <textarea class="form-textarea" name="contenu" id="modContenu"
                placeholder="Modifiez la réponse..." required
                style="border-color:rgba(245,158,11,.25);"></textarea>
            <div class="modal-actions">
                <button type="button" class="btn-annuler" onclick="closeModifier()">Annuler</button>
                <button type="submit" class="btn-envoyer"
                    style="background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 6px 18px rgba(245,158,11,.25);">
                    <i class="bi bi-pencil-fill"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">P</div>
        <div><div class="logo-name">Protex</div><div class="logo-sub">Back-Office</div></div>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar">KM</div>
        <div><div class="user-name">Karim Miledi</div><div class="user-role">Administrateur</div></div>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section">Principal</div>
        <a class="nav-item" href="index.html"><i class="bi bi-grid-1x2"></i> Tableau de bord</a>
        <div class="sidebar-section">Gestion</div>
        <a class="nav-item " href="admin-users.html"><i class="bi bi-people"></i> Utilisateurs <span class="nav-badge">24</span></a>
        <a class="nav-item " href="Contrat.html"><i class="bi bi-file-earmark-text"></i> Contrats</a>
        <a class="nav-item " href="Sinistres.html"><i class="bi bi-shield-exclamation"></i> Sinistres</a>
        <a class="nav-item " href="Paiement.html"><i class="bi bi-credit-card"></i> Paiements</a>
        <a class="nav-item active" href="Reponse.php"><i class="bi bi-chat-dots"></i> Réclamations <span class="nav-badge orange"><?php echo $pending; ?></span></a>
        <a class="nav-item " href="Agence.html"><i class="bi bi-geo-alt"></i> Agences</a>
        <div class="sidebar-section">Compte</div>
        <a class="nav-item " href="adminprofile.html"><i class="bi bi-person-circle"></i> Mon profil</a>
    </nav>
    <div class="sidebar-footer">
        <a class="nav-item logout" href="#"><i class="bi bi-box-arrow-right"></i> Se déconnecter</a>
    </div>
</aside>

<!-- TOPBAR -->
<header class="topbar">
    <div>
        <div class="topbar-title">Gestion des réclamations</div>
        <div class="topbar-date" id="currentDate"></div>
    </div>
    <div class="topbar-right">
        <div class="tb-btn"><i class="bi bi-bell"></i><span class="notif-dot"></span></div>
        <div class="tb-btn"><i class="bi bi-question-circle"></i></div>
    </div>
</header>

<!-- MAIN -->
<main class="main">
    <div class="page-header">
        <div>
            <div class="page-title">Réclamations</div>
            <div class="breadcrumb">
                <i class="bi bi-house"></i><a href="index.html">Accueil</a>
                <i class="bi bi-chevron-right"></i><span>Réclamations</span>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon si-teal"><i class="bi bi-chat-dots"></i></div>
            <div class="stat-val"><?php echo $total; ?></div>
            <div class="stat-lbl">Total réclamations</div>
            <div class="stat-sub"><i class="bi bi-inbox"></i> Toutes périodes</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-blue"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-val"><?php echo $answered; ?></div>
            <div class="stat-lbl">Répondues</div>
            <div class="stat-sub up"><i class="bi bi-check-circle"></i> Traitées</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-orange"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-val"><?php echo $pending; ?></div>
            <div class="stat-lbl">En attente</div>
            <div class="stat-sub warn"><i class="bi bi-exclamation-circle"></i> À traiter</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-red"><i class="bi bi-flag"></i></div>
            <div class="stat-val"><?php echo $urgent; ?></div>
            <div class="stat-lbl">Urgentes</div>
            <div class="stat-sub warn"><i class="bi bi-lightning"></i> Priorité haute</div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-header-left"><i class="bi bi-table"></i><span>Liste des réclamations</span></div>
            <button class="btn-export"><i class="bi bi-download"></i> Exporter</button>
        </div>
        <div class="filters-row">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Rechercher par email, objet...">
            </div>
            <select class="filter-sel" id="filterType">
                <option value="">Tous les types</option>
                <option value="Santé">Santé</option>
                <option value="Auto">Auto</option>
                <option value="Habitation">Habitation</option>
                <option value="Autre">Autre</option>
            </select>
            <select class="filter-sel" id="filterStatut">
                <option value="">Tous les statuts</option>
                <option value="open">En cours</option>
                <option value="closed">Résolue</option>
                <option value="pending">En attente</option>
                <option value="rejected">Rejetée</option>
            </select>
            <button class="btn-reset" onclick="resetFilters()"><i class="bi bi-arrow-counterclockwise"></i> Réinitialiser</button>
        </div>

        <table class="rec-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Objet / Description</th>
                    <th>Type</th>
                    <th>Priorité</th>
                    <th>Statut</th>
                    <th>Date dépôt</th>
                    <th>Réponse</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="recBody">
            <?php if (!empty($rows)) { ?>
                <?php foreach ($rows as $rec) {
                    $statut  = badgeStatut($rec['statut'] ?? 'open');
                    $replied = !empty($rec['reponse_contenu']);
                ?>
                <tr data-email="<?php echo h($rec['email']??''); ?>"
                    data-objet="<?php echo h($rec['objet']??''); ?>"
                    data-type="<?php echo h($rec['type']??''); ?>"
                    data-statut="<?php echo h($rec['statut']??''); ?>">

                    <td>
                        <div class="client-cell">
                            <div class="client-avatar"><?php echo initiales($rec['email']??'cl'); ?></div>
                            <div>
                                <div class="client-name"><?php echo h($rec['email']??'—'); ?></div>
                                <div class="client-ref"><?php echo h($rec['rec_ref']??'—'); ?></div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="desc-objet"><?php echo h($rec['objet']??'—'); ?></div>
                        <div class="desc-text"><?php echo h($rec['description']??''); ?></div>
                    </td>

                    <td><span class="badge <?php echo badgeType($rec['type']??'Autre'); ?>"><?php echo h($rec['type']??'—'); ?></span></td>
                    <td><span class="badge <?php echo badgePriorite($rec['priorite']??'Normale'); ?>"><?php echo h($rec['priorite']??'Normale'); ?></span></td>
                    <td><span class="badge <?php echo $statut['class']; ?>"><?php echo $statut['label']; ?></span></td>
                    <td style="font-size:12px;color:var(--text-3);"><?php echo formatDate($rec['date_depot']??''); ?></td>

                    <td>
                        <?php if ($replied) { ?>
                            <span class="rep-done"><i class="bi bi-check-circle-fill"></i> Répondue</span>
                        <?php } else { ?>
                            <span class="rep-none"><i class="bi bi-clock"></i> En attente</span>
                        <?php } ?>
                    </td>

                    <td>
                        <div class="action-btns">
                            <!-- Voir -->
                            <button class="act-btn view" title="Voir détails"
                                onclick="openVoir(
                                    '<?php echo h($rec['rec_ref']??''); ?>',
                                    '<?php echo h($rec['email']??''); ?>',
                                    '<?php echo h($rec['objet']??''); ?>',
                                    '<?php echo h($rec['type']??''); ?>',
                                    '<?php echo h($rec['priorite']??''); ?>',
                                    '<?php echo formatDate($rec['date_depot']??''); ?>',
                                    <?php echo json_encode($rec['description']??''); ?>,
                                    <?php echo json_encode($rec['reponse_contenu']??''); ?>,
                                    '<?php echo formatDate($rec['rep_date']??''); ?>'
                                )">
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- Répondre -->
                            <?php if (!$replied) { ?>
                            <button class="act-btn reply" title="Répondre"
                                onclick="openRepondre(
                                    '<?php echo (int)$rec['id']; ?>',
                                    '<?php echo h($rec['rec_ref']??''); ?>',
                                    '<?php echo h($rec['email']??''); ?>',
                                    '<?php echo h($rec['objet']??''); ?>',
                                    <?php echo json_encode($rec['description']??''); ?>
                                )">
                                <i class="bi bi-reply-fill"></i>
                            </button>
                            <?php } else { ?>
                            <button class="act-btn disabled" title="Déjà répondue" disabled>
                                <i class="bi bi-reply-fill"></i>
                            </button>
                            <!-- Bouton modifier (visible uniquement si déjà répondue) -->
                            <button class="act-btn edit btn-modifier" title="Modifier la réponse"
                                data-rep-id="<?php echo (int)($rec['rep_id']??0); ?>"
                                data-ref="<?php echo h($rec['rec_ref']??''); ?>"
                                data-client="<?php echo h($rec['email']??''); ?>"
                                data-objet="<?php echo h($rec['objet']??''); ?>"
                                data-contenu="<?php echo h($rec['reponse_contenu']??''); ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="8">
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Aucune réclamation trouvée</p>
                    </div>
                </td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</main>

<script>
const ds = new Date().toLocaleDateString('fr-FR',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
document.getElementById('currentDate').textContent = ds.charAt(0).toUpperCase()+ds.slice(1);

function openRepondre(id,ref,email,objet,description){
    document.getElementById('fRecId').value=id;
    document.getElementById('modalRefLabel').textContent=ref;
    document.getElementById('mClient').textContent=email;
    document.getElementById('mObjet').textContent=objet;
    document.getElementById('mDesc').textContent=description;
    document.getElementById('modalRepondre').style.display='flex';
}
function closeRepondre(){ document.getElementById('modalRepondre').style.display='none'; }

function openVoir(ref,client,objet,type,priorite,date,description,reponse,dateRep){
    document.getElementById('vRefLabel').textContent=ref;
    document.getElementById('vClient').textContent=client;
    document.getElementById('vObjet').textContent=objet;
    document.getElementById('vType').textContent=type;
    document.getElementById('vPriorite').textContent=priorite;
    document.getElementById('vDate').textContent=date;
    document.getElementById('vDesc').textContent=description;
    const b=document.getElementById('vReponseBlock');
    if(reponse){ document.getElementById('vReponse').textContent=reponse; document.getElementById('vDateRep').textContent=dateRep; b.style.display='block'; }
    else { b.style.display='none'; }
    document.getElementById('modalVoir').style.display='flex';
}
function closeVoir(){ document.getElementById('modalVoir').style.display='none'; }

function openModifier(repId, ref, client, objet, contenu) {
    document.getElementById('fRepId').value           = repId;
    document.getElementById('modRefLabel').textContent = ref;
    document.getElementById('modClient').textContent   = client;
    document.getElementById('modObjet').textContent    = objet;
    document.getElementById('modContenu').value        = contenu;
    document.getElementById('modalModifier').style.display = 'flex';
}
function closeModifier() { document.getElementById('modalModifier').style.display = 'none'; }

// Délégation pour le bouton modifier (data-* attributes)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-modifier');
    if (btn) {
        openModifier(
            btn.dataset.repId,
            btn.dataset.ref,
            btn.dataset.client,
            btn.dataset.objet,
            btn.dataset.contenu
        );
    }
});

['modalRepondre','modalVoir','modalModifier'].forEach(id=>{
    document.getElementById(id).addEventListener('click',function(e){ if(e.target===this) this.style.display='none'; });
});

function filterTable(){
    const s=document.getElementById('searchInput').value.toLowerCase();
    const t=document.getElementById('filterType').value.toLowerCase();
    const st=document.getElementById('filterStatut').value.toLowerCase();
    document.querySelectorAll('#recBody tr[data-email]').forEach(tr=>{
        const match = (!s||(tr.dataset.email||'').toLowerCase().includes(s)||(tr.dataset.objet||'').toLowerCase().includes(s))
                   && (!t||(tr.dataset.type||'').toLowerCase()===t)
                   && (!st||(tr.dataset.statut||'').toLowerCase()===st);
        tr.style.display=match?'':'none';
    });
}
function resetFilters(){
    document.getElementById('searchInput').value='';
    document.getElementById('filterType').value='';
    document.getElementById('filterStatut').value='';
    filterTable();
}
document.getElementById('searchInput').addEventListener('input',filterTable);
document.getElementById('filterType').addEventListener('change',filterTable);
document.getElementById('filterStatut').addEventListener('change',filterTable);
</script>
</body>
</html>
