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

function autoNiveauLabel(string $niveau): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'Essentiel';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'Intermédiaire';
    if ($niveau === 'premium') return 'Premium';
    return ucfirst($niveau ?: 'Standard');
}

function autoProfileLabel(string $niveau, string $nom): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'Budget maîtrisé';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'Usage régulier';
    if ($niveau === 'premium') return 'Protection maximale';

    $nom = mb_strtolower(trim($nom), 'UTF-8');
    if (str_contains($nom, 'class')) return 'Budget maîtrisé';
    if (str_contains($nom, 'tierce')) return 'Usage régulier';
    if (str_contains($nom, 'risque')) return 'Protection maximale';
    return 'Profil standard';
}

function autoIconClass(int $index, string $niveau): string {
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

function autoIconBi(int $index, string $niveau): string {
    $niveau = mb_strtolower(trim($niveau), 'UTF-8');
    if ($niveau === 'essentiel') return 'bi-car-front-fill';
    if ($niveau === 'intermédiaire' || $niveau === 'intermediaire') return 'bi-shield-plus';
    if ($niveau === 'premium') return 'bi-stars';
    return match ($index % 3) {
        0 => 'bi-car-front-fill',
        1 => 'bi-shield-plus',
        default => 'bi-stars',
    };
}

$pdo = config::getConnexion();
$categorie = null;
$formules = [];
$garantiesByFormule = [];
$formulePanels = [];

try {
    $catStmt = $pdo->prepare("SELECT * FROM categorie WHERE LOWER(nom_categorie) = 'auto' LIMIT 1");
    $catStmt->execute();
    $categorie = $catStmt->fetch(PDO::FETCH_ASSOC);

    if (!$categorie) {
        $catStmt = $pdo->prepare("SELECT * FROM categorie WHERE id_categorie = 2 LIMIT 1");
        $catStmt->execute();
        $categorie = $catStmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($categorie) {
        $stmt = $pdo->prepare("SELECT * FROM formule WHERE id_categorie = ?");
        $stmt->execute([$categorie['id_categorie']]);
        $formules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($formules as $f) {
            $gid = $f['id_formule'];
            $g = $pdo->prepare("SELECT * FROM garantie WHERE id_formule = ?");
            $g->execute([$gid]);
            $garantiesByFormule[$gid] = $g->fetchAll(PDO::FETCH_ASSOC);
            $formulePanels[$f['nom_formule']] = 'panel-' . slugify($f['nom_formule']);
        }
    }
} catch(Exception $e) {}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Assurance Auto — Protex</title>
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
                <div class="page-title-main">Assurance Auto</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.html" style="color:inherit;text-decoration:none;">Accueil</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <a href="contrat.php" style="color:inherit;text-decoration:none;">Contrats</a>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <span>Auto</span>
                </div>
            </div>
        </div>

        <section class="auto-hero">
            <div class="hero-content">
                <div class="hero-chip">
                    <i class="bi bi-shield-check"></i>
                    Protection automobile flexible
                </div>

                <h1 class="hero-title">Choisissez la formule auto qui vous ressemble</h1>

                <p class="hero-text">
                    Comparez les niveaux de couverture, découvrez les garanties incluses
                    et lancez votre demande en quelques clics avec une expérience simple,
                    moderne et rassurante.
                </p>

                <div class="hero-actions">
                    <a href="#formules-auto" class="hero-btn primary">
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
                    <h3>Pourquoi choisir Protex Auto ?</h3>
                    <ul class="hero-points">
                        <li><i class="bi bi-check-circle-fill"></i><span>Des formules claires adaptées à votre budget.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Des garanties détaillées avant même de remplir votre demande.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Un formulaire guidé qui s’ouvre seulement après votre choix.</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Une expérience plus propre qu’un long formulaire affiché d’un coup.</span></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section-block" id="formules-auto">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Nos formules auto</h2>
                    <p class="section-subtitle">Comparez les garanties et choisissez la couverture qui correspond à votre besoin.</p>
                </div>
            </div>

            <div class="formules-grid">
                <?php if (!empty($formules)): ?>
                    <?php foreach ($formules as $index => $formule): ?>
                        <?php
                            $fid = (int)$formule['id_formule'];
                            $niveau = autoNiveauLabel((string)($formule['niveau_formule'] ?? ''));
                            $profil = autoProfileLabel((string)($formule['niveau_formule'] ?? ''), (string)$formule['nom_formule']);
                            $iconClass = autoIconClass($index, (string)($formule['niveau_formule'] ?? ''));
                            $iconBi = autoIconBi($index, (string)($formule['niveau_formule'] ?? ''));
                            $garanties = $garantiesByFormule[$fid] ?? [];
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
                                            $icon = $ng === 'basique' ? 'bi-check2-circle' : ($ng === 'option' ? 'bi-plus-circle' : 'bi-x-circle');
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
                                <button type="button"
                                        class="choose-btn choose-auto-btn"
                                        data-formule="<?= h($formule['nom_formule']) ?>"
                                        onclick="openAutoModal(<?= json_encode($formule['nom_formule']) ?>)">
                                    Choisir cette formule
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="formule-card">
                        <div class="formule-icon icon-classique">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <h3 class="formule-name">Aucune formule</h3>
                        <p class="formule-desc">Ajoutez d’abord des formules auto depuis le back-office pour les afficher ici.</p>
                    </article>
                <?php endif; ?>
            </div><div class="explication-box">
                <div class="info-card">
                    <h3>Comment ça marche ?</h3>
                    <p>
                        Vous commencez par consulter les formules et leurs garanties.
                        Une fois votre choix fait, un formulaire détaillé s’ouvre dans une fenêtre
                        popup propre pour saisir les informations du véhicule et de l’assuré.
                    </p>
                </div>

                <div class="info-card">
                    <h3>Pourquoi cette approche ?</h3>
                    <p>
                        L’utilisateur ne se retrouve pas directement face à un grand formulaire.
                        Il comprend d’abord le produit, puis passe à l’étape de saisie avec plus de clarté.
                    </p>
                </div>
            </div>
        </section>
    </main>
</div>

<div id="autoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2>Demande d’assurance auto</h2>
                <p>Complétez les informations nécessaires pour préparer votre contrat.</p>
            </div>
            <button type="button" class="close-btn" onclick="closeAutoModal()">&times;</button>
        </div>

        <div class="modal-body">
            <form id="contratAutoForm" method="post" action="#">
                <input type="hidden" name="type_contrat" value="Auto">
                <input type="hidden" name="id_categorie" value="1">

                <div class="form-section">
                    <h2 class="form-section-title">I - Couvertures souhaitées</h2>

                    <div class="form-grid-1">
                        <div class="form-group">
                            <label for="formule">Formule choisie <span class="req">*</span></label>
                            <select class="form-select" id="formule" name="formule" onchange="toggleCoveragePanels()" required>
                                <option value="">— Veuillez choisir une option —</option>
                                <option value="Classique">Classique</option>
                                <option value="Tierce collision">Tierce collision</option>
                                <option value="Tous risques">Tous risques</option>
                            </select>
                            <div class="error-message" id="error_formule"></div>
                        </div>
                    </div>

                    <div id="panel-classique" class="coverage-panel">
    <h3>Garanties de la formule Classique</h3>

    <div class="check-grid">
        <label class="check-item fixed">
            <input type="checkbox" checked disabled>
            Responsabilité civile
        </label>

        <label class="check-item fixed">
            <input type="checkbox" checked disabled>
            Défense et recours
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Vol">
            Vol
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Incendie">
            Incendie
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Bris de glace">
            Bris de glace
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Assistance">
            Assistance
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Individuelle conducteur">
            Individuelle conducteur
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Personnes transportées">
            Personnes transportées
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Catastrophes naturelles">
            Catastrophes naturelles
        </label>

        <label class="check-item disabled">
            <input type="checkbox" disabled>
            Tierce collision
        </label>

        <label class="check-item disabled">
            <input type="checkbox" disabled>
            Dommages aux véhicules
        </label>

        <label class="check-item disabled">
            <input type="checkbox" disabled>
            Perte totale
        </label>
    </div>

    <div class="hint-box">
        Cette formule contient les garanties minimales obligatoires. Vous pouvez ajouter quelques garanties complémentaires, mais les protections avancées restent indisponibles.
    </div>
</div>

                    <div id="panel-tierce" class="coverage-panel">
    <h3>Garanties de la formule Intermédiaire</h3>

    <div class="check-grid">
        <label class="check-item fixed">
            <input type="checkbox" checked disabled>
            Responsabilité civile
        </label>

        <label class="check-item fixed">
            <input type="checkbox" checked disabled>
            Défense et recours
        </label>

        <label class="check-item fixed">
            <input type="checkbox" checked disabled>
            Tierce collision
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Vol">
            Vol
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Incendie">
            Incendie
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Bris de glace">
            Bris de glace
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Assistance">
            Assistance
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Individuelle conducteur">
            Individuelle conducteur
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Personnes transportées">
            Personnes transportées
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Catastrophes naturelles">
            Catastrophes naturelles
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Perte totale">
            Perte totale
        </label>

        <label class="check-item disabled">
            <input type="checkbox" disabled>
            Dommages aux véhicules
        </label>
    </div>

    <div class="hint-box">
        Cette formule ajoute la garantie Tierce collision à la base classique. Elle offre une protection intermédiaire avec plus d’options activables.
    </div>
</div>

                    <div id="panel-tous-risques" class="coverage-panel">
    <h3>Garanties de la formule Tous risques</h3>

    <div class="check-grid">
        <label class="check-item fixed">
            <input type="checkbox" checked disabled>
            Responsabilité civile
        </label>

        <label class="check-item fixed">
            <input type="checkbox" checked disabled>
            Défense et recours
        </label>

        <label class="check-item fixed">
            <input type="checkbox" checked disabled>
            Dommages aux véhicules
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Vol">
            Vol
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Incendie">
            Incendie
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Bris de glace">
            Bris de glace
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Assistance">
            Assistance
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Individuelle conducteur">
            Individuelle conducteur
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Personnes transportées">
            Personnes transportées
        </label>

        <label class="check-item">
            <input type="checkbox" name="garanties[]" value="Catastrophes naturelles">
            Catastrophes naturelles
        </label>

        <label class="check-item disabled">
            <input type="checkbox" disabled>
            Tierce collision
        </label>

        <label class="check-item disabled">
            <input type="checkbox" disabled>
            Perte totale
        </label>
    </div>

    <div class="hint-box">
        Cette formule est la plus forte. Elle couvre déjà les dommages du véhicule, donc certaines garanties inférieures ne sont plus nécessaires.
    </div>
</div>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">II - Votre véhicule</h2>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="immatriculation">Immatriculation du véhicule <span class="req">*</span></label>
                            <input type="text" class="form-control" id="immatriculation" name="immatriculation" placeholder="Ex : 123 TUN 4567">
                            <div class="error-message" id="error_immatriculation"></div>
                        </div>

                        <div class="form-group">
                            <label for="marque">Marque du véhicule <span class="req">*</span></label>
                            <select class="form-select" id="marque" name="marque">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Toyota</option>
                                <option>Hyundai</option>
                                <option>Kia</option>
                                <option>Peugeot</option>
                                <option>Renault</option>
                                <option>Volkswagen</option>
                                <option>Mercedes</option>
                                <option>BMW</option>
                            </select>
                            <div class="error-message" id="error_marque"></div>
                        </div>

                        <div class="form-group">
                            <label for="usage_vehicule">Usage du véhicule <span class="req">*</span></label>
                            <select class="form-select" id="usage_vehicule" name="usage_vehicule">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Personnel</option>
                                <option>Professionnel</option>
                                <option>Mixte</option>
                                <option>Transport</option>
                            </select>
                            <div class="error-message" id="error_usage_vehicule"></div>
                        </div>

                        <div class="form-group">
                            <label for="kilometrage">Kilométrage du véhicule <span class="req">*</span></label>
                            <select class="form-select" id="kilometrage" name="kilometrage">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Moins de 10 000 km</option>
                                <option>10 000 - 30 000 km</option>
                                <option>30 000 - 60 000 km</option>
                                <option>Plus de 60 000 km</option>
                            </select>
                            <div class="error-message" id="error_kilometrage"></div>
                        </div>

                        <div class="form-group">
                            <label for="puissance">Puissance du véhicule (CV) <span class="req">*</span></label>
                            <input type="number" class="form-control" id="puissance" name="puissance" placeholder="Puissance en CV">
                            <div class="error-message" id="error_puissance"></div>
                        </div>

                        <div class="form-group">
                            <label for="date_circulation">Date de 1ère mise en circulation <span class="req">*</span></label>
                            <input type="date" class="form-control" id="date_circulation" name="date_circulation">
                            <div class="error-message" id="error_date_circulation"></div>
                        </div>

                        <div class="form-group">
                            <label for="valeur_venale">Valeur vénale <span class="req">*</span></label>
                            <input type="number" class="form-control" id="valeur_venale" name="valeur_venale" placeholder="Valeur marchande">
                            <div class="error-message" id="error_valeur_venale"></div>
                        </div>

                        <div class="form-group">
                            <label for="financement">Financement du véhicule <span class="req">*</span></label>
                            <select class="form-select" id="financement" name="financement">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Achat comptant</option>
                                <option>Crédit bancaire</option>
                                <option>Leasing</option>
                            </select>
                            <div class="error-message" id="error_financement"></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">III - L’assuré</h2>

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
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($clientEmail) ?>" placeholder="Adresse e-mail">
                            <div class="error-message" id="error_email"></div>
                        </div>

                        <div class="form-group">
                            <label for="nom">Nom <span class="req">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($clientNom) ?>" placeholder="Nom de famille">
                            <div class="error-message" id="error_nom"></div>
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom <span class="req">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($clientPrenom) ?>" placeholder="Prénom">
                            <div class="error-message" id="error_prenom"></div>
                        </div>

                        <div class="form-group">
                            <label for="telephone">N° de téléphone <span class="req">*</span></label>
                            <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Votre numéro de téléphone">
                            <div class="error-message" id="error_telephone"></div>
                        </div>

                        <div class="form-group">
                            <label for="date_naissance">Date de naissance <span class="req">*</span></label>
                            <input type="date" class="form-control" id="date_naissance" name="date_naissance">
                            <div class="error-message" id="error_date_naissance"></div>
                        </div>

                        <div class="form-group">
                            <label for="nationalite">Nationalité <span class="req">*</span></label>
                            <select class="form-select" id="nationalite" name="nationalite">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Tunisienne</option>
                                <option>Française</option>
                                <option>Algérienne</option>
                                <option>Autre</option>
                            </select>
                            <div class="error-message" id="error_nationalite"></div>
                        </div>

                        <div class="form-group">
                            <label for="situation_professionnelle">Situation professionnelle <span class="req">*</span></label>
                            <select class="form-select" id="situation_professionnelle" name="situation_professionnelle">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Salarié</option>
                                <option>Étudiant</option>
                                <option>Fonctionnaire</option>
                                <option>Indépendant</option>
                                <option>Retraité</option>
                            </select>
                            <div class="error-message" id="error_situation_professionnelle"></div>
                        </div>

                        <div class="form-group">
                            <label for="adresse">Adresse personnelle principale <span class="req">*</span></label>
                            <input type="text" class="form-control" id="adresse" name="adresse" placeholder="Votre adresse personnelle">
                            <div class="error-message" id="error_adresse"></div>
                        </div>

                        <div class="form-group">
                            <label for="situation_matrimoniale">Situation matrimoniale</label>
                            <select class="form-select" id="situation_matrimoniale" name="situation_matrimoniale">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Célibataire</option>
                                <option>Marié(e)</option>
                                <option>Divorcé(e)</option>
                                <option>Veuf / Veuve</option>
                            </select>
                            <div class="error-message" id="error_situation_matrimoniale"></div>
                        </div>

                        <div class="form-group">
                            <label for="revenu_annuel">Niveau de revenu annuel brut en Dinars</label>
                            <select class="form-select" id="revenu_annuel" name="revenu_annuel">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Moins de 10 000 DT</option>
                                <option>10 000 - 20 000 DT</option>
                                <option>20 000 - 40 000 DT</option>
                                <option>Plus de 40 000 DT</option>
                            </select>
                            <div class="error-message" id="error_revenu_annuel"></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">IV - Informations complémentaires</h2>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="estimation_km">Estimation kilométrage annuel parcouru</label>
                            <select class="form-select" id="estimation_km" name="estimation_km">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Moins de 10 000 km</option>
                                <option>10 000 - 20 000 km</option>
                                <option>20 000 - 30 000 km</option>
                                <option>Plus de 30 000 km</option>
                            </select>
                            <div class="error-message" id="error_estimation_km"></div>
                        </div>

                        <div class="form-group">
                            <label for="conducteurs">Le ou les conducteurs du véhicule</label>
                            <select class="form-select" id="conducteurs" name="conducteurs">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Conducteur unique</option>
                                <option>Conducteur + conjoint</option>
                                <option>Conducteurs multiples</option>
                            </select>
                            <div class="error-message" id="error_conducteurs"></div>
                        </div>

                        <div class="form-group">
                            <label for="stationnement">Mode de stationnement la nuit</label>
                            <select class="form-select" id="stationnement" name="stationnement">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Garage privé</option>
                                <option>Parking collectif</option>
                                <option>Voie publique</option>
                            </select>
                            <div class="error-message" id="error_stationnement"></div>
                        </div>

                        <div class="form-group">
                            <label for="utilisation">Utilisation du véhicule</label>
                            <select class="form-select" id="utilisation" name="utilisation">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Déplacements quotidiens</option>
                                <option>Usage occasionnel</option>
                                <option>Longs trajets</option>
                            </select>
                            <div class="error-message" id="error_utilisation"></div>
                        </div>

                        <div class="form-group">
                            <label for="trajets_prevus">Trajets prévus</label>
                            <select class="form-select" id="trajets_prevus" name="trajets_prevus">
                                <option value="">— Veuillez choisir une option —</option>
                                <option>Ville</option>
                                <option>Ville + route</option>
                                <option>National</option>
                                <option>International</option>
                            </select>
                            <div class="error-message" id="error_trajets_prevus"></div>
                        </div>

                        <div class="form-group">
                            <label for="details_formule">Commentaires / précisions</label>
                            <textarea class="form-textarea" id="details_formule" name="details_formule" placeholder="Ajoutez des détails utiles sur votre besoin..."></textarea>
                            <div class="error-message" id="error_details_formule"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-protex btn-light-protex" onclick="closeAutoModal()">Annuler</button>
                    <button type="reset" class="btn-protex btn-light-protex">Réinitialiser</button>
                    <button type="submit" class="btn-protex btn-primary-protex">Valider votre demande</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const formulePanels = <?= json_encode($formulePanels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

function openAutoModal(formule = '') {
    const modal = document.getElementById('autoModal');
    const formuleSelect = document.getElementById('formule');

    if (!modal) return;

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';

    if (formuleSelect) {
        formuleSelect.value = formule || '';
    }

    toggleCoveragePanels();
}

function closeAutoModal() {
    const modal = document.getElementById('autoModal');
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
    const modal = document.getElementById('autoModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target.id === 'autoModal') {
                closeAutoModal();
            }
        });
    }

    document.querySelectorAll('.choose-auto-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openAutoModal(this.getAttribute('data-formule') || '');
        });
    });

    toggleCoveragePanels();
});
</script>

</body>
</html>