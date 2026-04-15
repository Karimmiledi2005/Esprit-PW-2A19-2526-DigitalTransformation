<?php
class Paiement
{
    private ?int    $id_paiement;
    private ?int    $id_offre;
    private ?string $reference;
    private ?float  $montant;
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
        ?int    $id_paiement      = null,
        ?int    $id_offre         = null,
        ?string $reference        = null,
        ?float  $montant          = null,
        ?string $methode          = null,
        ?string $periodicite      = null,
        ?string $statut           = 'en_attente',
        ?string $date_paiement    = null,
        ?string $date_echeance    = null,
        ?string $num_carte_masque = null,
        ?string $motif_refus      = null,
        ?string $nom_offre        = null,
        ?string $type_offre       = null
    ) {
        $this->id_paiement      = $id_paiement;
        $this->id_offre         = $id_offre;
        $this->reference        = $reference;
        $this->montant          = $montant;
        $this->methode          = $methode;
        $this->periodicite      = $periodicite;
        $this->statut           = $statut;
        $this->date_paiement    = $date_paiement;
        $this->date_echeance    = $date_echeance;
        $this->num_carte_masque = $num_carte_masque;
        $this->motif_refus      = $motif_refus;
        $this->nom_offre        = $nom_offre;
        $this->type_offre       = $type_offre;
    }

    public function getIdPaiement():     ?int    { return $this->id_paiement;      }
    public function getIdOffre():        ?int    { return $this->id_offre;         }
    public function getReference():      ?string { return $this->reference;        }
    public function getMontant():        ?float  { return $this->montant;          }
    public function getMethode():        ?string { return $this->methode;          }
    public function getPeriodicite():    ?string { return $this->periodicite;      }
    public function getStatut():         ?string { return $this->statut;           }
    public function getDatePaiement():   ?string { return $this->date_paiement;    }
    public function getDateEcheance():   ?string { return $this->date_echeance;    }
    public function getNumCarteMasque(): ?string { return $this->num_carte_masque; }
    public function getMotifRefus():     ?string { return $this->motif_refus;      }
    public function getNomOffre():       ?string { return $this->nom_offre;        }
    public function getTypeOffre():      ?string { return $this->type_offre;       }

    public function setIdPaiement(?int $v):       void { $this->id_paiement      = $v; }
    public function setIdOffre(?int $v):          void { $this->id_offre         = $v; }
    public function setReference(?string $v):     void { $this->reference        = $v; }
    public function setMontant(?float $v):        void { $this->montant          = $v; }
    public function setMethode(?string $v):       void { $this->methode          = $v; }
    public function setPeriodicite(?string $v):   void { $this->periodicite      = $v; }
    public function setStatut(?string $v):        void { $this->statut           = $v; }
    public function setDatePaiement(?string $v):  void { $this->date_paiement    = $v; }
    public function setDateEcheance(?string $v):  void { $this->date_echeance    = $v; }
    public function setNumCarteMasque(?string $v):void { $this->num_carte_masque = $v; }
    public function setMotifRefus(?string $v):    void { $this->motif_refus      = $v; }
    public function setNomOffre(?string $v):      void { $this->nom_offre        = $v; }
    public function setTypeOffre(?string $v):     void { $this->type_offre       = $v; }
}
?>