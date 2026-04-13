<?php
require_once "User.php";

class Client extends User
{
    private  string $numero_client;

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
        string $numero_client
    ) {
        parent::__construct(
             $nom, $prenom, $email, $mot_de_passe,
            $telephone, $adresse, $date_naissance, $cin,
            $avatar, "client", $statut, $last_login, $date_creation
        );

        $this->numero_client = $numero_client;
    }

    public function getNumeroClient():  string {
        return $this->numero_client;
    }

    public function setNumeroClient( string $numero): void {
        $this->numero_client = $numero;
    }
}
?>