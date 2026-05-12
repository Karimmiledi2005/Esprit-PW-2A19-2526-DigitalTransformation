<?php
require 'connexion.php';
$db = config::getConnexion();

echo "Mise a jour des agences pour les sinistres existants...\n";

// Mettre a jour sinistre.id_agence en fonction de client.id_agence pour chaque utilisateur
$sql = "UPDATE sinistre s
        JOIN client c ON s.id_user = c.id_user
        SET s.id_agence = c.id_agence
        WHERE s.id_agence IS NULL";

$count = $db->exec($sql);

echo "Succes : $count sinistres ont ete rattaches a une agence.\n";
