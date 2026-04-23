<?php

class Formule {
    private $id_formule;
    private $nom_formule;
    private $description_formule;
    private $prix_formule;
    private $niveau_formule;
    private $id_categorie;

    public function __construct($nom_formule, $description_formule, $prix_formule, $niveau_formule, $id_categorie) {
        $this->nom_formule = $nom_formule;
        $this->description_formule = $description_formule;
        $this->prix_formule = $prix_formule;
        $this->niveau_formule = $niveau_formule;
        $this->id_categorie = $id_categorie;
    }

    public function getIdFormule() { return $this->id_formule; }
    public function getNomFormule() { return $this->nom_formule; }
    public function getDescriptionFormule() { return $this->description_formule; }
    public function getPrixFormule() { return $this->prix_formule; }
    public function getNiveauFormule() { return $this->niveau_formule; }
    public function getIdCategorie() { return $this->id_categorie; }

    public function setIdFormule($id) { $this->id_formule = $id; }
    public function setNomFormule($nom) { $this->nom_formule = $nom; }
    public function setDescriptionFormule($description) { $this->description_formule = $description; }
    public function setPrixFormule($prix) { $this->prix_formule = $prix; }
    public function setNiveauFormule($niveau) { $this->niveau_formule = $niveau; }
    public function setIdCategorie($id_categorie) { $this->id_categorie = $id_categorie; }
}