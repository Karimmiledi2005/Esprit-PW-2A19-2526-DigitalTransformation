<?php

class Garantie
{
    private ?int $id_garantie = null;
    private string $nom_garantie;
    private string $description_garantie;
    private float $plafond_couvert_garantie;
    private ?int $id_formule;
    private ?int $id_categorie;

    private ?string $nom_formule = null;
    private ?string $nom_categorie = null;

    public function __construct(
        string $nom_garantie,
        string $description_garantie,
        float $plafond_couvert_garantie,
        ?int $id_formule = null,
        ?int $id_categorie = null
    ) {
        $this->nom_garantie = $nom_garantie;
        $this->description_garantie = $description_garantie;
        $this->plafond_couvert_garantie = $plafond_couvert_garantie;
        $this->id_formule = $id_formule;
        $this->id_categorie = $id_categorie;
    }

    public function getIdGarantie(): ?int { return $this->id_garantie; }
    public function getNomGarantie(): string { return $this->nom_garantie; }
    public function getDescriptionGarantie(): string { return $this->description_garantie; }
    public function getPlafondCouvertGarantie(): float { return $this->plafond_couvert_garantie; }
    public function getIdFormule(): ?int { return $this->id_formule; }
    public function getIdCategorie(): ?int { return $this->id_categorie; }
    public function getNomFormule(): ?string { return $this->nom_formule; }
    public function getNomCategorie(): ?string { return $this->nom_categorie; }

    public function setIdGarantie(int $id): void { $this->id_garantie = $id; }
    public function setNomFormule(?string $nom): void { $this->nom_formule = $nom; }
    public function setNomCategorie(?string $nom): void { $this->nom_categorie = $nom; }
}
