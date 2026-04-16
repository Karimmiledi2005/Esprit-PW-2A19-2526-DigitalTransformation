<?php
require_once '../../controller/contratController.php';

$contratC = new ContratController();
$list = $contratC->listContrats();

$nom   = "Karim Miledi";
$email = "karim.miledi@email.com";
$msg   = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Tableau de bord — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <style>
        /* ===== TOAST ===== */
        .toast-notif {
            position: fixed; bottom: 24px; right: 24px;
            background: var(--navy-mid); border: 1px solid var(--border);
            border-radius: 12px; padding: 14px 20px;
            display: flex; align-items: center; gap: 10px;
            font-size: 14px; color: var(--text-primary);
            z-index: 9999; opacity: 0; transform: translateY(10px);
            transition: all 0.3s ease; box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }
        .toast-notif.show { opacity: 1; transform: translateY(0); }
        .toast-success i { color: var(--success); font-size: 18px; }
        .toast-warning i { color: var(--gold); font-size: 18px; }
        .toast-danger  i { color: var(--danger); font-size: 18px; }

        /* ===== MODAL OVERLAY ===== */
        .modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }

        /* ===== MODAL BOX ===== */
        .modal-box {
            background: linear-gradient(145deg, rgba(13,21,44,0.97), rgba(18,28,58,0.97));
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 22px;
            padding: 36px 40px;
            width: 100%;
            max-width: 580px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.04);
            animation: slideIn 0.25s cubic-bezier(.16,1,.3,1);
            position: relative;
        }
        @keyframes slideIn {
            from { opacity:0; transform: scale(0.94) translateY(16px); }
            to   { opacity:1; transform: scale(1) translateY(0); }
        }
        .modal-box::-webkit-scrollbar { width: 5px; }
        .modal-box::-webkit-scrollbar-track { background: transparent; }
        .modal-box::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 10px; }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .modal-header h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.3px;
        }
        .modal-close-btn {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6);
            width: 36px; height: 36px;
            border-radius: 50%;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .modal-close-btn:hover { background: rgba(255,255,255,0.14); color: #fff; transform: rotate(90deg); }

        /* ===== FORM ELEMENTS ===== */
        .modal-box .form-group  { margin-bottom: 18px; }
        .modal-box .form-row    { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .modal-box label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: rgba(255,255,255,0.55);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 7px;
        }
        .modal-box input,
        .modal-box select,
        .modal-box textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 11px;
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        .modal-box input:focus,
        .modal-box select:focus,
        .modal-box textarea:focus {
            outline: none;
            border-color: rgba(88,166,255,0.6);
            background: rgba(255,255,255,0.09);
            box-shadow: 0 0 0 3px rgba(88,166,255,0.12);
        }
        .modal-box input[readonly],
        .modal-box textarea[readonly] {
            opacity: 0.5;
            cursor: default;
        }
        .modal-box select option { background: #0d1528; color: #fff; }
        .modal-box .input-error  { border-color: #f56565 !important; background: rgba(245,101,101,0.06) !important; }
        .modal-box .input-valid  { border-color: #68d391 !important; }
        .error-msg { color: #fc8181; font-size: 12px; margin-top: 5px; display: block; min-height: 16px; }

        /* ===== SECTION DIVIDER ===== */
        .modal-divider {
            font-size: 11px;
            font-weight: 700;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 20px 0 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-divider::after {
            content: ''; flex: 1;
            height: 1px; background: rgba(255,255,255,0.07);
        }

        /* ===== MODAL ACTIONS ===== */
        .modal-footer {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .btn-modal-primary {
            flex: 1;
            padding: 13px;
            background: linear-gradient(135deg, #4f9ef8 0%, #5a67d8 100%);
            border: none;
            border-radius: 11px;
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
            letter-spacing: 0.3px;
        }
        .btn-modal-primary:hover { opacity: 0.88; transform: translateY(-1px); }
        .btn-modal-secondary {
            padding: 13px 22px;
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 11px;
            color: rgba(255,255,255,0.7);
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-modal-secondary:hover { background: rgba(255,255,255,0.13); color: #fff; }

        /* ===== VIEW MODAL DETAILS ===== */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .detail-item label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; }
        .detail-value {
            padding: 10px 14px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 9px;
            font-size: 14px;
            color: #fff;
            margin-top: 5px;
        }

        /* ===== CONFIRM MODAL ===== */
        .confirm-icon { text-align: center; font-size: 48px; margin-bottom: 12px; }
        .confirm-title { text-align: center; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px; }
        .confirm-text  { text-align: center; font-size: 14px; color: rgba(255,255,255,0.55); margin-bottom: 24px; }
    </style>
</head>
<body>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">
    <!-- ===== NAVBAR ===== -->
    <nav class="navbar">
        <a href="client.php" class="navbar-brand">
            <img src="logo.png" alt="logo" width="40" height="40">
            <div>
                <div class="logo-text">Protex</div>
                <div class="logo-sub">Assurance Digitale</div>
            </div>
        </a>

        <div class="navbar-nav">
            <a class="nav-link" href="client.php"><i class="bi bi-grid-1x2"></i><span class="nav-label">Tableau de bord</span></a>
            <a class="nav-link active" href="client.php">
                <i class="bi bi-file-earmark-text"></i>
                <span class="nav-label">Contrats</span>
                <span class="nav-badge accent"><?php echo count($list); ?></span>
            </a>
            <a class="nav-link" href="#"><i class="bi bi-shield-exclamation"></i><span class="nav-label">Sinistres</span></a>
            <a class="nav-link" href="#"><i class="bi bi-credit-card"></i><span class="nav-label">Paiements</span></a>
            <div class="nav-separator"></div>
            <a class="nav-link" href="#"><i class="bi bi-chat-dots"></i><span class="nav-label">Réclamations</span></a>
        </div>

        <div class="navbar-right">
            <a href="#" class="nav-btn"><i class="bi bi-bell"></i><span class="notif-dot"></span></a>
            <a href="#" class="nav-btn"><i class="bi bi-question-circle"></i></a>
            <div class="avatar-wrap">
                <div class="avatar-btn" id="avatarBtn">KM</div>
                <div class="avatar-dropdown" id="avatarDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">KM</div>
                        <div class="dropdown-info">
                            <div class="dropdown-name"><?php echo htmlspecialchars($nom); ?></div>
                            <div class="dropdown-email"><?php echo htmlspecialchars($email); ?></div>
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

    <!-- ===== MAIN ===== -->
    <main class="main">

        <div class="page-header">
            <div>
                <div class="page-title-main">Contrats</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.php" style="color:inherit;text-decoration:none;">Accueil</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <span>Contrats</span>
                </div>
            </div>
            
        </div>

        <!-- Categories -->
        <div class="contracts-intro"><div><h2>Choisissez une catégorie</h2><p>Sélectionnez le type d'assurance avant de remplir votre contrat.</p></div></div>
        <div class="categories-grid">
            <a href="contratauto.php" class="category-card active"><div class="category-icon auto"><i class="bi bi-car-front-fill"></i></div><h3>Auto</h3><p>Assurance automobile et mobilité.</p></a>
            <a href="contrathabitation.php" class="category-card"><div class="category-icon habitation"><i class="bi bi-house-door-fill"></i></div><h3>Habitation</h3><p>Protection du logement et du patrimoine.</p></a>
            <a href="contratsante.php" class="category-card"><div class="category-icon sante"><i class="bi bi-heart-pulse-fill"></i></div><h3>Santé</h3><p>Couverture santé et assistance médicale.</p></a>
            <a href="contratprotection.php" class="category-card"><div class="category-icon protection"><i class="bi bi-shield-check"></i></div><h3>Protection</h3><p>Prévoyance, sécurité et assistance.</p></a>
        </div>

        <!-- Contracts List -->
        <section class="content contracts-page">
            <div class="contracts-header">
                <div><h2>Mes contrats</h2><p>Consultez et gérez facilement tous vos contrats</p></div>
            </div>

            <div class="contracts-list">
<?php foreach ($list as $contrat):
    if ($contrat['statut'] === 'resilie' || $contrat['statut'] === 'résilié') continue;
    $iconClass = match($contrat['type_contrat']) { 'Auto'=>'auto','Sante','Santé'=>'health','Habitation'=>'home','Protection'=>'protection', default=>'' };
    $icon = match($contrat['type_contrat']) {
        'Auto'      => '<i class="bi bi-car-front-fill"></i>',
        'Sante','Santé' => '<i class="bi bi-heart-pulse-fill"></i>',
        'Habitation'=> '<i class="bi bi-house-door-fill"></i>',
        'Protection'=> '<i class="bi bi-shield-check"></i>',
        default     => '<i class="bi bi-file-earmark"></i>'
    };
    $statusClass = match($contrat['statut']) { 'actif'=>'active','en attente'=>'waiting',default=>'expired' };
?>
                <div class="contract-banner">
                    <div class="contract-banner-left">
                        <div class="contract-icon <?php echo $iconClass; ?>"><?php echo $icon; ?></div>
                        <div>
                            <h3>Contrat <?php echo htmlspecialchars($contrat['type_contrat']); ?></h3>
                            <span class="contract-ref">N° <?php echo htmlspecialchars($contrat['numero_contrat']); ?></span>
                        </div>
                    </div>

                    <div class="contract-banner-center">
                        <div class="info-item"><span class="label">Date début</span><strong><?php echo $contrat['date_debut']; ?></strong></div>
                        <div class="info-item"><span class="label">Date fin</span><strong><?php echo $contrat['date_fin']; ?></strong></div>
                        <div class="info-item"><span class="label">Prime</span><strong><?php echo number_format($contrat['montant_prime'],2); ?> DT</strong></div>
                        <div class="info-item"><span class="label">Franchise</span><strong><?php echo number_format($contrat['franchise'],2); ?> DT</strong></div>
                        <?php if ($contrat['formule']): ?>
                        <div class="info-item"><span class="label">Formule</span><strong><?php echo htmlspecialchars($contrat['formule']); ?></strong></div>
                        <?php endif; ?>
                    </div>

                    <div class="contract-banner-right">
                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($contrat['statut']); ?></span>
                        <div class="contract-actions">
                            <!-- Voir -->
                            <button type="button" class="action-btn" onclick="openVoirModal(
                                '<?php echo $contrat['id_contrat']; ?>',
                                '<?php echo htmlspecialchars($contrat['numero_contrat'],ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($contrat['type_contrat'],ENT_QUOTES); ?>',
                                '<?php echo $contrat['date_debut']; ?>',
                                '<?php echo $contrat['date_fin']; ?>',
                                '<?php echo $contrat['montant_prime']; ?>',
                                '<?php echo $contrat['franchise']; ?>',
                                '<?php echo htmlspecialchars($contrat['statut'],ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($contrat['formule']??'',ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($contrat['details_formule']??'',ENT_QUOTES); ?>'
                            )">Voir</button>

                            <!-- Modifier (seulement si pas actif / en attente) -->
                            <?php if ($contrat['statut'] === 'en attente'): ?>
                            <button type="button" class="action-btn secondary" onclick="openEditModal(
                                '<?php echo $contrat['id_contrat']; ?>',
                                '<?php echo htmlspecialchars($contrat['numero_contrat'],ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($contrat['type_contrat'],ENT_QUOTES); ?>',
                                '<?php echo $contrat['date_debut']; ?>',
                                '<?php echo $contrat['date_fin']; ?>',
                                '<?php echo $contrat['montant_prime']; ?>',
                                '<?php echo $contrat['franchise']; ?>',
                                '<?php echo htmlspecialchars($contrat['statut'],ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($contrat['formule']??'',ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($contrat['details_formule']??'',ENT_QUOTES); ?>'
                            )">Modifier</button>
                            <?php endif; ?>

                            <!-- Résilier -->
                            <button type="button" class="action-btn danger"
                                onclick="confirmResiliation(<?php echo $contrat['id_contrat']; ?>)">
                                Résilier
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Garanties -->
                <div id="garantie-<?php echo $contrat['id_contrat']; ?>" class="garanties-box">
                    <div class="garanties-header">
                        <div><h3>Garanties associées</h3><p>Les garanties liées à ce contrat</p></div>
                    </div>
                    <?php foreach ($contratC->getGarantiesByContrat($contrat['id_contrat']) as $g): ?>
                    <div class="garantie-item">
                        <div class="garantie-left">
                            <h4><?php echo htmlspecialchars($g['nom_garantie']); ?></h4>
                            <p><?php echo htmlspecialchars($g['description']); ?></p>
                        </div>
                        <div class="garantie-middle">
                            <span><strong>Plafond :</strong> <?php echo $g['plafond_couverture']; ?> DT</span>
                            <span><strong>Niveau :</strong> <?php echo htmlspecialchars($g['niveau_couverture']); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

<?php endforeach; ?>
            </div>
        </section>
    </main>
</div>

<!-- ===== TOAST ===== -->
<div class="toast-notif" id="toast">
    <i class="bi bi-check-circle-fill"></i>
    <span id="toastMsg">Opération effectuée.</span>
</div>

<!-- ===== MODAL AJOUTER / MODIFIER ===== -->
<div class="modal-overlay" id="formModal">
    <div class="modal-box">
        <div class="modal-header">
            <h2 id="modalTitle">Nouveau contrat</h2>
            <button class="modal-close-btn" onclick="closeModal('formModal')">×</button>
        </div>
        <form method="POST" id="contratForm" action="addcontrat.php" novalidate>
            <input type="hidden" name="id_contrat" id="id_contrat">
            <input type="hidden" name="statut" id="statut_hidden">

            <div class="modal-divider">Informations générales</div>

            <div class="form-group">
                <label>Numéro du contrat *</label>
                <input type="text" name="numero_contrat" id="numero_contrat" placeholder="Ex: CTR-2026-001">
                <span class="error-msg" id="err_numero"></span>
            </div>

            <div class="form-group">
                <label>Type de contrat *</label>
                <select name="type_contrat" id="type_contrat" onchange="onTypeChange(this.value)">
                    <option value="">— Sélectionner —</option>
                    <option value="Auto">🚗 Auto</option>
                    <option value="Sante">❤️ Santé</option>
                    <option value="Habitation">🏠 Habitation</option>
                    <option value="Protection">🛡️ Protection</option>
                </select>
                <span class="error-msg" id="err_type"></span>
            </div>

            <div class="modal-divider">Dates & Montants</div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date début *</label>
                    <input type="date" name="date_debut" id="date_debut">
                    <span class="error-msg" id="err_debut"></span>
                </div>
                <div class="form-group">
                    <label>Date fin *</label>
                    <input type="date" name="date_fin" id="date_fin">
                    <span class="error-msg" id="err_fin"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Prime mensuelle (DT) *</label>
                    <input type="number" name="montant_prime" id="montant_prime" placeholder="0.00" min="0" step="0.01">
                    <span class="error-msg" id="err_prime"></span>
                </div>
                <div class="form-group">
                    <label>Franchise (DT) *</label>
                    <input type="number" name="franchise" id="franchise" placeholder="0.00" min="0" step="0.01">
                    <span class="error-msg" id="err_franchise"></span>
                </div>
            </div>

            <div class="modal-divider">Formule</div>

            <div class="form-group">
                <label>Formule <small style="opacity:0.5">(auto-rempli)</small></label>
                <input type="text" name="formule" id="formule" readonly>
            </div>
            <div class="form-group">
                <label>Détails <small style="opacity:0.5">(auto-rempli)</small></label>
                <textarea name="details_formule" id="details_formule" rows="3" readonly style="resize:none;"></textarea>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn-modal-primary" id="submitBtn">Enregistrer</button>
                <button type="button" class="btn-modal-secondary" onclick="closeModal('formModal')">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL VOIR ===== -->
<div class="modal-overlay" id="viewModal">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Détail du contrat</h2>
            <button class="modal-close-btn" onclick="closeModal('viewModal')">×</button>
        </div>
        <div class="detail-grid">
            <div class="detail-item"><label>N° Contrat</label><div class="detail-value" id="v_numero"></div></div>
            <div class="detail-item"><label>Type</label><div class="detail-value" id="v_type"></div></div>
            <div class="detail-item"><label>Date début</label><div class="detail-value" id="v_debut"></div></div>
            <div class="detail-item"><label>Date fin</label><div class="detail-value" id="v_fin"></div></div>
            <div class="detail-item"><label>Prime</label><div class="detail-value" id="v_prime"></div></div>
            <div class="detail-item"><label>Franchise</label><div class="detail-value" id="v_franchise"></div></div>
            <div class="detail-item"><label>Statut</label><div class="detail-value" id="v_statut"></div></div>
            <div class="detail-item"><label>Formule</label><div class="detail-value" id="v_formule"></div></div>
        </div>
        <div style="margin-top:14px;">
            <label style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:rgba(255,255,255,.45)">Détails formule</label>
            <div class="detail-value" id="v_details" style="margin-top:6px;min-height:44px;line-height:1.6;"></div>
        </div>
    </div>
</div>

<!-- ===== MODAL CONFIRMATION RÉSILIATION ===== -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box" style="max-width:420px;">
        <div class="confirm-icon">⚠️</div>
        <div class="confirm-title">Résilier ce contrat ?</div>
        <div class="confirm-text">Cette action est irréversible. Le contrat sera marqué comme résilié.</div>
        <input type="hidden" id="resilierId">
        <div class="modal-footer" style="justify-content:center;">
            <a id="resilierLink" href="#" class="btn-modal-primary" style="text-align:center;text-decoration:none;max-width:160px;">Confirmer</a>
            <button class="btn-modal-secondary" onclick="closeModal('confirmModal')">Annuler</button>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
<script>
/* ===== FORMULES PAR TYPE ===== */
const FORMULES = {
    'Auto': {
        formule: 'Tous risques',
        details: 'Responsabilité civile, Vol, Incendie, Bris de glace, Dommages tous accidents, Assistance premium'
    },
    'Sante': {
        formule: 'Premium',
        details: 'Consultations, Médicaments, Hospitalisation, Soins dentaires, Optique, Assistance médicale'
    },
    'Habitation': {
        formule: 'Essentielle',
        details: 'Incendie, Dégâts des eaux, Bris de glace, Vol, Responsabilité civile habitation'
    },
    'Protection': {
        formule: 'Premium',
        details: 'Décès, Invalidité, Incapacité de travail, Assistance, Protection juridique'
    }
};

/* ===== REMPLISSAGE AUTO FORMULE + DETAILS ===== */
function onTypeChange(type) {
    const data = FORMULES[type];

    if (!data) {
        document.getElementById('formule').value = '';
        document.getElementById('details_formule').value = '';
        return;
    }

    document.getElementById('formule').value = data.formule;
    document.getElementById('details_formule').value = data.details;
}

/* ===== MODAL HELPERS ===== */
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
});

/* ===== DATE D'AUJOURD'HUI ===== */
function getTodayDate() {
    const today = new Date();
    const y = today.getFullYear();
    const m = String(today.getMonth() + 1).padStart(2, '0');
    const d = String(today.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

/* ===== OPEN ADD MODAL ===== */
function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Nouveau contrat';
    document.getElementById('submitBtn').innerText = 'Enregistrer';
    document.getElementById('contratForm').action = 'addcontrat.php';
    document.getElementById('contratForm').reset();

    document.getElementById('id_contrat').value = '';
    document.getElementById('formule').value = '';
    document.getElementById('details_formule').value = '';

    document.getElementById('date_debut').min = getTodayDate();
    document.getElementById('date_fin').min = getTodayDate();

    clearErrors();
    document.getElementById('formModal').classList.add('active');
}

/* ===== OPEN EDIT MODAL ===== */
function openEditModal(id, numero, type, debut, fin, prime, franchise, statut, formule, details) {
    document.getElementById('modalTitle').innerText = 'Modifier le contrat';
    document.getElementById('submitBtn').innerText = 'Enregistrer les modifications';
    document.getElementById('contratForm').action = 'updatecontrat.php';

    document.getElementById('id_contrat').value = id;
    document.getElementById('numero_contrat').value = numero;
    document.getElementById('type_contrat').value = type;
    document.getElementById('date_debut').value = debut;
    document.getElementById('date_fin').value = fin;
    document.getElementById('montant_prime').value = prime;
    document.getElementById('franchise').value = franchise;
    document.getElementById('statut_hidden').value = statut;

    document.getElementById('date_debut').min = getTodayDate();
    document.getElementById('date_fin').min = debut ? debut : getTodayDate();

    onTypeChange(type);

    if (formule && formule.trim() !== '') {
        document.getElementById('formule').value = formule;
    }
    if (details && details.trim() !== '') {
        document.getElementById('details_formule').value = details;
    }

    clearErrors();
    document.getElementById('formModal').classList.add('active');
}

/* ===== OPEN VIEW MODAL ===== */
function openVoirModal(id, numero, type, debut, fin, prime, franchise, statut, formule, details) {
    document.getElementById('v_numero').innerText = numero;
    document.getElementById('v_type').innerText = type;
    document.getElementById('v_debut').innerText = debut;
    document.getElementById('v_fin').innerText = fin;
    document.getElementById('v_prime').innerText = parseFloat(prime).toFixed(2) + ' DT';
    document.getElementById('v_franchise').innerText = parseFloat(franchise).toFixed(2) + ' DT';
    document.getElementById('v_statut').innerText = statut;
    document.getElementById('v_formule').innerText = formule || '—';
    document.getElementById('v_details').innerText = details || '—';

    document.getElementById('viewModal').classList.add('active');
}

/* ===== CONFIRMATION RÉSILIATION ===== */
function confirmResiliation(id) {
    document.getElementById('resilierLink').href = 'deletecontrat.php?id=' + id;
    document.getElementById('confirmModal').classList.add('active');
}

/* ===== GESTION DES ERREURS ===== */
function setE(errId, msg) {
    const err = document.getElementById(errId);
    if (err) err.innerText = msg;

    const map = {
        err_numero: 'numero_contrat',
        err_type: 'type_contrat',
        err_debut: 'date_debut',
        err_fin: 'date_fin',
        err_prime: 'montant_prime',
        err_franchise: 'franchise'
    };

    const fieldId = map[errId];
    if (fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.add('input-error');
            field.classList.remove('input-valid');
        }
    }
}

function setOk(errId, msg = '') {
    const err = document.getElementById(errId);
    if (err) err.innerText = msg;

    const map = {
        err_numero: 'numero_contrat',
        err_type: 'type_contrat',
        err_debut: 'date_debut',
        err_fin: 'date_fin',
        err_prime: 'montant_prime',
        err_franchise: 'franchise'
    };

    const fieldId = map[errId];
    if (fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('input-error');
            field.classList.add('input-valid');
        }
    }
}

function clearErrors() {
    const errorIds = [
        'err_numero',
        'err_type',
        'err_debut',
        'err_fin',
        'err_prime',
        'err_franchise'
    ];

    errorIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerText = '';
    });

    document.querySelectorAll('#contratForm .input-error, #contratForm .input-valid').forEach(el => {
        el.classList.remove('input-error', 'input-valid');
    });
}

/* ===== VALIDATION EN TEMPS RÉEL ===== */
function validateNumero() {
    const num = document.getElementById('numero_contrat').value.trim();

    if (!num) {
        setE('err_numero', 'Numéro obligatoire.');
        return false;
    }

    if (!/^CTR-\d{4}-\d{3,}$/.test(num)) {
        setE('err_numero', 'Format : CTR-2026-001');
        return false;
    }

    setOk('err_numero', 'Numéro valide.');
    return true;
}

function validateType() {
    const type = document.getElementById('type_contrat').value;

    if (!type) {
        setE('err_type', 'Sélectionnez un type.');
        return false;
    }

    setOk('err_type', 'Type valide.');
    return true;
}

function validateDateDebut() {
    const debut = document.getElementById('date_debut').value;
    const today = getTodayDate();

    if (!debut) {
        setE('err_debut', 'Date début obligatoire.');
        return false;
    }

    if (debut < today) {
        setE('err_debut', 'La date de début doit être à partir d’aujourd’hui.');
        return false;
    }

    setOk('err_debut', 'Date début valide.');
    return true;
}

function validateDateFin() {
    const debut = document.getElementById('date_debut').value;
    const fin = document.getElementById('date_fin').value;

    if (!fin) {
        setE('err_fin', 'Date fin obligatoire.');
        return false;
    }

    if (debut && fin <= debut) {
        setE('err_fin', 'La date fin doit être après la date début.');
        return false;
    }

    setOk('err_fin', 'Date fin valide.');
    return true;
}

function validatePrime() {
    const value = document.getElementById('montant_prime').value;

    if (value === '') {
        setE('err_prime', 'Prime obligatoire.');
        return false;
    }

    if (isNaN(value) || parseFloat(value) <= 0) {
        setE('err_prime', 'La prime doit être strictement positive.');
        return false;
    }

    setOk('err_prime', 'Prime valide.');
    return true;
}

function validateFranchise() {
    const value = document.getElementById('franchise').value;

    if (value === '') {
        setE('err_franchise', 'Franchise obligatoire.');
        return false;
    }

    if (isNaN(value) || parseFloat(value) <= 0) {
        setE('err_franchise', 'La franchise doit être strictement positive.');
        return false;
    }

    setOk('err_franchise', 'Franchise valide.');
    return true;
}

/* ===== EVENTS ===== */
document.addEventListener('DOMContentLoaded', function() {
    const typeContrat = document.getElementById('type_contrat');
    const numeroContrat = document.getElementById('numero_contrat');
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const prime = document.getElementById('montant_prime');
    const franchise = document.getElementById('franchise');
    const form = document.getElementById('contratForm');

    if (typeContrat) {
        typeContrat.addEventListener('change', function() {
            onTypeChange(this.value);
            validateType();
        });
    }

    if (numeroContrat) {
        numeroContrat.addEventListener('input', validateNumero);
        numeroContrat.addEventListener('blur', validateNumero);
    }

    if (dateDebut) {
        dateDebut.addEventListener('input', function() {
            validateDateDebut();

            const debut = this.value;
            if (debut) {
                dateFin.min = debut;
                if (dateFin.value && dateFin.value < debut) {
                    dateFin.value = '';
                }
            }

            validateDateFin();
        });

        dateDebut.addEventListener('blur', validateDateDebut);
    }

    if (dateFin) {
        dateFin.addEventListener('input', validateDateFin);
        dateFin.addEventListener('blur', validateDateFin);
    }

    if (prime) {
        prime.addEventListener('input', validatePrime);
        prime.addEventListener('blur', validatePrime);
    }

    if (franchise) {
        franchise.addEventListener('input', validateFranchise);
        franchise.addEventListener('blur', validateFranchise);
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            const okNumero = validateNumero();
            const okType = validateType();
            const okDebut = validateDateDebut();
            const okFin = validateDateFin();
            const okPrime = validatePrime();
            const okFranchise = validateFranchise();

            if (!(okNumero && okType && okDebut && okFin && okPrime && okFranchise)) {
                e.preventDefault();
            }
        });
    }
});

/* ===== TOAST ===== */
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    const i = t.querySelector('i');

    document.getElementById('toastMsg').innerText = msg;

    i.className =
        type === 'success'
            ? 'bi bi-check-circle-fill'
            : type === 'danger'
            ? 'bi bi-x-circle-fill'
            : 'bi bi-info-circle-fill';

    t.className = 'toast-notif toast-' + type + ' show';

    setTimeout(() => t.classList.remove('show'), 3500);
}

<?php if ($msg === 'ajoute'): ?>
window.addEventListener('load', () => showToast('Contrat ajouté avec succès !'));
<?php elseif ($msg === 'modifie'): ?>
window.addEventListener('load', () => showToast('Contrat modifié avec succès !'));
<?php elseif ($msg === 'resilie'): ?>
window.addEventListener('load', () => showToast('Contrat résilié.', 'warning'));
<?php elseif ($msg === 'erreur'): ?>
window.addEventListener('load', () => showToast('Erreur de validation. Vérifiez vos données.', 'danger'));
<?php endif; ?>
</script>
</body>
</html>
