<?php

namespace Model;

class Offre
{
    private ?int    $id_offre;
    private ?string $nom_offre;
    private ?string $type_offre;
    private ?string $description;
    private ?float  $prix_mensuel;
    private ?float  $prix_annuel;
    private ?string $couverture;
    private ?float  $plafond;
    private ?int    $duree_min;
    private ?string $statut;
    private ?string $date_creation;

    public function __construct(
        ?int    $id_offre      = null,
        ?string $nom_offre     = null,
        ?string $type_offre    = null,
        ?string $description   = null,
        ?float  $prix_mensuel  = null,
        ?float  $prix_annuel   = null,
        ?string $couverture    = null,
        ?float  $plafond       = null,
        ?int    $duree_min     = 1,
        ?string $statut        = 'active',
        ?string $date_creation = null
    ) {
        $this->id_offre      = $id_offre;
        $this->nom_offre     = $nom_offre;
        $this->type_offre    = $type_offre;
        $this->description   = $description;
        $this->prix_mensuel  = $prix_mensuel;
        $this->prix_annuel   = $prix_annuel;
        $this->couverture    = $couverture;
        $this->plafond       = $plafond;
        $this->duree_min     = $duree_min;
        $this->statut        = $statut;
        $this->date_creation = $date_creation ?? date('Y-m-d H:i:s');
    }

    // ===== GETTERS =====
    public function getIdOffre():      ?int    { return $this->id_offre; }
    public function getNomOffre():     ?string { return $this->nom_offre; }
    public function getTypeOffre():    ?string { return $this->type_offre; }
    public function getDescription():  ?string { return $this->description; }
    public function getPrixMensuel():  ?float  { return $this->prix_mensuel; }
    public function getPrixAnnuel():   ?float  { return $this->prix_annuel; }
    public function getCouverture():   ?string { return $this->couverture; }
    public function getPlafond():      ?float  { return $this->plafond; }
    public function getDureeMin():     ?int    { return $this->duree_min; }
    public function getStatut():       ?string { return $this->statut; }
    public function getDateCreation(): ?string { return $this->date_creation; }

    // ===== SETTERS =====
    public function setIdOffre(?int $v): void { $this->id_offre = $v; }

    public function setNomOffre(?string $v): void {
        $this->nom_offre = trim($v);
    }

    public function setPrixMensuel(?float $v): void {
        if ($v < 0) throw new \Exception("Prix mensuel invalide");
        $this->prix_mensuel = $v;
    }

    public function setPrixAnnuel(?float $v): void {
        if ($v < 0) throw new \Exception("Prix annuel invalide");
        $this->prix_annuel = $v;
    }

    public function setTypeOffre(?string $v): void { $this->type_offre = $v; }
    public function setDescription(?string $v): void { $this->description = $v; }
    public function setCouverture(?string $v): void { $this->couverture = $v; }
    public function setPlafond(?float $v): void { $this->plafond = $v; }
    public function setDureeMin(?int $v): void { $this->duree_min = $v; }
    public function setStatut(?string $v): void { $this->statut = $v; }

    // ===== UTILE POUR DEBUG / JSON =====
    public function toArray(): array {
        return [
            'id_offre' => $this->id_offre,
            'nom_offre' => $this->nom_offre,
            'type_offre' => $this->type_offre,
            'description' => $this->description,
            'prix_mensuel' => $this->prix_mensuel,
            'prix_annuel' => $this->prix_annuel,
            'couverture' => $this->couverture,
            'plafond' => $this->plafond,
            'duree_min' => $this->duree_min,
            'statut' => $this->statut,
            'date_creation' => $this->date_creation
        ];
    }
}