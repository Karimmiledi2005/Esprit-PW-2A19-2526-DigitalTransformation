<?php
session_start();

require_once '../../config/database.php';

$clientNom = $_SESSION['nom'] ?? '';
$clientPrenom = $_SESSION['prenom'] ?? '';
$clientEmail = $_SESSION['email'] ?? '';

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = trim($text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'item';
}

function niveauBadge(string $niveau): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'Essentiel';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'Intermédiaire';
    if ($niveau === 'premium') return 'Premium';
    return ucfirst($niveau ?: 'Standard');
}

function profileLabel(string $niveau, string $nom): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'Budget limité';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'Usage courant';
    if ($niveau === 'premium') return 'Protection maximale';

    $nom = mb_strtolower(trim($nom), 'UTF-8');
    if (str_contains($nom, 'eco')) return 'Budget limité';
    if (str_contains($nom, 'confort')) return 'Usage courant';
    if (str_contains($nom, 'premium')) return 'Protection maximale';
    return 'Profil standard';
}

function formuleIconClass(int $index, string $niveau): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'icon-classique';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'icon-tierce';
    if ($niveau === 'premium') return 'icon-risque';
    return match ($index % 3) {
        0 => 'icon-classique',
        1 => 'icon-tierce',
        default => 'icon-risque',
    };
}

function formuleIconBi(int $index, string $niveau): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'bi-shield-check';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'bi-heart-pulse-fill';
    if ($niveau === 'premium') return 'bi-stars';
    return match ($index % 3) {
        0 => 'bi-shield-check',
        1 => 'bi-heart-pulse-fill',
        default => 'bi-stars',
    };
}

$db = config::getConnexion();

$categorie = null;
$formules = [];
$garantiesByFormule = [];

