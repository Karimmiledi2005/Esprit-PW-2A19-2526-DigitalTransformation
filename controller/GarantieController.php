<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/Garantie.php';

class GarantieController
{
    private $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function listGaranties()
    {
        $sql = "
            SELECT
                g.id_garantie,
                g.nom_garantie,
                g.description_garantie,
                g.plafond_couvert_garantie,
                g.id_categorie,
                c.nom_categorie
            FROM garantie g
            LEFT JOIN categorie c ON g.id_categorie = c.id_categorie
            ORDER BY g.id_garantie DESC
        ";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $garanties = [];

        foreach ($rows as $row) {
            $garantie = new Garantie(
                $row['nom_garantie'],
                $row['description_garantie'],
                (float)$row['plafond_couvert_garantie'],
                $row['id_categorie'] !== null ? (int)$row['id_categorie'] : null
            );

            $garantie->setIdGarantie((int)$row['id_garantie']);
            $garantie->setNomCategorie($row['nom_categorie'] ?? null);

            $garanties[] = $garantie;
        }

        return $garanties;
    }

    public function getAll()
    {
        return $this->listGaranties();
    }

    public function showGarantie($id)
    {
        $sql = "
            SELECT
                g.id_garantie,
                g.nom_garantie,
                g.description_garantie,
                g.plafond_couvert_garantie,
                g.id_categorie,
                c.nom_categorie
            FROM garantie g
            LEFT JOIN categorie c ON g.id_categorie = c.id_categorie
            WHERE g.id_garantie = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function deleteGarantie($id)
    {
        $stmt = $this->db->prepare("DELETE FROM garantie WHERE id_garantie = :id");
        return $stmt->execute(['id' => $id]);
    }
}
