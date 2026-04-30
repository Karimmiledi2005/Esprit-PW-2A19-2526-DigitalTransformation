<?php
session_start();
require_once __DIR__ . '/../../controller/ContratController.php';

// ===== ID client =====
// Remplace cette ligne selon ton système de session réel
$idClient = (int)($_SESSION['id_user'] ?? $_GET['id_user'] ?? $_POST['id_user'] ?? 1);

$controller = new ContratController();
$contrats = $controller->getByClient($idClient);
require_once __DIR__ . '/../../controller/CategorieController.php';

$categorieC = new CategorieController();
$categories = $categorieC->listCategories();
if ($categories instanceof PDOStatement) {
    $categories = $categories->fetchAll(PDO::FETCH_ASSOC);
}
if (!is_array($categories)) {
    $categories = [];
}

// ===== Helpers =====
function statusClass(?string $statut): string
{
    $s = strtolower(trim((string)$statut));

    return match ($s) {
        'actif', 'active' => 'active',
        'en attente', 'pending' => 'waiting',
        'expiré', 'expire', 'résilié', 'resilie', 'inactive' => 'expired',
        'refusé', 'refuse' => 'refused',
        default => 'waiting',
    };
}

function typeIcon(?string $type): array
{
    $t = strtolower(trim((string)$type));

    return match ($t) {
        'auto' => ['icon' => 'bi-car-front-fill', 'class' => 'auto'],
        'habitation' => ['icon' => 'bi-house-door-fill', 'class' => 'habitation'],
        'sante', 'santé' => ['icon' => 'bi-heart-pulse-fill', 'class' => 'sante'],
        'protection' => ['icon' => 'bi-shield-check', 'class' => 'protection'],
        default => ['icon' => 'bi-file-earmark-text', 'class' => 'default'],
    };
}

function formatDateFr(?string $date): string
{
    if (!$date) return '-';

    $timestamp = strtotime($date);
    if ($timestamp === false) return htmlspecialchars($date);

    return date('d/m/Y', $timestamp);
}


function normalizeCategoryName(?string $name): string
{
    $name = strtolower(trim((string)$name));
    return str_replace(['é', 'è', 'ê', 'à', 'ù', 'ô', 'ï', 'î', 'â'], ['e', 'e', 'e', 'a', 'u', 'o', 'i', 'i', 'a'], $name);
}

