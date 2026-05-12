<?php
/**
 * model/ReclamationModel.php
 * Recréé : ce fichier manquait dans le ZIP des camarades (référencé mais absent).
 * Classe déduite de l'usage dans ReclamationController.php et addReclamation.php.
 */

class Reclamation
{
    private string    $objet;
    private string    $type;
    private string    $refContrat;
    private string    $priorite;
    private string    $statut;
    private DateTime  $dateDepot;
    private string    $recRef;
    private string    $description;
    private string    $email;

    public ?int $id = null;

    public function __construct(
        ?int $id = null,
        ?string $objet = null,
        ?string $type = null,
        ?string $refContrat = null,
        ?string $priorite = null,
        ?string $statut = null,
        ?DateTime $dateDepot = null,
        ?string $recRef = null,
        ?string $description = null,
        ?string $email = null
    ) {
        $this->id          = $id;
        $this->objet       = (string)($objet       ?? '');
        $this->type        = (string)($type        ?? 'general');
        $this->refContrat  = (string)($refContrat  ?? '');
        $this->priorite    = (string)($priorite    ?? 'moyenne');
        $this->statut      = (string)($statut      ?? 'en_attente');
        $this->dateDepot   = $dateDepot ?? new DateTime();
        $this->recRef      = (string)($recRef      ?? '');
        $this->description = (string)($description ?? '');
        $this->email       = (string)($email       ?? '');
    }

    public function getObjet(): string       { return $this->objet; }
    public function getType(): string        { return $this->type; }
    public function getRefContrat(): string  { return $this->refContrat; }
    public function getPriorite(): string    { return $this->priorite; }
    public function getStatut(): string      { return $this->statut; }
    public function getDateDepot(): DateTime { return $this->dateDepot; }
    public function getRecRef(): string      { return $this->recRef; }
    public function getDescription(): string { return $this->description; }
    public function getEmail(): string       { return $this->email; }

    public function setStatut(string $s): void { $this->statut = $s; }

    /**
     * Factory depuis tableau POST (usage dans addReclamation.php)
     */
    public static function fromPost(array $post): self
    {
        $refContrat = trim($post['ref_contrat'] ?? '');
        $recRef     = 'REC-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        $priorite   = trim($post['priorite'] ?? 'Normale');

        return new self(
            trim($post['objet']       ?? ''),
            trim($post['type']        ?? 'Autre'),
            $refContrat,
            $priorite,
            'open',
            new DateTime(),
            $recRef,
            trim($post['description'] ?? ''),
            trim($post['email']       ?? '')
        );
    }
}
