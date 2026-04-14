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
            <a class="nav-link active" href="mes-contrats.html">
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
        <div class="page-title-main">Contrats Auto</div>
        <div class="page-breadcrumb">
            <i class="bi bi-house"></i>
            <a href="client.php">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:10px"></i>
            <a href="client.php">Contrats</a>
            <i class="bi bi-chevron-right" style="font-size:10px"></i>
            <span>Auto</span>
        </div>
    </div>
</div>
<section class="category-hero-overlay auto-hero">
    <img src="assets/auto-cover.jpg" alt="Assurance auto" class="hero-bg-img">

    <div class="hero-overlay"></div>

    <div class="category-hero-content">
        <span class="category-kicker">Auto</span>
        <h1>Roulez en toute sécurité, à chaque trajet.</h1>
        <p>
            Protégez votre véhicule avec une assurance adaptée à vos déplacements,
            vos besoins et votre tranquillité au quotidien.
        </p>

        <div class="category-hero-actions">
            <a href="#" class="hero-btn primary" onclick="openModal()">Souscrire</a>
            <a href="#auto-contracts" class="hero-btn secondary">Voir mes contrats</a>
        </div>
    </div>
</section>

<section class="auto-types-section">
    <div class="auto-types-header">
        <span class="section-kicker">Formules auto</span>
        <h2>Choisissez votre type d’assurance auto</h2>
        <p>
            Sélectionnez la formule qui correspond à votre budget et à votre niveau
            de protection souhaité avant de remplir votre contrat.
        </p>
    </div>

    <div class="auto-types-grid">
        <div class="auto-type-card recommended" onclick="selectAutoType('Tiers')">
            <div class="auto-type-badge">Basique</div>
            <div class="auto-type-icon tiers">
                <i class="bi bi-shield-check"></i>
            </div>
            <h3>Tiers</h3>
            <p>
                Formule incluant les garanties essentielles avec un niveau de couverture basique
                et un plafond adapté à une protection minimale.
            </p>

            <ul class="auto-type-features">
    <li><i class="bi bi-check-circle-fill included"></i> Garantie Responsabilité civile</li>
    <li><i class="bi bi-check-circle-fill included"></i> Garantie Défense et recours</li>

    <li><i class="bi bi-plus-circle option"></i> Garantie Assistance</li>

    <li><i class="bi bi-circle not-available"></i> Garantie Vol</li>
    <li><i class="bi bi-circle not-available"></i> Garantie Incendie</li>
    <li><i class="bi bi-circle not-available"></i> Garantie Bris de glace</li>
    <li><i class="bi bi-circle not-available"></i> Garantie Dommages tous accidents</li>
</ul>

            <div class="auto-type-footer">
                <span class="auto-type-price">À partir de 850 DT</span>
                <button type="button" class="choose-type-btn">Choisir</button>
            </div>
        </div>

        <div class="auto-type-card featured" onclick="selectAutoType('Tiers étendu')">
            <div class="auto-type-badge">Recommandé</div>
            <div class="auto-type-icon etendu">
                <i class="bi bi-car-front-fill"></i>
            </div>
            <h3>Tiers étendu</h3>
            <p>
                Formule comprenant des garanties complémentaires avec un niveau de couverture
    intermédiaire et un plafond de remboursement plus large.
            </p>

            <ul class="auto-type-features">
    <li><i class="bi bi-check-circle-fill included"></i> Garantie Responsabilité civile</li>
    <li><i class="bi bi-check-circle-fill included"></i> Garantie Vol</li>
    <li><i class="bi bi-check-circle-fill included"></i> Garantie Incendie</li>

    <li><i class="bi bi-plus-circle option"></i> Garantie Bris de glace</li>
    <li><i class="bi bi-plus-circle option"></i> Garantie Assistance</li>

    <li><i class="bi bi-circle not-available"></i> Garantie Dommages tous accidents</li>
</ul>

            <div class="auto-type-footer">
                <span class="auto-type-price">À partir de 1100 DT</span>
                <button type="button" class="choose-type-btn">Choisir</button>
            </div>
        </div>

        <div class="auto-type-card premium" onclick="selectAutoType('Tous risques')">
            <div class="auto-type-badge">Premium</div>
            <div class="auto-type-icon tous-risques">
                <i class="bi bi-stars"></i>
            </div>
            <h3>Tous risques</h3>
            <p>
                Formule regroupant les garanties les plus complètes avec un niveau de couverture
    premium et un plafond de couverture élevé.
            </p>

           <ul class="auto-type-features">
    <li><i class="bi bi-check-circle-fill included"></i> Garantie Responsabilité civile</li>
    <li><i class="bi bi-check-circle-fill included"></i> Garantie Vol</li>
    <li><i class="bi bi-check-circle-fill included"></i> Garantie Incendie</li>
    <li><i class="bi bi-check-circle-fill included"></i> Garantie Bris de glace</li>
    <li><i class="bi bi-check-circle-fill included"></i> Garantie Dommages tous accidents</li>

    <li><i class="bi bi-plus-circle option"></i> Garantie Assistance premium</li>
</ul>
            <div class="auto-type-footer">
                <span class="auto-type-price">À partir de 1450 DT</span>
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

    <div class="guarantees-container"> <!-- ✅ CADRE AJOUTÉ -->

        <h2 class="guarantees-section-title">Comprendre les garanties auto</h2>

        <p class="guarantees-section-subtitle">
            Consultez les garanties essentielles et les garanties facultatives
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
                        <span>Responsabilité civile</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Couvre les dommages matériels et corporels causés aux tiers par le véhicule assuré.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Défense et recours</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Permet la prise en charge des frais juridiques en cas de litige lié à un sinistre.
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
                        <span>Vol</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Couvre le véhicule en cas de vol ou de tentative de vol ainsi que les dommages associés.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Incendie</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Prend en charge les dommages causés par un incendie ou une explosion du véhicule.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Bris de glace</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Couvre la réparation ou le remplacement des vitrages endommagés du véhicule.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Dommages tous accidents</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Prend en charge les dommages du véhicule même lorsque le conducteur est responsable.
                    </div>
                </div>
            </div>

        </div>

    </div> <!-- ✅ FIN CADRE -->

</section>

<section class="content contracts-page">

    <div class="contracts-header">
    <div>
        <h2>Mes Contrats Auto</h2>
        <p>Consultez et gérez vos contrats automobiles.</p>
    </div>
    <a href="#" class="btn btn-primary" onclick="openModal()">
        + Ajouter un contrat
    </a>
</div>

        <div class="contracts-list">

<?php foreach ($list as $contrat) {
    if ($contrat['type_contrat'] === 'Auto') {
?>

<div class="contract-banner">
    <div class="contract-banner-left">
        <div class="contract-icon auto">
            <i class="bi bi-car-front-fill"></i>
        </div>
        <div>
            <h3>Contrat Auto</h3>
            <span class="contract-ref">N° <?php echo $contrat['numero_contrat']; ?></span>
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
            <option value="Auto" selected>Auto</option>
            <option value="Sante">Santé</option>
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

function selectAutoType(type) {
    openModal(type);
}
</script>
</body>
</html>