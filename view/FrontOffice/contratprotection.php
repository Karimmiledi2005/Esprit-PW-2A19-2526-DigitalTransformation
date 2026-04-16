<?php
require_once '../../controller/contratController.php';

$contratC = new ContratController();
$list = $contratC->listContrats();

$nom = "Karim Miledi";
$email = "karim.miledi@email.com";
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
        <div class="page-title-main">Contrats Protection</div>
        <div class="page-breadcrumb">
            <i class="bi bi-house"></i>
            <a href="clientcontrat.php">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:10px"></i>
            <a href="clientcontrat.php">Contrats</a>
            <i class="bi bi-chevron-right" style="font-size:10px"></i>
            <span>Protection</span>
        </div>
    </div>
</div>
<section class="category-hero-overlay protection-hero">
    <img src="assets/protection-cover.jpg" alt="Assurance protection" class="hero-bg-img">

    <div class="hero-overlay"></div>

    <div class="category-hero-content">
        <span class="category-kicker">Protection</span>
        <h1>Protégez ce qui compte le plus pour vous.</h1>
        <p>
            Choisissez une couverture de protection pour faire face aux aléas
            de la vie avec plus de sérénité et de sécurité.
        </p>

        <div class="category-hero-actions">
            <a href="#" class="hero-btn primary" onclick="openModal()">Souscrire</a>
            <a href="#protection-contracts" class="hero-btn secondary">Voir mes contrats</a>
        </div>
    </div>
</section>
<section class="auto-types-section">
    <div class="auto-types-header">
        <span class="section-kicker">Formules protection</span>
        <h2>Choisissez votre formule de protection</h2>
        <p>
            Sélectionnez la formule adaptée à votre situation pour protéger
            votre avenir et celui de vos proches.
        </p>
    </div>

    <div class="auto-types-grid">

        <!-- Basique -->
        <div class="auto-type-card" onclick="selectProtectionType('Protection')">
            <div class="auto-type-badge">Basique</div>
            <div class="auto-type-icon tiers">
                <i class="bi bi-person"></i>
            </div>

            <h3>Individuelle</h3>
            <p>Protection essentielle pour une seule personne.</p>

            <ul class="auto-type-features">
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Décès</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Invalidité</li>

                <li><i class="bi bi-plus-circle option"></i> Garantie Incapacité de travail</li>

                <li><i class="bi bi-circle not-available"></i> Garantie Assistance</li>
            </ul>

            <div class="auto-type-footer">
                <span class="auto-type-price">À partir de 250 DT</span>
                <button type="button" class="choose-type-btn">Choisir</button>
            </div>
        </div>

        <!-- Recommandé -->
        <div class="auto-type-card featured" onclick="selectProtectionType('Protection')">
            <div class="auto-type-badge">Recommandé</div>
            <div class="auto-type-icon etendu">
                <i class="bi bi-people"></i>
            </div>

            <h3>Familiale</h3>
            <p>Protection pour vous et votre famille.</p>

            <ul class="auto-type-features">
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Décès</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Invalidité</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Incapacité de travail</li>

                <li><i class="bi bi-plus-circle option"></i> Garantie Assistance</li>
            </ul>

            <div class="auto-type-footer">
                <span class="auto-type-price">À partir de 390 DT</span>
                <button class="choose-type-btn">Choisir</button>
            </div>
        </div>

        <!-- Premium -->
        <div class="auto-type-card premium" onclick="selectProtectionType('Protection')">
            <div class="auto-type-badge">Premium</div>
            <div class="auto-type-icon tous-risques">
                <i class="bi bi-shield-check"></i>
            </div>

            <h3>Premium</h3>
            <p>Protection complète contre tous les imprévus de la vie.</p>

            <ul class="auto-type-features">
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Décès</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Invalidité</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Incapacité de travail</li>
                <li><i class="bi bi-check-circle-fill included"></i> Garantie Assistance</li>

                <li><i class="bi bi-plus-circle option"></i> Garantie Protection juridique</li>
            </ul>

            <div class="auto-type-footer">
                <span class="auto-type-price">À partir de 590 DT</span>
                <button class="choose-type-btn">Choisir</button>
            </div>
        </div>

    </div>
