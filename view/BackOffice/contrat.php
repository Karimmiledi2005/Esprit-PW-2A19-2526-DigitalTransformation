<?php
require_once '../../controller/contratController.php';

$contratC = new ContratController();
$list = $contratC->listContrats();
$total = count($list);
$attente = 0;
$valides = 0;
$refuses = 0;
$resilies = 0;

foreach ($list as $c) {
    if ($c['statut'] == 'en attente') $attente++;
    elseif ($c['statut'] == 'actif') $valides++;
    elseif ($c['statut'] == 'refuse' || $c['statut'] == 'refusé') $refuses++;
    elseif ($c['statut'] == 'resilie' || $c['statut'] == 'résilié') $resilies++;
}
$typeFiltre = $_GET['type'] ?? '';

// Messages de succès/erreur
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office Contrats - Protex</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/templatemo-glass-admin-style.css">
    <style>
        /* ===== MODAL STYLES ===== */
        .modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(6px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }

        .modal-box {
            background: rgba(18, 25, 48, 0.95);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 20px;
            padding: 36px 40px;
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0,0,0,0.5);
            animation: modalIn 0.25s ease;
        }
        @keyframes modalIn {
            from { opacity:0; transform: scale(0.95) translateY(10px); }
            to   { opacity:1; transform: scale(1) translateY(0); }
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 1.3rem;
            color: #fff;
            font-weight: 600;
        }
        .modal-close {
            background: rgba(255,255,255,0.08);
            border: none;
            color: #ccc;
            width: 34px; height: 34px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .modal-close:hover { background: rgba(255,255,255,0.18); color: #fff; }

        /* Form fields inside modal */
        .modal-box .form-group { margin-bottom: 16px; }
        .modal-box label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: rgba(255,255,255,0.7);
            margin-bottom: 6px;
        }
        .modal-box input,
        .modal-box select,
        .modal-box textarea {
            width: 100%;
            padding: 11px 14px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
            transition: border-color 0.2s, background 0.2s;
        }
        .modal-box input:focus,
        .modal-box select:focus,
        .modal-box textarea:focus {
            outline: none;
            border-color: rgba(99,179,237,0.6);
            background: rgba(255,255,255,0.11);
        }
        .modal-box select option { background: #1a233a; color: #fff; }
        .modal-box .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .modal-box .input-error { border-color: #f56565 !important; }
        .modal-box .input-valid { border-color: #68d391 !important; }
        .modal-box .error-msg {
            color: #fc8181;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }
        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn-save {
            flex: 1;
            padding: 12px;
            background: linear-gradient(135deg, #4f9ef8, #5a67d8);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn-save:hover { opacity: 0.88; transform: translateY(-1px); }
        .btn-cancel {
            padding: 12px 20px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            color: #ccc;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-cancel:hover { background: rgba(255,255,255,0.14); color: #fff; }

        /* ===== STATUS BADGES ===== */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-attente  { background: rgba(237,189,66,0.2); color: #edbd42; border: 1px solid rgba(237,189,66,0.4); }
        .badge-actif    { background: rgba(72,199,142,0.2); color: #48c78e; border: 1px solid rgba(72,199,142,0.4); }
        .badge-refuse   { background: rgba(240,82,82,0.2);  color: #f05252; border: 1px solid rgba(240,82,82,0.4); }
        .badge-resilie  { background: rgba(148,163,184,0.2); color: #94a3b8; border: 1px solid rgba(148,163,184,0.4); }

        /* ===== ACTION BUTTONS in table ===== */
        .action-btns { display: flex; gap: 6px; flex-wrap: wrap; }
        .btn-xs {
            padding: 5px 10px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: opacity 0.2s;
        }
        .btn-xs:hover { opacity: 0.8; }
        .btn-xs-info    { background: rgba(99,179,237,0.2); color: #63b3ed; border: 1px solid rgba(99,179,237,0.4); }
        .btn-xs-success { background: rgba(72,199,142,0.2); color: #48c78e; border: 1px solid rgba(72,199,142,0.4); }
        .btn-xs-danger  { background: rgba(240,82,82,0.2);  color: #f05252; border: 1px solid rgba(240,82,82,0.4); }
        .btn-xs-warning { background: rgba(237,189,66,0.2); color: #edbd42; border: 1px solid rgba(237,189,66,0.4); }

        /* ===== ALERT ===== */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-success { background: rgba(72,199,142,0.15); border: 1px solid rgba(72,199,142,0.4); color: #48c78e; }
        .alert-danger  { background: rgba(240,82,82,0.15);  border: 1px solid rgba(240,82,82,0.4);  color: #f05252; }

        /* ===== DETAIL VIEW in modal ===== */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .detail-item label { font-size: 12px; color: rgba(255,255,255,0.5); margin-bottom: 4px; }
        .detail-item .detail-value {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 9px 13px;
            font-size: 14px;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="logo.png" alt="logo" width="50" height="50">
                <span class="logo-text">Protex</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-section">
                    <span class="nav-section-title">Main Menu</span>
                    <ul>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                                </svg> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="contrat.php" class="nav-link active">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg> Contrats
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="garantie.php" class="nav-link">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg> Garanties
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">AP</div>
                    <div class="user-info">
                        <div class="user-name">Admin Protex</div>
                        <div class="user-role">Gestionnaire</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <nav class="navbar">
                <div class="page-header">
                    <h1 class="page-title">Gestion des contrats</h1>
                    <div class="page-breadcrumb">
                        <a href="#">Dashboard</a> <span>/</span> <span>Contrats</span>
                    </div>
                </div>
                <div class="navbar-right">
                    <button class="card-btn success-btn" onclick="openAddModal()">
                        <i class="bi bi-plus-circle"></i> Nouveau contrat
                    </button>
                </div>
            </nav>

            <?php if ($msg === 'valide'): ?>
                <div class="alert alert-success"><i class="bi bi-check-circle"></i> Contrat validé avec succès.</div>
            <?php elseif ($msg === 'refuse'): ?>
                <div class="alert alert-danger"><i class="bi bi-x-circle"></i> Contrat refusé.</div>
            <?php elseif ($msg === 'supprime'): ?>
                <div class="alert alert-success"><i class="bi bi-trash"></i> Contrat supprimé.</div>
            <?php endif; ?>

            <?php if ($attente > 0): ?>
            <div class="glass-card" style="margin-bottom:20px; padding:20px; border-left:4px solid #edbd42;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h3 style="margin:0;"><i class="bi bi-bell-fill" style="color:#edbd42"></i> <?php echo $attente; ?> contrat(s) en attente de validation</h3>
                        <p style="margin:4px 0 0; opacity:0.65; font-size:13px;">Nouvelles demandes depuis le front office</p>
                    </div>
                    <a href="#table-contrats" class="card-btn success-btn">Voir <i class="bi bi-arrow-down"></i></a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <section class="stats-grid">
                <div class="glass-card glass-card-3d stat-card">
                    <div class="stat-card-inner">
                        <div class="stat-info">
                            <h3>Total contrats</h3>
                            <div class="stat-value"><?php echo $total; ?></div>
                        </div>
                        <div class="stat-icon cyan">📄</div>
                    </div>
                </div>
                <div class="glass-card glass-card-3d stat-card">
                    <div class="stat-card-inner">
                        <div class="stat-info">
                            <h3>En attente</h3>
                            <div class="stat-value"><?php echo $attente; ?></div>
                        </div>
                        <div class="stat-icon magenta">⏳</div>
                    </div>
                </div>
                <div class="glass-card glass-card-3d stat-card">
                    <div class="stat-card-inner">
                        <div class="stat-info">
                            <h3>Validés</h3>
                            <div class="stat-value"><?php echo $valides; ?></div>
                        </div>
                        <div class="stat-icon purple">✅</div>
                    </div>
                </div>
                <div class="glass-card glass-card-3d stat-card">
                    <div class="stat-card-inner">
                        <div class="stat-info">
                            <h3>Refusés</h3>
                            <div class="stat-value"><?php echo $refuses; ?></div>
                        </div>
                        <div class="stat-icon success">❌</div>
                    </div>
                </div>
            </section>

            <!-- Filtre -->
            <div class="glass-card" style="margin-bottom:20px; padding:16px 20px;">
                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                    <span style="font-size:13px; opacity:0.6; margin-right:4px;"><i class="bi bi-funnel"></i> Filtrer :</span>
                    <a href="contrat.php" class="card-btn <?php echo $typeFiltre==='' ? 'success-btn' : ''; ?>">Tous</a>
                    <a href="contrat.php?type=Auto" class="card-btn <?php echo $typeFiltre==='Auto' ? 'success-btn' : ''; ?>">🚗 Auto</a>
                    <a href="contrat.php?type=Sante" class="card-btn <?php echo $typeFiltre==='Sante' ? 'success-btn' : ''; ?>">❤️ Santé</a>
                    <a href="contrat.php?type=Habitation" class="card-btn <?php echo $typeFiltre==='Habitation' ? 'success-btn' : ''; ?>">🏠 Habitation</a>
                    <a href="contrat.php?type=Protection" class="card-btn <?php echo $typeFiltre==='Protection' ? 'success-btn' : ''; ?>">🛡️ Protection</a>
                </div>
            </div>

            <!-- TABLE -->
            <section class="content-grid" style="grid-template-columns:1fr;" id="table-contrats">
                <div class="glass-card table-card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Gestion des contrats</h2>
                            <p class="card-subtitle">Validation et suivi des demandes de souscription</p>
                        </div>
                        <div class="card-actions">
                            <input type="text" id="searchInput" placeholder="🔍 Rechercher..." onkeyup="filterTable()"
                                style="padding:8px 12px; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.15);
                                       border-radius:8px; color:#fff; font-size:13px; width:200px;">
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table class="data-table" id="mainTable">
                            <thead>
                                <tr>
                                    <th>N° Contrat</th>
                                    <th>Type</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Prime</th>
                                    <th>Franchise</th>
                                    <th>Formule</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($list as $c):
                                if ($typeFiltre !== '' && $c['type_contrat'] !== $typeFiltre) continue;
                                $statut = $c['statut'];
                                $badgeClass = 'badge-attente';
                                if ($statut === 'actif') $badgeClass = 'badge-actif';
                                elseif ($statut === 'refuse' || $statut === 'refusé') $badgeClass = 'badge-refuse';
                                elseif ($statut === 'resilie' || $statut === 'résilié') $badgeClass = 'badge-resilie';
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['numero_contrat']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['type_contrat']); ?></td>
                                <td><?php echo htmlspecialchars($c['date_debut']); ?></td>
                                <td><?php echo htmlspecialchars($c['date_fin']); ?></td>
                                <td><?php echo number_format($c['montant_prime'], 2); ?> DT</td>
                                <td><?php echo number_format($c['franchise'], 2); ?> DT</td>
                                <td><?php echo htmlspecialchars($c['formule'] ?? '-'); ?></td>
                                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($statut); ?></span></td>
                                <td>
                                    <div class="action-btns">
                                        <!-- Voir -->
                                        <button class="btn-xs btn-xs-info"
                                            onclick="openViewModal(
                                                '<?php echo $c['id_contrat']; ?>',
                                                '<?php echo htmlspecialchars($c['numero_contrat'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($c['type_contrat'], ENT_QUOTES); ?>',
                                                '<?php echo $c['date_debut']; ?>',
                                                '<?php echo $c['date_fin']; ?>',
                                                '<?php echo $c['montant_prime']; ?>',
                                                '<?php echo $c['franchise']; ?>',
                                                '<?php echo htmlspecialchars($statut, ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($c['formule'] ?? '', ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($c['details_formule'] ?? '', ENT_QUOTES); ?>'
                                            )">
                                            <i class="bi bi-eye"></i> Voir
                                        </button>

                                        <?php if ($statut === 'en attente'): ?>
                                        <!-- Valider -->
                                        <a href="validercontrat.php?id=<?php echo $c['id_contrat']; ?>"
                                           class="btn-xs btn-xs-success"
                                           onclick="return confirm('Valider ce contrat ?')">
                                            <i class="bi bi-check-lg"></i> Valider
                                        </a>
                                        <!-- Refuser -->
                                        <a href="refusercontrat.php?id=<?php echo $c['id_contrat']; ?>"
                                           class="btn-xs btn-xs-warning"
                                           onclick="return confirm('Refuser ce contrat ?')">
                                            <i class="bi bi-x-lg"></i> Refuser
                                        </a>
                                        <?php endif; ?>

                                        <?php if ($statut !== 'resilie' && $statut !== 'résilié'): ?>
                                        <!-- Modifier -->
                                        <button class="btn-xs btn-xs-info"
                                            onclick="openEditModal(
                                                '<?php echo $c['id_contrat']; ?>',
                                                '<?php echo htmlspecialchars($c['numero_contrat'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($c['type_contrat'], ENT_QUOTES); ?>',
                                                '<?php echo $c['date_debut']; ?>',
                                                '<?php echo $c['date_fin']; ?>',
                                                '<?php echo $c['montant_prime']; ?>',
                                                '<?php echo $c['franchise']; ?>',
                                                '<?php echo htmlspecialchars($statut, ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($c['formule'] ?? '', ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($c['details_formule'] ?? '', ENT_QUOTES); ?>'
                                            )">
                                            <i class="bi bi-pencil"></i> Modifier
                                        </button>
                                        <?php endif; ?>

                                        <!-- Supprimer -->
                                        <a href="deletecontrat.php?id=<?php echo $c['id_contrat']; ?>"
                                           class="btn-xs btn-xs-danger"
                                           onclick="return confirm('Supprimer définitivement ce contrat ?')">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- ===== MODAL AJOUTER / MODIFIER ===== -->
    <div class="modal-overlay" id="formModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2 id="modalTitle">Nouveau contrat</h2>
                <button class="modal-close" onclick="closeFormModal()">×</button>
            </div>
            <form method="POST" id="contratForm" action="addcontrat.php" novalidate>
                <input type="hidden" name="id_contrat" id="id_contrat">

                <div class="form-group">
                    <label>Numéro du contrat *</label>
                    <input type="text" name="numero_contrat" id="numero_contrat" placeholder="Ex: CTR-2026-001">
                    <span class="error-msg" id="err_numero"></span>
                </div>

                <div class="form-group">
                    <label>Type de contrat *</label>
                    <select name="type_contrat" id="type_contrat" onchange="autoFillFormule(this.value)">
                        <option value="">-- Sélectionner --</option>
                        <option value="Auto">Auto</option>
                        <option value="Sante">Santé</option>
                        <option value="Habitation">Habitation</option>
                        <option value="Protection">Protection</option>
                    </select>
                    <span class="error-msg" id="err_type"></span>
                </div>

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
                        <label>Prime (DT) *</label>
                        <input type="number" name="montant_prime" id="montant_prime" placeholder="0.00" min="0" step="0.01">
                        <span class="error-msg" id="err_prime"></span>
                    </div>
                    <div class="form-group">
                        <label>Franchise (DT) *</label>
                        <input type="number" name="franchise" id="franchise" placeholder="0.00" min="0" step="0.01">
                        <span class="error-msg" id="err_franchise"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Statut *</label>
                    <select name="statut" id="statut">
                        <option value="en attente">En attente</option>
                        <option value="actif">Actif</option>
                        <option value="refuse">Refusé</option>
                        <option value="resilie">Résilié</option>
                    </select>
                    <span class="error-msg" id="err_statut"></span>
                </div>

                <div class="form-group">
                    <label>Formule</label>
                    <input type="text" name="formule" id="formule" readonly
                           style="opacity:0.6; cursor:not-allowed;">
                </div>

                <div class="form-group">
                    <label>Détails de la formule</label>
                    <textarea name="details_formule" id="details_formule" rows="3" readonly
                              style="opacity:0.6; cursor:not-allowed; resize:none;"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-save" id="submitBtn">Enregistrer</button>
                    <button type="button" class="btn-cancel" onclick="closeFormModal()">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL VOIR (lecture seule) ===== -->
    <div class="modal-overlay" id="viewModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Détail du contrat</h2>
                <button class="modal-close" onclick="document.getElementById('viewModal').classList.remove('active')">×</button>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>N° Contrat</label>
                    <div class="detail-value" id="v_numero"></div>
                </div>
                <div class="detail-item">
                    <label>Type</label>
                    <div class="detail-value" id="v_type"></div>
                </div>
                <div class="detail-item">
                    <label>Date début</label>
                    <div class="detail-value" id="v_debut"></div>
                </div>
                <div class="detail-item">
                    <label>Date fin</label>
                    <div class="detail-value" id="v_fin"></div>
                </div>
                <div class="detail-item">
                    <label>Prime</label>
                    <div class="detail-value" id="v_prime"></div>
                </div>
                <div class="detail-item">
                    <label>Franchise</label>
                    <div class="detail-value" id="v_franchise"></div>
                </div>
                <div class="detail-item">
                    <label>Statut</label>
                    <div class="detail-value" id="v_statut"></div>
                </div>
                <div class="detail-item">
                    <label>Formule</label>
                    <div class="detail-value" id="v_formule"></div>
                </div>
            </div>
            <div style="margin-top:16px;">
                <label style="font-size:12px; color:rgba(255,255,255,0.5);">Détails formule</label>
                <div class="detail-value" id="v_details" style="margin-top:6px; min-height:50px;"></div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <footer class="site-footer">
        <p>Copyright &copy; 2026 Protex. Administration.</p>
    </footer>

    <script src="js/templatemo-glass-admin-script.js"></script>
    <script>
    /* ===== FORMULE AUTO-FILL ===== */
    const formuleData = {
        'Auto':       { formule: 'Tous risques',  details: 'Responsabilité civile, Vol, Incendie, Bris de glace, Dommages tous accidents, Assistance premium' },
        'Sante':      { formule: 'Premium',        details: 'Consultations, Médicaments, Hospitalisation, Soins dentaires, Optique, Assistance médicale' },
        'Habitation': { formule: 'Essentielle',    details: 'Incendie, Dégâts des eaux, Bris de glace, Vol, Responsabilité civile habitation' },
        'Protection': { formule: 'Premium',        details: 'Décès, Invalidité, Incapacité de travail, Assistance, Protection juridique' }
    };
    const minValues = {
        'Auto':       { prime: 120, franchise: 80 },
        'Sante':      { prime: 180, franchise: 50 },
        'Habitation': { prime: 320, franchise: 150 },
        'Protection': { prime: 140, franchise: 70 }
    };

    function autoFillFormule(type) {
        const d = formuleData[type] || { formule: '', details: '' };
        document.getElementById('formule').value = d.formule;
        document.getElementById('details_formule').value = d.details;
    }

    /* ===== MODAL AJOUTER ===== */
    function openAddModal() {
        document.getElementById('modalTitle').innerText = 'Nouveau contrat';
        document.getElementById('submitBtn').innerText = 'Enregistrer';
        document.getElementById('contratForm').action = 'addcontrat.php';
        document.getElementById('id_contrat').value = '';

        // Reset
        document.getElementById('contratForm').reset();
        document.getElementById('date_debut').min = getTodayDate();
        clearErrors();
        document.getElementById('formModal').classList.add('active');
    }

    /* ===== MODAL MODIFIER ===== */
    function openEditModal(id, numero, type, debut, fin, prime, franchise, statut, formule, details) {
        document.getElementById('modalTitle').innerText = 'Modifier le contrat';
        document.getElementById('submitBtn').innerText = 'Enregistrer';
        document.getElementById('contratForm').action = 'updatecontrat.php';
        document.getElementById('id_contrat').value = id;
        document.getElementById('numero_contrat').value = numero;
        document.getElementById('type_contrat').value = type;
        document.getElementById('date_debut').value = debut;
        document.getElementById('date_fin').value = fin;
        document.getElementById('montant_prime').value = prime;
        document.getElementById('franchise').value = franchise;
        document.getElementById('statut').value = statut;
        document.getElementById('formule').value = formule;
        document.getElementById('details_formule').value = details;
        clearErrors();
        document.getElementById('formModal').classList.add('active');
    }

    function closeFormModal() {
        document.getElementById('formModal').classList.remove('active');
    }

    /* ===== MODAL VOIR ===== */
    function openViewModal(id, numero, type, debut, fin, prime, franchise, statut, formule, details) {
        document.getElementById('v_numero').innerText   = numero;
        document.getElementById('v_type').innerText     = type;
        document.getElementById('v_debut').innerText    = debut;
        document.getElementById('v_fin').innerText      = fin;
        document.getElementById('v_prime').innerText    = parseFloat(prime).toFixed(2) + ' DT';
        document.getElementById('v_franchise').innerText= parseFloat(franchise).toFixed(2) + ' DT';
        document.getElementById('v_statut').innerText   = statut;
        document.getElementById('v_formule').innerText  = formule || '-';
        document.getElementById('v_details').innerText  = details || '-';
        document.getElementById('viewModal').classList.add('active');
    }

    /* ===== FERMER EN CLIQUANT DEHORS ===== */
    document.querySelectorAll('.modal-overlay').forEach(m => {
        m.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    });

    /* ===== VALIDATION ===== */
    function getTodayDate() {
        const d = new Date();
        return d.toISOString().split('T')[0];
    }
    function setError(errId, msg) {
        const el = document.getElementById(errId);
        if (el) el.innerText = msg;
        const field = errId.replace('err_', '');
        const fid = { 'numero':'numero_contrat','type':'type_contrat','debut':'date_debut','fin':'date_fin','prime':'montant_prime','franchise':'franchise','statut':'statut' }[field];
        if (fid) { const f = document.getElementById(fid); if (f) { f.classList.add('input-error'); f.classList.remove('input-valid'); } }
    }
    function setOk(errId) {
        const el = document.getElementById(errId);
        if (el) el.innerText = '';
        const field = errId.replace('err_', '');
        const fid = { 'numero':'numero_contrat','type':'type_contrat','debut':'date_debut','fin':'date_fin','prime':'montant_prime','franchise':'franchise','statut':'statut' }[field];
        if (fid) { const f = document.getElementById(fid); if (f) { f.classList.remove('input-error'); f.classList.add('input-valid'); } }
    }
    function clearErrors() {
        ['err_numero','err_type','err_debut','err_fin','err_prime','err_franchise','err_statut'].forEach(id => {
            const el = document.getElementById(id); if (el) el.innerText = '';
        });
        document.querySelectorAll('#contratForm .input-error, #contratForm .input-valid').forEach(el => {
            el.classList.remove('input-error','input-valid');
        });
    }

    document.getElementById('contratForm').addEventListener('submit', function(e) {
        let ok = true;
        const isEdit = document.getElementById('contratForm').action.includes('update');

        // Numéro
        const num = document.getElementById('numero_contrat').value.trim();
        if (!num) { setError('err_numero','Le numéro est obligatoire.'); ok=false; }
        else if (!/^CTR-\d{4}-\d{3,}$/.test(num)) { setError('err_numero','Format: CTR-2026-001'); ok=false; }
        else setOk('err_numero');

        // Type
        const type = document.getElementById('type_contrat').value;
        if (!type) { setError('err_type','Le type est obligatoire.'); ok=false; } else setOk('err_type');

        // Dates
        const debut = document.getElementById('date_debut').value;
        const fin   = document.getElementById('date_fin').value;
        if (!debut) { setError('err_debut','Date début obligatoire.'); ok=false; }
        else if (!isEdit && debut < getTodayDate()) { setError('err_debut','La date doit être future.'); ok=false; }
        else setOk('err_debut');

        if (!fin) { setError('err_fin','Date fin obligatoire.'); ok=false; }
        else if (debut && fin <= debut) { setError('err_fin','Doit être après la date de début.'); ok=false; }
        else setOk('err_fin');

        // Prime
        const prime = parseFloat(document.getElementById('montant_prime').value);
        const minP = type ? (minValues[type]?.prime || 0) : 0;
        if (!document.getElementById('montant_prime').value) { setError('err_prime','La prime est obligatoire.'); ok=false; }
        else if (isNaN(prime) || prime < minP) { setError('err_prime','Minimum: ' + minP + ' DT'); ok=false; }
        else setOk('err_prime');

        // Franchise
        const franc = parseFloat(document.getElementById('franchise').value);
        const minF = type ? (minValues[type]?.franchise || 0) : 0;
        if (!document.getElementById('franchise').value) { setError('err_franchise','La franchise est obligatoire.'); ok=false; }
        else if (isNaN(franc) || franc < minF) { setError('err_franchise','Minimum: ' + minF + ' DT'); ok=false; }
        else setOk('err_franchise');

        // Statut
        if (!document.getElementById('statut').value) { setError('err_statut','Le statut est obligatoire.'); ok=false; } else setOk('err_statut');

        if (!ok) e.preventDefault();
    });

    /* ===== SEARCH TABLE ===== */
    function filterTable() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#mainTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    }
    </script>
</body>
</html>
