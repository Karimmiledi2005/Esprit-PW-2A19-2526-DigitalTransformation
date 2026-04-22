<?php
$errors = $errors ?? [];
$old    = $old ?? [];
$offre  = $offre ?? [];

$BASE_URL = defined('BASE_URL')
    ? BASE_URL
    : '/final/Esprit-PW-2A19-2526-DigitalTransformation-integration/integration';

$formData = !empty($old) ? $old : $offre;

function fieldValue(array $data, string $key, $default = '')
{
    return htmlspecialchars((string)($data[$key] ?? $default));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier une offre — Protex Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/variables.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/base.css">
    <link rel="stylesheet" href="<?= $BASE_URL ?>/view/BackOffice/assets/css/layout.css">

    <style>
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .page-shell {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .form-panel, .side-panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(20px);
            overflow: hidden;
            animation: fadeUp .4s ease both;
        }

        .form-panel-head {
            padding: 22px 24px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .form-head-left {
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .form-main-icon {
            width: 48px;
            height: 48px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            background: rgba(0,180,216,.12);
            border: 1px solid rgba(0,180,216,.25);
            color: var(--accent);
            font-size: 20px;
            flex-shrink: 0;
        }

        .form-panel-title {
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .form-panel-sub {
            color: var(--text-secondary);
            font-size: 13px;
        }

        .head-badge {
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.05);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .form-panel-body {
            padding: 24px;
        }

        .section-card {
            border: 1px solid rgba(255,255,255,.06);
            background: rgba(255,255,255,.03);
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 18px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .section-title i {
            color: var(--accent);
        }

        .form-grid {
            display: grid;
            gap: 18px;
        }

        .form-row-2 {
            display: grid;
            grid-template-columns: repeat(2,1fr);
            gap: 18px;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: repeat(3,1fr);
            gap: 18px;
        }

        .form-group {
            display: grid;
            gap: 7px;
        }

        .form-label {
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap > i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 14px;
            pointer-events: none;
        }

        .input-wrap.ta > i {
            top: 16px;
            transform: none;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            border-radius: 13px;
            border: 1px solid var(--glass-border);
            background: rgba(255,255,255,.045);
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 14px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            box-sizing: border-box;
        }

        .form-input, .form-select {
            height: 46px;
            padding: 0 14px 0 42px;
        }

        .form-select {
            padding-left: 14px;
        }

        .form-textarea {
            min-height: 110px;
            resize: vertical;
            padding: 14px 14px 14px 42px;
            line-height: 1.55;
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: var(--text-secondary);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: rgba(0,180,216,.5);
            box-shadow: 0 0 0 4px rgba(0,180,216,.08);
        }

        .form-input.error,
        .form-select.error,
        .form-textarea.error {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px rgba(230,57,70,.08);
        }

        .form-select option {
            background: var(--navy-mid);
        }

        .field-error {
            font-size: 11px;
            color: var(--danger);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-hint {
            font-size: 11px;
            color: var(--text-secondary);
        }

        .pricing-box {
            background: rgba(0,180,216,.06);
            border: 1px solid rgba(0,180,216,.16);
            border-radius: 13px;
            padding: 13px 15px;
            display: none;
            margin-top: 12px;
        }

        .pricing-box.show {
            display: block;
        }

        .pricing-box-title {
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .pricing-line {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .pricing-line:last-child {
            margin-bottom: 0;
        }

        .pricing-line strong {
            color: var(--accent);
        }

        .alert-error {
            background: rgba(230,57,70,.08);
            border: 1px solid rgba(230,57,70,.2);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
        }

        .alert-error > i {
            color: var(--danger);
            font-size: 17px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .alert-error strong {
            color: #fff;
            font-size: 13px;
        }

        .alert-error ul {
            margin: 6px 0 0;
            padding-left: 16px;
            color: var(--text-secondary);
            font-size: 13px;
            line-height: 1.75;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid var(--glass-border);
            flex-wrap: wrap;
        }

        .side-panel {
            padding: 20px;
        }

        .side-block {
            border: 1px solid rgba(255,255,255,.06);
            background: rgba(255,255,255,.03);
            border-radius: 17px;
            padding: 17px;
            margin-bottom: 14px;
        }

        .side-block:last-child {
            margin-bottom: 0;
        }

        .side-title {
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 13px;
        }

        .side-title i {
            color: var(--accent);
        }

        .summary-card {
            border-radius: 14px;
            padding: 15px;
            background: linear-gradient(135deg, rgba(0,180,216,.12), rgba(255,255,255,.04));
            border: 1px solid rgba(0,180,216,.16);
        }

        .sum-name {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sum-type {
            color: var(--text-secondary);
            font-size: 12px;
            margin-bottom: 11px;
        }

        .sum-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 7px 0;
            border-bottom: 1px dashed rgba(255,255,255,.08);
            font-size: 12px;
        }

        .sum-row:last-child {
            border-bottom: none;
        }

        .sum-row span:first-child {
            color: var(--text-secondary);
        }

        .sum-row span:last-child {
            color: #fff;
            font-weight: 600;
        }

        .tip-list {
            display: grid;
            gap: 10px;
        }

        .tip-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: var(--text-secondary);
            font-size: 12px;
            line-height: 1.55;
        }

        .tip-item i {
            color: var(--accent);
            margin-top: 1px;
            flex-shrink: 0;
        }

        .progress-mini {
            display: grid;
            gap: 9px;
        }

        .progress-line {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .progress-bar {
            height: 7px;
            border-radius: 999px;
            background: rgba(255,255,255,.06);
            overflow: hidden;
        }

        .progress-fill {
            width: 0%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, rgba(0,180,216,.9), rgba(0,220,180,.9));
            transition: width .25s ease;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: var(--font-body);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: .2s;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--accent-dark);
            box-shadow: 0 4px 12px rgba(0,180,216,.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--accent);
            border: 1px solid rgba(0,180,216,.4);
        }

        .btn-outline:hover {
            background: var(--accent-glow);
        }

        @media(max-width:1100px) {
            .page-shell {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width:768px) {
            .form-row-2,
            .form-row-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">

    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="<?= $BASE_URL ?>/view/FrontOffice/logo.png" alt="logo" width="38" height="38" style="border-radius:9px;flex-shrink:0;">
            <div>
                <div class="logo-text">Protex</div>
                <div class="logo-sub">Back-Office</div>
            </div>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">AD</div>
            <div>
                <div class="user-name">Admin Protex</div>
                <span class="user-role">Administrateur</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">Principal</div>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-grid-1x2"></i> Tableau de bord
            </a>

            <div class="nav-section">Gestion</div>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
            <a class="nav-item active" href="<?= $BASE_URL ?>/controller/OffreController.php?action=index">
                <i class="bi bi-tags"></i> Offres
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/controller/PaiementController.php?action=index">
                <i class="bi bi-credit-card"></i> Paiements
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-file-earmark-text"></i> Contrats
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-shield-exclamation"></i> Sinistres
            </a>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/admin-users.html">
                <i class="bi bi-chat-dots"></i> Réclamations
            </a>

            <div class="nav-section">Compte</div>
            <a class="nav-item" href="<?= $BASE_URL ?>/view/BackOffice/adminprofile.html">
                <i class="bi bi-person-gear"></i> Mon profil
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= $BASE_URL ?>/view/FrontOffice/login.html" class="logout-btn">
                <i class="bi bi-box-arrow-left"></i> Se déconnecter
            </a>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Modifier une offre</div>
                <div class="topbar-sub">Mise à jour des informations</div>
            </div>
            <div class="topbar-actions">
                <a href="<?= $BASE_URL ?>/controller/OffreController.php?action=index" class="btn btn-outline btn-sm">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        <div class="content">

            <div class="page-breadcrumb" style="margin-bottom:20px;">
                <i class="bi bi-house"></i><span>Admin</span>
                <i class="bi bi-chevron-right" style="font-size:10px"></i>
                <a href="<?= $BASE_URL ?>/controller/OffreController.php?action=index" style="color:inherit;text-decoration:none;">Offres</a>
                <i class="bi bi-chevron-right" style="font-size:10px"></i>
                <span>Modifier</span>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Veuillez corriger les erreurs :</strong>
                        <ul>
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars(is_array($e) ? implode(', ', $e) : $e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="page-shell">

                <div class="form-panel">
                    <div class="form-panel-head">
                        <div class="form-head-left">
                            <div class="form-main-icon"><i class="bi bi-pencil-square"></i></div>
                            <div>
                                <div class="form-panel-title">Modifier l'offre #<?= (int)($formData['id_offre'] ?? 0) ?></div>
                                <div class="form-panel-sub">Mettez à jour les informations de cette offre</div>
                            </div>
                        </div>
                        <div class="head-badge">Champs * obligatoires</div>
                    </div>

                    <div class="form-panel-body">
                        <form class="form-grid" method="post" action="<?= $BASE_URL ?>/controller/OffreController.php?action=modifier&id=<?= (int)($formData['id_offre'] ?? ($_GET['id'] ?? 0)) ?>" novalidate>

                            <div class="section-card">
                                <div class="section-title">
                                    <i class="bi bi-info-circle"></i> Informations générales
                                </div>

                                <div class="form-row-2" style="margin-bottom:16px;">
                                    <div class="form-group">
                                        <label class="form-label">Nom de l'offre *</label>
                                        <div class="input-wrap">
                                            <i class="bi bi-card-text"></i>
                                            <input
                                                class="form-input <?= isset($errors['nom_offre']) ? 'error' : '' ?>"
                                                type="text"
                                                name="nom_offre"
                                                id="nomOffre"
                                                placeholder="Ex : Auto Premium"
                                                value="<?= fieldValue($formData, 'nom_offre') ?>"
                                                oninput="updatePreview(); updateProgress();">
                                        </div>
                                        <?php if (isset($errors['nom_offre'])): ?>
                                            <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['nom_offre']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Type *</label>
                                        <select
                                            class="form-select <?= isset($errors['type_offre']) ? 'error' : '' ?>"
                                            name="type_offre"
                                            id="typeOffre"
                                            onchange="updatePreview(); updateProgress();">
                                            <option value="">-- Sélectionner --</option>
                                            <option value="auto" <?= (($formData['type_offre'] ?? '') === 'auto') ? 'selected' : '' ?>>Auto</option>
                                            <option value="sante" <?= (($formData['type_offre'] ?? '') === 'sante') ? 'selected' : '' ?>>Santé</option>
                                            <option value="habitation" <?= (($formData['type_offre'] ?? '') === 'habitation') ? 'selected' : '' ?>>Habitation</option>
                                            <option value="vie" <?= (($formData['type_offre'] ?? '') === 'vie') ? 'selected' : '' ?>>Vie</option>
                                        </select>
                                        <?php if (isset($errors['type_offre'])): ?>
                                            <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['type_offre']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom:16px;">
                                    <label class="form-label">Description *</label>
                                    <div class="input-wrap ta">
                                        <i class="bi bi-text-paragraph"></i>
                                        <textarea
                                            class="form-textarea <?= isset($errors['description']) ? 'error' : '' ?>"
                                            name="description"
                                            id="descriptionOffre"
                                            placeholder="Décrivez l'offre et ses avantages..."
                                            oninput="updateProgress();"><?= fieldValue($formData, 'description') ?></textarea>
                                    </div>
                                    <?php if (isset($errors['description'])): ?>
                                        <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['description']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Couverture *</label>
                                    <div class="input-wrap ta">
                                        <i class="bi bi-shield-check"></i>
                                        <textarea
                                            class="form-textarea <?= isset($errors['couverture']) ? 'error' : '' ?>"
                                            name="couverture"
                                            id="couvertureOffre"
                                            style="min-height:90px"
                                            placeholder="Ex : Tous risques, incendie, vol..."
                                            oninput="updateProgress();"><?= fieldValue($formData, 'couverture') ?></textarea>
                                    </div>
                                    <?php if (isset($errors['couverture'])): ?>
                                        <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['couverture']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="section-card">
                                <div class="section-title">
                                    <i class="bi bi-currency-exchange"></i> Tarification
                                </div>

                                <div class="form-row-3">
                                    <div class="form-group">
                                        <label class="form-label">Prix mensuel (TND) *</label>
                                        <div class="input-wrap">
                                            <i class="bi bi-cash-stack"></i>
                                            <input
                                                class="form-input <?= isset($errors['prix_mensuel']) ? 'error' : '' ?>"
                                                type="number"
                                                name="prix_mensuel"
                                                id="prixMensuel"
                                                step="0.001"
                                                min="0"
                                                placeholder="85.000"
                                                value="<?= fieldValue($formData, 'prix_mensuel') ?>"
                                                oninput="calcEco(); updatePreview(); updateProgress();">
                                        </div>
                                        <?php if (isset($errors['prix_mensuel'])): ?>
                                            <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['prix_mensuel']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Prix annuel (TND) *</label>
                                        <div class="input-wrap">
                                            <i class="bi bi-wallet2"></i>
                                            <input
                                                class="form-input <?= isset($errors['prix_annuel']) ? 'error' : '' ?>"
                                                type="number"
                                                name="prix_annuel"
                                                id="prixAnnuel"
                                                step="0.001"
                                                min="0"
                                                placeholder="950.000"
                                                value="<?= fieldValue($formData, 'prix_annuel') ?>"
                                                oninput="calcEco(); updatePreview(); updateProgress();">
                                        </div>
                                        <?php if (isset($errors['prix_annuel'])): ?>
                                            <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['prix_annuel']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Plafond (TND)</label>
                                        <div class="input-wrap">
                                            <i class="bi bi-graph-up-arrow"></i>
                                            <input
                                                class="form-input <?= isset($errors['plafond']) ? 'error' : '' ?>"
                                                type="number"
                                                name="plafond"
                                                id="plafondOffre"
                                                step="0.001"
                                                min="0"
                                                placeholder="50000.000"
                                                value="<?= fieldValue($formData, 'plafond') ?>"
                                                oninput="updatePreview();">
                                        </div>
                                        <?php if (isset($errors['plafond'])): ?>
                                            <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['plafond']) ?></span>
                                        <?php else: ?>
                                            <span class="form-hint">Optionnel</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="pricing-box" id="prixInfo">
                                    <div class="pricing-box-title">Analyse tarifaire</div>
                                    <div class="pricing-line"><span>Mensuel × 12</span><strong id="m12Val">—</strong></div>
                                    <div class="pricing-line"><span>Économie annuelle</span><strong id="ecoVal">—</strong></div>
                                </div>
                            </div>

                            <div class="section-card">
                                <div class="section-title">
                                    <i class="bi bi-sliders"></i> Conditions
                                </div>

                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label class="form-label">Durée minimale (mois) *</label>
                                        <div class="input-wrap">
                                            <i class="bi bi-calendar-range"></i>
                                            <input
                                                class="form-input <?= isset($errors['duree_min']) ? 'error' : '' ?>"
                                                type="number"
                                                name="duree_min"
                                                id="dureeMin"
                                                min="1"
                                                placeholder="1"
                                                value="<?= fieldValue($formData, 'duree_min', '1') ?>"
                                                oninput="updatePreview(); updateProgress();">
                                        </div>
                                        <?php if (isset($errors['duree_min'])): ?>
                                            <span class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['duree_min']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Statut *</label>
                                        <select class="form-select" name="statut" id="statutOffre" onchange="updatePreview();">
                                            <option value="active" <?= (($formData['statut'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                                            <option value="suspendue" <?= (($formData['statut'] ?? '') === 'suspendue') ? 'selected' : '' ?>>Suspendue</option>
                                            <option value="archivee" <?= (($formData['statut'] ?? '') === 'archivee') ? 'selected' : '' ?>>Archivée</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="<?= $BASE_URL ?>/controller/OffreController.php?action=index" class="btn btn-outline">
                                    <i class="bi bi-x-lg"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Enregistrer les modifications
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                <aside class="side-panel">
                    <div class="side-block">
                        <div class="side-title"><i class="bi bi-eye"></i> Aperçu rapide</div>
                        <div class="summary-card">
                            <div class="sum-name" id="previewNom"><?= fieldValue($formData, 'nom_offre', 'Offre') ?></div>
                            <div class="sum-type" id="previewType">
                                <?= !empty($formData['type_offre']) ? 'Assurance ' . ucfirst($formData['type_offre']) : 'Type non sélectionné' ?>
                            </div>
                            <div class="sum-row"><span>Mensuel</span><span id="previewMensuel">—</span></div>
                            <div class="sum-row"><span>Annuel</span><span id="previewAnnuel">—</span></div>
                            <div class="sum-row"><span>Plafond</span><span id="previewPlafond">—</span></div>
                            <div class="sum-row"><span>Durée min.</span><span id="previewDuree">—</span></div>
                            <div class="sum-row"><span>Statut</span><span id="previewStatut"><?= fieldValue($formData, 'statut', 'Active') ?></span></div>
                        </div>
                    </div>

                    <div class="side-block">
                        <div class="side-title"><i class="bi bi-bar-chart"></i> Complétion</div>
                        <div class="progress-mini">
                            <div class="progress-line">
                                <span>Avancement</span>
                                <strong id="progressText" style="color:#fff;">0%</strong>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" id="progressFill"></div>
                            </div>
                        </div>
                    </div>

                    <div class="side-block">
                        <div class="side-title"><i class="bi bi-lightbulb"></i> Conseils</div>
                        <div class="tip-list">
                            <div class="tip-item">
                                <i class="bi bi-check2-circle"></i>
                                <span>Vérifie que le nom reste clair et compréhensible pour l’utilisateur.</span>
                            </div>
                            <div class="tip-item">
                                <i class="bi bi-check2-circle"></i>
                                <span>Le prix annuel doit rester cohérent avec le prix mensuel.</span>
                            </div>
                            <div class="tip-item">
                                <i class="bi bi-check2-circle"></i>
                                <span>Une couverture claire facilite la compréhension de l’offre.</span>
                            </div>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </main>
</div>

<script src="<?= $BASE_URL ?>/view/BackOffice/assets/js/main.js"></script>
<script>
    function fmt(v) {
        const n = parseFloat(v);
        return (!isNaN(n) && n > 0) ? n.toFixed(3) + ' TND' : '—';
    }

    function calcEco() {
        const m = parseFloat(document.getElementById('prixMensuel').value);
        const a = parseFloat(document.getElementById('prixAnnuel').value);
        const box = document.getElementById('prixInfo');

        if (!m || m <= 0) {
            box.classList.remove('show');
            return;
        }

        const m12 = (m * 12).toFixed(3) + ' TND';
        const eco = a ? Math.max(0, (m * 12) - a).toFixed(3) + ' TND' : '0.000 TND';

        document.getElementById('m12Val').textContent = m12;
        document.getElementById('ecoVal').textContent = eco;
        box.classList.add('show');
    }

    function updatePreview() {
        const nom = document.getElementById('nomOffre').value.trim();
        const type = document.getElementById('typeOffre').value;
        const statut = document.getElementById('statutOffre').value;

        document.getElementById('previewNom').textContent = nom || 'Offre';
        document.getElementById('previewType').textContent = type
            ? 'Assurance ' + type.charAt(0).toUpperCase() + type.slice(1)
            : 'Type non sélectionné';

        document.getElementById('previewMensuel').textContent = fmt(document.getElementById('prixMensuel').value);
        document.getElementById('previewAnnuel').textContent = fmt(document.getElementById('prixAnnuel').value);
        document.getElementById('previewPlafond').textContent = fmt(document.getElementById('plafondOffre').value);

        const dur = document.getElementById('dureeMin').value;
        document.getElementById('previewDuree').textContent = dur ? dur + ' mois' : '—';

        document.getElementById('previewStatut').textContent = statut
            ? statut.charAt(0).toUpperCase() + statut.slice(1)
            : '—';
    }

    function updateProgress() {
        const fields = [
            document.getElementById('nomOffre').value.trim(),
            document.getElementById('typeOffre').value.trim(),
            document.getElementById('descriptionOffre').value.trim(),
            document.getElementById('couvertureOffre').value.trim(),
            document.getElementById('prixMensuel').value.trim(),
            document.getElementById('prixAnnuel').value.trim(),
            document.getElementById('dureeMin').value.trim()
        ];

        const pct = Math.round(fields.filter(v => v !== '').length / fields.length * 100);
        document.getElementById('progressFill').style.width = pct + '%';
        document.getElementById('progressText').textContent = pct + '%';
    }

    updatePreview();
    updateProgress();
    calcEco();
</script>
</body>
</html>