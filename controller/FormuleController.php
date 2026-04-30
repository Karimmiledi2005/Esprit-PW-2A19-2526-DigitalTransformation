<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/Formule.php';

class FormuleController {

    public function listFormules() {
        $sql = "
            SELECT f.*, c.nom_categorie
            FROM formule f
            LEFT JOIN categorie c ON f.id_categorie = c.id_categorie
            ORDER BY f.id_formule DESC
        ";

        $db = config::getConnexion();

        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function listFormulesByCategorie($id_categorie) {
        $sql = "
            SELECT *
            FROM formule
            WHERE id_categorie = :id_categorie
            ORDER BY id_formule DESC
        ";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_categorie' => $id_categorie
            ]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addFormule($formule) {
        $sql = "INSERT INTO formule (nom_formule, description_formule, prix_formule, franchise_formule, niveau_formule, id_categorie)
                VALUES (:nom_formule, :description_formule, :prix_formule, :franchise_formule, :niveau_formule, :id_categorie)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom_formule' => $formule->getNomFormule(),
                'description_formule' => $formule->getDescriptionFormule(),
                'prix_formule' => $formule->getPrixFormule(),
                'franchise_formule' => method_exists($formule, 'getFranchiseFormule') ? $formule->getFranchiseFormule() : 0,
                'niveau_formule' => $formule->getNiveauFormule(),
                'id_categorie' => $formule->getIdCategorie()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function showFormule($id) {
        $sql = "
            SELECT f.*, c.nom_categorie
            FROM formule f
            LEFT JOIN categorie c ON f.id_categorie = c.id_categorie
            WHERE f.id_formule = :id
        ";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateFormule($id, $formule) {
        $sql = "UPDATE formule
                SET nom_formule = :nom_formule,
                    description_formule = :description_formule,
                    prix_formule = :prix_formule,
                    franchise_formule = :franchise_formule,
                    niveau_formule = :niveau_formule,
                    id_categorie = :id_categorie
                WHERE id_formule = :id";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'nom_formule' => $formule->getNomFormule(),
                'description_formule' => $formule->getDescriptionFormule(),
                'prix_formule' => $formule->getPrixFormule(),
                'franchise_formule' => method_exists($formule, 'getFranchiseFormule') ? $formule->getFranchiseFormule() : 0,
                'niveau_formule' => $formule->getNiveauFormule(),
                'id_categorie' => $formule->getIdCategorie()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteFormule($id) {
        $sql = "DELETE FROM formule WHERE id_formule = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function countFormules() {
        $sql = "SELECT COUNT(*) AS total FROM formule";
        $db = config::getConnexion();

        try {
            $query = $db->query($sql);
            $row = $query->fetch();
            return (int)$row['total'];
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}