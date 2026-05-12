<?php
/**
 * view/FrontOffice/assets/includes/navbar.php
 * FIX P-07 : BASE_URL dynamique
 * FIX P-09 : lien roulette via BASE_URL
 * FIX P-10 : tous les liens cassés corrigés
 * FIX P-11 : avatar depuis $_SESSION
 */

if (!defined('BASE_URL')) {
    $cfgPath = dirname(__DIR__, 4) . '/config.php';
    if (file_exists($cfgPath)) require_once $cfgPath;
}

$frontBase = BASE_URL . '/view/FrontOffice';
$ctrlBase  = BASE_URL . '/controller';
$uri = strtolower($_SERVER['REQUEST_URI'] ?? '');

function frontActive(string ...$keys): string {
    global $uri;
    foreach ($keys as $key) {
        if (str_contains($uri, strtolower($key))) return 'active';
    }
    return '';
}

// Données utilisateur depuis session (FIX P-11)
$sessionPrenom = $_SESSION['user_prenom'] ?? $_SESSION['prenom'] ?? '';
$sessionNom    = $_SESSION['user_nom']    ?? $_SESSION['nom']    ?? '';
$sessionEmail  = $_SESSION['user_email']  ?? '';
$sessionRole   = $_SESSION['user_role']   ?? $_SESSION['role']   ?? 'client';

$displayName = trim($sessionPrenom . ' ' . $sessionNom);
if ($displayName === '') $displayName = 'Mon compte';

$initiales = '';
foreach (array_slice(array_filter(explode(' ', $displayName)), 0, 2) as $p) {
    $initiales .= mb_strtoupper(mb_substr($p, 0, 1));
}
if ($initiales === '') $initiales = '?';

$roleLabel = match($sessionRole) {
    'superadmin' => 'Super Admin',
    'admin'      => 'Administrateur',
    'agent'      => 'Agent',
    default      => 'Client',
};
?>

<style>
/* Scroll horizontal SANS barre visible — défilement smooth */
.navbar { overflow:hidden; position:sticky; top:0; z-index:100; }
.navbar-nav {
    overflow-x:auto;
    overflow-y:hidden;
    flex-wrap:nowrap;
    scroll-behavior:smooth;
    scrollbar-width:none;            /* Firefox */
    -ms-overflow-style:none;         /* IE/Edge */
}
.navbar-nav::-webkit-scrollbar { display:none; height:0; width:0; }  /* Chrome/Safari */
.navbar-nav .nav-link { flex-shrink:0; white-space:nowrap; }
</style>

<nav class="navbar">
    <a href="<?= $frontBase ?>/client.php" class="navbar-brand">
        <img src="<?= $frontBase ?>/logo.png" alt="logo" width="40" height="40" style="border-radius:10px;">
        <div>
            <div class="logo-text">Protex</div>
            <div class="logo-sub">Assurance Digitale</div>
        </div>
    </a>

    <div class="navbar-nav">
        <a class="nav-link <?= frontActive('ajoutdevis') ?>" href="<?= $frontBase ?>/ajoutdevis.php">
            <i class="bi bi-file-earmark-text"></i><span class="nav-label">Demande devis</span>
        </a>
        <a class="nav-link <?= frontActive('mes_devis') ?>" href="<?= $frontBase ?>/mes_devis.php">
            <i class="bi bi-file-earmark-check"></i><span class="nav-label">Mes devis</span>
        </a>
        <a class="nav-link <?= frontActive('client.php','client.html') ?>" href="<?= $frontBase ?>/client.php">
            <i class="bi bi-grid-1x2"></i><span class="nav-label">Tableau de bord</span>
        </a>
        <a class="nav-link <?= frontActive('contrat') ?>" href="<?= $frontBase ?>/contrat.php">
            <i class="bi bi-file-earmark-text"></i><span class="nav-label">Contrats</span>
        </a>
        <a class="nav-link <?= frontActive('sinistre','mes-sinistres') ?>"
           href="<?= $frontBase ?>/mes-sinistres.php">
            <i class="bi bi-shield-exclamation"></i><span class="nav-label">Sinistres</span>
        </a>
        <a class="nav-link <?= frontActive('paiement') ?>" href="<?= $frontBase ?>/paiement.php">
            <i class="bi bi-credit-card"></i><span class="nav-label">Paiements</span>
        </a>
        <a class="nav-link <?= frontActive('roulette') ?>" href="<?= $ctrlBase ?>/RouletteController.php">
            <i class="bi bi-dice-5"></i><span class="nav-label">Roulette 🎰</span>
        </a>
        <a class="nav-link <?= frontActive('jeux','snake','memory') ?>" href="<?= $frontBase ?>/jeux.php">
            <i class="bi bi-controller"></i><span class="nav-label">Jeux</span>
        </a>
        <div class="nav-separator"></div>
        <a class="nav-link <?= frontActive('reseau','chat','friend') ?>" href="<?= $frontBase ?>/reseau.php">
            <i class="bi bi-people"></i><span class="nav-label">Réseau</span>
        </a>
        <a class="nav-link <?= frontActive('reclamation') ?>" href="<?= $frontBase ?>/reclamationList.php">
            <i class="bi bi-chat-dots"></i><span class="nav-label">Réclamations</span>
        </a>
        <a class="nav-link <?= frontActive('agences') ?>" href="<?= $frontBase ?>/agences.html">
            <i class="bi bi-geo-alt"></i><span class="nav-label">Agences</span>
        </a>
        <a class="nav-link <?= frontActive('offres') ?>" href="<?= $frontBase ?>/offres.php">
            <i class="bi bi-stars"></i><span class="nav-label">Nos offres</span>
        </a>
    </div>

    <div class="navbar-right">
        <a href="<?= $frontBase ?>/jeux.php" class="nav-btn" title="Jeux">
            <i class="bi bi-controller"></i>
        </a>
        <a href="#" class="nav-btn" title="Notifications">
            <i class="bi bi-bell"></i><span class="notif-dot"></span>
        </a>
        <a href="#" class="nav-btn" title="Aide"><i class="bi bi-question-circle"></i></a>

        <div class="avatar-wrap">
            <div class="avatar-btn" id="avatarBtn"><?= htmlspecialchars($initiales, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="avatar-dropdown" id="avatarDropdown">
                <div class="dropdown-header">
                    <div class="dropdown-avatar"><?= htmlspecialchars($initiales, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="dropdown-info">
                        <div class="dropdown-name"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="dropdown-email"><?= htmlspecialchars($sessionEmail, ENT_QUOTES, 'UTF-8') ?></div>
                        <span class="dropdown-role"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
                <a href="<?= $frontBase ?>/monprofile.php" class="dropdown-item">
                    <i class="bi bi-person-circle"></i> Mon profil
                </a>
                <div class="dropdown-divider"></div>
                <a href="<?= $ctrlBase ?>/AuthController.php?action=logout" class="dropdown-item logout">
                    <i class="bi bi-box-arrow-right"></i> Se déconnecter
                </a>
            </div>
        </div>
    </div>
</nav>

