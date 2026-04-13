<?php
require_once __DIR__ . '/../../Controller/ReclamationController.php';

$reclamationC = new ReclamationController();
$error = '';

function clean($value) {
    return trim((string)$value);
}

function validateReclamationInput($data, $isUpdate = false) {
    $errors = [];

    $objet = clean($data['objet'] ?? '');
    $type = clean($data['type'] ?? '');
    $priorite = clean($data['priorite'] ?? '');
    $email = clean($data['email'] ?? '');
    $description = clean($data['description'] ?? '');
    $id = $data['id'] ?? null;

    $typesAutorises = ['Santé', 'Auto', 'Habitation', 'Autre'];
    $prioritesAutorisees = ['Normale', 'Urgente', 'Faible'];

    if ($isUpdate) {
        if (!filter_var($id, FILTER_VALIDATE_INT) || (int)$id <= 0) {
            $errors[] = "Identifiant invalide.";
        }
    }

    if ($objet === '') {
    $errors[] = "L'objet est obligatoire.";
} elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $objet)) {
    $errors[] = "L'objet doit contenir uniquement des lettres.";
} elseif (mb_strlen($objet) < 3 || mb_strlen($objet) > 100) {
    $errors[] = "L'objet doit contenir entre 3 et 100 caractères.";
}

    if (!in_array($type, $typesAutorises, true)) {
        $errors[] = "Type invalide.";
    }

    if (!in_array($priorite, $prioritesAutorisees, true)) {
        $errors[] = "Priorité invalide.";
    }

    if ($email === '') {
    $errors[] = "Email obligatoire.";
} elseif (!preg_match("/^[a-zA-Z0-9.]+@[a-z]+\.[a-z]{2,}$/", $email)) {
    $errors[] = "Format email invalide (ex: nom@email.com)";
}

    if ($description === '') {
        $errors[] = "Description obligatoire.";
    } elseif (mb_strlen($description) < 10) {
        $errors[] = "Description trop courte.";
    }

    return $errors;
}

