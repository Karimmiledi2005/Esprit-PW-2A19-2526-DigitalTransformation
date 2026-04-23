<?php
session_start();

require_once __DIR__ . '/../../config/database.php';

$clientNom = $_SESSION['nom'] ?? '';
$clientPrenom = $_SESSION['prenom'] ?? '';
$clientEmail = $_SESSION['email'] ?? '';

$db = config::getConnexion();

function h(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = strtolower((string) $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'formule';
}

function currentDatabase(PDO $db): string {
    return (string) $db->query("SELECT DATABASE()")->fetchColumn();
}

function columnExists(PDO $db, string $table, string $column): bool {
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = :db_name
          AND TABLE_NAME = :table_name
          AND COLUMN_NAME = :column_name
    ");
    $stmt->execute([
        'db_name' => currentDatabase($db),
        'table_name' => $table,
        'column_name' => $column,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

function getFormulaLevelColumn(PDO $db): ?string {
    $possible = ['niveau_formule', 'niveau', 'niveau_couverture'];

    foreach ($possible as $column) {
        if (columnExists($db, 'formule', $column)) {
            return $column;
        }
    }

    return null;
}

function getGarantieFormulaColumn(PDO $db): ?string {
    $possible = ['id_formule', 'formule_id'];

    foreach ($possible as $column) {
        if (columnExists($db, 'garantie', $column)) {
            return $column;
        }
    }

    return null;
}

function normalizeGarantieLevel(?string $value): string {
    $value = strtolower(trim((string) $value));

    if ($value === '') {
        return 'basique';
    }

    if (in_array($value, ['basique', 'base', 'inclus', 'incluse', 'fixe', 'standard', 'obligatoire'], true)) {
        return 'basique';
    }

    if (in_array($value, ['option', 'optionnel', 'optionnelle', 'facultatif', 'facultative'], true)) {
        return 'option';
    }

    if (in_array($value, ['non disponible', 'indisponible', 'aucun', 'non inclus', 'exclu'], true)) {
        return 'non disponible';
    }

    return $value;
}

function garantieIcon(string $level): string {
    return match ($level) {
        'basique' => 'bi bi-check2-circle',
        'option' => 'bi bi-plus-circle',
        'non disponible' => 'bi bi-x-circle',
        default => 'bi bi-check2-circle',
    };
}

function garantieStatusTag(string $level): string {
    return match ($level) {
        'basique' => '<strong>(basique)</strong>',
        'option' => '<span>(option)</span>',
        'non disponible' => '<span>(non disponible)</span>',
        default => '<span>(' . h($level) . ')</span>',
    };
}

$formuleLevelColumn = getFormulaLevelColumn($db);
$garantieFormulaColumn = getGarantieFormulaColumn($db);

$categorieStmt = $db->prepare("SELECT * FROM categorie WHERE LOWER(nom_categorie) = 'protection' LIMIT 1");
$categorieStmt->execute();
$categorie = $categorieStmt->fetch(PDO::FETCH_ASSOC) ?: null;

$formules = [];
$garantiesByFormule = [];
$schemaMessage = null;

if ($categorie) {
    $sqlFormules = "SELECT * FROM formule WHERE id_categorie = :id_categorie ORDER BY id_formule ASC";
    $stmtFormules = $db->prepare($sqlFormules);
    $stmtFormules->execute(['id_categorie' => $categorie['id_categorie']]);
    $formules = $stmtFormules->fetchAll(PDO::FETCH_ASSOC);

    if ($garantieFormulaColumn !== null) {
        $sqlGaranties = "
            SELECT g.*
            FROM garantie g
            INNER JOIN formule f ON g.`$garantieFormulaColumn` = f.id_formule
            WHERE f.id_categorie = :id_categorie
            ORDER BY f.id_formule ASC, g.id_garantie ASC
        ";
        $stmtGaranties = $db->prepare($sqlGaranties);
        $stmtGaranties->execute(['id_categorie' => $categorie['id_categorie']]);

        foreach ($stmtGaranties->fetchAll(PDO::FETCH_ASSOC) as $garantie) {
            $formuleId = (int) ($garantie[$garantieFormulaColumn] ?? 0);
            $garantiesByFormule[$formuleId][] = $garantie;
        }
    } else {
        $schemaMessage = "Pour afficher les garanties par formule, ajoutez une colonne id_formule (ou formule_id) dans la table garantie et liez-la à formule.id_formule.";
    }
}

$iconClasses = ['icon-classique', 'icon-tierce', 'icon-risque'];
$iconSymbols = ['bi bi-shield-check', 'bi bi-shield-plus', 'bi bi-stars'];
$profileDefaults = ['Protection de base', 'Usage régulier', 'Protection maximale'];

$highlightIndex = count($formules) >= 2 ? 1 : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Assurance Protection — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <link rel="stylesheet" href="assets/css/contrat.css">

    <style>
        .protection-hero{
            background:
                linear-gradient(135deg, rgba(20,39,56,0.96), rgba(53,92,125,0.92)),
                url('https://images.unsplash.com/photo-1556741533-f6acd647d2fb?q=80&w=1400&auto=format&fit=crop') center/cover no-repeat;
        }

        .formules-grid{
            display:grid;
            grid-template-columns:repeat(3, minmax(0, 1fr));
            gap:22px;
            align-items:stretch;
        }

        .formule-card{
            position:relative;
            display:flex;
            flex-direction:column;
            gap:18px;
            min-height:100%;
        }

        .formule-desc{
            min-height:72px;
        }

        .mini-meta{
            display:grid;
            grid-template-columns:repeat(2, minmax(0,1fr));
            gap:10px;
        }

        .garantie-list{
            list-style:none;
            padding:0;
            margin:0;
            display:flex;
            flex-direction:column;
            gap:10px;
            flex:1;
        }

        .formule-footer{
            display:flex;
            flex-direction:column;
            gap:12px;
            margin-top:auto;
        }

        .explication-box{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:22px;
            margin-top:18px;
        }

        .info-card{
            background:#fff;
            border-radius:22px;
            border:1px solid rgba(20,39,56,0.08);
            padding:22px;
        }

        .info-card h3{
            margin:0 0 10px;
            color:#142738;
            font-size:22px;
        }

        .info-card p{
            margin:0;
            color:#708198;
            line-height:1.7;
        }

        .info-steps{
            display:flex;
            flex-direction:column;
            gap:12px;
        }

        .info-step{
            display:flex;
            gap:12px;
            align-items:flex-start;
        }

        .info-step span{
            width:28px;
            height:28px;
            border-radius:999px;
            display:grid;
            place-items:center;
            background:rgba(238,88,40,0.12);
            color:#EE5828;
            font-weight:800;
            flex:0 0 28px;
        }

        .info-step p{
            margin:0;
        }

        @media (max-width:1100px){
            .formules-grid,
            .explication-box,
            .form-grid-2,
            .check-grid{
                grid-template-columns:1fr;
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
    <nav class="navbar">
        <a href="client.html" class="navbar-brand">
            <img src="logo.png" alt="logo" width="40" height="40">
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

            <a class="nav-link active" href="contrat.php">
                <i class="bi bi-file-earmark-text"></i>
                <span class="nav-label">Contrats</span>
                <span class="nav-badge accent">3</span>
            </a>

            <a class="nav-link" href="mes-sinistres.html">
                <i class="bi bi-shield-exclamation"></i>
                <span class="nav-label">Sinistres</span>
                <span class="nav-badge">1</span>
            </a>

            <a class="nav-link" href="paiement.html">
                <i class="bi bi-credit-card"></i>
                <span class="nav-label">Paiements</span>
            </a>

            <div class="nav-separator"></div>

            <a class="nav-link" href="reclamations.html">
                <i class="bi bi-chat-dots"></i>
                <span class="nav-label">Réclamations</span>
            </a>

            <a class="nav-link" href="agences.html">
                <i class="bi bi-geo-alt"></i>
                <span class="nav-label">Agences</span>
            </a>

            <a class="nav-link" href="offres.html">
                <i class="bi bi-stars"></i>
                <span class="nav-label">Nos offres</span>
            </a>
        </div>
    </nav>

    <main class="main auto-wrapper">
        <div class="page-header">
            <div>
                <div class="page-title-main">Assurance Protection</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.html" style="color:inherit;text-decoration:none;">Accueil</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <a href="contrat.php" style="color:inherit;text-decoration:none;">Contrats</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <span>Protection</span>
                </div>
            </div>
        </div>

        <section class="auto-hero">
            <div class="hero-content">
                <div class="hero-chip">
                    <i class="bi bi-shield-lock"></i>
                    Protection personnelle flexible
                </div>

                <h1 class="hero-title">Choisissez la formule protection qui vous rassure</h1>

                <p class="hero-text">
                    Comparez les niveaux de couverture, découvrez les garanties incluses
                    et sélectionnez la formule protection la plus adaptée à votre profil,
                    à votre situation et à votre niveau de sécurité recherché.
                </p>

                <div class="hero-actions">
                    <a href="#formules-protection" class="hero-btn primary">
                        <i class="bi bi-lightning-charge-fill"></i>
                        Voir les formules
                    </a>

                    <a href="contrat.php" class="hero-btn secondary">
                        <i class="bi bi-arrow-left"></i>
                        Retour aux catégories
                    </a>
                </div>
            </div>

            <div class="hero-side">
                <div class="hero-glass">
                    <h3>Pourquoi choisir Protex Protection ?</h3>
                    <ul class="hero-points">
                        <li><i class="bi bi-check-circle-fill"></i><span>Des formules claires selon le niveau de protection souhaité.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Des garanties visibles avant la demande.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Un parcours simple avec ouverture du formulaire après le choix.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Une expérience plus propre, plus moderne et mieux organisée.</span></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section-block" id="formules-protection">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Nos formules protection</h2>
                    <p class="section-subtitle">Comparez les formules disponibles et choisissez celle qui correspond à votre besoin.</p>
                </div>
            </div>

            <?php if (!$categorie): ?>
                <div class="empty-contracts" style="display:block; text-align:left;">
                    <strong>Catégorie introuvable.</strong><br>
                    La catégorie Protection n’existe pas encore dans la base.
                </div>
            <?php elseif (empty($formules)): ?>
                <div class="empty-contracts" style="display:block; text-align:left;">
                    <strong>Aucune formule trouvée.</strong><br>
                    Ajoutez des formules dans la catégorie Protection depuis le back-office.
                </div>
            <?php else: ?>
                <div class="formules-grid">
                    <?php foreach ($formules as $index => $formule): ?>
                        <?php
                            $formuleId = (int) $formule['id_formule'];
                            $isHighlight = ($index === $highlightIndex);
                            $iconClass = $iconClasses[$index % count($iconClasses)];
                            $iconSymbol = $iconSymbols[$index % count($iconSymbols)];
                            $slug = slugify($formule['nom_formule']);
                            $niveau = $formuleLevelColumn && !empty($formule[$formuleLevelColumn]) ? $formule[$formuleLevelColumn] : (($index === 0) ? 'Essentiel' : (($index === 1) ? 'Intermédiaire' : 'Premium'));
                            $profil = $profileDefaults[$index % count($profileDefaults)];
                            $garanties = $garantiesByFormule[$formuleId] ?? [];
                        ?>
                        <article class="formule-card<?= $isHighlight ? ' highlight' : '' ?>">
                            <?php if (!empty($niveau)): ?>
                                <span class="badge-top"><?= h($niveau) ?></span>
                            <?php endif; ?>

                            <div class="formule-icon <?= h($iconClass) ?>">
                                <i class="<?= h($iconSymbol) ?>"></i>
                            </div>

                            <h3 class="formule-name"><?= h($formule['nom_formule']) ?></h3>
                            <p class="formule-desc"><?= h($formule['description_formule']) ?></p>

                            <div class="mini-meta">
                                <div class="meta-box">
                                    <span class="meta-label">Profil conseillé</span>
                                    <span class="meta-value"><?= h($profil) ?></span>
                                </div>
                                <div class="meta-box">
                                    <span class="meta-label">Prix</span>
                                    <span class="meta-value"><?= number_format((float) ($formule['prix_formule'] ?? 0), 2, '.', ' ') ?> DT</span>
                                </div>
                            </div>

                            <ul class="garantie-list">
                                <?php if (!empty($garanties)): ?>
                                    <?php foreach ($garanties as $garantie): ?>
                                        <?php $niveauGarantie = normalizeGarantieLevel($garantie['niveau_couvert_garantie'] ?? ''); ?>
                                        <li>
                                            <i class="<?= h(garantieIcon($niveauGarantie)) ?>"></i>
                                            <?= h($garantie['nom_garantie'] ?? 'Garantie') ?>
                                            <?= garantieStatusTag($niveauGarantie) ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li><i class="bi bi-check2-circle"></i> Description <strong><?= h($formule['description_formule']) ?></strong></li>
                                    <li><i class="bi bi-check2-circle"></i> Catégorie <strong><?= h($categorie['nom_categorie']) ?></strong></li>
                                    <li><i class="bi bi-check2-circle"></i> Niveau <strong><?= h($niveau) ?></strong></li>
                                <?php endif; ?>
                            </ul>

                            <div class="formule-footer">
                                <button type="button" class="choose-btn" onclick="openProtectionModal('<?= h($formule['nom_formule']) ?>')">
                                    Choisir cette formule
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($schemaMessage): ?>
                    <div class="hint-box" style="margin-top:18px;">
                        <?= h($schemaMessage) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <section class="explication-box">
            <article class="info-card">
                <h3>Comment ça marche ?</h3>
                <div class="info-steps">
                    <div class="info-step"><span>1</span><p>Comparez les formules protection disponibles.</p></div>
                    <div class="info-step"><span>2</span><p>Choisissez la formule qui correspond à votre besoin.</p></div>
                    <div class="info-step"><span>3</span><p>Remplissez votre demande dans le formulaire de souscription.</p></div>
                </div>
            </article>

            <article class="info-card">
                <h3>Pourquoi cette approche ?</h3>
                <p>
                    L’objectif est de vous laisser comparer clairement les niveaux de protection
                    avant d’ouvrir le formulaire, afin de rendre le parcours plus fluide,
                    plus lisible et plus rassurant.
                </p>
            </article>
        </section>
    </main>
</div>

<div id="protectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h1>Demande d’assurance protection</h1>
                <p>Complétez les informations nécessaires pour préparer votre contrat.</p>
            </div>
            <button type="button" class="close-btn" onclick="closeProtectionModal()">&times;</button>
        </div>

        <div class="modal-body">
            <form id="contratProtectionForm" method="post" action="#">
                <input type="hidden" name="type_contrat" value="Protection">
                <input type="hidden" name="id_categorie" value="<?= h($categorie['id_categorie'] ?? '') ?>">

                <div class="form-section">
                    <h2 class="form-section-title">I - Couvertures souhaitées</h2>

                    <div class="form-grid-1">
                        <div class="form-group">
                            <label for="formule">Formule choisie <span class="req">*</span></label>
                            <select class="form-select" id="formule" name="formule" onchange="toggleCoveragePanels()" required>
                                <option value="">— Veuillez choisir une option —</option>
                                <?php foreach ($formules as $formule): ?>
                                    <option value="<?= h($formule['nom_formule']) ?>"><?= h($formule['nom_formule']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="error-message" id="error_formule"></div>
                        </div>
                    </div>

                    <?php foreach ($formules as $index => $formule): ?>
                        <?php
                            $formuleId = (int) $formule['id_formule'];
                            $slug = slugify($formule['nom_formule']);
                            $garanties = $garantiesByFormule[$formuleId] ?? [];
                        ?>
                        <div id="panel-<?= h($slug) ?>" class="coverage-panel">
                            <h3>Garanties de la formule <?= h($formule['nom_formule']) ?></h3>

                            <?php if (!empty($garanties)): ?>
                                <div class="check-grid">
                                    <?php foreach ($garanties as $garantie): ?>
                                        <?php
                                            $niveauGarantie = normalizeGarantieLevel($garantie['niveau_couvert_garantie'] ?? '');
                                            $isFixed = ($niveauGarantie === 'basique');
                                            $isDisabled = ($niveauGarantie === 'non disponible');
                                        ?>
                                        <label class="check-item<?= $isFixed ? ' fixed' : '' ?><?= $isDisabled ? ' disabled' : '' ?>">
                                            <input
                                                type="checkbox"
                                                <?= $isFixed ? 'checked' : '' ?>
                                                <?= $isFixed || $isDisabled ? 'disabled' : 'name="garanties[]" value="' . h($garantie['nom_garantie']) . '"' ?>
                                            >
                                            <?= h($garantie['nom_garantie']) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <div class="hint-box">
                                    <?= h($formule['description_formule']) ?>
                                </div>
                            <?php else: ?>
                                <div class="hint-box">
                                    <?= h($formule['description_formule']) ?><br>
                                    <?php if ($schemaMessage): ?>
                                        <?= h($schemaMessage) ?>
                                    <?php else: ?>
                                        Aucune garantie liée à cette formule pour le moment.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">II - Informations personnelles</h2>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="email">E-mail <span class="req">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= h($clientEmail) ?>" placeholder="Adresse e-mail">
                        </div>

                        <div class="form-group">
                            <label for="nom">Nom <span class="req">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?= h($clientNom) ?>" placeholder="Nom de famille">
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom <span class="req">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= h($clientPrenom) ?>" placeholder="Prénom">
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-protex btn-light-protex" onclick="closeProtectionModal()">Annuler</button>
                    <button type="reset" class="btn-protex btn-light-protex">Réinitialiser</button>
                    <button type="submit" class="btn-protex btn-primary-protex">Valider votre demande</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const formulaPanels = {
        <?php foreach ($formules as $formule): ?>
            <?= json_encode($formule['nom_formule']) ?>: <?= json_encode('panel-' . slugify($formule['nom_formule'])) ?>,
        <?php endforeach; ?>
    };

    function openProtectionModal(formule = '') {
        const modal = document.getElementById('protectionModal');
        const formuleSelect = document.getElementById('formule');

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        if (formuleSelect && formule) {
            formuleSelect.value = formule;
        }

        toggleCoveragePanels();
    }

    function closeProtectionModal() {
        document.getElementById('protectionModal').classList.remove('show');
        document.body.style.overflow = '';
    }

    function toggleCoveragePanels() {
        const select = document.getElementById('formule');
        const value = select ? select.value : '';

        Object.values(formulaPanels).forEach(function(panelId) {
            const panel = document.getElementById(panelId);
            if (panel) {
                panel.classList.remove('active');
            }
        });

        if (value && formulaPanels[value]) {
            const activePanel = document.getElementById(formulaPanels[value]);
            if (activePanel) {
                activePanel.classList.add('active');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const overlay = document.getElementById('protectionModal');
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target.id === 'protectionModal') {
                    closeProtectionModal();
                }
            });
        }
    });
</script>

</body>
</html>
