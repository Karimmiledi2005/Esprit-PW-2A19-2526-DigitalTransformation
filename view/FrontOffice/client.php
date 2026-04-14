<?php
require_once '../Controller/ContratController.php';

$contratC = new ContratController();
$list = $contratC->listContrats();

// (optionnel) simuler utilisateur connecté
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
                            <div class="dropdown-name"><?php echo $nom; ?></div>
                            <div class="dropdown-email"><?php echo $email; ?></div>
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
            <div class="page-title-main">Contrats</div>
            <div class="page-breadcrumb">
                <i class="bi bi-house"></i>
                <a href="client.php" style="color:inherit;text-decoration:none;">Accueil</a>
                <i class="bi bi-chevron-right" style="font-size:10px"></i>
                <span>Contrats</span>
            </div>
        </div>
    </div>
    <div class="contracts-intro">
    <div>
        <h2>Choisissez une catégorie</h2>
        <p>Sélectionnez le type d’assurance avant de remplir votre contrat.</p>
    </div>
</div>

<div class="categories-grid">
    <a href="contratauto.php" class="category-card active">
        <div class="category-icon auto">
            <i class="bi bi-car-front-fill"></i>
        </div>
        <h3>Auto</h3>
        <p>Assurance automobile et mobilité.</p>
    </a>

    <a href="contrathabitation.php" class="category-card">
        <div class="category-icon habitation">
            <i class="bi bi-house-door-fill"></i>
        </div>
        <h3>Habitation</h3>
        <p>Protection du logement et du patrimoine.</p>
    </a>

    <a href="contratsante.php" class="category-card">
        <div class="category-icon sante">
            <i class="bi bi-heart-pulse-fill"></i>
        </div>
        <h3>Santé</h3>
        <p>Couverture santé et assistance médicale.</p>
    </a>

    <a href="contratprotection.php" class="category-card">
        <div class="category-icon protection">
            <i class="bi bi-shield-check"></i>
        </div>
        <h3>Protection</h3>
        <p>Prévoyance, sécurité et assistance.</p>
    </a>
</div>
    <section class="content contracts-page">

        <div class="contracts-header">
            <div>
                <h2>Mes contrats</h2>
                <p>Consultez et gérez facilement tous vos contrats</p>
            </div>
        </div>

        <div class="contracts-list">

<?php foreach ($list as $contrat) { ?>

    <div class="contract-banner">
        <div class="contract-banner-left">
            <div class="contract-icon 
                <?php
                    if ($contrat['type_contrat'] === 'Auto') echo 'auto';
                    elseif ($contrat['type_contrat'] === 'Santé' || $contrat['type_contrat'] === 'Sante') echo 'health';
                    elseif ($contrat['type_contrat'] === 'Habitation') echo 'home';
                    elseif ($contrat['type_contrat'] === 'Protection') echo 'protection';
                ?>">
                
                <?php if ($contrat['type_contrat'] === 'Auto') { ?>
                    <i class="bi bi-car-front-fill"></i>
                <?php } elseif ($contrat['type_contrat'] === 'Santé' || $contrat['type_contrat'] === 'Sante') { ?>
                    <i class="bi bi-heart-pulse-fill"></i>
                <?php } elseif ($contrat['type_contrat'] === 'Habitation') { ?>
                    <i class="bi bi-house-door-fill"></i>
                <?php } elseif ($contrat['type_contrat'] === 'Protection') { ?>
                    <i class="bi bi-shield-check"></i>
                <?php } ?>
            </div>

            <div>
                <h3>Contrat <?php echo $contrat['type_contrat']; ?></h3>
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
if ($contrat['statut'] === 'actif') {
    $statusClass = 'active';
} elseif ($contrat['statut'] === 'en attente') {
    $statusClass = 'waiting';
} elseif ($contrat['statut'] === 'resilie' || $contrat['statut'] === 'résilié') {
    $statusClass = 'expired';
}
?>
<span class="status-badge <?php echo $statusClass; ?>">
    <?php echo $contrat['statut']; ?>
</span>
                <?php echo $contrat['statut']; ?>
            </span>

            <div class="contract-actions">
                <a href="#" class="action-btn" onclick="toggleGaranties('garantie-<?php echo $contrat['id_contrat']; ?>'); return false;">Voir</a>
                <a href="#" class="action-btn secondary">Modifier</a>
            </div>
        </div>
    </div>

    <div id="garantie-<?php echo $contrat['id_contrat']; ?>" class="garanties-box">
        <div class="garanties-header">
            <div>
                <h3>Garanties associées</h3>
                <p>Les garanties liées à ce contrat</p>
            </div>
            <a href="#" class="btn btn-primary">+ Ajouter une garantie</a>
        </div>

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
                <div class="garantie-actions">
                    <a href="#" class="action-btn">Modifier</a>
                    <a href="#" class="action-btn secondary">Supprimer</a>
                </div>
            </div>
        <?php } ?>
    </div>

<?php } ?>

</div>
    </section>

</main>
</div>
<script src="assets/js/main.js"></script>
<div id="modalContrat" class="modal">

    <div class="modal-content">

        <div class="modal-header">
            <h2>Nouveau contrat</h2>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>

    <form method="POST" action="../View/BackOffice/addcontrat.php">

    <div class="form-group">
        <label>Type de contrat</label>
        <select name="type_contrat">
            <option value="Auto">Auto</option>
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
<script>
function openModal() {
    document.getElementById("modalContrat").style.display = "flex";
}

function closeModal() {
    document.getElementById("modalContrat").style.display = "none";
}
function toggleGaranties(id) {
    const box = document.getElementById(id);

    if (box.style.display === "block") {
        box.style.display = "none";
    } else {
        box.style.display = "block";
    }
}
</script>
</body>
</html>
