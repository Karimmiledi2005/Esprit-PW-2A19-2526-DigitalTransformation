<?php

class Reclamation
{
    private ?int $id;
    private string $objet;
    private string $type;
    private string $ref_contrat;
    private string $priorite;
    private string $statut;
    private DateTime $date_depot;
    private string $rec_ref;
    private string $email;
    private string $description;

    public function __construct(
        ?int $id,
        string $objet,
        string $type,
        string $ref_contrat,
        string $priorite,
        string $statut,
        DateTime $date_depot,
        string $rec_ref,
        string $email,
        string $description
    ) {
        $this->id = $id;
        $this->objet = $objet;
        $this->type = $type;
        $this->ref_contrat = $ref_contrat;
        $this->priorite = $priorite;
        $this->statut = $statut;
        $this->date_depot = $date_depot;
        $this->rec_ref = $rec_ref;
        $this->email = $email;
        $this->description = $description;
    }

    // GETTERS
    public function getObjet() { return $this->objet; }
    public function getType() { return $this->type; }
    public function getRefContrat() { return $this->ref_contrat; }
    public function getPriorite() { return $this->priorite; }
    public function getStatut() { return $this->statut; }
    public function getDateDepot() { return $this->date_depot; }
    public function getRecRef() { return $this->rec_ref; }
    public function getEmail() { return $this->email; }
    public function getDescription() { return $this->description; }
}