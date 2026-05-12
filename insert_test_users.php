<?php
require_once 'connexion.php';

try {
    $db = config::getConnexion();

    // 1. Création de l'agence
    $db->exec("INSERT INTO agence (id_agence, nom_agence, pays, statut) 
               VALUES (1, 'Agence Centrale Tunis', 'Tunisie', 'active')
               ON DUPLICATE KEY UPDATE nom_agence='Agence Centrale Tunis'");

    // 2. Nettoyage
    $emails = ['medkarimmiledi@gmail.com', 'medkarimmiledi123@gmail.com', 'muledikarim@gmail.com', 'sansbibi@gmail.com'];
    $placeholders = implode(',', array_fill(0, count($emails), '?'));
    
    // Supprimer les dépendances d'abord pour éviter les erreurs de FK
    $db->prepare("DELETE FROM otp_codes WHERE id_user IN (SELECT id_user FROM user WHERE email IN ($placeholders))")->execute($emails);
    $db->prepare("DELETE FROM admin WHERE id_user IN (SELECT id_user FROM user WHERE email IN ($placeholders))")->execute($emails);
    $db->prepare("DELETE FROM agent WHERE id_user IN (SELECT id_user FROM user WHERE email IN ($placeholders))")->execute($emails);
    $db->prepare("DELETE FROM client WHERE id_user IN (SELECT id_user FROM user WHERE email IN ($placeholders))")->execute($emails);
    $db->prepare("DELETE FROM user WHERE email IN ($placeholders)")->execute($emails);

    // 3. Insertion des utilisateurs (Mot de passe : Protex123!)
    $hash = '$2y$10$bQLujgpoVCSaqgtTX2u3MegKrTOLZgjd20QdOM.StnNzf8Ygz0uum';
    
    $users = [
        [101, 'Miledi', 'Karim', 'medkarimmiledi@gmail.com', $hash, 'superadmin', 1],
        [102, 'Admin', 'Agent', 'medkarimmiledi123@gmail.com', $hash, 'admin', 1],
        [103, 'Muledi', 'Agent', 'muledikarim@gmail.com', $hash, 'agent', 1],
        [104, 'Sans', 'Bibi', 'sansbibi@gmail.com', $hash, 'client', 1]
    ];

    $stmt = $db->prepare("INSERT INTO user (id_user, nom, prenom, email, mot_de_passe, role, statut, id_agence, date_creation) 
                          VALUES (?, ?, ?, ?, ?, ?, 'actif', ?, NOW())");
    
    foreach ($users as $u) {
        $stmt->execute($u);
    }

    // 4. Rôles spécifiques
    $db->exec("INSERT INTO admin (id_user, niveau_acces, id_agence) VALUES (101, 'superadmin', NULL), (102, 'admin_agence', 1)");
    $db->exec("INSERT INTO agent (id_user, id_agence, salaire, agence) VALUES (103, 1, 1500.00, 'Agence Centrale Tunis')");
    $db->exec("INSERT INTO client (id_user, numero_client, id_agence) VALUES (104, 'CL-TEST-01', 1)");

    // 5. Données métiers
    $db->exec("DELETE FROM traitement WHERE id_traitement = 300");
    $db->exec("DELETE FROM sinistre WHERE id_sinistre = 200");
    $db->exec("DELETE FROM contrat WHERE id_contrat = 100");

    $db->exec("INSERT INTO contrat (id_contrat, id_user, numero_contrat, date_debut_contrat, statut_contrat) 
               VALUES (100, 104, 'CNT-TEST-001', '2025-01-01', 'actif')");

    $db->exec("INSERT INTO sinistre (id_sinistre, id_contrat, id_user, type, description, date_declaration, statut, id_agence, id_agent_assigne)
               VALUES (200, 100, 104, 'Accident auto', 'Choc arrière au feu rouge à Tunis.', CURDATE(), 'en_attente', 1, 103)");

    $db->exec("INSERT INTO traitement (id_traitement, id_sinistre, id_user, decision, montant_indemnise, statut, date_traitement, message_agent)
               VALUES (300, 200, 103, 'Remboursement partiel', 450.00, 'accepte', CURDATE(), 'Dossier complet, validation après expertise.')");

    echo "Insertion réussie des 4 utilisateurs de test et des données métiers.";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
