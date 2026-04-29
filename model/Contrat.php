<?php

class Contrat {
    private $id_contrat;
    private $numero_contrat;
    private $type_contrat;
    private $date_debut_contrat;
    private $date_fin_contrat;
    private $prime_contrat;
    private $franchise_contrat;
    private $statut_contrat;
    private $id_client;
    private $id_categorie;

    // Constructor
    public function __construct($numero_contrat, $type_contrat, $id_client, $id_categorie, $prime_contrat = null, $franchise_contrat = null) {
        $this->numero_contrat = $numero_contrat;
        $this->type_contrat = $type_contrat;
        $this->id_client = $id_client;
        $this->id_categorie = $id_categorie;
        $this->prime_contrat = $prime_contrat;
        $this->franchise_contrat = $franchise_contrat;
    }

    // GETTERS
    public function getIdContrat() { return $this->id_contrat; }
    public function getNumeroContrat() { return $this->numero_contrat; }
    public function getTypeContrat() { return $this->type_contrat; }
    public function getDateDebutContrat() { return $this->date_debut_contrat; }
    public function getDateFinContrat() { return $this->date_fin_contrat; }
    public function getPrimeContrat() { return $this->prime_contrat; }
    public function getFranchiseContrat() { return $this->franchise_contrat; }
    public function getStatutContrat() { return $this->statut_contrat; }
    public function getIdClient() { return $this->id_client; }
    public function getIdCategorie() { return $this->id_categorie; }

    // SETTERS
    public function setIdContrat($id) { $this->id_contrat = $id; }
    public function setNumeroContrat($numero) { $this->numero_contrat = $numero; }
    public function setTypeContrat($type) { $this->type_contrat = $type; }
    public function setDateDebutContrat($date) { $this->date_debut_contrat = $date; }
    public function setDateFinContrat($date) { $this->date_fin_contrat = $date; }
    public function setPrimeContrat($prime) { $this->prime_contrat = $prime; }
    public function setFranchiseContrat($franchise) { $this->franchise_contrat = $franchise; }
    public function setStatutContrat($statut) { $this->statut_contrat = $statut; }
    public function setIdClient($id) { $this->id_client = $id; }
    public function setIdCategorie($id) { $this->id_categorie = $id; }
}
