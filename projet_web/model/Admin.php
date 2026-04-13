<?php
require_once "User.php";

class Admin extends user
{
    private  int $niveau_acces;

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
         int $niveau_acces
    ) {
        parent::__construct(
            $nom, $prenom, $email, $mot_de_passe,
            $telephone, $adresse, $date_naissance, $cin,
            $avatar, "admin", $statut, $last_login, $date_creation
        );

        $this->niveau_acces = $niveau_acces;
    }

    public function getNiveauAcces():  int {
        return $this->niveau_acces;
    }

    public function setNiveauAcces( int $niveau): void {
        $this->niveau_acces = $niveau;
    }
}
?>