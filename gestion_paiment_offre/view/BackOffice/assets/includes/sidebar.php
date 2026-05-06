<?php
$uri = strtolower($_SERVER['REQUEST_URI'] ?? '');
function sidebarActive(string ...$keywords): string {
    global $uri;
    foreach ($keywords as $kw) {
        if (str_contains($uri, strtolower($kw))) return 'active';
    }
    return '';
}
$_sidebarBase = '/projet_web1/gestion_paiment_offre/view/BackOffice';
$_sidebarCtrl = '/projet_web1/gestion_paiment_offre/controller';

// Safe session read — only if already started by controller
$sidebarUser = null;
$unreadCount = 0;
$activeMentions = 0;
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
    $sidebarUid = (int)$_SESSION['user_id'];
    try {
        if (class_exists('config')) {
            $uDb = config::getConnexion();
            $uStmt = $uDb->prepare("SELECT nom, prenom, role FROM user WHERE id_user = ?");
            $uStmt->execute([$sidebarUid]);
            $sidebarUser = $uStmt->fetch(PDO::FETCH_ASSOC) ?: null;
            $msgStmt = $uDb->prepare("SELECT COALESCE(SUM((SELECT COUNT(*) FROM messages_admin m WHERE m.id_conversation = cp.id_conversation AND m.id_expediteur != ? AND (m.date_envoi > cp.dernier_message_lu OR cp.dernier_message_lu IS NULL))), 0) as unread FROM conversation_participants cp WHERE cp.id_user = ?");
            $msgStmt->execute([$sidebarUid, $sidebarUid]);
            $unreadCount = (int)($msgStmt->fetch(PDO::FETCH_ASSOC)['unread'] ?? 0);
            $amStmt = $uDb->prepare("SELECT COUNT(*) FROM message_mentions WHERE id_user_mentionne = ? AND est_resolu = 0");
            $amStmt->execute([$sidebarUid]);
            $activeMentions = (int)($amStmt->fetchColumn() ?? 0);
        }
    } catch (Exception $ign) {}
}
$sidebarNom = $sidebarUser ? ($sidebarUser['prenom'] . ' ' . $sidebarUser['nom']) : 'Admin';
$sidebarRole = $sidebarUser ? ucfirst($sidebarUser['role'] ?? 'Utilisateur') : 'Administrateur';
$sidebarInit = strtoupper(mb_substr($sidebarNom, 0, 1) . (strpos($sidebarNom, ' ') !== false ? mb_substr($sidebarNom, (int)strpos($sidebarNom, ' ') + 1, 1) : ''));
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="/projet_web1/gestion_paiment_offre/view/FrontOffice/logo.png" alt="Protex" width="40" height="40" style="border-radius:10px;object-fit:cover;">
        <div><div class="logo-text">Protex</div><div class="logo-sub">Back-Office</div></div>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar"><?= $sidebarInit ?></div>
        <div><div class="user-name"><?= htmlspecialchars($sidebarNom, ENT_QUOTES, 'UTF-8') ?></div><span class="user-role"><?= htmlspecialchars($sidebarRole, ENT_QUOTES, 'UTF-8') ?></span></div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a class="nav-item <?= sidebarActive('dashboard','admin.php') ?>" href="<?= $_sidebarCtrl ?>/DashboardController.php"><i class="bi bi-grid-1x2"></i> Tableau de bord</a>
        <div class="nav-section">Gestion</div>
        <a class="nav-item <?= sidebarActive('offres') ?>" href="<?= $_sidebarCtrl ?>/OffreController.php"><i class="bi bi-tag"></i> Offres</a>
        <a class="nav-item <?= sidebarActive('devis') ?>" href="<?= $_sidebarCtrl ?>/DevisController.php"><i class="bi bi-file-earmark-text"></i> Devis</a>
        <a class="nav-item <?= sidebarActive('contrats') ?>" href="<?= $_sidebarCtrl ?>/ContratController.php"><i class="bi bi-file-earmark-check"></i> Contrats</a>
        <a class="nav-item <?= sidebarActive('paiements') ?>" href="<?= $_sidebarCtrl ?>/PaiementController.php"><i class="bi bi-credit-card"></i> Paiements</a>
        <a class="nav-item <?= sidebarActive('admin-users') ?>" href="<?= $_sidebarBase ?>/admin-users.php"><i class="bi bi-people"></i> Utilisateurs</a>
        <a class="nav-item <?= sidebarActive('messagerie') ?>" href="<?= $_sidebarCtrl ?>/MessagerieController.php"><i class="bi bi-chat-left-text"></i> Messagerie<?php if ($unreadCount + $activeMentions > 0): ?><span class="nav-badge accent"><?= $unreadCount + $activeMentions ?></span><?php endif; ?></a>
        <div class="nav-section">Compte</div>
        <a class="nav-item" href="<?= $_sidebarCtrl ?>/AuthController.php?action=logout"><i class="bi bi-box-arrow-left"></i> Se déconnecter</a>
    </nav>
</aside>
