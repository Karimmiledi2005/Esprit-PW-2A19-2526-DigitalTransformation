<?php
require_once __DIR__ . '/connexion.php';
$db = config::getConnexion();

try {
    // 1. Créer une agence de test si aucune n'existe
    $stmt = $db->query("SELECT id_agence FROM agence LIMIT 1");
    $agence = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$agence) {
        $db->exec("INSERT INTO agence (nom_agence, pays, statut, adresse) VALUES ('Agence Tunis Centrale', 'Tunisie', 'active', 'Avenue Habib Bourguiba, Tunis')");
        $id_agence = $db->lastInsertId();
    } else {
        $id_agence = $agence['id_agence'];
    }

    $accounts = [
        [
            'email' => 'sansbibi@gmail.com',
            'pass' => password_hash('Protex@2024SuperAdmin', PASSWORD_DEFAULT),
            'role' => 'superadmin',
            'nom' => 'SansBibi',
            'prenom' => 'Super',
            'extra_table' => 'admin',
            'extra_data' => ['niveau_acces' => 'superadmin', 'id_agence' => null]
        ],
        [
            'email' => 'medkarimmiledi@gmail.com',
            'pass' => password_hash('Protex@2024Admin', PASSWORD_DEFAULT),
            'role' => 'admin',
            'nom' => 'Miledi',
            'prenom' => 'Karim',
            'extra_table' => 'admin',
            'extra_data' => ['niveau_acces' => 'admin_agence', 'id_agence' => $id_agence]
        ],
        [
            'email' => 'medkarimmiledi123@gmail.com',
            'pass' => password_hash('Protex@2024Agent', PASSWORD_DEFAULT),
            'role' => 'agent',
            'nom' => 'Miledi',
            'prenom' => 'Agent',
            'extra_table' => 'agent',
            'extra_data' => ['id_agence' => $id_agence, 'salaire' => 1500]
        ],
        [
            'email' => 'muledikarim@gmail.com',
            'pass' => password_hash('Protex@2024Client', PASSWORD_DEFAULT),
            'role' => 'client',
            'nom' => 'Miledi',
            'prenom' => 'Client',
            'extra_table' => 'client',
            'extra_data' => ['id_agence' => $id_agence, 'numero_client' => 'CLT-' . time()]
        ]
    ];

    foreach ($accounts as $acc) {
        // Supprimer si existe déjà pour éviter les doublons
        $del = $db->prepare("DELETE FROM user WHERE email = ?");
        $del->execute([$acc['email']]);

        // Insertion table user
        $ins = $db->prepare("INSERT INTO user (nom, prenom, email, mot_de_passe, role, statut) VALUES (?, ?, ?, ?, ?, 'actif')");
        $ins->execute([$acc['nom'], $acc['prenom'], $acc['email'], $acc['pass'], $acc['role']]);
        $id_user = $db->lastInsertId();

        // Insertion table spécifique (admin, agent, client)
        if ($acc['extra_table'] === 'admin') {
            $ins_extra = $db->prepare("INSERT INTO admin (id_user, niveau_acces, id_agence) VALUES (?, ?, ?)");
            $ins_extra->execute([$id_user, $acc['extra_data']['niveau_acces'], $acc['extra_data']['id_agence']]);
        } elseif ($acc['extra_table'] === 'agent') {
            $ins_extra = $db->prepare("INSERT INTO agent (id_user, id_agence, salaire) VALUES (?, ?, ?)");
            $ins_extra->execute([$id_user, $acc['extra_data']['id_agence'], $acc['extra_data']['salaire']]);
        } elseif ($acc['extra_table'] === 'client') {
            $ins_extra = $db->prepare("INSERT INTO client (id_user, id_agence, numero_client) VALUES (?, ?, ?)");
            $ins_extra->execute([$id_user, $acc['extra_data']['id_agence'], $acc['extra_data']['numero_client']]);
        }
        echo "Compte créé : {$acc['email']} ({$acc['role']})\n";
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
