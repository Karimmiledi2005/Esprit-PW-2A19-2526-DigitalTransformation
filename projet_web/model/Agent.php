<?php
require_once "User.php";

class Agent extends User
{
    private string $agence;
    private float $salaire;

    public function __construct(
        string $nom,
        string $prenom,
        string $email,
        string $mot_de_passe,
        string $telephone,
        string $adresse,
        DateTime $date_naissance,
        string $cin,
        string $avatar,
        string $statut,
        DateTime $last_login,
        DateTime $date_creation,
        string $agence,
        float $salaire
    ) {
        parent::__construct(
           $nom, $prenom, $email, $mot_de_passe,
            $telephone, $adresse, $date_naissance, $cin,
            $avatar, "agent", $statut, $last_login, $date_creation
        );

        $this->agence = $agence;
        $this->salaire = $salaire;
    }

    public function getAgence(): ?string {
        return $this->agence;
    }

    public function setAgence(?string $agence): void {
        $this->agence = $agence;
    }

    public function getSalaire(): ?float {
        return $this->salaire;
    }

    public function setSalaire(?float $salaire): void {
        $this->salaire = $salaire;
    }
}
?>