function categoryConfig(?string $name): array
{
    $normalized = normalizeCategoryName($name);

    return match ($normalized) {
        'auto' => [
            'href' => 'contrat_auto.php',
            'icon' => 'bi-car-front-fill',
            'class' => 'auto',
            'default_description' => 'Assurance automobile et mobilité.',
        ],
        'habitation' => [
            'href' => 'contrat_habitation.php',
            'icon' => 'bi-house-door-fill',
            'class' => 'habitation',
            'default_description' => 'Protection du logement et du patrimoine.',
        ],
        'sante' => [
            'href' => 'contrat_sante.php',
            'icon' => 'bi-heart-pulse-fill',
            'class' => 'sante',
            'default_description' => 'Couverture santé et assistance médicale.',
        ],
        'protection' => [
            'href' => 'contrat_protection.php',
            'icon' => 'bi-shield-check',
            'class' => 'protection',
            'default_description' => 'Prévoyance, sécurité et assistance.',
        ],
        default => [
            'href' => '#',
            'icon' => 'bi-grid-1x2',
            'class' => 'default',
            'default_description' => 'Découvrez cette catégorie d’assurance.',
        ],
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Contrats — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/client.css">

    <style>
        .toast-notif {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--navy-mid);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--text-primary);
            z-index: 9999;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }

        .toast-notif.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-success i { color: var(--success); font-size: 18px; }
        .toast-warning i { color: var(--gold); font-size: 18px; }
        .toast-danger i  { color: var(--danger); font-size: 18px; }

        .empty-contracts {
            padding: 26px;
            border: 1px dashed var(--border);
            border-radius: 18px;
            background: rgba(255,255,255,0.04);
            text-align: center;
            color: var(--text-secondary);
        }
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
                <span class="nav-badge accent"><?= count($contrats) ?></span>
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
                <div class="page-title-main">Contrats</div>
                <div class="page-breadcrumb">
                    <i class="bi bi-house"></i>
                    <a href="client.html" style="color:inherit;text-decoration:none;">Accueil</a>
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
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $index => $categorie): ?>
                    <?php
                        $config = categoryConfig($categorie['nom_categorie'] ?? '');
                        $descriptionCategorie = trim((string)($categorie['description_categorie'] ?? ''));
                        $descriptionToShow = $descriptionCategorie !== ''
                            ? $descriptionCategorie
                            : $config['default_description'];
                    ?>
                    <a href="<?= htmlspecialchars($config['href']) ?>" class="category-card <?= $index === 0 ? 'active' : '' ?>">
                        <div class="category-icon <?= htmlspecialchars($config['class']) ?>">
                            <i class="bi <?= htmlspecialchars($config['icon']) ?>"></i>
                        </div>
                        <h3><?= htmlspecialchars($categorie['nom_categorie'] ?? 'Catégorie') ?></h3>
                        <p><?= htmlspecialchars($descriptionToShow) ?></p>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-contracts" style="grid-column: 1 / -1;">
                    <h3>Aucune catégorie trouvée</h3>
                    <p>Ajoutez d’abord des catégories dans le back-office.</p>
                </div>
            <?php endif; ?>
        </div>

        <section class="content contracts-page">
            <div class="contracts-header">
                <div>
                    <h2>Mes contrats</h2>
                    <p>Consultez et gérez facilement tous vos contrats</p>
                </div>
            </div>

            <div class="contracts-list">
                <?php if (!empty($contrats)): ?>
                    <?php foreach ($contrats as $contrat): ?>
                        <?php
                            $typeData = typeIcon($contrat->getTypeContrat());
                            $badgeClass = statusClass($contrat->getStatutContrat());
                        ?>

                        <div class="contract-banner">
                            <div class="contract-banner-left">
                                <div class="contract-icon <?= htmlspecialchars($typeData['class']) ?>">
                                    <i class="bi <?= htmlspecialchars($typeData['icon']) ?>"></i>
                                </div>

                                <div>
                                    <h3>Contrat <?= htmlspecialchars($contrat->getTypeContrat()) ?></h3>
                                    <span class="contract-ref">
                                        N° <?= htmlspecialchars($contrat->getNumeroContrat()) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="contract-banner-center">
                                <div class="info-item">
                                    <span class="label">Date début</span>
                                    <strong><?= formatDateFr($contrat->getDateDebutContrat()) ?></strong>
                                </div>

                                <div class="info-item">
                                    <span class="label">Date fin</span>
                                    <strong><?= formatDateFr($contrat->getDateFinContrat()) ?></strong>
                                </div>

                                <div class="info-item">
                                    <span class="label">Prime</span>
                                    <strong><?= htmlspecialchars((string)$contrat->getPrimeContrat()) ?> DT</strong>
                                </div>

                                <div class="info-item">
                                    <span class="label">Franchise</span>
                                    <strong><?= htmlspecialchars((string)$contrat->getFranchiseContrat()) ?> DT</strong>
                                </div>
                            </div>

                            <div class="contract-banner-right">
                                <span class="status-badge <?= htmlspecialchars($badgeClass) ?>">
                                    <?= htmlspecialchars($contrat->getStatutContrat()) ?>
                                </span>

                                <div class="contract-actions">
                                    <a href="contratshow.php?id=<?= urlencode((string)$contrat->getIdContrat()) ?>" class="action-btn">
                                        Voir
                                    </a>
                                    <a href="contrat_update_client.php?id=<?= urlencode((string)$contrat->getIdContrat()) ?>" class="action-btn secondary">
                                        Modifier
                                    </a>
                                    <a href="contratcancel.php?id=<?= urlencode((string)$contrat->getIdContrat()) ?>" class="action-btn secondary" onclick="return confirm('Résilier ce contrat ?')">
                                        Résilier
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-contracts">
                        <h3>Aucun contrat trouvé</h3>
                        <p>Le client n’a pas encore de contrats enregistrés.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>