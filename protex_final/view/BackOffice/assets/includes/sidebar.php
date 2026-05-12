<?php
/**
 * view/BackOffice/assets/includes/sidebar.php
 * Sidebar BackOffice — structure camarades + mes 2 tâches (Diagnostique + Messagerie)
 */

if (!defined('BASE_URL')) {
    $cfgPath = dirname(__DIR__, 4) . '/config.php';
    if (file_exists($cfgPath)) require_once $cfgPath;
}

$uri = strtolower($_SERVER['REQUEST_URI'] ?? '');
function sidebarActive(string ...$keywords): string {
    global $uri;
    foreach ($keywords as $kw) {
        if (str_contains($uri, strtolower($kw))) return 'active';
    }
    return '';
}

$_back  = BASE_URL . '/view/BackOffice';
$_ctrl  = BASE_URL . '/controller';
$_front = BASE_URL . '/view/FrontOffice';

// Lecture session
$sidebarUser    = null;
$unreadCount    = 0;
$activeMentions = 0;
$sidebarUid     = (int)($_SESSION['user_id'] ?? $_SESSION['id_user'] ?? 0);

if ($sidebarUid > 0) {
    try {
        if (class_exists('config')) {
            $uDb   = config::getConnexion();
            $uStmt = $uDb->prepare("SELECT nom, prenom, role FROM user WHERE id_user = ?");
            $uStmt->execute([$sidebarUid]);
            $sidebarUser = $uStmt->fetch(PDO::FETCH_ASSOC) ?: null;

            try {
                $msgStmt = $uDb->prepare("
                    SELECT COALESCE(SUM(
                        (SELECT COUNT(*) FROM messages_admin m
                         WHERE m.id_conversation = cp.id_conversation
                           AND m.id_expediteur != ?
                           AND (m.date_envoi > cp.dernier_message_lu OR cp.dernier_message_lu IS NULL))
                    ), 0) AS unread
                    FROM conversation_participants cp WHERE cp.id_user = ?
                ");
                $msgStmt->execute([$sidebarUid, $sidebarUid]);
                $unreadCount = (int)($msgStmt->fetch(PDO::FETCH_ASSOC)['unread'] ?? 0);

                $amStmt = $uDb->prepare("SELECT COUNT(*) FROM message_mentions WHERE id_user_mentionne = ? AND est_resolu = 0");
                $amStmt->execute([$sidebarUid]);
                $activeMentions = (int)($amStmt->fetchColumn() ?? 0);
            } catch (Exception $ign2) {}
        }
    } catch (Exception $ign) {}
}

$sidebarNom = $sidebarUser
    ? trim(($sidebarUser['prenom'] ?? '') . ' ' . ($sidebarUser['nom'] ?? ''))
    : trim(($_SESSION['user_prenom'] ?? $_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? $_SESSION['nom'] ?? ''));
if ($sidebarNom === '') $sidebarNom = 'Admin';

$sidebarRole = $sidebarUser
    ? ucfirst($sidebarUser['role'] ?? 'Utilisateur')
    : ucfirst($_SESSION['user_role'] ?? $_SESSION['role'] ?? 'Administrateur');

$parts       = array_filter(explode(' ', $sidebarNom));
$sidebarInit = implode('', array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2)));
if ($sidebarInit === '') $sidebarInit = 'A';
?>
<style>
/* Sidebar scrollable SANS barre visible — défilement smooth */
.sidebar { display:flex; flex-direction:column; max-height:100vh; }
.sidebar-nav {
    flex:1;
    overflow-y:auto;
    overflow-x:hidden;
    scroll-behavior:smooth;
    scrollbar-width:none;       /* Firefox */
    -ms-overflow-style:none;    /* IE/Edge */
}
.sidebar-nav::-webkit-scrollbar { display:none; width:0; height:0; }  /* Chrome/Safari */
.sidebar-footer { flex-shrink:0; }
</style>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <img src="<?= $_front ?>/logo.png" alt="logo" width="40" height="40" style="border-radius:10px;object-fit:cover;">
        <div>
            <div class="logo-text">Protex</div>
            <div class="logo-sub">Back-Office</div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= htmlspecialchars($sidebarInit, ENT_QUOTES, 'UTF-8') ?></div>
        <div>
            <div class="user-name"><?= htmlspecialchars($sidebarNom, ENT_QUOTES, 'UTF-8') ?></div>
            <span class="user-role"><?= htmlspecialchars($sidebarRole, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a class="nav-item <?= sidebarActive('admin.php','admin.html','dashboard') ?>"
           href="<?= $_back ?>/admin.php">
            <i class="bi bi-grid-1x2"></i> Tableau de bord
        </a>

        <div class="nav-section">Gestion</div>
        <a class="nav-item <?= sidebarActive('admin-users') ?>" href="<?= $_back ?>/admin-users.php">
            <i class="bi bi-people"></i> Utilisateurs
        </a>

        <a class="nav-item <?= sidebarActive('sinistre','sinsiter') ?>" href="<?= $_back ?>/sinsiter.php">
            <i class="bi bi-shield-exclamation"></i> Sinistres
        </a>

        <a class="nav-item <?= sidebarActive('traitement') ?>" href="<?= $_back ?>/traitement.php">
            <i class="bi bi-file-earmark-text"></i> Traitements
        </a>

        <a class="nav-item <?= sidebarActive('contrats_back','contrat') ?>" href="<?= $_back ?>/contrats_back.php">
            <i class="bi bi-file-earmark-check"></i> Contrats
        </a>

        <a class="nav-item <?= sidebarActive('categories_back') ?>" href="<?= $_back ?>/categories_back.php">
            <i class="bi bi-grid-3x3-gap"></i> Catégories
        </a>

        <a class="nav-item <?= sidebarActive('garanties_back') ?>" href="<?= $_back ?>/garanties_back.php">
            <i class="bi bi-shield-check"></i> Garanties
        </a>

        <a class="nav-item <?= sidebarActive('paiement') ?>" href="<?= $_ctrl ?>/PaiementController.php">
            <i class="bi bi-credit-card"></i> Paiements
        </a>

        <a class="nav-item <?= sidebarActive('offres') ?>" href="<?= $_ctrl ?>/OffreController.php">
            <i class="bi bi-tags"></i> Offres
        </a>

        <a class="nav-item <?= sidebarActive('reclamation','reponse') ?>" href="<?= $_back ?>/reponse.php">
            <i class="bi bi-chat-dots"></i> Réclamations
        </a>

        <a class="nav-item <?= sidebarActive('agence') ?>" href="<?= $_back ?>/add_agence.php">
            <i class="bi bi-geo-alt"></i> Agences
        </a>

        <!-- ===== MES TÂCHES (mon_dossier) ===== -->
        <a class="nav-item <?= sidebarActive('dashboard','DashboardController') ?>"
           href="<?= $_ctrl ?>/DashboardController.php">
            <i class="bi bi-bar-chart-line"></i> Diagnostique devis/offres
        </a>

        <a class="nav-item <?= sidebarActive('devis') ?>" href="<?= $_ctrl ?>/DevisController.php">
            <i class="bi bi-file-earmark-medical"></i> Devis
        </a>

        <a class="nav-item <?= sidebarActive('messagerie') ?>" href="<?= $_ctrl ?>/MessagerieController.php">
            <i class="bi bi-chat-left-text"></i> Messagerie
            <?php if ($unreadCount + $activeMentions > 0): ?>
                <span class="nav-badge accent"><?= $unreadCount + $activeMentions ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section">Compte</div>
        <a class="nav-item <?= sidebarActive('adminprofile') ?>" href="<?= $_back ?>/adminprofile.php">
            <i class="bi bi-person-gear"></i> Mon profil
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= $_ctrl ?>/AuthController.php?action=logout" class="logout-btn">
            <i class="bi bi-box-arrow-left"></i> Se déconnecter
        </a>
    </div>
</aside>
