<?php
require_once __DIR__ . '/../../controller/ReponseController.php';
require_once __DIR__ . '/../../controller/ReclamationController.php';

$reponseC     = new ReponseController();
$reclamationC = new ReclamationController();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function formatDateFr($date) {
    if (empty($date)) return '—';
    $ts = strtotime($date);
    if (!$ts) return $date;
    $months = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
               7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
    return date('d', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: reclamationList.php');
    exit();
}

// Récupère la réclamation avec sa réponse via le JOIN
$allRows = $reponseC->listAllReclamations();
$row = null;
foreach ($allRows as $r) {
    if ((int)$r['id'] === $id) { $row = $r; break; }
}

if (!$row) {
    header('Location: reclamationList.php');
    exit();
}

$statut      = $row['statut'] ?? 'open';
$rep_statut  = $row['rep_statut'] ?? '';

$badgeClass = ['closed'=>'badge-success','rejected'=>'badge-danger','pending'=>'badge-info'];
$badgeLabel = ['closed'=>'Résolue','rejected'=>'Rejetée','pending'=>'En attente','open'=>'En cours'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Détail réclamation — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <link rel="stylesheet" href="assets/css/reclamation.css">
</head>
<body>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">
    <!-- ======== NAVBAR ======== -->
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
            </a>
            <a class="nav-link" href="mes-sinistres.html">
                <i class="bi bi-shield-exclamation"></i>
                <span class="nav-label">Sinistres</span>
            </a>
            <a class="nav-link" href="paiements.html">
                <i class="bi bi-credit-card"></i>
                <span class="nav-label">Paiements</span>
            </a>
            <div class="nav-separator"></div>
            <a class="nav-link active" href="reclamationList.php">
                <i class="bi bi-chat-dots"></i>
                <span class="nav-label">Réclamations</span>
            </a>
            <a class="nav-link" href="agences.html">
                <i class="bi bi-geo-alt"></i>
                <span class="nav-label">Agences</span>
            </a>
        </div>

        <div class="navbar-right">
            <a href="#" class="nav-btn" title="Notifications">
                <i class="bi bi-bell"></i><span class="notif-dot"></span>
            </a>
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
                    <a href="login.html" class="dropdown-item logout"><i class="bi bi-box-arrow-right"></i> Se déconnecter</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ======== MAIN CONTENT ======== -->
    <main class="main">

        <div class="page-header">
            <div>
                <div class="page-title-main">Détail de la réclamation</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.html">Accueil</a>
                    <i class="bi bi-chevron-right"></i>
                    <a href="reclamationList.php">Réclamations</a>
                    <i class="bi bi-chevron-right"></i>
                    <span><?php echo h($row['rec_ref'] ?? ''); ?></span>
                </div>
            </div>
            <a href="reclamationList.php" class="btn-new" style="background:transparent;border:1px solid var(--border,#e2e8f0);color:var(--text,#334155)">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>

        <!-- ===== RÉCLAMATION ===== -->
        <div class="form-page-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px">
                <div>
                    <div style="font-size:18px;font-weight:600;color:var(--text-main,#1e293b)"><?php echo h($row['objet'] ?? ''); ?></div>
                    <div style="font-size:13px;color:var(--text-muted,#64748b);margin-top:3px">
                        <?php echo h($row['rec_ref'] ?? ''); ?>
                        &nbsp;·&nbsp;Contrat : <?php echo h($row['ref_contrat'] ?? ''); ?>
                    </div>
                </div>
                <span class="badge <?php echo $badgeClass[$statut] ?? 'badge-warning'; ?>" style="font-size:13px;padding:5px 14px">
                    <?php echo $badgeLabel[$statut] ?? 'En cours'; ?>
                </span>
            </div>

            <div class="rec-body" style="margin-bottom:16px">
                <div class="rec-meta-item">
                    <label>Type</label>
                    <span><?php echo h($row['type'] ?? '—'); ?></span>
                </div>
                <div class="rec-meta-item">
                    <label>Priorité</label>
                    <span><?php echo h(ucfirst($row['priorite'] ?? '—')); ?></span>
                </div>
                <div class="rec-meta-item">
                    <label>Date de dépôt</label>
                    <span><?php echo h(formatDateFr($row['date_depot'] ?? '')); ?></span>
                </div>
                <div class="rec-meta-item">
                    <label>Email</label>
                    <span><?php echo h($row['email'] ?? '—'); ?></span>
                </div>
            </div>

            <?php if (!empty($row['description'])): ?>
            <div style="margin-bottom:16px">
                <div style="font-size:12px;font-weight:600;color:var(--text-muted,#64748b);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Description</div>
                <div class="rec-desc" style="margin:0"><?php echo h($row['description']); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===== RÉPONSE (jointure) ===== -->
        <?php if (!empty($row['rep_id'])): ?>
        <div class="form-page-card" style="margin-top:16px;border-left:4px solid var(--accent,#6366f1)">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap">
                <i class="bi bi-chat-left-text" style="font-size:20px;color:var(--accent,#6366f1)"></i>
                <div style="font-size:16px;font-weight:600;color:var(--text-main,#1e293b)">Réponse de l'administration</div>
                <span style="font-size:12px;color:var(--text-muted,#64748b);margin-left:auto">
                    <?php echo h(formatDateFr($row['rep_date'] ?? '')); ?>
                </span>
                <?php if ($rep_statut === 'rejetee'): ?>
                <span class="badge badge-danger">Rejetée</span>
                <?php elseif ($rep_statut === 'envoyee'): ?>
                <span class="badge badge-success">Envoyée</span>
                <?php endif; ?>
            </div>

            <div style="background:var(--surface2,#f8fafc);border-radius:8px;padding:16px;font-size:14px;line-height:1.7;color:var(--text-main,#334155)">
                <?php echo nl2br(h($row['reponse_contenu'] ?? '')); ?>
            </div>
        </div>
        <?php else: ?>
        <div class="form-page-card" style="margin-top:16px;text-align:center;color:var(--text-muted,#64748b)">
            <i class="bi bi-hourglass-split" style="font-size:32px;margin-bottom:8px;display:block"></i>
            <div style="font-size:14px">Votre réclamation est en cours de traitement. Une réponse vous sera communiquée prochainement.</div>
        </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
