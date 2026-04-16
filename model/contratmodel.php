<?php

class Contrat
{
    private ?int $id_contrat;
    private ?string $numero_contrat;
    private ?string $type_contrat;
    private ?DateTime $date_debut;
    private ?DateTime $date_fin;
    private ?float $montant_prime;
    private ?float $franchise;
    private ?string $statut;
    private ?int $id_categorie;
    private ?string $formule;
    private ?string $details_formule;

    public function __construct(
        ?int $id_contrat,
        ?string $numero_contrat,
        ?string $type_contrat,
        ?DateTime $date_debut,
        ?DateTime $date_fin,
        ?float $montant_prime,
        ?float $franchise,
        ?string $statut,
        ?int $id_categorie,
        ?string $formule = null,
        ?string $details_formule = null
    ) {
        $this->id_contrat = $id_contrat;
        $this->numero_contrat = $numero_contrat;
        $this->type_contrat = $type_contrat;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
        $this->montant_prime = $montant_prime;
        $this->franchise = $franchise;
        $this->statut = $statut;
        $this->id_categorie = $id_categorie;
        $this->formule = $formule;
        $this->details_formule = $details_formule;
    }

    public function getIdContrat(): ?int { return $this->id_contrat; }
    public function setIdContrat(?int $id_contrat): void { $this->id_contrat = $id_contrat; }

    public function getNumeroContrat(): ?string { return $this->numero_contrat; }
    public function setNumeroContrat(?string $numero_contrat): void { $this->numero_contrat = $numero_contrat; }

    public function getTypeContrat(): ?string { return $this->type_contrat; }
    public function setTypeContrat(?string $type_contrat): void { $this->type_contrat = $type_contrat; }

    public function getDateDebut(): ?DateTime { return $this->date_debut; }
    public function setDateDebut(?DateTime $date_debut): void { $this->date_debut = $date_debut; }

    public function getDateFin(): ?DateTime { return $this->date_fin; }
    public function setDateFin(?DateTime $date_fin): void { $this->date_fin = $date_fin; }

    public function getMontantPrime(): ?float { return $this->montant_prime; }
    public function setMontantPrime(?float $montant_prime): void { $this->montant_prime = $montant_prime; }

    public function getFranchise(): ?float { return $this->franchise; }
    public function setFranchise(?float $franchise): void { $this->franchise = $franchise; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }

    public function getIdCategorie(): ?int { return $this->id_categorie; }
    public function setIdCategorie(?int $id_categorie): void { $this->id_categorie = $id_categorie; }

    public function getFormule(): ?string { return $this->formule; }
    public function setFormule(?string $formule): void { $this->formule = $formule; }

    public function getDetailsFormule(): ?string { return $this->details_formule; }
    public function setDetailsFormule(?string $details_formule): void { $this->details_formule = $details_formule; }
}
?>