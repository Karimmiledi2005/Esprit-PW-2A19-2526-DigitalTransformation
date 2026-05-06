<?php

class Reponse
{
    private ?int    $id_re;
    private ?string $date_reponse;
    private string  $contenu;
    private string  $statut;
    private int     $reclamation_id;

    public function __construct(
        ?int    $id_re,
        ?string $date_reponse,
        string  $contenu,
        string  $statut,
        int     $reclamation_id
    ) {
        $this->id_re          = $id_re;
        $this->date_reponse   = $date_reponse ?? date('Y-m-d');
        $this->contenu        = $contenu;
        $this->statut         = $statut;
        $this->reclamation_id = $reclamation_id;
    }

    public function getIdRe()          { return $this->id_re; }
    public function getDateReponse()   { return $this->date_reponse; }
    public function getContenu()       { return $this->contenu; }
    public function getStatut()        { return $this->statut; }
    public function getReclamationId() { return $this->reclamation_id; }

    // SETTERS
    public function setContenu(string $contenu)             { $this->contenu        = $contenu; }
    public function setStatut(string $statut)               { $this->statut         = $statut; }
    public function setDateReponse(?string $date_reponse)   { $this->date_reponse   = $date_reponse; }
    public function setReclamationId(int $reclamation_id)   { $this->reclamation_id = $reclamation_id; }
}
?>
