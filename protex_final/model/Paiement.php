<?php
declare(strict_types=1);

class Paiement
{
    private ?int $id_paiement;
    private ?int $id_offre;
    private ?string $reference;
    private ?float $montant;
    private ?string $methode;
    private ?string $periodicite;
    private ?string $statut;
    private ?string $date_paiement;
    private ?string $date_echeance;
    private ?string $num_carte_masque;
    private ?string $motif_refus;
    private ?string $nom_offre;
    private ?string $type_offre;

    public function __construct(
        ?int $id_paiement = null,
        ?int $id_offre = null,
        ?string $reference = null,
        ?float $montant = null,
        ?string $methode = null,
        ?string $periodicite = null,
        ?string $statut = 'en_attente',
        ?string $date_paiement = null,
        ?string $date_echeance = null,
        ?string $num_carte_masque = null,
        ?string $motif_refus = null,
        ?string $nom_offre = null,
        ?string $type_offre = null
    ) {
        $this->id_paiement = $id_paiement;
        $this->id_offre = $id_offre;
        $this->reference = $reference;
        $this->montant = $montant;
        $this->methode = $methode;
        $this->periodicite = $periodicite;
        $this->statut = $statut;
        $this->date_paiement = $date_paiement;
        $this->date_echeance = $date_echeance;
        $this->num_carte_masque = $num_carte_masque;
        $this->motif_refus = $motif_refus;
        $this->nom_offre = $nom_offre;
        $this->type_offre = $type_offre;
    }

    /* ========================= GETTERS ========================= */

    public function getIdPaiement(): ?int
    {
        return $this->id_paiement;
    }

    public function getIdOffre(): ?int
    {
        return $this->id_offre;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function getMethode(): ?string
    {
        return $this->methode;
    }

    public function getPeriodicite(): ?string
    {
        return $this->periodicite;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function getDatePaiement(): ?string
    {
        return $this->date_paiement;
    }

    public function getDateEcheance(): ?string
    {
        return $this->date_echeance;
    }

    public function getNumCarteMasque(): ?string
    {
        return $this->num_carte_masque;
    }

    public function getMotifRefus(): ?string
    {
        return $this->motif_refus;
    }

    public function getNomOffre(): ?string
    {
        return $this->nom_offre;
    }

    public function getTypeOffre(): ?string
    {
        return $this->type_offre;
    }

    /* ========================= SETTERS ========================= */

    public function setIdPaiement(?int $value): void
    {
        $this->id_paiement = $value;
    }

    public function setIdOffre(?int $value): void
    {
        $this->id_offre = $value;
    }

    public function setReference(?string $value): void
    {
        $this->reference = $value;
    }

    public function setMontant(?float $value): void
    {
        $this->montant = $value;
    }

    public function setMethode(?string $value): void
    {
        $this->methode = $value;
    }

    public function setPeriodicite(?string $value): void
    {
        $this->periodicite = $value;
    }

    public function setStatut(?string $value): void
    {
        $this->statut = $value;
    }

    public function setDatePaiement(?string $value): void
    {
        $this->date_paiement = $value;
    }

    public function setDateEcheance(?string $value): void
    {
        $this->date_echeance = $value;
    }

    public function setNumCarteMasque(?string $value): void
    {
        $this->num_carte_masque = $value;
    }

    public function setMotifRefus(?string $value): void
    {
        $this->motif_refus = $value;
    }

    public function setNomOffre(?string $value): void
    {
        $this->nom_offre = $value;
    }

    public function setTypeOffre(?string $value): void
    {
        $this->type_offre = $value;
    }

    /* ========================= OUTILS ========================= */

    public function toArray(): array
    {
        return [
            'id_paiement'      => $this->id_paiement,
            'id_offre'         => $this->id_offre,
            'reference'        => $this->reference,
            'montant'          => $this->montant,
            'methode'          => $this->methode,
            'periodicite'      => $this->periodicite,
            'statut'           => $this->statut,
            'date_paiement'    => $this->date_paiement,
            'date_echeance'    => $this->date_echeance,
            'num_carte_masque' => $this->num_carte_masque,
            'motif_refus'      => $this->motif_refus,
            'nom_offre'        => $this->nom_offre,
            'type_offre'       => $this->type_offre,
        ];
    }

    public static function fromArray(array $data): Paiement
    {
        return new self(
            isset($data['id_paiement']) ? (int)$data['id_paiement'] : null,
            isset($data['id_offre']) ? (int)$data['id_offre'] : null,
            $data['reference'] ?? null,
            isset($data['montant']) ? (float)$data['montant'] : null,
            $data['methode'] ?? null,
            $data['periodicite'] ?? null,
            $data['statut'] ?? 'en_attente',
            $data['date_paiement'] ?? null,
            $data['date_echeance'] ?? null,
            $data['num_carte_masque'] ?? null,
            $data['motif_refus'] ?? null,
            $data['nom_offre'] ?? null,
            $data['type_offre'] ?? null
        );
    }
}
?>