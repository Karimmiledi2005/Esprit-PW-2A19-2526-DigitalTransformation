<?php
require_once __DIR__ . '/../../bootstrap.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_user'])) {
    header('Location: ../../login.html');
    exit();
}

$reclamationC = new ReclamationController();
$error        = '';
$reclamation  = null;

function clean($value) {
    return trim((string)$value);
}

function validateReclamationInput($data, $isUpdate = false) {
    $errors = [];

    $objet       = clean($data['objet'] ?? '');
    $type        = clean($data['type'] ?? '');
    $priorite    = clean($data['priorite'] ?? '');
    $description = clean($data['description'] ?? '');
    $id          = $data['id'] ?? null;

    $typesAutorises      = ['Santé', 'Auto', 'Habitation', 'Autre'];
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

    if ($description === '') {
        $errors[] = "Description obligatoire.";
    } elseif (mb_strlen($description) < 10) {
        $errors[] = "Description trop courte (min. 10 caractères).";
    }

    return $errors;
}

// CHARGEMENT de la réclamation à modifier
if (isset($_GET['id']) && !isset($_POST['action'])) {
    $reclamation = $reclamationC->showReclamation((int)$_GET['id']);
} elseif (isset($_POST['id']) && !isset($_POST['action'])) {
    $reclamation = $reclamationC->showReclamation((int)$_POST['id']);
}

