<?php
require_once 'connexion.php';
$db = config::getConnexion();

// 1. Get all reclamations that have no id_user
$stmt = $db->query("SELECT id, email FROM reclamation WHERE id_user IS NULL OR id_user = 0");
$recs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($recs as $r) {
    // 2. Find user with this email
    $stmtU = $db->prepare("SELECT id_user FROM user WHERE email = ?");
    $stmtU->execute([$r['email']]);
    $u = $stmtU->fetch();
    
    if ($u) {
        // 3. Update reclamation
        $stmtUp = $db->prepare("UPDATE reclamation SET id_user = ? WHERE id = ?");
        $stmtUp->execute([$u['id_user'], $r['id']]);
        echo "Linked reclamation {$r['id']} ({$r['email']}) to user {$u['id_user']}\n";
    } else {
        echo "No user found for email {$r['email']} (reclamation {$r['id']})\n";
    }
}
?>
