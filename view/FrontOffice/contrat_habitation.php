<?php
session_start();

$clientNom = $_SESSION['nom'] ?? '';
$clientPrenom = $_SESSION['prenom'] ?? '';
$clientEmail = $_SESSION['email'] ?? '';

require_once '../../config/database.php';

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

function habNiveauLabel(string $niveau): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'Essentiel';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'Intermédiaire';
    if ($niveau === 'premium') return 'Premium';
    return ucfirst($niveau ?: 'Standard');
}

function habProfileLabel(string $niveau, string $nom): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'Budget maîtrisé';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'Usage régulier';
    if ($niveau === 'premium') return 'Protection maximale';

    $nom = mb_strtolower(trim($nom), 'UTF-8');
    if (str_contains($nom, 'eco')) return 'Budget maîtrisé';
    if (str_contains($nom, 'confort')) return 'Usage régulier';
    if (str_contains($nom, 'premium') || str_contains($nom, 'priv')) return 'Protection maximale';
    return 'Profil standard';
}

function habIconClass(int $index, string $niveau): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'economique';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'privilege';
    if ($niveau === 'premium') return 'privilege';
    return $index % 2 === 0 ? 'economique' : 'privilege';
}

function habIconBi(int $index, string $niveau): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'bi-house-door-fill';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'bi-building-check';
    if ($niveau === 'premium') return 'bi-shield-lock-fill';
    return $index % 2 === 0 ? 'bi-house-door-fill' : 'bi-building-check';
}

$db = config::getConnexion();

$categorie = null;
$formules = [];
$garantiesByFormule = [];
$formulePanels = [];