// AJOUT
if (
    isset($_POST['action']) && $_POST['action'] === 'add'
) {
    try {
        $errors = validateReclamationInput($_POST);

        if (empty($errors)) {
            $reclamation = new Reclamation(
                null,
                clean($_POST['objet']),
                clean($_POST['type']),
                'REF-2024-001',
                clean($_POST['priorite']),
                'open',
                new DateTime(),
                'REC-' . date('YmdHis'),
                clean($_POST['email']),
                clean($_POST['description'])
            );

            $reclamationC->addReclamation($reclamation);
            header('Location: Reclamation.php');
            exit();
        } else {
            $error = implode('<br>', $errors);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
// MODIFICATION
if (
    isset($_POST['action']) && $_POST['action'] === 'update'
) {
    try {
        $errors = validateReclamationInput($_POST, true);

        if (empty($errors)) {
            $old = $reclamationC->showReclamation($_POST['id']);

            if ($old) {
                $reclamation = new Reclamation(
                    (int)$_POST['id'],
                    clean($_POST['objet']),
                    clean($_POST['type']),
                    $old['ref_contrat'],
                    clean($_POST['priorite']),
                    $old['statut'],
                    new DateTime($old['date_depot']),
                    $old['rec_ref'],
                    clean($_POST['email']),
                    clean($_POST['description'])
                );

                $reclamationC->updateReclamation($reclamation, $_POST['id']);
                header('Location: Reclamation.php');
                exit();
            } else {
                $error = "Réclamation introuvable.";
            }
        } else {
            $error = implode('<br>', $errors);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// SUPPRESSION
if (isset($_GET['delete'])) {
    $reclamationC->deleteReclamation($_GET['delete']);
    header('Location: Reclamation.php');
    exit();
}

$list = $reclamationC->listReclamations();

$total = 0;
$openCount = 0;
$closedCount = 0;
$rejectedCount = 0;
$rows = [];

foreach ($list as $row) {
    $rows[] = $row;
    $total++;

    if (($row['statut'] ?? '') === 'open') $openCount++;
    if (($row['statut'] ?? '') === 'closed') $closedCount++;
    if (($row['statut'] ?? '') === 'rejected') $rejectedCount++;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function badgeClass($statut) {
    switch ($statut) {
        case 'closed': return 'badge-success';
        case 'pending': return 'badge-info';
        case 'rejected': return 'badge-danger';
        default: return 'badge-warning';
    }
}

function badgeLabel($statut) {
    switch ($statut) {
        case 'closed': return 'Résolue';
        case 'pending': return 'En attente';
        case 'rejected': return 'Rejetée';
        default: return 'En cours';
    }
}

function cardClass($statut) {
    $allowed = ['open', 'closed', 'pending', 'rejected'];
    return in_array($statut, $allowed, true) ? $statut : 'open';
}

function iconWrapClass($type) {
    switch ($type) {
        case 'Santé': return 'icon-sante';
        case 'Auto': return 'icon-auto';
        case 'Habitation': return 'icon-habitat';
        default: return 'icon-autre';
    }
}

function iconBiClass($type) {
    switch ($type) {
        case 'Santé': return 'bi-heart-pulse';
        case 'Auto': return 'bi-car-front';
        case 'Habitation': return 'bi-house';
        default: return 'bi-three-dots';
    }
}

function formatDateFr($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    if ($timestamp === false) return $date;

    $months = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];

    $day = date('d', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);

    return $day . ' ' . $month . ' ' . $year;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Réclamations — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <style>
        :root {
            --navy-deep:   #0A1931;
            --navy-mid:    #0d2240;
            --accent:      #2dd4bf;
            --accent2:     #FF8C00;
            --gold:        #f59e0b;
            --success:     #22c55e;
            --danger:      #ef4444;
            --warning:     #f59e0b;
            --info:        #3b82f6;
            --border:      rgba(255,255,255,0.08);
            --glass:       rgba(255,255,255,0.04);
            --glass-hover: rgba(255,255,255,0.07);
            --text-primary:   #f1f5f9;
            --text-secondary: rgba(241,245,249,0.65);
            --text-muted:     rgba(241,245,249,0.35);
            --card-radius: 18px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--navy-deep); color: var(--text-primary); min-height: 100vh; overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }

        .background { position: fixed; inset: 0; z-index: -2; background: linear-gradient(135deg, #0A1931 0%, #001F3F 55%, #1a0a00 100%); }
        .background::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 70% 50% at 15% 35%, rgba(45,212,191,0.08) 0%, transparent 55%), radial-gradient(ellipse 55% 40% at 85% 65%, rgba(255,140,0,0.10) 0%, transparent 55%), radial-gradient(ellipse 45% 30% at 50% 85%, rgba(245,158,11,0.07) 0%, transparent 50%); }
        .orb { position: fixed; border-radius: 50%; filter: blur(90px); opacity: .35; z-index: -1; animation: floatOrb 22s ease-in-out infinite; }
        .orb-1 { width:380px; height:380px; background:#059669; top:8%;  left:8%;  animation-delay:0s; }
        .orb-2 { width:320px; height:320px; background:#d97706; top:58%; right:8%; animation-delay:-7s; }
        .orb-3 { width:260px; height:260px; background:#e07a5f; bottom:8%; left:32%; animation-delay:-14s; }
        @keyframes floatOrb { 0%,100%{transform:translate(0,0) scale(1)} 25%{transform:translate(28px,-28px) scale(1.04)} 50%{transform:translate(-18px,18px) scale(0.96)} 75%{transform:translate(18px,10px) scale(1.02)} }

        .layout { display: flex; min-height: 100vh; }

        .navbar { position: fixed; top: 0; left: 0; right: 0; height: 68px; background: rgba(10,25,49,0.85); backdrop-filter: blur(18px); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 28px; z-index: 200; }
        .navbar-brand { display: flex; align-items: center; gap: 12px; margin-right: 36px; flex-shrink: 0; }
        .logo-text { font-size:17px; font-weight:700; letter-spacing:.3px; }
        .logo-sub  { font-size:10px; color:var(--text-muted); letter-spacing:.5px; }
        .navbar-nav { display: flex; align-items: center; gap: 2px; flex: 1; }
        .nav-link { display:flex; align-items:center; gap:7px; padding:8px 14px; border-radius:10px; color:var(--text-secondary); font-size:13.5px; transition:all .2s; white-space:nowrap; }
        .nav-link:hover { background: var(--glass-hover); color: var(--text-primary); }
        .nav-link.active { color: var(--text-primary); border-bottom: 2px solid var(--accent); border-radius: 0; }
        .nav-link i { font-size: 15px; }
        .nav-badge { background:var(--accent2); color:#fff; font-size:10px; font-weight:700; padding:1px 6px; border-radius:20px; }
        .nav-badge.accent { background:var(--accent); color:#0A1931; }
        .nav-separator { width:1px; height:24px; background:var(--border); margin:0 6px; }
        .navbar-right { display:flex; align-items:center; gap:10px; margin-left:auto; }
        .nav-btn { width:38px; height:38px; border-radius:10px; background:var(--glass); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--text-secondary); font-size:16px; transition:all .2s; cursor:pointer; position:relative; }
        .nav-btn:hover { background:var(--glass-hover); color:var(--text-primary); }
        .notif-dot { position:absolute; top:8px; right:8px; width:7px; height:7px; background:var(--danger); border-radius:50%; box-shadow:0 0 6px var(--danger); }
        .avatar-wrap { position:relative; }
        .avatar-btn { width:38px; height:38px; border-radius:10px; background:linear-gradient(135deg,#2dd4bf,#059669); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:#fff; cursor:pointer; transition:all .2s; }
        .avatar-btn:hover { transform:scale(1.05); }
        .avatar-dropdown { display:none; position:absolute; top:calc(100% + 10px); right:0; width:240px; background:rgba(10,28,55,0.97); backdrop-filter:blur(20px); border:1px solid var(--border); border-radius:14px; padding:8px; box-shadow:0 20px 50px rgba(0,0,0,.5); z-index:999; }
        .avatar-wrap:hover .avatar-dropdown { display:block; }
        .dropdown-header { display:flex; align-items:center; gap:10px; padding:10px 10px 14px; border-bottom:1px solid var(--border); margin-bottom:6px; }
        .dropdown-avatar { width:38px; height:38px; border-radius:10px; background:linear-gradient(135deg,#2dd4bf,#059669); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:#fff; }
        .dropdown-name  { font-size:13.5px; font-weight:600; }
        .dropdown-email { font-size:11px; color:var(--text-muted); }
        .dropdown-role  { font-size:10px; background:rgba(45,212,191,.15); color:var(--accent); padding:2px 8px; border-radius:20px; }
        .dropdown-item  { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; font-size:13px; color:var(--text-secondary); transition:all .2s; }
        .dropdown-item:hover { background:var(--glass-hover); color:var(--text-primary); }
        .dropdown-item.logout { color:var(--danger); }
        .dropdown-divider { height:1px; background:var(--border); margin:6px 0; }

        .main { flex:1; padding:88px 32px 40px; max-width:1200px; margin:0 auto; width:100%; }
        .page-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:28px; }
        .page-title-main { font-size:26px; font-weight:700; margin-bottom:6px; }
        .page-breadcrumb { display:flex; align-items:center; gap:6px; font-size:12.5px; color:var(--text-muted); }
        .page-breadcrumb a { color:var(--accent); }
        .page-breadcrumb i { font-size:10px; }
        .btn-new { display:inline-flex; align-items:center; gap:8px; padding:11px 22px; border-radius:12px; background:linear-gradient(135deg,var(--accent),#059669); color:#0A1931; font-weight:700; font-size:14px; border:none; cursor:pointer; box-shadow:0 6px 20px rgba(45,212,191,.25); transition:all .25s; white-space:nowrap; }
        .btn-new:hover { transform:translateY(-2px); box-shadow:0 10px 28px rgba(45,212,191,.35); }
        .btn-new i { font-size:16px; }

        .stats-row { display:flex; gap:14px; margin-bottom:28px; flex-wrap:wrap; }
        .stat-pill { display:flex; align-items:center; gap:10px; background:var(--glass); border:1px solid var(--border); border-radius:14px; padding:14px 20px; flex:1; min-width:160px; backdrop-filter:blur(10px); transition:all .25s; }
        .stat-pill:hover { background:var(--glass-hover); transform:translateY(-2px); }
        .stat-pill-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; }
        .sp-blue  .stat-pill-icon { background:rgba(59,130,246,.15); color:#3b82f6; }
        .sp-warn  .stat-pill-icon { background:rgba(245,158,11,.15);  color:var(--gold); }
        .sp-green .stat-pill-icon { background:rgba(34,197,94,.15);   color:var(--success); }
        .sp-red   .stat-pill-icon { background:rgba(239,68,68,.15);   color:var(--danger); }
        .stat-pill-val { font-size:22px; font-weight:800; line-height:1; }
        .stat-pill-lbl { font-size:11.5px; color:var(--text-muted); margin-top:2px; }

        .filters-bar { display:flex; align-items:center; gap:12px; margin-bottom:22px; flex-wrap:wrap; }
        .search-wrap { position:relative; flex:1; min-width:200px; }
        .search-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:15px; }
        .search-input { width:100%; padding:10px 16px 10px 40px; background:var(--glass); border:1px solid var(--border); border-radius:11px; color:var(--text-primary); font-size:13.5px; transition:all .2s; outline:none; }
        .search-input::placeholder { color:var(--text-muted); }
        .search-input:focus { border-color:var(--accent); box-shadow:0 0 16px rgba(45,212,191,.15); }
        .filter-select { padding:10px 14px; background:var(--glass); border:1px solid var(--border); border-radius:11px; color:var(--text-primary); font-size:13px; outline:none; cursor:pointer; transition:all .2s; }
        .filter-select:focus { border-color:var(--accent); }

        .reclamations-list { display:flex; flex-direction:column; gap:16px; }
        .rec-card { background:var(--glass); border:1px solid var(--border); border-radius:var(--card-radius); padding:22px 24px; backdrop-filter:blur(14px); transition:all .25s; position:relative; overflow:hidden; animation:fadeUp .5s ease backwards; }
        .rec-card::before { content:''; position:absolute; top:0; left:0; right:0; height:1px; background:linear-gradient(90deg,transparent,rgba(255,255,255,.12),transparent); }
        .rec-card:hover { background:var(--glass-hover); transform:translateY(-2px); box-shadow:0 16px 40px rgba(0,0,0,.3); }
        .rec-card.open     { border-left:3px solid var(--warning); }
        .rec-card.closed   { border-left:3px solid var(--success); }
        .rec-card.pending  { border-left:3px solid var(--info); }
        .rec-card.rejected { border-left:3px solid var(--danger); }
        .rec-card:nth-child(1){animation-delay:.05s} .rec-card:nth-child(2){animation-delay:.10s} .rec-card:nth-child(3){animation-delay:.15s} .rec-card:nth-child(4){animation-delay:.20s} .rec-card:nth-child(5){animation-delay:.25s}
        @keyframes fadeUp { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }

        .rec-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:12px; gap:12px; }
        .rec-title-group { display:flex; align-items:center; gap:12px; }
        .rec-icon { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:19px; flex-shrink:0; }
        .icon-sante   { background:rgba(45,212,191,.15); color:var(--accent); }
        .icon-auto    { background:rgba(59,130,246,.15); color:#60a5fa; }
        .icon-habitat { background:rgba(245,158,11,.15); color:var(--gold); }
        .icon-autre   { background:rgba(239,68,68,.15);  color:var(--danger); }
        .rec-name { font-size:15.5px; font-weight:700; margin-bottom:3px; }
        .rec-ref  { font-size:11.5px; color:var(--text-muted); }
        .rec-actions { display:flex; gap:8px; flex-shrink:0; }
        .btn-action { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:9px; font-size:12.5px; font-weight:600; border:1px solid var(--border); background:var(--glass); color:var(--text-secondary); cursor:pointer; transition:all .2s; }
        .btn-action:hover { background:var(--glass-hover); color:var(--text-primary); }
        .btn-action.edit:hover { border-color:var(--accent); color:var(--accent); }
        .btn-action.del:hover  { border-color:var(--danger); color:var(--danger); }
        .rec-body { display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:16px; align-items:center; }
        .rec-meta-item label { font-size:10.5px; text-transform:uppercase; letter-spacing:.6px; color:var(--text-muted); display:block; margin-bottom:4px; }
        .rec-meta-item span  { font-size:13.5px; font-weight:500; }
        .badge { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:20px; font-size:11.5px; font-weight:600; }
        .badge::before { content:''; width:6px; height:6px; border-radius:50%; }
        .badge-warning { background:rgba(245,158,11,.15); color:var(--warning); }
        .badge-warning::before { background:var(--warning); box-shadow:0 0 6px var(--warning); }
        .badge-success { background:rgba(34,197,94,.15); color:var(--success); }
        .badge-success::before { background:var(--success); box-shadow:0 0 6px var(--success); }
        .badge-info { background:rgba(59,130,246,.15); color:#60a5fa; }
        .badge-info::before { background:#60a5fa; box-shadow:0 0 6px #60a5fa; }
        .badge-danger { background:rgba(239,68,68,.15); color:var(--danger); }
        .badge-danger::before { background:var(--danger); box-shadow:0 0 6px var(--danger); }
        .rec-desc { font-size:13px; color:var(--text-secondary); margin-top:12px; padding-top:12px; border-top:1px solid var(--border); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
        .empty-state i { font-size:52px; margin-bottom:16px; opacity:.4; display:block; }
        .empty-state p { font-size:15px; }

        @media(max-width:900px){ .rec-body{grid-template-columns:1fr 1fr} }
        @media(max-width:640px){ .main{padding:80px 16px 32px} .rec-body{grid-template-columns:1fr} .navbar-nav .nav-label{display:none} }

        /* ── MODAL ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 900;
            background: rgba(5, 12, 28, 0.75);
            backdrop-filter: blur(6px);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal {
            background: #0d1e35;
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 20px;
            padding: 32px 30px 28px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 30px 70px rgba(0,0,0,0.5);
            animation: modalIn .25s ease;
        }
        @keyframes modalIn { from{opacity:0;transform:translateY(20px) scale(0.97)} to{opacity:1;transform:translateY(0) scale(1)} }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 26px;
        }
        .modal-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
        }
        .modal-close {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.10);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            cursor: pointer;
            transition: all .2s;
        }
        .modal-close:hover { background: rgba(255,255,255,0.13); color: var(--text-primary); }

        .form-group { margin-bottom: 18px; }
        .form-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        .form-label i { color: var(--accent); font-size: 13px; }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 11px;
            color: var(--text-primary);
            font-size: 13.5px;
            font-family: inherit;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            appearance: none;
            -webkit-appearance: none;
        }
        .form-control::placeholder { color: rgba(241,245,249,0.28); }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(45,212,191,0.12);
            background: rgba(45,212,191,0.04);
        }
        select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%2364748b' d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
            cursor: pointer;
        }
        textarea.form-control { resize: vertical; min-height: 110px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .modal-footer {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn-cancel {
            flex: 1;
            padding: 13px;
            border-radius: 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
        }
        .btn-cancel:hover { background: rgba(255,255,255,0.11); }
        .btn-submit {
            flex: 1;
            padding: 13px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2dd4bf, #059669);
            border: none;
            color: #0A1931;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all .2s;
            box-shadow: 0 6px 20px rgba(45,212,191,0.25);
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(45,212,191,0.35); }
        .btn-submit i { font-size: 15px; }
    </style>
</head>
<body>

<!-- MODAL FORMULAIRE -->
<div class="modal-overlay" id="modalForm">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title" id="modalTitle">Nouvelle réclamation</div>
            <button type="button" class="modal-close" onclick="closeModal()">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <?php if (!empty($error)) { ?>
            <div style="margin-bottom:16px; padding:12px 14px; border-radius:10px; background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#fecaca; font-size:13px;">
                <?php echo h($error); ?>
            </div>
        <?php } ?>

        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="fId" value="">

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-pencil-square"></i> OBJET *
                </label>
                <input type="text" class="form-control" id="fObjet" name="objet" placeholder="Ex : Remboursement refusé" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-tag"></i> TYPE *
                    </label>
                    <select class="form-control" id="fType" name="type">
                        <option value="Santé">Santé</option>
                        <option value="Auto">Auto</option>
                        <option value="Habitation">Habitation</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-flag"></i> PRIORITÉ
                    </label>
                    <select class="form-control" id="fPriorite" name="priorite">
                        <option value="Normale">Normale</option>
                        <option value="Urgente">Urgente</option>
                        <option value="Faible">Faible</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-envelope"></i> EMAIL *
                </label>
                <input type="email" class="form-control" id="fEmail" name="email" placeholder="Ex : karim.miledi@email.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-chat-dots"></i> DESCRIPTION *
                </label>
                <textarea class="form-control" id="fDesc" name="description" placeholder="Décrivez votre réclamation en détail..." required></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-send"></i> Envoyer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CONFIRMATION SUPPRESSION -->
<div class="modal-overlay" id="modalDelete" style="display:none;position:fixed;inset:0;z-index:900;background:rgba(5,12,28,0.75);backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:20px;">
    <div class="modal" style="max-width:420px;text-align:center;padding:36px 30px;">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(239,68,68,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <i class="bi bi-trash3" style="font-size:28px;color:var(--danger);"></i>
        </div>
        <div style="font-size:18px;font-weight:700;margin-bottom:10px;">Confirmer la suppression</div>
        <div style="font-size:13.5px;color:var(--text-secondary);margin-bottom:28px;">
            Êtes-vous sûr de vouloir supprimer cette réclamation ?<br>
            <span style="color:var(--danger);font-size:12px;">Cette action est irréversible.</span>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()" style="flex:1;">
                <i class="bi bi-x-lg"></i> Annuler
            </button>
            <a id="deleteLinkConfirm" href="#" style="flex:1;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;border-radius:12px;padding:13px;background:linear-gradient(135deg,#ef4444,#b91c1c);color:#fff;font-weight:700;font-size:14px;box-shadow:0 6px 20px rgba(239,68,68,0.3);">
                <i class="bi bi-trash3"></i> Supprimer
            </a>
        </div>
    </div>
</div>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">
    <nav class="navbar">
        <a href="index.html" class="navbar-brand">
            <img src="logo.png" alt="logo" width="40" height="40" onerror="this.style.display='none'">
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
            <a class="nav-link" href="paiements.html">
                <i class="bi bi-credit-card"></i>
                <span class="nav-label">Paiements</span>
            </a>
            <div class="nav-separator"></div>
            <a class="nav-link" href="Reclamation.php">
                <i class="bi bi-chat-dots"></i>
                <span class="nav-label">Réclamations</span>
            </a>
            <a class="nav-link" href="agences.html">
                <i class="bi bi-geo-alt"></i>
                <span class="nav-label">Agences</span>
            </a>
            <a class="nav-link" href="nos-offres.html">
                <i class="bi bi-stars"></i>
                <span class="nav-label">Nos offres</span>
            </a>
        </div>
        
        <div class="navbar-right">
            <a href="#" class="nav-btn" title="Notifications"><i class="bi bi-bell"></i><span class="notif-dot"></span></a>
            <a href="#" class="nav-btn" title="Aide"><i class="bi bi-question-circle"></i></a>
            <div class="avatar-wrap">
                <div class="avatar-btn">KM</div>
                <div class="avatar-dropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">KM</div>
                        <div>
                            <div class="dropdown-name">Karim Miledi</div>
                            <div class="dropdown-email">karim.miledi@email.com</div>
                            <span class="dropdown-role">Client Premium</span>
                        </div>
                    </div>
                    <a href="#" class="dropdown-item"><i class="bi bi-person-circle"></i> Mon profil</a>
                    <a href="#" class="dropdown-item"><i class="bi bi-gear"></i> Paramètres</a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item logout"><i class="bi bi-box-arrow-right"></i> Se déconnecter</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="main">
        <div class="page-header">
            <div>
                <div class="page-title-main">Mes réclamations &nbsp;<i class="bi bi-chat-dots" style="color:var(--accent);font-size:22px;vertical-align:middle"></i></div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="index.html">Accueil</a>
                    <i class="bi bi-chevron-right"></i>
                    <span>Réclamations</span>
                    &nbsp;·&nbsp;
                    <span id="currentDate"></span>
                </div>
            </div>
            <button type="button" class="btn-new" onclick="openModal()">
    <i class="bi bi-plus-lg"></i> Nouvelle réclamation
</button>
        </div>

        <div class="stats-row">
            <div class="stat-pill sp-blue">
                <div class="stat-pill-icon"><i class="bi bi-chat-dots"></i></div>
                <div><div class="stat-pill-val"><?php echo $total; ?></div><div class="stat-pill-lbl">Total réclamations</div></div>
            </div>
            <div class="stat-pill sp-warn">
                <div class="stat-pill-icon"><i class="bi bi-clock"></i></div>
                <div><div class="stat-pill-val"><?php echo $openCount; ?></div><div class="stat-pill-lbl">En cours</div></div>
            </div>
            <div class="stat-pill sp-green">
                <div class="stat-pill-icon"><i class="bi bi-check-circle"></i></div>
                <div><div class="stat-pill-val"><?php echo $closedCount; ?></div><div class="stat-pill-lbl">Résolues</div></div>
            </div>
            <div class="stat-pill sp-red">
                <div class="stat-pill-icon"><i class="bi bi-x-circle"></i></div>
                <div><div class="stat-pill-val"><?php echo $rejectedCount; ?></div><div class="stat-pill-lbl">Rejetées</div></div>
            </div>
        </div>

        <div class="filters-bar">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" class="search-input" id="searchInput"
                    placeholder="Rechercher une réclamation...">
            </div>
            <select class="filter-select" id="filterStatut">
                <option value="">Tous les statuts</option>
                <option value="open">En cours</option>
                <option value="closed">Résolue</option>
                <option value="pending">En attente</option>
                <option value="rejected">Rejetée</option>
            </select>
            <select class="filter-select" id="filterType">
                <option value="">Tous les types</option>
                <option value="Santé">Santé</option>
                <option value="Auto">Auto</option>
                <option value="Habitation">Habitation</option>
                <option value="Autre">Autre</option>
            </select>
        </div>

        <div class="reclamations-list">
            <?php if (!empty($rows)) { ?>
                <?php foreach ($rows as $reclamation) { ?>
                    <div class="rec-card <?php echo cardClass($reclamation['statut'] ?? 'open'); ?>" data-statut="<?php echo h($reclamation['statut'] ?? 'open'); ?>" data-type="<?php echo h($reclamation['type'] ?? ''); ?>">
                        <div class="rec-header">
                            <div class="rec-title-group">
                                <div class="rec-icon <?php echo iconWrapClass($reclamation['type'] ?? 'Autre'); ?>">
                                    <i class="bi <?php echo iconBiClass($reclamation['type'] ?? 'Autre'); ?>"></i>
                                </div>
                                <div>
                                    <div class="rec-name"><?php echo h($reclamation['objet'] ?? ''); ?></div>
                                    <div class="rec-ref">
                                        <?php echo h($reclamation['rec_ref'] ?? ''); ?>
                                        &nbsp;·&nbsp;
                                        <?php echo h($reclamation['ref_contrat'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="rec-actions">
<button
    class="btn-action edit btn-edit-rec"
    type="button"
    data-id="<?php echo (int)$reclamation['id']; ?>"
    data-objet="<?php echo h($reclamation['objet'] ?? ''); ?>"
    data-type="<?php echo h($reclamation['type'] ?? ''); ?>"
    data-priorite="<?php echo h($reclamation['priorite'] ?? ''); ?>"
    data-email="<?php echo h($reclamation['email'] ?? ''); ?>"
    data-description="<?php echo h($reclamation['description'] ?? ''); ?>"
>
    <i class="bi bi-pencil"></i> Modifier
</button>                                <button
                                    class="btn-action del btn-del-rec"
                                    type="button"
                                    data-id="<?php echo (int)$reclamation['id']; ?>"
                                >
                                    <i class="bi bi-trash3"></i> Supprimer
                                </button>                           
                            </div>
                        </div>

                        <div class="rec-body">
                            <div class="rec-meta-item">
                                <label>Type</label>
                                <span><?php echo h($reclamation['type'] ?? ''); ?></span>
                            </div>
                            <div class="rec-meta-item">
                                <label>Date de dépôt</label>
                                <span><?php echo h(formatDateFr($reclamation['date_depot'] ?? '')); ?></span>
                            </div>
                            <div class="rec-meta-item">
                                <label>Priorité</label>
                                <span><?php echo h($reclamation['priorite'] ?? ''); ?></span>
                            </div>
                            <div class="rec-meta-item">
                                <label>Statut</label>
                                <span class="badge <?php echo badgeClass($reclamation['statut'] ?? 'open'); ?>">
                                    <?php echo badgeLabel($reclamation['statut'] ?? 'open'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="rec-desc"><?php echo h($reclamation['description'] ?? ''); ?></div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Aucune réclamation trouvée</p>
                </div>
            <?php } ?>
        </div>
    </main>
</div>

<script>
const ds = new Date().toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
});
document.getElementById('currentDate').textContent =
    ds.charAt(0).toUpperCase() + ds.slice(1);
</script>

<script>
function openModal() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('fId').value = '';
    document.getElementById('modalTitle').textContent = 'Nouvelle réclamation';
    document.getElementById('fObjet').value = '';
    document.getElementById('fType').value = 'Santé';
    document.getElementById('fPriorite').value = 'Normale';
    document.getElementById('fEmail').value = '';
    document.getElementById('fDesc').value = '';
    document.getElementById('modalForm').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modalForm').style.display = 'none';
}

function closeDeleteModal() {
    document.getElementById('modalDelete').style.display = 'none';
}

// Délégation d'événements — data-* évite les conflits de guillemets
document.addEventListener('click', function(e) {

    // Bouton Modifier
    const btnEdit = e.target.closest('.btn-edit-rec');
    if (btnEdit) {
        document.getElementById('formAction').value = 'update';
        document.getElementById('fId').value        = btnEdit.dataset.id;
        document.getElementById('modalTitle').textContent = 'Modifier la réclamation';
        document.getElementById('fObjet').value     = btnEdit.dataset.objet;
        document.getElementById('fType').value      = btnEdit.dataset.type;
        document.getElementById('fPriorite').value  = btnEdit.dataset.priorite;
        document.getElementById('fEmail').value     = btnEdit.dataset.email;
        document.getElementById('fDesc').value      = btnEdit.dataset.description;
        document.getElementById('modalForm').style.display = 'flex';
        return;
    }

    // Bouton Supprimer → ouvre confirmation
    const btnDel = e.target.closest('.btn-del-rec');
    if (btnDel) {
        document.getElementById('deleteLinkConfirm').href = '?delete=' + btnDel.dataset.id;
        document.getElementById('modalDelete').style.display = 'flex';
        return;
    }

    // Fermer modals en cliquant sur l'overlay
    if (e.target.id === 'modalForm')   closeModal();
    if (e.target.id === 'modalDelete') closeDeleteModal();
});

// ── Filtres live ──
function filterCards() {
    const search = (document.getElementById('searchInput').value || '').toLowerCase();
    const statut = (document.getElementById('filterStatut').value || '').toLowerCase();
    const type   = (document.getElementById('filterType').value || '').toLowerCase();

    document.querySelectorAll('.rec-card').forEach(card => {
        const cardText   = card.textContent.toLowerCase();
        const cardStatut = (card.dataset.statut || '').toLowerCase();
        const cardType   = (card.dataset.type   || '').toLowerCase();

        const matchSearch = !search || cardText.includes(search);
        const matchStatut = !statut || cardStatut === statut;
        const matchType   = !type   || cardType   === type;

        card.style.display = (matchSearch && matchStatut && matchType) ? '' : 'none';
    });
}

document.getElementById('searchInput').addEventListener('input',  filterCards);
document.getElementById('filterStatut').addEventListener('change', filterCards);
document.getElementById('filterType').addEventListener('change',   filterCards);
</script>
</body>
</html>