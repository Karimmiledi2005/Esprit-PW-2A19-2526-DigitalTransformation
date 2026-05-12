<?php

class PosteModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllPostes(): array {
        $sql = "SELECT 
                    p.id_poste,
                    p.contenu,
                    p.date_publication,
                    p.note,
                    p.auteur,
                    p.nb_likes,
                    p.nb_commentaires,
                    p.id_agence,
                    a.nom_agence AS agence
                FROM poste p
                LEFT JOIN agence a ON p.id_agence = a.id_agence
                ORDER BY p.id_poste DESC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllAgences(): array {
        $sql = "SELECT id_agence, nom_agence FROM agence ORDER BY nom_agence ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPosteById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM poste WHERE id_poste = ?");
        $stmt->execute([$id]);
        $poste = $stmt->fetch(PDO::FETCH_ASSOC);
        return $poste ?: null;
    }

    public function createPoste(array $data): bool {
        $sql = "INSERT INTO poste (
                    contenu,
                    date_publication,
                    note,
                    auteur,
                    nb_likes,
                    nb_commentaires,
                    id_agence
                ) VALUES (
                    :contenu,
                    :date_publication,
                    :note,
                    :auteur,
                    :nb_likes,
                    :nb_commentaires,
                    :id_agence
                )";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':contenu' => trim($data['contenu']),
            ':date_publication' => $data['date_publication'],
            ':note' => $data['note'] !== '' ? $data['note'] : 0,
            ':auteur' => trim($data['auteur']),
            ':nb_likes' => $data['nb_likes'] !== '' ? $data['nb_likes'] : 0,
            ':nb_commentaires' => $data['nb_commentaires'] !== '' ? $data['nb_commentaires'] : 0,
            ':id_agence' => (int)$data['id_agence']
        ]);
    }

    public function updatePoste(array $data): bool {
        $sql = "UPDATE poste SET
                    contenu = :contenu,
                    date_publication = :date_publication,
                    note = :note,
                    auteur = :auteur,
                    nb_likes = :nb_likes,
                    nb_commentaires = :nb_commentaires,
                    id_agence = :id_agence
                WHERE id_poste = :id_poste";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id_poste' => (int)$data['id_poste'],
            ':contenu' => trim($data['contenu']),
            ':date_publication' => $data['date_publication'],
            ':note' => $data['note'] !== '' ? $data['note'] : 0,
            ':auteur' => trim($data['auteur']),
            ':nb_likes' => $data['nb_likes'] !== '' ? $data['nb_likes'] : 0,
            ':nb_commentaires' => $data['nb_commentaires'] !== '' ? $data['nb_commentaires'] : 0,
            ':id_agence' => (int)$data['id_agence']
        ]);
    }

    public function deletePoste(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM poste WHERE id_poste = ?");
        return $stmt->execute([$id]);
    }
}
?>