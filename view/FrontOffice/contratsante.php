<?php
require_once '../Controller/ContratController.php';

$contratC = new ContratController();
$list = $contratC->listContrats();
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
            <a class="nav-link" href="client.php">
                <i class="bi bi-grid-1x2"></i>
                <span class="nav-label">Tableau de bord</span>
            </a>
            <a class="nav-link active" href="client.php">
    <i class="bi bi-file-earmark-text"></i>
    <span class="nav-label">Contrats</span>
    <span class="nav-badge accent"><?php echo count($list); ?></span>
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
            <div class="nav-separator"></div>
            <a class="nav-link" href="reclamations.html">
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
                <div class="avatar-btn" id="avatarBtn" title="Mon compte">KM</div>
                <div class="avatar-dropdown" id="avatarDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">KM</div>
                        <div class="dropdown-info">
                            <div class="dropdown-name">Karim Miledi</div>
                            <div class="dropdown-email">karim.miledi@email.com</div>
                            <span class="dropdown-role">Client Premium</span>
                        </div>
                    </div>
                    <a href="monprofile.html" class="dropdown-item">
                        <i class="bi bi-person-circle"></i> Mon profil
                    </a>
                    <a href="parametres.html" class="dropdown-item">
                        <i class="bi bi-gear"></i> Paramètres
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="login.html" class="dropdown-item logout">
                        <i class="bi bi-box-arrow-right"></i> Se déconnecter
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ===== MAIN ===== -->
    <main class="main">

    <div class="page-header">
    <div>
        <div class="page-title-main">Contrats Santé</div>
        <div class="page-breadcrumb">
            <i class="bi bi-house"></i>
            <a href="client.php">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:10px"></i>
            <a href="client.php">Contrats</a>
            <i class="bi bi-chevron-right" style="font-size:10px"></i>
            <span>Santé</span>
        </div>
    </div>
</div>
<section class="category-hero-overlay sante-hero">
    <img src="assets/sante-cover.jpg" alt="Assurance santé" class="hero-bg-img">

    <div class="hero-overlay"></div>

    <div class="category-hero-content">
        <span class="category-kicker">Santé</span>
        <h1>Prenez soin de votre santé en toute confiance.</h1>
        <p>
            Profitez d’une couverture santé pensée pour vous accompagner
            dans vos soins, vos consultations et vos imprévus médicaux.
        </p>

        <div class="category-hero-actions">
            <a href="#" class="hero-btn primary" onclick="openModal()">Souscrire</a>
            <a href="#sante-contracts" class="hero-btn secondary">Voir mes contrats</a>
        </div>
    </div>
