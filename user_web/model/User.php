<?php
class User
{
    private string    $nom;
    private string    $prenom;
    private string    $email;
    private string    $mot_de_passe;
    private ?string   $telephone;
    private ?string   $adresse;
    private ?DateTime $date_naissance;
    private ?string   $cin;
    private string    $avatar;
    private string    $role;
    private string    $statut;
    private ?DateTime $last_login;
    private DateTime  $date_creation;

    public function __construct(
        string    $nom,
        string    $prenom,
        string    $email,
        string    $mot_de_passe,
        ?string   $telephone,
        ?string   $adresse,
        ?DateTime $date_naissance,
        ?string   $cin,
        string    $avatar,
        string    $role,
        string    $statut,
        ?DateTime $last_login,
        DateTime  $date_creation
    ) {
        $this->nom            = $nom;
        $this->prenom         = $prenom;
        $this->email          = $email;
        $this->mot_de_passe   = $mot_de_passe;
        $this->telephone      = $telephone;
        $this->adresse        = $adresse;
        $this->date_naissance = $date_naissance;
        $this->cin            = $cin;
        $this->avatar         = $avatar;
        $this->role           = $role;
        $this->statut         = $statut;
        $this->last_login     = $last_login;
        $this->date_creation  = $date_creation;
    }

    public function getNom(): string            { return $this->nom; }
    public function getPrenom(): string          { return $this->prenom; }
    public function getEmail(): string           { return $this->email; }
    public function getMotDePasse(): string      { return $this->mot_de_passe; }
    public function getTelephone(): ?string      { return $this->telephone; }
    public function getAdresse(): ?string        { return $this->adresse; }
    public function getDateNaissance(): ?DateTime{ return $this->date_naissance; }
    public function getCin(): ?string            { return $this->cin; }
    public function getAvatar(): string          { return $this->avatar; }
    public function getRole(): string            { return $this->role; }
    public function getStatut(): string          { return $this->statut; }
    public function getLastLogin(): ?DateTime    { return $this->last_login; }
    public function getDateCreation(): DateTime  { return $this->date_creation; }

    public function setNom(string $nom): void            { $this->nom = $nom; }
    public function setPrenom(string $prenom): void      { $this->prenom = $prenom; }
    public function setEmail(string $email): void        { $this->email = $email; }
    public function setMotDePasse(string $mdp): void     { $this->mot_de_passe = password_hash($mdp, PASSWORD_DEFAULT); }
    public function setTelephone(?string $t): void       { $this->telephone = $t; }
    public function setAdresse(?string $a): void         { $this->adresse = $a; }
    public function setDateNaissance(?DateTime $d): void { $this->date_naissance = $d; }
    public function setCin(?string $cin): void           { $this->cin = $cin; }
    public function setAvatar(string $avatar): void      { $this->avatar = $avatar; }
    public function setRole(string $role): void          { $this->role = $role; }
    public function setStatut(string $statut): void      { $this->statut = $statut; }
    public function setLastLogin(?DateTime $d): void     { $this->last_login = $d; }
}
?>
