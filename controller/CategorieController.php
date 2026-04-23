<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/Categorie.php';

class CategorieController {

    public function listCategories() {
        $sql = "
            SELECT c.*,
                   COUNT(ct.id_contrat) AS nb_contrats
            FROM categorie c
            LEFT JOIN contrat ct ON c.id_categorie = ct.id_categorie
            GROUP BY c.id_categorie, c.nom_categorie, c.description_categorie
            ORDER BY c.id_categorie DESC
        ";

        $db = config::getConnexion();

        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Erreur listCategories: ' . $e->getMessage());
        }
    }

    public function addCategorie($categorie) {
        $sql = "INSERT INTO categorie (nom_categorie, description_categorie)
                VALUES (:nom_categorie, :description_categorie)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom_categorie' => $categorie->getNomCategorie(),
                'description_categorie' => $categorie->getDescriptionCategorie()
            ]);
        } catch (Exception $e) {
            die('Erreur addCategorie: ' . $e->getMessage());
        }
    }

    public function deleteCategorie($id) {
        $sql = "DELETE FROM categorie WHERE id_categorie = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur deleteCategorie: ' . $e->getMessage());
        }
    }

    public function showCategorie($id) {
        $sql = "SELECT * FROM categorie WHERE id_categorie = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur showCategorie: ' . $e->getMessage());
        }
    }

    public function updateCategorie($id, $categorie) {
        $sql = "UPDATE categorie
                SET nom_categorie = :nom_categorie,
                    description_categorie = :description_categorie
                WHERE id_categorie = :id";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'nom_categorie' => $categorie->getNomCategorie(),
                'description_categorie' => $categorie->getDescriptionCategorie()
            ]);
        } catch (Exception $e) {
            die('Erreur updateCategorie: ' . $e->getMessage());
        }
    }

    public function countCategories() {
        $sql = "SELECT COUNT(*) AS total FROM categorie";
        $db = config::getConnexion();

        try {
            $query = $db->query($sql);
            $row = $query->fetch(PDO::FETCH_ASSOC);
            return (int)$row['total'];
        } catch (Exception $e) {
            die('Erreur countCategories: ' . $e->getMessage());
        }
    }

public function countGarantiesLiees() {
    $sql = "
        SELECT COUNT(*) AS total
        FROM garantie g
        WHERE g.id_categorie IS NOT NULL
    ";

    $db = config::getConnexion();

    try {
        $query = $db->query($sql);
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    } catch (Exception $e) {
        die('Erreur countGarantiesLiees: ' . $e->getMessage());
    }
}

    public function countContratsLiees() {
        $sql = "SELECT COUNT(*) AS total FROM contrat";
        $db = config::getConnexion();

        try {
            $query = $db->query($sql);
            $row = $query->fetch(PDO::FETCH_ASSOC);
            return (int)$row['total'];
        } catch (Exception $e) {
            die('Erreur countContratsLiees: ' . $e->getMessage());
        }
    }
}