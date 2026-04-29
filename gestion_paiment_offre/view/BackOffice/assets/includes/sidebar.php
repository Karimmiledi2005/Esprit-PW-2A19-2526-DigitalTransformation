<?php
/**
 * view/BackOffice/assets/includes/sidebar.php
 * Sidebar unifiée — inclure dans TOUTES les pages back-office
 *
 * Depuis la racine BackOffice/ :
 *   <?php include __DIR__ . '/assets/includes/sidebar.php'; ?>
 *
 * Depuis un sous-dossier (devis/, offres/, paiements/) :
 *   <?php include __DIR__ . '/../assets/includes/sidebar.php'; ?>
 */

$uri = strtolower($_SERVER['REQUEST_URI']);

function sidebarActive(string ...$keywords): string {
    global $uri;
    foreach ($keywords as $kw) {
        if (str_contains($uri, strtolower($kw))) return 'active';
    }
    return '';
}

$base = '/projet_web1/gestion_paiment_offre/view/BackOffice';
$ctrl = '/projet_web1/gestion_paiment_offre/controller';
?>
<aside class="sidebar">

    <div class="sidebar-logo">
        <img src="/projet_web1/gestion_paiment_offre/view/FrontOffice/logo.png"
             alt="Protex" width="40" height="40"
             style="border-radius:10px;object-fit:cover;">
        <div>
            <div class="logo-text">Protex</div>
            <div class="logo-sub">Back-Office</div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">KM</div>
        <div>
            <div class="user-name">Karim Miledi</div>
            <span class="user-role">Administrateur</span>
        </div>
    </div>

    <nav class="sidebar-nav">

        <div class="nav-section">Principal</div>
        <a class="nav-item <?= sidebarActive('admin.php','admin.html') ?>"
           href="<?= $base ?>/admin.html" id="navDashboard">
            <i class="bi bi-grid-1x2"></i> Tableau de bord
        </a>

        <div class="nav-section">Gestion</div>
        <a class="nav-item <?= sidebarActive('admin-users') ?>"
           href="<?= $base ?>/admin-users.php" id="navUsers">
            <i class="bi bi-people"></i> Utilisateurs
            <span class="nav-badge accent">24</span>
        </a>
        <a class="nav-item <?= sidebarActive('admin-contrats') ?>"
           href="<?= $base ?>/admin-contrats.html" id="navContrats">
            <i class="bi bi-file-earmark-text"></i> Contrats
        </a>
        <a class="nav-item <?= sidebarActive('admin-sinistres') ?>"
           href="<?= $base ?>/admin-sinistres.html" id="navSinistres">
            <i class="bi bi-shield-exclamation"></i> Sinistres
        </a>
        <a class="nav-item <?= sidebarActive('paiements') ?>"
           href="<?= $base ?>/paiements/liste.php" id="navPaiements">
            <i class="bi bi-credit-card"></i> Paiements
        </a>
        <a class="nav-item <?= sidebarActive('reclamations') ?>"
           href="<?= $base ?>/admin-reclamations.html" id="navReclamations">
            <i class="bi bi-chat-dots"></i> Réclamations
        </a>
        <a class="nav-item <?= sidebarActive('agences') ?>"
           href="<?= $base ?>/admin-agences.html" id="navAgences">
            <i class="bi bi-geo-alt"></i> Agences
        </a>

        <div class="nav-section">Mes modules</div>
        <a class="nav-item <?= sidebarActive('offres') ?>"
           href="<?= $base ?>/offres/liste.php" id="navOffres">
            <i class="bi bi-tags"></i> Offres
        </a>
        <a class="nav-item <?= sidebarActive('devis','deviscontroller') ?>"
           href="<?= $ctrl ?>/DevisController.php?action=index" id="navDevis">
            <i class="bi bi-file-earmark-text"></i> Devis
        </a>

        <div class="nav-section">Compte</div>
        <a class="nav-item <?= sidebarActive('adminprofile') ?>"
           href="<?= $base ?>/adminprofile.php" id="navProfile">
            <i class="bi bi-person-gear"></i> Mon profil
        </a>

    </nav>

    <div class="sidebar-footer">
        <a href="<?= $base ?>/connexion.html" class="logout-btn">
            <i class="bi bi-box-arrow-left"></i> Se déconnecter
        </a>
    </div>

</aside>