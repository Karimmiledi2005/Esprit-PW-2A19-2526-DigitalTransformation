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
    private string $description;
    private string $email;

    public function __construct(
        ?int $id,
        string $objet,
        string $type,
        string $ref_contrat,
        string $priorite,
        string $statut,
        DateTime $date_depot,
        string $rec_ref,
        string $description,
        string $email = ''
    ) {
        $this->id          = $id;
        $this->objet       = $objet;
        $this->type        = $type;
        $this->ref_contrat = $ref_contrat;
        $this->priorite    = $priorite;
        $this->statut      = $statut;
        $this->date_depot  = $date_depot;
        $this->rec_ref     = $rec_ref;
        $this->description = $description;
        $this->email       = $email;
    }

    // GETTERS
    public function getId()          { return $this->id; }
    public function getObjet()       { return $this->objet; }
    public function getType()        { return $this->type; }
    public function getRefContrat()  { return $this->ref_contrat; }
    public function getPriorite()    { return $this->priorite; }
    public function getStatut()      { return $this->statut; }
    public function getDateDepot()   { return $this->date_depot; }
    public function getRecRef()      { return $this->rec_ref; }
    public function getDescription() { return $this->description; }
    public function getEmail()       { return $this->email; }

    // SETTERS
    public function setObjet(string $objet)             { $this->objet       = $objet; }
    public function setType(string $type)               { $this->type        = $type; }
    public function setRefContrat(string $ref_contrat)  { $this->ref_contrat = $ref_contrat; }
    public function setPriorite(string $priorite)       { $this->priorite    = $priorite; }
    public function setStatut(string $statut)           { $this->statut      = $statut; }
    public function setDateDepot(DateTime $date_depot)  { $this->date_depot  = $date_depot; }
    public function setRecRef(string $rec_ref)          { $this->rec_ref     = $rec_ref; }
    public function setDescription(string $description) { $this->description = $description; }
    public function setEmail(string $email)             { $this->email       = $email; }
}