</section>
<section class="auto-types-section">
    <div class="auto-types-header">
        <span class="section-kicker">Formules santé</span>
        <h2>Choisissez votre formule santé</h2>
        <p>
            Sélectionnez la formule qui correspond à vos besoins médicaux
            et au niveau de couverture souhaité avant de remplir votre contrat.
        </p>
    </div>

    <div class="auto-types-grid">
        <div class="auto-type-card recommended" onclick="selectHealthType('Essentielle')">
            <div class="auto-type-badge">Basique</div>
            <div class="auto-type-icon tiers">
                <i class="bi bi-heart-pulse"></i>
            </div>
            <h3>Essentielle</h3>
            <p>
                Formule de base pour couvrir les soins courants avec une prise en charge simple et accessible.
            </p>

            <ul class="auto-type-features">
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Consultations médicales</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Médicaments</li>

                <li><i class="bi bi-plus-circle option"></i> Garantie Analyses médicales</li>

                <li><i class="bi bi-circle not-available"></i> Garantie Hospitalisation</li>
                <li><i class="bi bi-circle not-available"></i> Garantie Soins dentaires</li>
                <li><i class="bi bi-circle not-available"></i> Garantie Optique</li>
            </ul>

            <div class="auto-type-footer">
                <span class="auto-type-price">À partir de 420 DT</span>
                <button type="button" class="choose-type-btn">Choisir</button>
            </div>
        </div>

        <div class="auto-type-card featured" onclick="selectHealthType('Confort')">
            <div class="auto-type-badge">Recommandé</div>
            <div class="auto-type-icon etendu">
                <i class="bi bi-heart"></i>
            </div>
            <h3>Confort</h3>
            <p>
                Formule équilibrée avec des garanties complémentaires pour une meilleure prise en charge santé.
            </p>

            <ul class="auto-type-features">
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Consultations médicales</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Médicaments</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Hospitalisation</li>

                <li><i class="bi bi-plus-circle option"></i> Garantie Soins dentaires</li>
                <li><i class="bi bi-plus-circle option"></i> Garantie Optique</li>

                <li><i class="bi bi-circle not-available"></i> Garantie Assistance médicale premium</li>
            </ul>

            <div class="auto-type-footer">
                <span class="auto-type-price">À partir de 620 DT</span>
                <button type="button" class="choose-type-btn">Choisir</button>
            </div>
        </div>

        <div class="auto-type-card premium" onclick="selectHealthType('Premium')">
            <div class="auto-type-badge">Premium</div>
            <div class="auto-type-icon tous-risques">
                <i class="bi bi-stars"></i>
            </div>
            <h3>Premium</h3>
            <p>
                Formule complète avec un niveau de couverture élevé pour vos soins essentiels et spécialisés.
            </p>

            <ul class="auto-type-features">
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Consultations médicales</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Médicaments</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Hospitalisation</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Soins dentaires</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Optique</li>

                <li><i class="bi bi-plus-circle option"></i> Garantie Assistance médicale premium</li>
            </ul>

            <div class="auto-type-footer">
                <span class="auto-type-price">À partir de 890 DT</span>
                <button type="button" class="choose-type-btn">Choisir</button>
            </div>
        </div>
    </div>

    <div class="auto-types-legend">
        <div class="legend-item">
            <i class="bi bi-check-circle-fill included"></i>
            <span>Garantie incluse</span>
        </div>

        <div class="legend-item">
            <i class="bi bi-plus-circle option"></i>
            <span>Garantie en option</span>
        </div>

        <div class="legend-item">
            <i class="bi bi-circle not-available"></i>
            <span>Garantie non disponible</span>
        </div>
    </div>
</section>
<section class="guarantees-details">

    <div class="guarantees-container">

        <h2 class="guarantees-section-title">Comprendre les garanties santé</h2>

        <p class="guarantees-section-subtitle">
            Consultez les garanties essentielles et les garanties complémentaires
            pour mieux comprendre le niveau de protection proposé par chaque formule.
        </p>

        <div class="guarantees-wrapper">

            <div class="guarantees-panel">
                <div class="guarantees-panel-header">
                    <h3>Garanties de base</h3>
                    <p>Les protections essentielles incluses dans les formules de base.</p>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Consultations médicales</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Couvre les visites chez les médecins généralistes et spécialistes selon la formule choisie.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Médicaments</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Prend en charge une partie ou la totalité des médicaments prescrits et remboursables.
                    </div>
                </div>
            </div>

            <div class="guarantees-panel">
                <div class="guarantees-panel-header">
                    <h3>Garanties facultatives</h3>
                    <p>Les protections complémentaires selon la formule choisie.</p>
                </div>

                <div class="accordion-item active">
                    <div class="accordion-header">
                        <span>Hospitalisation</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Couvre les frais liés à l’hospitalisation, au séjour et à certains actes médicaux.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Soins dentaires</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Prend en charge certains soins dentaires, traitements et actes de prévention.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Optique</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Couvre les lunettes, verres correcteurs et certains frais liés à la vision.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Assistance médicale premium</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Offre un accompagnement renforcé pour certains besoins médicaux et situations urgentes.
                    </div>
                </div>
            </div>

        </div>

    </div>

</section>

<section class="content contracts-page">

    <div class="contracts-header">
    <div>
        <h2>Contrats Santé</h2>
        <p>Consultez et gérez vos contrats santé.</p>
    </div>
    <a href="#" class="btn btn-primary" onclick="openModal()">
        + Ajouter un contrat
    </a>
</div>

        <div class="contracts-list">

