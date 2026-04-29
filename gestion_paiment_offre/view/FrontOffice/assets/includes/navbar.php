<?php
$frontBase = '/projet_web1/gestion_paiment_offre/view/FrontOffice';
$uri = strtolower($_SERVER['REQUEST_URI']);

function frontActive(string ...$keys): string {
    global $uri;
    foreach ($keys as $key) {
        if (str_contains($uri, strtolower($key))) {
            return 'active';
        }
    }
    return '';
}
?>

<nav class="navbar">
    <a href="<?= $frontBase ?>/client.html" class="navbar-brand">
        <img src="<?= $frontBase ?>/logo.png" alt="logo" width="40" height="40" style="border-radius:10px;">
        <div>
            <div class="logo-text">Protex</div>
            <div class="logo-sub">Assurance Digitale</div>
        </div>
    </a>

    <div class="navbar-nav">

        <a class="nav-link <?= frontActive('ajoutdevis.php') ?>"
           href="<?= $frontBase ?>/ajoutdevis.php">
            <i class="bi bi-file-earmark-text"></i>
            <span class="nav-label">Demande devis</span>
        </a>
        <a class="nav-link <?= frontActive('mes_devis.php') ?>"
            href="<?= $frontBase ?>/mes_devis.php">
            <i class="bi bi-file-earmark-check"></i>
             <span class="nav-label">Mes devis</span>
        </a>

        <a class="nav-link <?= frontActive('client.html') ?>"
           href="<?= $frontBase ?>/client.html">
            <i class="bi bi-grid-1x2"></i>
            <span class="nav-label">Tableau de bord</span>
        </a>

        <a class="nav-link <?= frontActive('mes-contrats.html') ?>"
           href="<?= $frontBase ?>/mes-contrats.html">
            <i class="bi bi-file-earmark-text"></i>
            <span class="nav-label">Contrats</span>
            <span class="nav-badge accent">3</span>
        </a>

        <a class="nav-link <?= frontActive('mes-sinistres.html') ?>"
           href="<?= $frontBase ?>/mes-sinistres.html">
            <i class="bi bi-shield-exclamation"></i>
            <span class="nav-label">Sinistres</span>
            <span class="nav-badge">1</span>
        </a>

        <a class="nav-link <?= frontActive('paiement.php') ?>"
           href="<?= $frontBase ?>/paiement.php">
            <i class="bi bi-credit-card"></i>
            <span class="nav-label">Paiements</span>
        </a>

        <div class="nav-separator"></div>

        <a class="nav-link <?= frontActive('reclamations.html') ?>"
           href="<?= $frontBase ?>/reclamations.html">
            <i class="bi bi-chat-dots"></i>
            <span class="nav-label">Réclamations</span>
        </a>

        <a class="nav-link <?= frontActive('agences.html') ?>"
           href="<?= $frontBase ?>/agences.html">
            <i class="bi bi-geo-alt"></i>
            <span class="nav-label">Agences</span>
        </a>

        <a class="nav-link <?= frontActive('offres.php') ?>"
           href="<?= $frontBase ?>/offres.php">
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

                <a href="<?= $frontBase ?>/monprofile.html" class="dropdown-item">
                    <i class="bi bi-person-circle"></i> Mon profil
                </a>

                <a href="<?= $frontBase ?>/parametres.html" class="dropdown-item">
                    <i class="bi bi-gear"></i> Paramètres
                </a>

                <div class="dropdown-divider"></div>

                <a href="<?= $frontBase ?>/login.html" class="dropdown-item logout">
                    <i class="bi bi-box-arrow-right"></i> Se déconnecter
                </a>
            </div>
        </div>
    </div>
</nav>