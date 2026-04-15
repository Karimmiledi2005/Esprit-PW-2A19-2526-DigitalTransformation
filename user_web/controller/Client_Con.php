<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../connexion.php';
include(__DIR__ . '/../model/User.php');

class UserController
{

    public function login(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Champs requis'];
        }

        try {
            $db   = config::getConnexion();
            $stmt = $db->prepare(
                "SELECT id_user, nom, prenom, email, mot_de_passe, role, statut
                 FROM user WHERE email = :email LIMIT 1"
            );
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'Email incorrect'];
            }

            if (!password_verify($password, $user['mot_de_passe'])) {
                return ['success' => false, 'message' => 'Mot de passe incorrect'];
            }

            if ($user['statut'] === 'bloque') {
                return ['success' => false, 'message' => 'Compte bloqué'];
            }

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['role']    = $user['role'];

            // Mettre à jour last_login
            $db->prepare("UPDATE user SET last_login = NOW() WHERE id_user = :id")
               ->execute(['id' => $user['id_user']]);

            return ['success' => true, 'role' => $user['role']];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur serveur'];
        }
    }

    public function addclient(User $user): void
    {
        // Validation
        if (empty($user->getNom()) || empty($user->getPrenom())) {
            die("Nom et prénom obligatoires");
        }
        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            die("Email invalide");
        }

        $password = $user->getMotDePasse();
        if (strlen($password) < 8)            { die("Mot de passe : 8 caractères minimum"); }
        if (!preg_match('/[A-Z]/', $password)) { die("Mot de passe : au moins une majuscule"); }
        if (!preg_match('/[0-9]/', $password)) { die("Mot de passe : au moins un chiffre"); }
        if (!preg_match('/[\W]/',  $password)) { die("Mot de passe : au moins un symbole"); }

        if ($this->getUserByEmail($user->getEmail())) {
            die("Cet email est déjà utilisé");
        }

        try {
            $db  = config::getConnexion();
            $sql = "INSERT INTO user
                    (nom, prenom, email, mot_de_passe, telephone, adresse, date_naissance, role, statut, date_creation)
                    VALUES
                    (:nom, :prenom, :email, :mdp, :telephone, :adresse, :date_naissance, 'client', 'actif', NOW())";

            $query = $db->prepare($sql);
            $query->execute([
                'nom'            => htmlspecialchars($user->getNom()),
                'prenom'         => htmlspecialchars($user->getPrenom()),
                'email'          => $user->getEmail(),
                'mdp'            => password_hash($password, PASSWORD_DEFAULT),
                'telephone'      => $user->getTelephone(),
                'adresse'        => htmlspecialchars($user->getAdresse() ?? ''),
                'date_naissance' => $user->getDateNaissance()
                                    ? $user->getDateNaissance()->format('Y-m-d')
                                    : null,
            ]);

            $user_id = $db->lastInsertId();

            // ✅ Régénérer session après inscription
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role']    = 'client';

            header("Location: client.html");
            exit();

        } catch (Exception $e) {
            die("Erreur lors de l'inscription : " . $e->getMessage());
        }
    }

    public function addUserAdmin(
        string  $nom,
        string  $prenom,
        string  $email,
        string  $password,
        ?string $telephone    = null,
        ?string $cin          = null,
        string  $role         = 'client',
        string  $statut       = 'actif',
        ?int    $niveau_acces = null,
        ?string $agence       = null,
        ?float  $salaire      = null,
        ?string $numero_client = null
    ): void {
        if (empty($nom) || empty($prenom) || empty($email)) {
            throw new Exception("Champs obligatoires manquants");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide");
        }
        if (strlen($password) < 8) {
            throw new Exception("Mot de passe trop court (8 caractères minimum)");
        }
        if ($this->getUserByEmail($email)) {
            throw new Exception("Cet email est déjà utilisé");
        }

        $db  = config::getConnexion();
        $sql = "INSERT INTO user
                (nom, prenom, email, mot_de_passe, telephone, cin, role, statut, date_creation)
                VALUES
                (:nom, :prenom, :email, :password, :telephone, :cin, :role, :statut, NOW())";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            "nom"       => htmlspecialchars($nom),
            "prenom"    => htmlspecialchars($prenom),
            "email"     => $email,
            "password"  => password_hash($password, PASSWORD_DEFAULT),
            "telephone" => $telephone,
            "cin"       => $cin,
            "role"      => $role,
            "statut"    => $statut,
        ]);

        $newId = $db->lastInsertId();

        // Insertion dans la table spécifique au rôle
        if ($role === 'admin') {
            $db->prepare("INSERT INTO admin (id_user, niveau_acces) VALUES (:id, :niveau)")
               ->execute(['id' => $newId, 'niveau' => $niveau_acces ?? 1]);
        } elseif ($role === 'agent') {
            $db->prepare("INSERT INTO agent (id_user, agence, salaire) VALUES (:id, :agence, :salaire)")
               ->execute(['id' => $newId, 'agence' => $agence, 'salaire' => $salaire]);
        } elseif ($role === 'client') {
            $db->prepare("INSERT INTO client (id_user, numero_client) VALUES (:id, :numero)")
               ->execute(['id' => $newId, 'numero' => $numero_client]);
        }
    }

    public function getAllUsers(): array
    {
        $db  = config::getConnexion();
        $sql = "SELECT u.id_user, u.nom, u.prenom, u.email, u.telephone, u.cin,
                       u.role, u.statut, u.date_creation,
                       a.niveau_acces,
                       ag.agence, ag.salaire,
                       c.numero_client
                FROM user u
                LEFT JOIN admin  a  ON u.id_user = a.id_user
                LEFT JOIN agent  ag ON u.id_user = ag.id_user
                LEFT JOIN client c  ON u.id_user = c.id_user
                ORDER BY u.date_creation DESC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById(int $id): ?array
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare(
            "SELECT nom, prenom, email, telephone, adresse, cin, date_naissance
             FROM user WHERE id_user = :id_user"
        );
        $stmt->execute(['id_user' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * ✅ NOUVEAU : vérifier si un email existe déjà
     */
    public function getUserByEmail(string $email): ?array
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare("SELECT id_user, email FROM user WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateClient(
        int     $id_user,
        string  $nom,
        string  $prenom,
        string  $email,
        ?string $telephone      = null,
        ?string $adresse        = null,
        ?string $date_naissance = null
    ): void {
        $db  = config::getConnexion();
        $sql = "UPDATE user SET
                    nom            = :nom,
                    prenom         = :prenom,
                    email          = :email,
                    telephone      = :telephone,
                    adresse        = :adresse,
                    date_naissance = :date_naissance
                WHERE id_user = :id_user";
        try {
            $db->prepare($sql)->execute([
                'id_user'        => $id_user,
                'nom'            => htmlspecialchars(trim($nom)),
                'prenom'         => htmlspecialchars(trim($prenom)),
                'email'          => trim($email),
                'telephone'      => $telephone  ? trim($telephone)                  : null,
                'adresse'        => $adresse    ? htmlspecialchars(trim($adresse))  : null,
                'date_naissance' => $date_naissance ?: null,
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur update: ' . $e->getMessage());
        }
    }

    public function updateUserAdmin(
        int     $id_user,
        string  $nom,
        string  $prenom,
        string  $email,
        ?string $telephone     = null,
        ?string $cin           = null,
        string  $role          = 'client',
        string  $statut        = 'actif',
        ?int    $niveau_acces  = null,
        ?string $agence        = null,
        ?float  $salaire       = null,
        ?string $numero_client = null
    ): void {
        if (empty($nom) || empty($prenom) || empty($email)) {
            throw new Exception("Champs obligatoires manquants");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide");
        }

        $db  = config::getConnexion();
        $db->prepare(
            "UPDATE user SET nom=:nom, prenom=:prenom, email=:email,
             telephone=:telephone, cin=:cin, role=:role, statut=:statut
             WHERE id_user=:id_user"
        )->execute([
            'id_user'   => $id_user,
            'nom'       => htmlspecialchars(trim($nom)),
            'prenom'    => htmlspecialchars(trim($prenom)),
            'email'     => trim($email),
            'telephone' => $telephone ? trim($telephone) : null,
            'cin'       => $cin       ? trim($cin)       : null,
            'role'      => $role,
            'statut'    => $statut,
        ]);

        if ($role === 'admin' && $niveau_acces !== null) {
            $db->prepare("UPDATE admin SET niveau_acces=:niveau WHERE id_user=:id")
               ->execute(['niveau' => $niveau_acces, 'id' => $id_user]);
        } elseif ($role === 'agent') {
            $db->prepare("UPDATE agent SET agence=:agence, salaire=:salaire WHERE id_user=:id")
               ->execute(['agence' => $agence, 'salaire' => $salaire, 'id' => $id_user]);
        } elseif ($role === 'client' && $numero_client !== null) {
            $db->prepare("UPDATE client SET numero_client=:numero WHERE id_user=:id")
               ->execute(['numero' => $numero_client, 'id' => $id_user]);
        }
    }

    public function changePassword(int $id_user, string $ancienMdp, string $nouveauMdp): array
    {
        if (strlen($nouveauMdp) < 8) {
            return ['success' => false, 'message' => 'Nouveau mot de passe : 8 caractères minimum'];
        }
        if (!preg_match('/[A-Z]/', $nouveauMdp)) {
            return ['success' => false, 'message' => 'Nouveau mot de passe : au moins une majuscule'];
        }
        if (!preg_match('/[0-9]/', $nouveauMdp)) {
            return ['success' => false, 'message' => 'Nouveau mot de passe : au moins un chiffre'];
        }
        if (!preg_match('/[\W]/', $nouveauMdp)) {
            return ['success' => false, 'message' => 'Nouveau mot de passe : au moins un symbole'];
        }

        try {
            $db   = config::getConnexion();
            $stmt = $db->prepare("SELECT mot_de_passe FROM user WHERE id_user = :id");
            $stmt->execute(['id' => $id_user]);
            $row  = $stmt->fetch();

            if (!$row || !password_verify($ancienMdp, $row['mot_de_passe'])) {
                return ['success' => false, 'message' => 'Ancien mot de passe incorrect'];
            }

            $db->prepare("UPDATE user SET mot_de_passe = :mdp WHERE id_user = :id")
               ->execute([
                   'mdp' => password_hash($nouveauMdp, PASSWORD_DEFAULT),
                   'id'  => $id_user,
               ]);

            return ['success' => true, 'message' => 'Mot de passe mis à jour'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur serveur'];
        }
    }

    public function deleteUser(int $id_user): void
    {
        $db = config::getConnexion();
        $db->prepare("DELETE FROM user WHERE id_user = :id_user")
           ->execute(['id_user' => $id_user]);
    }

    public function toggleStatutUser(int $id_user): string
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare("SELECT statut FROM user WHERE id_user = :id_user");
        $stmt->execute(['id_user' => $id_user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("Utilisateur introuvable");
        }

        $newStatut = ($row['statut'] === 'actif') ? 'bloque' : 'actif';
        $db->prepare("UPDATE user SET statut = :statut WHERE id_user = :id_user")
           ->execute(['statut' => $newStatut, 'id_user' => $id_user]);

        return $newStatut;
    }

    public function getStats(): array
    {
        $db    = config::getConnexion();
        $stats = [];
        $stats['total']   = $db->query("SELECT COUNT(*) FROM user")->fetchColumn();
        $stats['actifs']  = $db->query("SELECT COUNT(*) FROM user WHERE statut='actif'")->fetchColumn();
        $stats['bloques'] = $db->query("SELECT COUNT(*) FROM user WHERE statut='bloque'")->fetchColumn();
        $stats['admins']  = $db->query("SELECT COUNT(*) FROM user WHERE role='admin'")->fetchColumn();
        $stats['agents']  = $db->query("SELECT COUNT(*) FROM user WHERE role='agent'")->fetchColumn();
        $stats['clients'] = $db->query("SELECT COUNT(*) FROM user WHERE role='client'")->fetchColumn();
        return $stats;
    }

    public function getAdminProfile(int $id_user): ?array
    {
        $db = config::getConnexion();
        $sql = "SELECT nom, prenom, email, telephone, cin, role, statut, date_creation
                FROM user WHERE id_user = :id_user";
        $query = $db->prepare($sql);
        $query->execute(['id_user' => $id_user]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        if (!empty($user['date_creation'])) {
            $dt = new DateTime($user['date_creation']);
            $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
            $user['date_creation_formatted'] = $dt->format('d') . ' ' . $mois[(int)$dt->format('n') - 1] . ' ' . $dt->format('Y') . ' · ' . $dt->format('H:i');
        }

        return $user;
    }

    public function getClientProfile(int $id_user): ?array
    {
        $db = config::getConnexion();
        $sql = "SELECT nom, prenom, email, telephone, adresse, cin, date_naissance
                FROM user WHERE id_user = :id_user";
        $query = $db->prepare($sql);
        $query->execute(['id_user' => $id_user]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function updateAdminProfile(int $id_user, string $nom, string $prenom, string $email, ?string $telephone): void
    {
        $db = config::getConnexion();
        $sql = "UPDATE user SET
                    nom       = :nom,
                    prenom    = :prenom,
                    email     = :email,
                    telephone = :telephone
                WHERE id_user = :id_user";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'id_user'   => $id_user,
            'nom'       => htmlspecialchars(trim($nom)),
            'prenom'    => htmlspecialchars(trim($prenom)),
            'email'     => trim($email),
            'telephone' => !empty($telephone) ? trim($telephone) : null,
        ]);
    }
}
