<?php
require_once __DIR__ . '/../config/config.php';

class ContratController {

    // 🔹 Ajouter contrat
    public function addContrat($numero, $type, $date_debut, $date_fin, $prime, $franchise) {

    try {
        $sql = "INSERT INTO contrat 
        (numero_contrat, type_contrat, date_debut, date_fin, montant_prime, franchise, statut)
        VALUES (:numero, :type, :date_debut, :date_fin, :prime, :franchise, :statut)";

        $db = config::getConnexion();
        $query = $db->prepare($sql);

        $query->execute([
            'numero' => $numero,
            'type' => $type,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'prime' => $prime,
            'franchise' => $franchise,
            'statut' => 'en attente'
        ]);

    } catch (Exception $e) {
        die('Erreur addContrat: ' . $e->getMessage());
    }
}

    // 🔹 Afficher tous les contrats
    public function listContrats() {
        $sql = "SELECT * FROM contrat";
        $db = config::getConnexion();
        return $db->query($sql);
    }

    // 🔹 Supprimer
    public function deleteContrat($id) {
        $sql = "DELETE FROM contrat WHERE id_contrat = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
    }

    // 🔹 Valider (agent)
    public function validerContrat($id) {
        $sql = "UPDATE contrat SET statut = 'actif' WHERE id_contrat = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
    }

    // 🔹 Garanties liées
    public function getGarantiesByContrat($id) {
        $sql = "SELECT * FROM garantie WHERE id_contrat = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
        return $query->fetchAll();
    }
}
?>