</section>
<section class="guarantees-details">

    <div class="guarantees-container">

        <h2 class="guarantees-section-title">Comprendre les garanties de protection</h2>

        <p class="guarantees-section-subtitle">
            Découvrez les garanties incluses et complémentaires pour mieux
            comprendre votre couverture.
        </p>

        <div class="guarantees-wrapper">

            <!-- BASE -->
            <div class="guarantees-panel">
                <div class="guarantees-panel-header">
                    <h3>Garanties de base</h3>
                    <p>Les protections essentielles incluses.</p>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Décès</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Versement d’un capital aux bénéficiaires en cas de décès.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Invalidité</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Indemnisation en cas d’invalidité permanente.
                    </div>
                </div>
            </div>

            <!-- OPTION -->
            <div class="guarantees-panel">
                <div class="guarantees-panel-header">
                    <h3>Garanties facultatives</h3>
                    <p>Protections complémentaires.</p>
                </div>

                <div class="accordion-item active">
                    <div class="accordion-header">
                        <span>Incapacité de travail</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Compensation financière en cas d’arrêt de travail.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Assistance</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Services d’aide en cas d’urgence ou situation critique.
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header">
                        <span>Protection juridique</span>
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div class="accordion-content">
                        Accompagnement et prise en charge des frais juridiques.
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>
<section class="content contracts-page">

    <div class="contracts-header">
    <div>
        <h2>Contrats Protection</h2>
        <p>Consultez et gérez vos contrats de protection.</p>
    </div>
    <a href="#" class="btn btn-primary" onclick="openModal()">
        + Ajouter un contrat
    </a>
</div>

        <div class="contracts-list">

<?php foreach ($list as $contrat) {
    if ($contrat['type_contrat'] === 'Protection') {
?>

<div class="contract-banner">

    <div class="contract-banner-left">
        <div class="contract-icon protection">
            <i class="bi bi-shield-check"></i>
        </div>
        <div>
            <h3>Contrat Protection</h3>
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

<!-- GARANTIES -->
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

        <form method="POST" action="../BackOffice/addContrat.php" id="contratForm" onsubmit="return validateAddContrat(this)" novalidate>

    <div class="form-group">
        <label>Type de contrat</label>
        <select name="type_contrat" id="type_contrat" required>
            <option value="Auto">Auto</option>
            <option value="Sante">Santé</option>
            <option value="Habitation">Habitation</option>
            <option value="Protection" selected>Protection</option>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Date début</label>
            <input type="date" name="date_debut" id="date_debut" required>
        </div>

        <div class="form-group">
            <label>Date fin</label>
            <input type="date" name="date_fin" id="date_fin" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Prime</label>
            <input type="number" name="montant_prime" id="montant_prime" placeholder="DT" required min="0" step="0.01">
        </div>

        <div class="form-group">
            <label>Franchise</label>
            <input type="number" name="franchise" id="franchise" placeholder="DT" required min="0" step="0.01">
        </div>
    </div>
    <div class="form-group">
    <label>Statut</label>
    <select name="statut" id="statut" required>
        <option value="en attente">En attente</option>
        <option value="actif">Actif</option>
        <option value="resilie">Résilié</option>
    </select>
</div>

    <!-- IMPORTANT : numéro contrat -->
    <div class="form-group">
        <label>Numéro du contrat</label>
        <input type="text" name="numero_contrat" id="numero_contrat" placeholder="Ex: CTR-2026-005" required pattern="CTR-[0-9]{4}-[0-9]{3,}">
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

function validateAddContrat(form) {
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    const type = form.querySelector('#type_contrat').value;
    const debut = form.querySelector('#date_debut').value;
    const fin = form.querySelector('#date_fin').value;
    const prime = parseFloat(form.querySelector('#montant_prime').value);
    const franchise = parseFloat(form.querySelector('#franchise').value);
    const numero = form.querySelector('#numero_contrat').value.trim();
    if (!/^CTR-\d{4}-\d{3,}$/.test(numero)) {
        alert('Numéro contrat invalide. Utilisez le format CTR-2026-001.');
        form.querySelector('#numero_contrat').focus();
        return false;
    }
    if (fin <= debut) {
        alert('La date fin doit être après la date début.');
        form.querySelector('#date_fin').focus();
        return false;
    }
    const minValues = {
        'Auto':       { prime: 120, franchise: 80 },
        'Sante':      { prime: 180, franchise: 50 },
        'Habitation': { prime: 320, franchise: 150 },
        'Protection': { prime: 140, franchise: 70 }
    };
    const min = minValues[type] || { prime: 0, franchise: 0 };
    if (prime < min.prime) {
        alert('Prime invalide. Minimum ' + min.prime + ' DT pour ' + type + '.');
        form.querySelector('#montant_prime').focus();
        return false;
    }
    if (franchise < min.franchise) {
        alert('Franchise invalide. Minimum ' + min.franchise + ' DT pour ' + type + '.');
        form.querySelector('#franchise').focus();
        return false;
    }
    return true;
}

function openModal(type = "") {
    const form = document.getElementById('contratForm');
    if (form) {
        form.reset();
        if (type) {
            const select = form.querySelector('#type_contrat');
            if (select) select.value = type;
        }
    }
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

function selectProtectionType(type) {
    openModal(type);
}
</script>

</body>
</html>