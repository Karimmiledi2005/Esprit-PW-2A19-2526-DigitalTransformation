<?php
require_once __DIR__ . '/../../bootstrap.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_user'])) {
    header('Location: ../../login.html');
    exit();
}

$reclamationC = new ReclamationController();
$error = '';

// Récupérer l'email et les infos de l'utilisateur depuis la session
$userId = (int)($_SESSION['user_id'] ?? $_SESSION['id_user'] ?? 0);
$userEmail = '';

if ($userId > 0) {
    try {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT email FROM user WHERE id_user = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $userEmail = $user['email'];
        }
    } catch (Exception $e) {
        error_log('Erreur lors de la récupération de l\'email: ' . $e->getMessage());
    }
}

function clean($value) {
    return trim((string)$value);
}

function validateReclamationInput($data) {
    $errors = [];

    $objet       = clean($data['objet'] ?? '');
    $type        = clean($data['type'] ?? '');
    $priorite    = clean($data['priorite'] ?? '');
    $description = clean($data['description'] ?? '');
    $email       = clean($data['email'] ?? '');

    $typesAutorises      = ['Santé', 'Auto', 'Habitation', 'Autre'];
    $prioritesAutorisees = ['Normale', 'Urgente', 'Faible'];

    if ($objet === '') {
        $errors[] = "L'objet est obligatoire.";
    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ0-9\s\-\.\'\#\(\)\[\]]+$/u", $objet)) {
        $errors[] = "L'objet contient des caractères non autorisés.";
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

    if ($email === '') {
        $errors[] = "L'adresse email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email est invalide.";
    } elseif (mb_strlen($email) > 150) {
        $errors[] = "L'adresse email est trop longue (max. 150 caractères).";
    }

    return $errors;
}

// TRAITEMENT AJOUT
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $errors = validateReclamationInput($_POST);

        if (empty($errors)) {
            $desc     = clean($_POST['description']);
            $priorite = clean($_POST['priorite']);
            
            // Auto-détection de la priorité si elle est à "Normale" par défaut
            if ($priorite === 'Normale') {
                $priorite = ReclamationController::detecterPriorite($desc);
            }

            $reclamation = new Reclamation(
                null,
                clean($_POST['objet']),
                clean($_POST['type']),
                clean($_POST['ref_contrat'] ?? 'REF-0000'),
                $priorite,
                'open',
                new DateTime(),
                'REC-' . date('YmdHis'),
                $desc,
                clean($_POST['email'])
            );

            $reclamationC->addReclamation($reclamation, (int)($_SESSION['user_id'] ?? $_SESSION['id_user'] ?? 0));
            header('Location: reclamationList.php');
            exit();
        } else {
            $error = implode('<br>', $errors);
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
    <title>Nouvelle réclamation — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="user/css/layout.css">
    <link rel="stylesheet" href="assets/css/reclamation.css">
    <script src="assets/js/reclamation-validation.js"></script>
    <script src="assets/js/reclamation.js"></script>
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
                <span class="nav-badge danger" id="invitationBadge" style="display:none">0</span>
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
                <div class="page-title-main">Nouvelle réclamation</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.php">Accueil</a>
                    <i class="bi bi-chevron-right"></i>
                    <a href="reclamationList.php">Réclamations</a>
                    <i class="bi bi-chevron-right"></i>
                    <span>Ajouter</span>
                </div>
            </div>
        </div>

        <!-- Formulaire d'ajout -->
        <div class="form-page-card">

            <?php if (!empty($error)) { ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <form method="POST" onsubmit="return validateReclamationForm()">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label class="form-label"><i class="bi bi-envelope"></i> EMAIL *</label>
                    <input type="email" class="form-control" id="fEmail" name="email"
                           placeholder="Ex : client@exemple.com"
                           value="<?php echo h($_POST['email'] ?? $userEmail); ?>"
                           readonly>
                    <span class="field-error" id="email_error"></span>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="bi bi-pencil-square"></i> OBJET *</label>
                    <input type="text" class="form-control" id="fObjet" name="objet"
                           placeholder="Ex : Remboursement refusé"
                           value="<?php echo h($_POST['objet'] ?? ''); ?>" >
                    <span class="field-error" id="objet_error"></span>
                    <div class="char-counter" id="charCountObjet"></div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="bi bi-file-earmark-text"></i> RÉFÉRENCE CONTRAT *</label>
                    <select class="form-control" id="fRefContrat" name="ref_contrat" required>
                        <option value="">-- Sélectionnez un contrat --</option>
                    </select>
                    <span class="field-error" id="ref_contrat_error"></span>
                    <div id="loadingContracts" style="display:none;color:#666;font-size:12px;margin-top:5px;">
                        <i class="bi bi-hourglass-split"></i> Chargement des contrats...
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="bi bi-tag"></i> TYPE *</label>
                        <select class="form-control" id="fType" name="type">
                            <option value="Santé"      <?php echo (($_POST['type'] ?? '') === 'Santé')      ? 'selected' : ''; ?>>Santé</option>
                            <option value="Auto"       <?php echo (($_POST['type'] ?? '') === 'Auto')       ? 'selected' : ''; ?>>Auto</option>
                            <option value="Habitation" <?php echo (($_POST['type'] ?? '') === 'Habitation') ? 'selected' : ''; ?>>Habitation</option>
                            <option value="Autre"      <?php echo (($_POST['type'] ?? '') === 'Autre')      ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="bi bi-flag"></i> PRIORITÉ</label>
                        <select class="form-control" id="fPriorite" name="priorite">
                            <option value="Normale" <?php echo (($_POST['priorite'] ?? '') === 'Normale') ? 'selected' : ''; ?>>Normale</option>
                            <option value="Urgente" <?php echo (($_POST['priorite'] ?? '') === 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                            <option value="Faible"  <?php echo (($_POST['priorite'] ?? '') === 'Faible')  ? 'selected' : ''; ?>>Faible</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="bi bi-chat-dots"></i> DESCRIPTION *</label>
                    <textarea class="form-control" id="fDesc" name="description"
                              placeholder="Décrivez votre réclamation en détail..." ><?php echo h($_POST['description'] ?? ''); ?></textarea>

                    <div class="voice-input-box">
                        <button type="button" class="btn-voice" id="btnVoiceDesc">
                            <i class="bi bi-mic"></i>
                            <span id="voiceBtnText">Dicter la description</span>
                        </button>
                        <span class="voice-status" id="voiceStatus">Cliquez puis parlez : le vocal sera converti en texte.</span>
                    </div>

                    <span class="field-error" id="desc_error"></span>
                    <div class="char-counter" id="charCountDesc"></div>
                </div>

                <div class="form-actions">
                    <a href="reclamationList.php" class="btn-cancel">
                        <i class="bi bi-arrow-left"></i> Annuler
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-send"></i> Envoyer
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
// Load user contracts on page load
document.addEventListener('DOMContentLoaded', function() {
    loadUserContracts();
});

function loadUserContracts() {
    const selectElement = document.getElementById('fRefContrat');
    const loadingElement = document.getElementById('loadingContracts');
    
    if (!selectElement) return;
    
    loadingElement.style.display = 'inline';
    
    fetch('api_get_user_contracts.php')
        .then(response => response.json())
        .then(data => {
            loadingElement.style.display = 'none';
            
            if (data.success && data.contracts) {
                // Clear existing options (except the placeholder)
                while (selectElement.options.length > 1) {
                    selectElement.remove(1);
                }
                
                // Add contracts as options
                data.contracts.forEach(contract => {
                    const option = document.createElement('option');
                    option.value = contract.numero_contrat;
                    option.textContent = contract.numero_contrat + ' (' + contract.type_contrat + ')';
                    selectElement.appendChild(option);
                });
            } else {
                console.warn('Erreur lors du chargement des contrats:', data.message);
                const option = document.createElement('option');
                option.value = '';
                option.textContent = '-- Aucun contrat disponible --';
                option.disabled = true;
                selectElement.appendChild(option);
            }
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des contrats:', error);
            loadingElement.style.display = 'none';
        });
}
</script>

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