// TRAITEMENT MODIFICATION
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    try {
        $errors = validateReclamationInput($_POST, true);

        if (empty($errors)) {
            $old = $reclamationC->showReclamation($_POST['id']);

            if ($old) {
                $updated = new Reclamation(
                    (int)$_POST['id'],
                    clean($_POST['objet']),
                    clean($_POST['type']),
                    $old['ref_contrat'],
                    clean($_POST['priorite']),
                    $old['statut'],
                    new DateTime($old['date_depot']),
                    $old['rec_ref'],
                    clean($_POST['description']),
                    clean($_POST['email'] ?? $old['email'] ?? '')
                );

                $reclamationC->updateReclamation($updated, $_POST['id']);
                header('Location: reclamationList.php');
                exit();
            } else {
                $error = "Réclamation introuvable.";
            }
        } else {
            $error = implode('<br>', $errors);
            $reclamation = $reclamationC->showReclamation($_POST['id']);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier réclamation — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="user/css/layout.css">
    <link rel="stylesheet" href="assets/css/reclamation.css">
    <script src="assets/js/reclamation-validation.js"></script>
    <script src="assets/js/reclamation.js"></script>
    <style>
        /* ===== OVERRIDE TO MATCH MODAL THEME ===== */
        .form-page-card {
            background: #ffffff;
            border: 1px solid rgba(26,58,122,0.15);
            box-shadow: 0 20px 60px rgba(26,58,122,0.18);
            border-radius: 18px;
            padding: 32px;
        }
        .form-page-card::after { display: none; }
        
        .form-label {
            color: #15233C;
            font-weight: 500;
            font-size: 13px;
        }
        .form-control {
            border-radius: 10px; 
            border: 1px solid rgba(26,58,122,0.12);
            background: #fbfdff; 
            color: #15233C;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            height: 46px;
        }
        .form-control:focus {
            border-color: #2b5baf; 
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(43,91,175,0.08);
        }
        textarea.form-control {
            min-height: 120px;
        }
        
        /* Orange submit button as requested */
        .btn-submit {
            background: #FF6B1A !important;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(255, 107, 26, 0.25) !important;
            height: 46px;
            border: none;
            color: #fff;
            transition: all 0.2s;
        }
        .btn-submit:hover {
            background: #e65b12 !important;
            box-shadow: 0 12px 25px rgba(255, 107, 26, 0.35) !important;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            height: 46px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">
    <!-- ======== NAVBAR ======== -->
    <nav class="navbar">
        <a href="client.php" class="navbar-brand">
            <img src="logo.png" alt="logo" width="40" height="40" onerror="this.style.display='none'">
            <div>
                <div class="logo-text">Protex</div>
                <div class="logo-sub">Assurance Digitale</div>
            </div>
        </a>

        <div class="navbar-nav">
            <a class="nav-link" href="client.php">
                <i class="bi bi-grid-1x2"></i>
                <span class="nav-label">Tableau de bord</span>
            </a>
            <a class="nav-link" href="contrat.php">
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
            <a class="nav-link" href="reseau.html">
                <i class="bi bi-people"></i>
                <span class="nav-label">Réseau</span>
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
            <a class="nav-link" href="nos-offres.html">
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
                <div class="ptx-avatar-3d" id="avatarBtn" title="Mon compte">
                    <div class="ptx-avatar-ring"></div>
                    <div class="ptx-avatar-inner" id="avatarInitials">
                        <?php echo strtoupper(substr($_SESSION['prenom']??'U',0,1).substr($_SESSION['nom']??'P',0,1)); ?>
                    </div>
                </div>
                <div class="avatar-dropdown" id="avatarDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">
                            <?php echo strtoupper(substr($_SESSION['prenom']??'U',0,1).substr($_SESSION['nom']??'P',0,1)); ?>
                        </div>
                        <div class="dropdown-info">
                            <div class="dropdown-name"><?php echo htmlspecialchars($_SESSION['prenom']??'').' '.htmlspecialchars($_SESSION['nom']??''); ?></div>
                            <div class="dropdown-email"><?php echo htmlspecialchars($_SESSION['email']??''); ?></div>
                            <span class="dropdown-role">Client</span>
                        </div>
                    </div>
                    <a href="monprofile.html" class="dropdown-item"><i class="bi bi-person-circle"></i> Mon profil</a>
                    <a href="parametres.html" class="dropdown-item"><i class="bi bi-gear"></i> Paramètres</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item logout"><i class="bi bi-box-arrow-right"></i> Se déconnecter</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ======== MAIN CONTENT ======== -->
    <main class="main">
        <div class="page-header">
            <div>
                <div class="page-title-main">Modifier la réclamation</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.php">Accueil</a>
                    <i class="bi bi-chevron-right"></i>
                    <a href="reclamationList.php">Réclamations</a>
                    <i class="bi bi-chevron-right"></i>
                    <span>Modifier</span>
                </div>
            </div>
        </div>

        <!-- Formulaire de modification -->
        <div class="form-page-card">

            <?php if (!empty($error)) { ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <form method="POST" onsubmit="return validateReclamationForm()">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo h($_POST['id'] ?? $_GET['id'] ?? ($reclamation['id'] ?? '')); ?>">

                <div class="form-group">
                    <label class="form-label"><i class="bi bi-envelope"></i> EMAIL *</label>
                    <input type="email" class="form-control" id="fEmail" name="email"
                           placeholder="Ex : client@exemple.com"
                           value="<?php echo h($_POST['email'] ?? ($reclamation['email'] ?? '')); ?>">
                    <span class="field-error" id="email_error"></span>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="bi bi-pencil-square"></i> OBJET *</label>
                    <input type="text" class="form-control" id="fObjet" name="objet"
                           placeholder="Ex : Remboursement refusé"
                           value="<?php echo h($_POST['objet'] ?? ($reclamation['objet'] ?? '')); ?>">
                    <span class="field-error" id="objet_error"></span>
                    <div class="char-counter" id="charCountObjet"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="bi bi-tag"></i> TYPE *</label>
                        <select class="form-control" id="fType" name="type">
                            <?php
                            $currentType = $_POST['type'] ?? ($reclamation['type'] ?? '');
                            foreach (['Santé', 'Auto', 'Habitation', 'Autre'] as $t) {
                                $sel = ($currentType === $t) ? 'selected' : '';
                                echo "<option value=\"$t\" $sel>$t</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="bi bi-flag"></i> PRIORITÉ</label>
                        <select class="form-control" id="fPriorite" name="priorite">
                            <?php
                            $currentPrio = $_POST['priorite'] ?? ($reclamation['priorite'] ?? '');
                            foreach (['Normale', 'Urgente', 'Faible'] as $p) {
                                $sel = ($currentPrio === $p) ? 'selected' : '';
                                echo "<option value=\"$p\" $sel>$p</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="bi bi-chat-dots"></i> DESCRIPTION *</label>
                    <textarea class="form-control" id="fDesc" name="description"
                              placeholder="Décrivez votre réclamation en détail..." ><?php echo h($_POST['description'] ?? ($reclamation['description'] ?? '')); ?></textarea>
                    <span class="field-error" id="desc_error"></span>
                    <div class="char-counter" id="charCountDesc"></div>
                </div>

                <div class="form-actions">
                    <a href="reclamationList.php" class="btn-cancel">
                        <i class="bi bi-arrow-left"></i> Annuler
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-pencil-square"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // Boton IA Flottant
    // Avatar Dropdown Toggle
    document.addEventListener('DOMContentLoaded', function() {
        const avatarBtn = document.getElementById('avatarBtn');
        const avatarDropdown = document.getElementById('avatarDropdown');
        if (avatarBtn && avatarDropdown) {
            avatarBtn.onclick = function(e) {
                e.stopPropagation();
                avatarDropdown.classList.toggle('show');
            };
            document.addEventListener('click', function() {
                avatarDropdown.classList.remove('show');
            });
        }
    });

    const aiBtn = document.createElement('div');
    aiBtn.innerHTML = `
        <div id="btnOpenChat" style="position:fixed;bottom:30px;right:30px;width:60px;height:60px;background:linear-gradient(135deg,#ff7a1a,#ef6b0a);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:28px;cursor:pointer;box-shadow:0 10px 25px rgba(239,107,10,0.4);z-index:7999;transition:all 0.3s ease;">
            <i class="bi bi-stars"></i>
        </div>
    `;
    document.body.appendChild(aiBtn);
    
    document.getElementById('btnOpenChat').addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1) rotate(15deg)';
    });
    document.getElementById('btnOpenChat').addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1) rotate(0deg)';
    });
</script>
<script src="assets/js/chatbot-assurance.js"></script>
</body>
</html>
