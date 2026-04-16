<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../model/contratmodel.php');

class ContratController
{
    public function listContrats()
    {
        $sql = "SELECT * FROM contrat ORDER BY id_contrat DESC";
        $db  = config::getConnexion();
        try {
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function addContrat($contrat)
    {
        $sql = "INSERT INTO contrat
            (numero_contrat, type_contrat, date_debut, date_fin, montant_prime, franchise, statut, id_categorie, formule, details_formule)
            VALUES
            (:numero_contrat, :type_contrat, :date_debut, :date_fin, :montant_prime, :franchise, :statut, :id_categorie, :formule, :details_formule)";
        $db = config::getConnexion();
        try {
            $q = $db->prepare($sql);
            $q->execute([
                'numero_contrat'  => $contrat->getNumeroContrat(),
                'type_contrat'    => $contrat->getTypeContrat(),
                'date_debut'      => $contrat->getDateDebut()->format('Y-m-d'),
                'date_fin'        => $contrat->getDateFin()->format('Y-m-d'),
                'montant_prime'   => $contrat->getMontantPrime(),
                'franchise'       => $contrat->getFranchise(),
                'statut'          => $contrat->getStatut(),
                'id_categorie'    => $contrat->getIdCategorie(),
                'formule'         => $contrat->getFormule(),
                'details_formule' => $contrat->getDetailsFormule()
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function updateContrat($contrat, $id)
    {
        $sql = "UPDATE contrat SET
                    numero_contrat   = :numero_contrat,
                    type_contrat     = :type_contrat,
                    date_debut       = :date_debut,
                    date_fin         = :date_fin,
                    montant_prime    = :montant_prime,
                    franchise        = :franchise,
                    statut           = :statut,
                    id_categorie     = :id_categorie,
                    formule          = :formule,
                    details_formule  = :details_formule
                WHERE id_contrat = :id";
        $db = config::getConnexion();
        try {
            $q = $db->prepare($sql);
            $q->execute([
                'id'              => $id,
                'numero_contrat'  => $contrat->getNumeroContrat(),
                'type_contrat'    => $contrat->getTypeContrat(),
                'date_debut'      => $contrat->getDateDebut()->format('Y-m-d'),
                'date_fin'        => $contrat->getDateFin()->format('Y-m-d'),
                'montant_prime'   => $contrat->getMontantPrime(),
                'franchise'       => $contrat->getFranchise(),
                'statut'          => $contrat->getStatut(),
                'id_categorie'    => $contrat->getIdCategorie(),
                'formule'         => $contrat->getFormule(),
                'details_formule' => $contrat->getDetailsFormule()
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function deleteContrat($id)
    {
        $db = config::getConnexion();
        $q  = $db->prepare("DELETE FROM contrat WHERE id_contrat = :id");
        $q->execute(['id' => (int)$id]);
    }

    public function validerContrat($id)
    {
        $db = config::getConnexion();
        $q  = $db->prepare("UPDATE contrat SET statut = 'actif' WHERE id_contrat = :id");
        $q->execute(['id' => (int)$id]);
    }

    public function refuserContrat($id)
    {
        $db = config::getConnexion();
        $q  = $db->prepare("UPDATE contrat SET statut = 'refuse' WHERE id_contrat = :id");
        $q->execute(['id' => (int)$id]);
    }

    public function resilierContrat($id)
    {
        $db = config::getConnexion();
        $q  = $db->prepare("UPDATE contrat SET statut = 'resilie' WHERE id_contrat = :id");
        $q->execute(['id' => (int)$id]);
    }

    public function getContratById($id)
    {
        $db = config::getConnexion();
        $q  = $db->prepare("SELECT * FROM contrat WHERE id_contrat = :id");
        $q->execute(['id' => (int)$id]);
        return $q->fetch(PDO::FETCH_ASSOC);
    }

    public function showContrat($id) { return $this->getContratById($id); }

    public function getGarantiesByContrat($id)
    {
        $db = config::getConnexion();
        $q  = $db->prepare("SELECT * FROM garantie WHERE id_contrat = :id");
        $q->execute(['id' => (int)$id]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
