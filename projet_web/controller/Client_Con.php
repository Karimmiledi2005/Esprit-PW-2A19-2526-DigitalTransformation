<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../connexion.php';
include (__DIR__ . '/../model/User.php');

class UserController
{
    public function addclient($user)
    {
        try {

            // 🔥 VALIDATION

            if (empty($user->getNom()) || empty($user->getPrenom())) {
                die("Nom et prénom obligatoires");
            }

            if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
                die("Email invalide");
            }

            $password = $user->getMotDePasse();

            if (strlen($password) < 8) {
                die("Password min 8 caractères");
            }

            if (!preg_match('/[A-Z]/', $password)) {
                die("Password doit avoir majuscule");
            }

            if (!preg_match('/[0-9]/', $password)) {
                die("Password doit avoir chiffre");
            }

            if (!preg_match('/[\W]/', $password)) {
                die("Password doit avoir symbole");
            }
            $passwordHash = password_hash($user->getMotDePasse(), PASSWORD_DEFAULT);

            // 🔥 DB
            $db = config::getConnexion();

            $sql = "INSERT INTO user 
            (nom, prenom, email, mot_de_passe, telephone, adresse, date_naissance, role, statut)
            VALUES (:nom, :prenom, :email, :mdp, :telephone, :adresse, :date_naissance, 'client', 'actif')";

            $query = $db->prepare($sql);

            $query->execute([
                'nom' => htmlspecialchars($user->getNom()),
                'prenom' => htmlspecialchars($user->getPrenom()),
                'email' => $user->getEmail(),
                'mdp' => password_hash($password, PASSWORD_DEFAULT),
                'telephone' => $user->getTelephone(),
                'adresse' => htmlspecialchars($user->getAdresse()),
                'date_naissance' => $user->getDateNaissance()
                    ? $user->getDateNaissance()->format('Y-m-d')
                    : null,
                'date_creation' => (new DateTime())->format('Y-m-d H:i:s')
            ]);
            $user_id = $db->lastInsertId();

            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = 'client';

            header("Location: http://localhost/projet_web/view/FrontOffice/client.html");
            exit();
        } catch (Exception $e) {
            die("ERROR: " . $e->getMessage());
        }
    }
    public function addUserAdmin($nom, $prenom, $email, $password, $telephone, $cin, $role, $statut,
                                 $niveau_acces = null, $agence = null, $salaire = null, $numero_client = null)
    {
        $db = config::getConnexion();

        // validation simple
        if (empty($nom) || empty($prenom) || empty($email)) {
            throw new Exception("Champs obligatoires manquants");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide");
        }

        if (strlen($password) < 8) {
            throw new Exception("Mot de passe trop court");
        }

        $sql = "INSERT INTO user
            (nom, prenom, email, mot_de_passe, telephone, cin, role, statut)
            VALUES
            (:nom, :prenom, :email, :password, :telephone, :cin, :role, :statut)";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            "nom"      => htmlspecialchars($nom),
            "prenom"   => htmlspecialchars($prenom),
            "email"    => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "telephone"=> $telephone,
            "cin"      => $cin,
            "role"     => $role,
            "statut"   => $statut
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
    public function getAllUsers()
    {
        $db = config::getConnexion();
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
        $query = $db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($id)
    {
        $db = config::getConnexion();

        $sql = "SELECT nom, prenom, email FROM user WHERE id_user = :id_user";
        $query = $db->prepare($sql);
        $query->execute(['id_user' => $id]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }
    public function deleteUser($id_user)
    {
        $sql = "DELETE FROM user WHERE id_user = :id_user";
        $db = config::getConnexion();

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'id_user' => $id_user
        ]);
    }
    public function updateClient($id_user, $nom, $prenom, $email, $telephone = null, $adresse = null, $date_naissance = null)
    {
        $sql = "UPDATE user SET
                    nom            = :nom,
                    prenom         = :prenom,
                    email          = :email,
                    telephone      = :telephone,
                    adresse        = :adresse,
                    date_naissance = :date_naissance
                WHERE id_user = :id_user";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_user'        => $id_user,
                'nom'            => htmlspecialchars(trim($nom)),
                'prenom'         => htmlspecialchars(trim($prenom)),
                'email'          => trim($email),
                'telephone'      => $telephone  ? trim($telephone)  : null,
                'adresse'        => $adresse    ? htmlspecialchars(trim($adresse)) : null,
                'date_naissance' => $date_naissance ?: null,
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur update: ' . $e->getMessage());
        }
    }
    public function toggleStatutUser($id_user)
    {
        $db = config::getConnexion();

        // Lire le statut actuel
        $stmt = $db->prepare("SELECT statut FROM user WHERE id_user = :id_user");
        $stmt->execute(['id_user' => $id_user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("Utilisateur introuvable");
        }

        $newStatut = ($row['statut'] === 'actif') ? 'bloque' : 'actif';

        $upd = $db->prepare("UPDATE user SET statut = :statut WHERE id_user = :id_user");
        $upd->execute(['statut' => $newStatut, 'id_user' => $id_user]);

        return $newStatut;
    }

    public function updateUserAdmin($id_user, $nom, $prenom, $email, $telephone = null, $cin = null,
                                    $role = 'client', $statut = 'actif',
                                    $niveau_acces = null, $agence = null, $salaire = null, $numero_client = null)
    {
        if (empty($nom) || empty($prenom) || empty($email)) {
            throw new Exception("Champs obligatoires manquants");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide");
        }

        $db = config::getConnexion();

        $sql = "UPDATE user SET
                    nom       = :nom,
                    prenom    = :prenom,
                    email     = :email,
                    telephone = :telephone,
                    cin       = :cin,
                    role      = :role,
                    statut    = :statut
                WHERE id_user = :id_user";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'id_user'   => $id_user,
            'nom'       => htmlspecialchars(trim($nom)),
            'prenom'    => htmlspecialchars(trim($prenom)),
            'email'     => trim($email),
            'telephone' => $telephone ? trim($telephone) : null,
            'cin'       => $cin       ? trim($cin)       : null,
            'role'      => $role,
            'statut'    => $statut,
        ]);

        // Mettre à jour la table spécifique au rôle
        if ($role === 'admin' && $niveau_acces !== null) {
            $db->prepare("UPDATE admin SET niveau_acces = :niveau WHERE id_user = :id")
               ->execute(['niveau' => $niveau_acces, 'id' => $id_user]);
        } elseif ($role === 'agent') {
            $db->prepare("UPDATE agent SET agence = :agence, salaire = :salaire WHERE id_user = :id")
               ->execute(['agence' => $agence, 'salaire' => $salaire, 'id' => $id_user]);
        } elseif ($role === 'client' && $numero_client !== null) {
            $db->prepare("UPDATE client SET numero_client = :numero WHERE id_user = :id")
               ->execute(['numero' => $numero_client, 'id' => $id_user]);
        }
    }
}