try {
    $catStmt = $db->prepare("
        SELECT *
        FROM categorie
        WHERE LOWER(nom_categorie) IN ('sante', 'santé')
        ORDER BY id_categorie DESC
        LIMIT 1
    ");
    $catStmt->execute();
    $categorie = $catStmt->fetch(PDO::FETCH_ASSOC);

    if (!$categorie) {
        $catStmt = $db->prepare("SELECT * FROM categorie WHERE id_categorie = 4 LIMIT 1");
        $catStmt->execute();
        $categorie = $catStmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($categorie) {
        $formuleStmt = $db->prepare("
            SELECT *
            FROM formule
            WHERE id_categorie = :id_categorie
            ORDER BY id_formule ASC
        ");
        $formuleStmt->execute(['id_categorie' => $categorie['id_categorie']]);
        $formules = $formuleStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($formules)) {
            $ids = array_map(fn($row) => (int)$row['id_formule'], $formules);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $garantieStmt = $db->prepare("
                SELECT *
                FROM garantie
                WHERE id_formule IN ($placeholders)
                ORDER BY id_garantie ASC
            ");
            $garantieStmt->execute($ids);

            foreach ($garantieStmt->fetchAll(PDO::FETCH_ASSOC) as $garantie) {
                $fid = (int)$garantie['id_formule'];
                if (!isset($garantiesByFormule[$fid])) {
                    $garantiesByFormule[$fid] = [];
                }
                $garantiesByFormule[$fid][] = $garantie;
            }
        }
    }
} catch (Exception $e) {
    $categorie = $categorie ?: ['id_categorie' => 4, 'nom_categorie' => 'Santé'];
    $formules = [];
    $garantiesByFormule = [];
}

$formulePanels = [];
foreach ($formules as $formule) {
    $formulePanels[$formule['nom_formule']] = 'panel-' . slugify($formule['nom_formule']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Assurance Santé — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/client.css">
    <link rel="stylesheet" href="assets/css/contrat.css">
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
                <div class="page-title-main">Assurance Santé</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.html" style="color:inherit;text-decoration:none;">Accueil</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <a href="contrat.php" style="color:inherit;text-decoration:none;">Contrats</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <span>Santé</span>
                </div>
            </div>
        </div>

        <section class="auto-hero">
            <div class="hero-content">
                <div class="hero-chip">
                    <i class="bi bi-heart-pulse"></i>
                    Protection santé flexible
                </div>

                <h1 class="hero-title">Choisissez la formule santé qui vous convient</h1>

                <p class="hero-text">
                    Comparez les niveaux de couverture, découvrez les garanties incluses
                    et sélectionnez la formule santé la plus adaptée à votre situation,
                    votre budget et vos besoins médicaux.
                </p>

                <div class="hero-actions">
                    <a href="#formules-sante" class="hero-btn primary">
                        <i class="bi bi-clipboard2-pulse"></i>
                        Comparer les formules
                    </a>
                    <button type="button" class="hero-btn secondary" onclick="openSanteModal()">
                        <i class="bi bi-file-earmark-medical"></i>
                        Faire une demande
                    </button>
                </div>
            </div>

            <div class="hero-side">
                <div class="hero-glass">
                    <h3>Pourquoi cette offre ?</h3>
                    <ul class="hero-points">
                        <li><i class="bi bi-check2-circle"></i><span>Des formules mises à jour depuis votre back-office, sans modifier la page manuellement.</span></li>
                        <li><i class="bi bi-check2-circle"></i><span>Des garanties lisibles, un parcours clair et un formulaire moderne pour préparer la demande.</span></li>
                        <li><i class="bi bi-check2-circle"></i><span>Une expérience cohérente avec le style Protex, plus simple et plus rassurante.</span></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section-block" id="formules-sante">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Nos formules santé</h2>
                    <p class="section-subtitle">Comparez les garanties et choisissez la couverture qui correspond à votre besoin.</p>
                </div>
            </div>

            <div class="formules-grid">
                <?php if (!empty($formules)): ?>
                    <?php foreach ($formules as $index => $formule): ?>
                        <?php
                            $fid = (int)$formule['id_formule'];
                            $niveau = niveauBadge((string)($formule['niveau_formule'] ?? ''));
                            $profil = profileLabel((string)($formule['niveau_formule'] ?? ''), (string)$formule['nom_formule']);
                            $iconClass = formuleIconClass($index, (string)($formule['niveau_formule'] ?? ''));
                            $iconBi = formuleIconBi($index, (string)($formule['niveau_formule'] ?? ''));
                            $garanties = $garantiesByFormule[$fid] ?? [];
                            $isHighlight = true;
                        ?>
                        <article class="formule-card <?= $index === 1 ? 'highlight' : '' ?>">
                            <span class="badge-top"><?= h($niveau) ?></span>

                            <div class="formule-icon <?= h($iconClass) ?>">
                                <i class="bi <?= h($iconBi) ?>"></i>
                            </div>

                            <h3 class="formule-name"><?= h($formule['nom_formule']) ?></h3>
                            <p class="formule-desc"><?= h($formule['description_formule'] ?? 'Description indisponible.') ?></p>

                            <div class="mini-meta">
                                <div class="meta-box">
                                    <span class="meta-label">Profil conseillé</span>
                                    <span class="meta-value"><?= h($profil) ?></span>
                                </div>
                                <div class="meta-box">
                                    <span class="meta-label">Prix</span>
                                    <span class="meta-value"><?= number_format((float)($formule['prix_formule'] ?? 0), 2, '.', ' ') ?> DT</span>
                                </div>
                            </div>

                            <ul class="garantie-list">
                                <?php if (!empty($garanties)): ?>
                                    <?php foreach ($garanties as $garantie): ?>
                                        <?php
                                            $ng = mb_strtolower(trim((string)($garantie['niveau_couvert_garantie'] ?? 'basique')), 'UTF-8');
                                            if ($ng === 'basique') {
                                                $icon = 'bi-check2-circle';
                                            } elseif ($ng === 'option') {
                                                $icon = 'bi-plus-circle';
                                            } else {
                                                $icon = 'bi-x-circle';
                                            }
                                        ?>
                                        <li>
                                            <i class="bi <?= h($icon) ?>"></i>
                                            <?= h($garantie['nom_garantie']) ?>
                                            <strong>(<?= h($garantie['niveau_couvert_garantie']) ?>)</strong>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li><i class="bi bi-info-circle"></i> Aucune garantie configurée <strong>(à compléter)</strong></li>
                                <?php endif; ?>
                            </ul>

                            <div class="formule-footer">
                                <button type="button" class="choose-btn choose-sante-btn" data-formule="<?= h($formule['nom_formule']) ?>" onclick="openSanteModal(<?= json_encode($formule['nom_formule']) ?>)">
                                    Choisir cette formule
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="formule-card">
                        <div class="formule-icon icon-classique">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                        <h3 class="formule-name">Aucune formule</h3>
                        <p class="formule-desc">Ajoutez d’abord des formules santé depuis le back-office pour les afficher ici.</p>
                    </article>
                <?php endif; ?>
            </div>

            <div class="explication-box">
                <div class="info-card">
                    <h3>Comment ça marche ?</h3>
                    <p>
                        Vous commencez par consulter les formules et leurs garanties. Une fois votre choix
                        effectué, un formulaire détaillé s’ouvre dans une fenêtre popup propre pour saisir
                        les informations de l’assuré et du besoin médical.
                    </p>
                </div>

                <div class="info-card">
                    <h3>Pourquoi cette approche ?</h3>
                    <p>
                        L’utilisateur comprend d’abord le produit avant de remplir sa demande.
                        Cela rend l’expérience plus claire, plus moderne et mieux organisée.
                    </p>
                </div>
            </div>
        </section>
    </main>
</div>

<div id="santeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2>Demande d’assurance santé</h2>
                <p>Complétez les informations nécessaires pour préparer votre contrat.</p>
            </div>
            <button type="button" class="close-btn" onclick="closeSanteModal()">&times;</button>
        </div>

        <div class="modal-body">
            <form id="contratSanteForm" method="post" action="#">
                <input type="hidden" name="type_contrat" value="Sante">
                <input type="hidden" name="id_categorie" value="<?= h($categorie['id_categorie'] ?? 4) ?>">

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

                    <?php foreach ($formules as $formule): ?>
                        <?php
                            $fid = (int)$formule['id_formule'];
                            $panelId = 'panel-' . slugify($formule['nom_formule']);
                            $garanties = $garantiesByFormule[$fid] ?? [];
                        ?>
                        <div id="<?= h($panelId) ?>" class="coverage-panel">
                            <h3>Garanties de la formule <?= h($formule['nom_formule']) ?></h3>

                            <div class="check-grid">
                                <?php if (!empty($garanties)): ?>
                                    <?php foreach ($garanties as $garantie): ?>
                                        <?php
                                            $niveauGarantie = mb_strtolower(trim((string)($garantie['niveau_couvert_garantie'] ?? 'basique')), 'UTF-8');
                                            $isFixed = $niveauGarantie === 'basique';
                                            $isDisabled = $niveauGarantie === 'non disponible';
                                            $labelClass = 'check-item';
                                            if ($isFixed) $labelClass .= ' fixed';
                                            if ($isDisabled) $labelClass .= ' disabled';
                                        ?>
                                        <label class="<?= h($labelClass) ?>">
                                            <input type="checkbox"
                                                   name="garanties[]"
                                                   value="<?= h($garantie['nom_garantie']) ?>"
                                                   <?= $isFixed ? 'checked disabled' : '' ?>
                                                   <?= $isDisabled ? 'disabled' : '' ?>>
                                            <?= h($garantie['nom_garantie']) ?>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <label class="check-item disabled">
                                        <input type="checkbox" disabled>
                                        Aucune garantie configurée
                                    </label>
                                <?php endif; ?>
                            </div>

                            <div class="hint-box">
                                <?= h($formule['description_formule'] ?? 'Cette formule propose un niveau de couverture santé adapté à différents besoins médicaux.') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">II - Informations personnelles</h2>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="identite">Identité de l’adhérent <span class="req">*</span></label>
                            <select class="form-select" id="identite" name="identite">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Monsieur</option>
                                <option>Madame</option>
                            </select>
                            <div class="error-message" id="error_identite"></div>
                        </div>

                        <div class="form-group">
                            <label for="email">E-mail <span class="req">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= h($clientEmail) ?>" placeholder="Adresse e-mail">
                            <div class="error-message" id="error_email"></div>
                        </div>

                        <div class="form-group">
                            <label for="nom">Nom <span class="req">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?= h($clientNom) ?>" placeholder="Nom de famille">
                            <div class="error-message" id="error_nom"></div>
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom <span class="req">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= h($clientPrenom) ?>" placeholder="Prénom">
                            <div class="error-message" id="error_prenom"></div>
                        </div>

                        <div class="form-group">
                            <label for="telephone">Téléphone <span class="req">*</span></label>
                            <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Numéro de téléphone">
                            <div class="error-message" id="error_telephone"></div>
                        </div>

                        <div class="form-group">
                            <label for="date_naissance">Date de naissance <span class="req">*</span></label>
                            <input type="date" class="form-control" id="date_naissance" name="date_naissance">
                            <div class="error-message" id="error_date_naissance"></div>
                        </div>

                        <div class="form-group">
                            <label for="nationalite">Nationalité <span class="req">*</span></label>
                            <input type="text" class="form-control" id="nationalite" name="nationalite" placeholder="Nationalité">
                            <div class="error-message" id="error_nationalite"></div>
                        </div>

                        <div class="form-group">
                            <label for="situation_professionnelle">Situation professionnelle <span class="req">*</span></label>
                            <select class="form-select" id="situation_professionnelle" name="situation_professionnelle">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Salarié</option>
                                <option>Indépendant</option>
                                <option>Étudiant</option>
                                <option>Retraité</option>
                                <option>Sans activité</option>
                            </select>
                            <div class="error-message" id="error_situation_professionnelle"></div>
                        </div>

                        <div class="form-group">
                            <label for="adresse">Adresse <span class="req">*</span></label>
                            <input type="text" class="form-control" id="adresse" name="adresse" placeholder="Adresse complète">
                            <div class="error-message" id="error_adresse"></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">III - Besoin de couverture</h2>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="type_couverture">Type de couverture souhaitée <span class="req">*</span></label>
                            <select class="form-select" id="type_couverture" name="type_couverture">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Individuelle</option>
                                <option>Couple</option>
                                <option>Familiale</option>
                            </select>
                            <div class="error-message" id="error_type_couverture"></div>
                        </div>

                        <div class="form-group">
                            <label for="nombre_beneficiaires">Nombre de bénéficiaires</label>
                            <select class="form-select" id="nombre_beneficiaires" name="nombre_beneficiaires">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                                <option>4 ou plus</option>
                            </select>
                            <div class="error-message" id="error_nombre_beneficiaires"></div>
                        </div>

                        <div class="form-group">
                            <label for="antecedents">Antécédents médicaux importants</label>
                            <select class="form-select" id="antecedents" name="antecedents">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Aucun</option>
                                <option>Diabète</option>
                                <option>Hypertension</option>
                                <option>Asthme</option>
                                <option>Autre</option>
                            </select>
                            <div class="error-message" id="error_antecedents"></div>
                        </div>

                        <div class="form-group">
                            <label for="frequence_soins">Fréquence estimée des soins</label>
                            <select class="form-select" id="frequence_soins" name="frequence_soins">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Faible</option>
                                <option>Moyenne</option>
                                <option>Élevée</option>
                            </select>
                            <div class="error-message" id="error_frequence_soins"></div>
                        </div>

                        <div class="form-group">
                            <label for="couverture_dentaire">Besoin d’une couverture dentaire ?</label>
                            <select class="form-select" id="couverture_dentaire" name="couverture_dentaire">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Oui</option>
                                <option>Non</option>
                            </select>
                            <div class="error-message" id="error_couverture_dentaire"></div>
                        </div>

                        <div class="form-group">
                            <label for="couverture_optique">Besoin d’une couverture optique ?</label>
                            <select class="form-select" id="couverture_optique" name="couverture_optique">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Oui</option>
                                <option>Non</option>
                            </select>
                            <div class="error-message" id="error_couverture_optique"></div>
                        </div>

                        <div class="form-group">
                            <label for="details_formule">Commentaires / précisions</label>
                            <textarea class="form-textarea" id="details_formule" name="details_formule" placeholder="Ajoutez des détails utiles sur votre besoin..."></textarea>
                            <div class="error-message" id="error_details_formule"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-protex btn-light-protex" onclick="closeSanteModal()">Annuler</button>
                    <button type="reset" class="btn-protex btn-light-protex">Réinitialiser</button>
                    <button type="submit" class="btn-protex btn-primary-protex">Valider votre demande</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const formulePanels = <?= json_encode($formulePanels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function openSanteModal(formule = '') {
        const modal = document.getElementById('santeModal');
        const formuleSelect = document.getElementById('formule');

        if (!modal) return;

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        if (formuleSelect) {
            formuleSelect.value = formule || '';
        }

        toggleCoveragePanels();
    }

    function closeSanteModal() {
        const modal = document.getElementById('santeModal');
        if (!modal) return;
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    function toggleCoveragePanels() {
        const select = document.getElementById('formule');
        const value = select ? select.value : '';

        Object.values(formulePanels || {}).forEach(panelId => {
            const panel = document.getElementById(panelId);
            if (panel) panel.classList.remove('active');
        });

        if (value && formulePanels && formulePanels[value]) {
            const activePanel = document.getElementById(formulePanels[value]);
            if (activePanel) activePanel.classList.add('active');
        }
    }

    function setError(id, message) {
        const error = document.getElementById('error_' + id);
        if (error) error.textContent = message;
    }

    function clearError(id) {
        const error = document.getElementById('error_' + id);
        if (error) error.textContent = '';
    }

    function validateContratSanteForm(e) {
        let valid = true;

        const requiredFields = [
            'formule',
            'identite',
            'email',
            'nom',
            'prenom',
            'telephone',
            'date_naissance',
            'nationalite',
            'situation_professionnelle',
            'adresse',
            'type_couverture'
        ];

        requiredFields.forEach(id => {
            const field = document.getElementById(id);
            if (!field) return;

            clearError(id);

            if (!String(field.value || '').trim()) {
                setError(id, 'Veuillez renseigner ce champ.');
                valid = false;
            }
        });

        const emailField = document.getElementById('email');
        const email = emailField ? emailField.value.trim() : '';
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            setError('email', 'Adresse e-mail invalide.');
            valid = false;
        }

        const telephoneField = document.getElementById('telephone');
        const telephone = telephoneField ? telephoneField.value.trim() : '';
        if (telephone && !/^[0-9+\s]{8,20}$/.test(telephone)) {
            setError('telephone', 'Numéro de téléphone invalide.');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('contratSanteForm');
        if (form) {
            form.addEventListener('submit', validateContratSanteForm);
        }

        const modal = document.getElementById('santeModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target.id === 'santeModal') {
                    closeSanteModal();
                }
            });
        }

        document.querySelectorAll('.choose-sante-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openSanteModal(this.getAttribute('data-formule') || '');
            });
        });

        toggleCoveragePanels();
    });
</script>

</body>
</html>