try {
    $catStmt = $db->prepare("
        SELECT *
        FROM categorie
        WHERE LOWER(nom_categorie) = 'habitation'
        ORDER BY id_categorie DESC
        LIMIT 1
    ");
    $catStmt->execute();
    $categorie = $catStmt->fetch(PDO::FETCH_ASSOC);

    if (!$categorie) {
        $catStmt = $db->prepare("SELECT * FROM categorie WHERE id_categorie = 3 LIMIT 1");
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
    $categorie = $categorie ?: ['id_categorie' => 3, 'nom_categorie' => 'Habitation'];
    $formules = [];
    $garantiesByFormule = [];
}

foreach ($formules as $formule) {
    $formulePanels[$formule['nom_formule']] = 'panel-' . slugify($formule['nom_formule']);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Assurance Habitation — Protex</title>
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

    <main class="main hab-page">
        <div class="page-header">
            <div>
                <div class="page-title-main">Assurance Habitation</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.html" style="color:inherit;text-decoration:none;">Accueil</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <a href="contrat.php" style="color:inherit;text-decoration:none;">Contrats</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <span>Habitation</span>
                </div>
            </div>
        </div>

        <section class="hab-hero">
    <div class="hab-hero-left">
        <div class="hab-chip">
            <i class="bi bi-house-heart-fill"></i>
            Protection habitation flexible
        </div>

        <h1 class="hab-title">Choisissez la formule habitation qui protège votre foyer</h1>

        <p class="hab-text">
            Comparez les niveaux de couverture, découvrez les garanties incluses
            et lancez votre demande en quelques clics avec une expérience simple,
            moderne et rassurante.
        </p>

        <div class="hab-hero-actions">
            <a href="#formules-habitation" class="hero-btn primary">
                <i class="bi bi-lightning-charge-fill"></i>
                Voir les formules
            </a>

            <a href="contrat.php" class="hero-btn secondary">
                <i class="bi bi-arrow-left"></i>
                Retour aux catégories
            </a>
        </div>
    </div>

    <div class="hab-hero-right">
        <div class="hero-glass">
            <h3>Pourquoi choisir Protex Habitation ?</h3>
            <ul class="hero-points">
                <li><i class="bi bi-check-circle-fill"></i><span>Des formules claires adaptées à votre logement.</span></li>
                <li><i class="bi bi-check-circle-fill"></i><span>Des garanties détaillées avant même de remplir votre demande.</span></li>
                <li><i class="bi bi-check-circle-fill"></i><span>Un formulaire guidé qui s’ouvre seulement après votre choix.</span></li>
                <li><i class="bi bi-check-circle-fill"></i><span>Une protection pensée pour les biens et les personnes du foyer.</span></li>
            </ul>
        </div>
    </div>
</section>

        <section class="hab-formules">
            <div class="hab-formules-header">
                <h2 class="hab-formules-title">Nos formules habitation</h2>
                <p class="hab-formules-subtitle">
                    Deux niveaux de couverture pour protéger votre logement, vos biens et votre famille contre les imprévus du quotidien.
                </p>
            </div>

            <div class="hab-cards-grid">
                <?php if (!empty($formules)): ?>
                    <?php foreach ($formules as $index => $formule): ?>
                        <?php
                            $fid = (int)$formule['id_formule'];
                            $niveau = habNiveauLabel((string)($formule['niveau_formule'] ?? ''));
                            $profil = habProfileLabel((string)($formule['niveau_formule'] ?? ''), (string)$formule['nom_formule']);
                            $iconClass = habIconClass($index, (string)($formule['niveau_formule'] ?? ''));
                            $iconBi = habIconBi($index, (string)($formule['niveau_formule'] ?? ''));
                            $garanties = $garantiesByFormule[$fid] ?? [];
                        ?>
                        <article class="hab-card <?= $index === 1 ? 'highlight' : '' ?>">
                            <span class="hab-badge-top"><?= h($niveau) ?></span>

                            <div class="hab-icon <?= h($iconClass) ?>">
                                <i class="bi <?= h($iconBi) ?>"></i>
                            </div>

                            <h3 class="hab-card-title"><?= h($formule['nom_formule']) ?></h3>
                            <p class="hab-card-desc"><?= h($formule['description_formule'] ?? 'Description indisponible.') ?></p>

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

                            <ul class="hab-list">
                                <?php if (!empty($garanties)): ?>
                                    <?php foreach ($garanties as $garantie): ?>
                                        <?php
                                            $ng = mb_strtolower(trim((string)($garantie['niveau_couvert_garantie'] ?? 'basique')), 'UTF-8');
                                            $icon = $ng === 'basique' ? 'bi-check2-circle' : ($ng === 'option' ? 'bi-plus-circle' : 'bi-x-circle');
                                        ?>
                                        <li>
                                            <i class="bi <?= h($icon) ?>"></i>
                                            <div>
                                                <?= h($garantie['nom_garantie']) ?>
                                                <span>(<?= h($garantie['niveau_couvert_garantie']) ?>)</span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li>
                                        <i class="bi bi-info-circle"></i>
                                        <div>Aucune garantie configurée <span>(à compléter)</span></div>
                                    </li>
                                <?php endif; ?>
                            </ul>

                            <div class="hab-actions">
                                <button type="button"
                                        class="devis-btn choose-hab-btn"
                                        data-formule="<?= h($formule['nom_formule']) ?>"
                                        onclick="openHabitationModal(<?= json_encode($formule['nom_formule']) ?>)">
                                    Choisir cette formule
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="hab-card">
                        <div class="hab-icon economique">
                            <i class="bi bi-house-door-fill"></i>
                        </div>
                        <h3 class="hab-card-title">Aucune formule</h3>
                        <p class="hab-card-desc">Ajoutez d’abord des formules habitation depuis le back-office pour les afficher ici.</p>
                    </article>
                <?php endif; ?>
            </div><div class="hab-bottom-note">
                <div class="hab-bottom-note-left">
                    <i class="bi bi-shield-check"></i>
                    <div>
                        <strong>Les deux formules protègent votre logement</strong>
                        <span>La formule Privilège ajoute une couverture plus large sur les biens et les personnes.</span>
                    </div>
                </div>

                <div class="hab-bottom-note-right">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <strong>Options supplémentaires possibles</strong>
                        <span>Vous pourrez compléter votre protection au moment de la demande de devis.</span>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Modal -->
<div id="habitationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2>Demande d’assurance habitation</h2>
                <p>Complétez les informations nécessaires pour préparer votre contrat habitation.</p>
            </div>
            <button type="button" class="close-btn" onclick="closeHabitationModal()">&times;</button>
        </div>

        <div class="modal-body">
            <form id="contratHabitationForm" method="post" action="#">
                <input type="hidden" name="type_contrat" value="Habitation">
                <input type="hidden" name="id_categorie" value="2">

                <div class="form-section">
    <h2 class="form-section-title">I - Formule choisie</h2>

    <div class="form-grid-1">
        <div class="form-group">
            <label for="formule_habitation">Formule habitation <span class="req">*</span></label>
            <select class="form-select" id="formule_habitation" name="formule_habitation" onchange="toggleHabitationPanels()" required>
                <option value="">— Veuillez choisir une option —</option>
                <option value="VIVATIS Économique">VIVATIS Économique</option>
                <option value="VIVATIS Privilège">VIVATIS Privilège</option>
            </select>
            <div class="error-message" id="error_formule_habitation"></div>
        </div>
    </div>

    <!-- Panel Economique -->
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
                                <?= h($formule['description_formule'] ?? 'Cette formule propose un niveau de couverture habitation adapté à différents besoins de logement et de protection.') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">II - Informations sur le logement</h2>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="type_logement">Type de logement <span class="req">*</span></label>
                            <select class="form-select" id="type_logement" name="type_logement">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Appartement</option>
                                <option>Maison</option>
                                <option>Villa</option>
                                <option>Studio</option>
                            </select>
                            <div class="error-message" id="error_type_logement"></div>
                        </div>

                        <div class="form-group">
                            <label for="statut_occupation">Statut d’occupation <span class="req">*</span></label>
                            <select class="form-select" id="statut_occupation" name="statut_occupation">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Propriétaire</option>
                                <option>Locataire</option>
                                <option>Occupant à titre gratuit</option>
                            </select>
                            <div class="error-message" id="error_statut_occupation"></div>
                        </div>

                        <div class="form-group">
                            <label for="adresse_logement">Adresse du logement <span class="req">*</span></label>
                            <input type="text" class="form-control" id="adresse_logement" name="adresse_logement" placeholder="Adresse complète">
                            <div class="error-message" id="error_adresse_logement"></div>
                        </div>

                        <div class="form-group">
                            <label for="surface_logement">Surface (m²) <span class="req">*</span></label>
                            <input type="number" class="form-control" id="surface_logement" name="surface_logement" placeholder="Surface en m²">
                            <div class="error-message" id="error_surface_logement"></div>
                        </div>

                        <div class="form-group">
                            <label for="nb_pieces">Nombre de pièces <span class="req">*</span></label>
                            <input type="number" class="form-control" id="nb_pieces" name="nb_pieces" placeholder="Nombre de pièces">
                            <div class="error-message" id="error_nb_pieces"></div>
                        </div>

                        <div class="form-group">
                            <label for="valeur_biens">Valeur estimée des biens <span class="req">*</span></label>
                            <input type="number" class="form-control" id="valeur_biens" name="valeur_biens" placeholder="Valeur en DT">
                            <div class="error-message" id="error_valeur_biens"></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">III - Informations de l’assuré</h2>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="nom">Nom <span class="req">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($clientNom) ?>" placeholder="Nom">
                            <div class="error-message" id="error_nom"></div>
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom <span class="req">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($clientPrenom) ?>" placeholder="Prénom">
                            <div class="error-message" id="error_prenom"></div>
                        </div>

                        <div class="form-group">
                            <label for="email">E-mail <span class="req">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($clientEmail) ?>" placeholder="Adresse e-mail">
                            <div class="error-message" id="error_email"></div>
                        </div>

                        <div class="form-group">
                            <label for="telephone">Téléphone <span class="req">*</span></label>
                            <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Numéro de téléphone">
                            <div class="error-message" id="error_telephone"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-protex btn-light-protex" onclick="closeHabitationModal()">Annuler</button>
                    <button type="reset" class="btn-protex btn-light-protex">Réinitialiser</button>
                    <button type="submit" class="btn-protex btn-primary-protex">Valider votre demande</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const formulePanels = <?= json_encode($formulePanels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function openHabitationModal(formule = '') {
        const modal = document.getElementById('habitationModal');
        const formuleSelect = document.getElementById('formule');

        if (!modal) return;

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        if (formuleSelect) {
            formuleSelect.value = formule || '';
        }

        toggleCoveragePanels();
    }

    function closeHabitationModal() {
        const modal = document.getElementById('habitationModal');
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

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('habitationModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target.id === 'habitationModal') {
                    closeHabitationModal();
                }
            });
        }

        document.querySelectorAll('.choose-hab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openHabitationModal(this.getAttribute('data-formule') || '');
            });
        });

        toggleCoveragePanels();
    });
</script>

</body>
</html>