<?php foreach ($list as $contrat) {
    if ($contrat['type_contrat'] === 'Sante' || $contrat['type_contrat'] === 'Santé') {
?>

<div class="contract-banner">

    <div class="contract-banner-left">
        <div class="contract-icon health">
            <i class="bi bi-heart-pulse-fill"></i>
        </div>
        <div>
            <h3>Contrat Santé</h3>
            <span class="contract-ref">
                N° <?php echo $contrat['numero_contrat']; ?>
            </span>
        </div>
    </div>

    <div class="contract-banner-center">
        <div class="info-item">
            <span class="label">Date début</span>
            <strong><?php echo $contrat['date_debut']; ?></strong>
        </div>

        <div class="info-item">
            <span class="label">Date fin</span>
            <strong><?php echo $contrat['date_fin']; ?></strong>
        </div>

        <div class="info-item">
            <span class="label">Prime</span>
            <strong><?php echo $contrat['montant_prime']; ?> DT</strong>
        </div>

        <div class="info-item">
            <span class="label">Franchise</span>
            <strong><?php echo $contrat['franchise']; ?> DT</strong>
        </div>
    </div>

    <div class="contract-banner-right">

    <?php
    $statusClass = '';
    if ($contrat['statut'] === 'actif') $statusClass = 'active';
    elseif ($contrat['statut'] === 'en attente') $statusClass = 'waiting';
    else $statusClass = 'expired';
    ?>

    <span class="status-badge <?php echo $statusClass; ?>">
        <?php echo $contrat['statut']; ?>
    </span>

    <div class="contract-actions">
        <a href="#" class="action-btn"
           onclick="toggleGaranties('garantie-<?php echo $contrat['id_contrat']; ?>'); return false;">
           Voir
        </a>
    </div>

    </div>

</div>

<div id="garantie-<?php echo $contrat['id_contrat']; ?>" class="garanties-box">

<?php
$garanties = $contratC->getGarantiesByContrat($contrat['id_contrat']);
foreach ($garanties as $g) {
?>

    <div class="garantie-item">
        <div class="garantie-left">
            <h4><?php echo $g['nom_garantie']; ?></h4>
            <p><?php echo $g['description']; ?></p>
        </div>

        <div class="garantie-middle">
            <span><strong>Plafond :</strong> <?php echo $g['plafond_couverture']; ?> DT</span>
            <span><strong>Niveau :</strong> <?php echo $g['niveau_couverture']; ?></span>
        </div>
    </div>

<?php } ?>

</div>

<?php } } ?>

</div>
    </section>

</main> <!-- fin de ton contenu -->

<!-- ✅ MODAL ICI -->
<div id="contractModal" class="modal">
    <div class="modal-content">

        <h2>Ajouter un contrat</h2>

        <form method="POST" action="../View/BackOffice/addcontrat.php">

    <div class="form-group">
        <label>Type de contrat</label>
        <select name="type_contrat">
            <option value="Auto">Auto</option>
            <option value="Sante" selected >Santé</option>
            <option value="Habitation">Habitation</option>
            <option value="Protection">Protection</option>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Date début</label>
            <input type="date" name="date_debut">
        </div>

        <div class="form-group">
            <label>Date fin</label>
            <input type="date" name="date_fin">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Prime</label>
            <input type="number" name="montant_prime" placeholder="DT">
        </div>

        <div class="form-group">
            <label>Franchise</label>
            <input type="number" name="franchise" placeholder="DT">
        </div>
    </div>
    <div class="form-group">
    <label>Statut</label>
    <select name="statut">
        <option value="en attente">En attente</option>
        <option value="actif">Actif</option>
        <option value="resilie">Résilié</option>
    </select>
</div>

    <!-- IMPORTANT : numéro contrat -->
    <div class="form-group">
        <label>Numéro du contrat</label>
        <input type="text" name="numero_contrat" placeholder="Ex: CTR-2026-005">
    </div>

    <div class="modal-actions">
        <button type="submit" class="btn btn-primary">Valider</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
    </div>

</form>

    </div>
</div>

<!-- ✅ SCRIPT ICI -->
<script>
function openModal(type = "") {
    document.getElementById("contractModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("contractModal").style.display = "none";
}

function toggleGaranties(id) {
    const box = document.getElementById(id);
    if (!box) return;

    box.style.display = (box.style.display === "block") ? "none" : "block";
}

function selectHealthType(type) {
    openModal(type);
}
</script>
</body>
</html>