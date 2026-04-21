<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/../mailer/Mailer.php';

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

            // Générer OTP 6 chiffres
            $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // Supprimer anciens OTP de cet user
            $db->prepare("DELETE FROM otp_codes WHERE id_user = :id")
            ->execute(['id' => $user['id_user']]);

            // Sauvegarder le nouveau OTP
            $db->prepare(
                "INSERT INTO otp_codes (id_user, code, expires_at) VALUES (:id, :code, :expires)"
            )->execute([
                'id'      => $user['id_user'],
                'code'    => $otp,
                'expires' => $expiresAt
            ]);

            // Stocker temporairement en session (pas encore connecté)
            session_regenerate_id(true);
            $_SESSION['otp_user_id'] = $user['id_user'];
            $_SESSION['otp_role']    = $user['role'];
            $_SESSION['otp_nom']     = $user['nom'];

            // Envoyer OTP par email
            try {
                (new Mailer())->sendOTP($user['email'], $user['prenom'], $otp);
            } catch (Exception $e) { /* silencieux */ }

            return ['success' => true, 'otp_required' => true];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur serveur'];
        }
    }
    public function verifyOTP(string $code): array
    {
        if (!isset($_SESSION['otp_user_id'])) {
            return ['success' => false, 'message' => 'Session expirée'];
        }

        $id_user = $_SESSION['otp_user_id'];

        try {
            $db   = config::getConnexion();
            $stmt = $db->prepare(
                "SELECT id, code, expires_at, used
                FROM otp_codes
                WHERE id_user = :id AND used = 0
                ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->execute(['id' => $id_user]);
            $otp = $stmt->fetch();

            if (!$otp) {
                return ['success' => false, 'message' => 'Code invalide'];
            }

            if (new DateTime() > new DateTime($otp['expires_at'])) {
                return ['success' => false, 'message' => 'Code expiré, reconnectez-vous'];
            }

            if ($otp['code'] !== $code) {
                return ['success' => false, 'message' => 'Code incorrect'];
            }

            // Marquer OTP comme utilisé
            $db->prepare("UPDATE otp_codes SET used = 1 WHERE id = :id")
            ->execute(['id' => $otp['id']]);

            // Mettre à jour last_login
            $db->prepare("UPDATE user SET last_login = NOW() WHERE id_user = :id")
            ->execute(['id' => $id_user]);

            // Finaliser la session
            $_SESSION['user_id'] = $_SESSION['otp_user_id'];
            $_SESSION['role']    = $_SESSION['otp_role'];
            $_SESSION['nom']     = $_SESSION['otp_nom'];

            // Nettoyer les clés temporaires
            unset($_SESSION['otp_user_id'], $_SESSION['otp_role'], $_SESSION['otp_nom']);

            return ['success' => true, 'role' => $_SESSION['role']];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur serveur'];
        }
    }

    public function addclient(User $user): void
    {
        if (empty($user->getNom()) || empty($user->getPrenom())) {
            throw new Exception("Nom et prénom obligatoires");
        }
        if (strlen($user->getNom()) < 2 || strlen($user->getPrenom()) < 2) {
            throw new Exception("Le nom et prénom doivent contenir au moins 2 lettres");
        }
        if (preg_match('/[0-9]/', $user->getNom()) || preg_match('/[0-9]/', $user->getPrenom())) {
            throw new Exception("Le nom et prénom ne doivent pas contenir de chiffres");
        }
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $user->getNom()) || !preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $user->getPrenom())) {
            throw new Exception("Le nom et prénom ne doivent contenir que des lettres");
        }
        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide");
        }

        $password = $user->getMotDePasse();
        if (strlen($password) < 8) {
            throw new Exception("Mot de passe : 8 caractères minimum");
        }
        if (!preg_match('/[A-Z]/', $password)) {
            throw new Exception("Mot de passe : au moins une majuscule");
        }
        if (!preg_match('/[0-9]/', $password)) {
            throw new Exception("Mot de passe : au moins un chiffre");
        }
        if (!preg_match('/[\W]/', $password)) {
            throw new Exception("Mot de passe : au moins un symbole");
        }

        if ($this->getUserByEmail($user->getEmail())) {
            throw new Exception("Cet email est déjà utilisé");
        }

        if ($user->getTelephone() !== null && !preg_match('/^[\d\s\-\+\(\)]{8,20}$/', $user->getTelephone())) {
            throw new Exception("Téléphone invalide");
        }

        $db  = config::getConnexion();

        $sql = "INSERT INTO user
                (nom, prenom, email, mot_de_passe, telephone, cin, adresse, date_naissance, role, statut, date_creation)
                VALUES
                (:nom, :prenom, :email, :mdp, :telephone, :cin, :adresse, :date_naissance, 'client', 'actif', NOW())";

// CORRECTION : Hash password with password_hash() before INSERT for security
        $hashedPassword = password_hash($user->getMotDePasse(), PASSWORD_DEFAULT);
        
        $query = $db->prepare($sql);
        $query->execute([
            'nom'            => htmlspecialchars($user->getNom()),
            'prenom'         => htmlspecialchars($user->getPrenom()),
            'email'          => $user->getEmail(),
            'mdp'            => $hashedPassword,
            'telephone'      => $user->getTelephone(),
            'cin'            => $user->getCin(),
            'adresse'        => htmlspecialchars($user->getAdresse() ?? ''),
            'date_naissance' => $user->getDateNaissance()
                                ? $user->getDateNaissance()->format('Y-m-d')
                                : null,
        ]);

        $user_id = $db->lastInsertId();

        $numero_client = $this->generateClientNumber($db);

        $stmtClient = $db->prepare("INSERT INTO client (id_user, numero_client) VALUES (:id_user, :numero_client)");
        $stmtClient->execute(['id_user' => $user_id, 'numero_client' => $numero_client]);

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        $db->prepare("INSERT INTO otp_codes (id_user, code, expires_at) VALUES (:id, :code, :expires)")
           ->execute(['id' => $user_id, 'code' => $otp, 'expires' => $expiresAt]);

        session_regenerate_id(true);
        $_SESSION['otp_user_id'] = $user_id;
        $_SESSION['otp_role']    = 'client';
        $_SESSION['otp_nom']     = $user->getNom();

        try {
            (new Mailer())->sendOTP($user->getEmail(), $user->getPrenom(), $otp);
        } catch (Exception $e) { /* silencieux */ }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'otp_required' => true]);
        exit();
    }

    private function generateClientNumber($db): string
    {
        do {
            $number = 'CL-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $stmt = $db->prepare("SELECT COUNT(*) FROM client WHERE numero_client = :num");
            $stmt->execute(['num' => $number]);
        } while ($stmt->fetchColumn() > 0);
        
        return $number;
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
        if (strlen($nom) < 2 || strlen($prenom) < 2) {
            throw new Exception("Le nom et prénom doivent contenir au moins 2 lettres");
        }
        if (preg_match('/[0-9]/', $nom) || preg_match('/[0-9]/', $prenom)) {
            throw new Exception("Le nom et prénom ne doivent pas contenir de chiffres");
        }
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $nom) || !preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $prenom)) {
            throw new Exception("Le nom et prénom ne doivent contenir que des lettres");
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

        if ($role === 'admin') {
            $db->prepare("INSERT INTO admin (id_user, niveau_acces) VALUES (:id, :niveau)")
               ->execute(['id' => $newId, 'niveau' => $niveau_acces ?? 1]);
        } elseif ($role === 'agent') {
            $db->prepare("INSERT INTO agent (id_user, agence, salaire) VALUES (:id, :agence, :salaire)")
               ->execute(['id' => $newId, 'agence' => $agence, 'salaire' => $salaire]);
        } elseif ($role === 'client') {
            $genNumero = $this->generateClientNumber($db);
            $db->prepare("INSERT INTO client (id_user, numero_client) VALUES (:id, :numero)")
               ->execute(['id' => $newId, 'numero' => $genNumero]);
        }
    }

    public function getAllUsers(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $db  = config::getConnexion();
        $sql = "SELECT u.id_user, u.nom, u.prenom, u.email, u.telephone, u.cin, u.avatar,
                       u.role, u.statut, u.date_creation,
                       a.niveau_acces,
                       ag.agence, ag.salaire,
                       c.numero_client
                FROM user u
                LEFT JOIN admin  a  ON u.id_user = a.id_user
                LEFT JOIN agent  ag ON u.id_user = ag.id_user
                LEFT JOIN client c  ON u.id_user = c.id_user
                ORDER BY u.date_creation DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAllUsers(): int
    {
        $db = config::getConnexion();
        return (int) $db->query("SELECT COUNT(*) FROM user")->fetchColumn();
    }

    public function getUserById(int $id): ?array
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare(
            "SELECT u.id_user, u.nom, u.prenom, u.email, u.telephone, u.adresse, u.cin,
                    u.date_naissance, u.avatar, u.role, u.statut,
                    a.niveau_acces,
                    ag.agence, ag.salaire,
                    c.numero_client
             FROM user u
             LEFT JOIN admin  a  ON u.id_user = a.id_user AND u.role = 'admin'
             LEFT JOIN agent  ag ON u.id_user = ag.id_user AND u.role = 'agent'
             LEFT JOIN client c  ON u.id_user = c.id_user AND u.role = 'client'
             WHERE u.id_user = :id_user"
        );
        $stmt->execute(['id_user' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
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
        if (empty($nom) || empty($prenom) || empty($email)) {
            throw new Exception("Champs obligatoires manquants");
        }
        if (strlen($nom) < 2 || strlen($prenom) < 2) {
            throw new Exception("Le nom et prénom doivent contenir au moins 2 lettres");
        }
        if (preg_match('/[0-9]/', $nom) || preg_match('/[0-9]/', $prenom)) {
            throw new Exception("Le nom et prénom ne doivent pas contenir de chiffres");
        }
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $nom) || !preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $prenom)) {
            throw new Exception("Le nom et prénom ne doivent contenir que des lettres");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide");
        }
        if ($telephone !== null && !preg_match('/^[\d\s\-\+\(\)]{8,20}$/', $telephone)) {
            throw new Exception("Téléphone invalide");
        }

        $existingUser = $this->getUserById($id_user);
        if (!$existingUser) {
            throw new Exception("Utilisateur introuvable");
        }
        if ($email !== $existingUser['email'] && $this->getUserByEmail($email)) {
            throw new Exception("Cet email est déjà utilisé");
        }

        $db  = config::getConnexion();
        $sql = "UPDATE user SET
                    nom            = :nom,
                    prenom         = :prenom,
                    email          = :email,
                    telephone      = :telephone,
                    adresse        = :adresse,
                    date_naissance = :date_naissance
                WHERE id_user = :id_user";
        $db->prepare($sql)->execute([
            'id_user'        => $id_user,
            'nom'            => htmlspecialchars(trim($nom)),
            'prenom'         => htmlspecialchars(trim($prenom)),
            'email'          => trim($email),
            'telephone'      => $telephone  ? trim($telephone)                  : null,
            'adresse'        => $adresse    ? htmlspecialchars(trim($adresse))  : null,
            'date_naissance' => $date_naissance ?: null,
        ]);
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
        if (strlen($nom) < 2 || strlen($prenom) < 2) {
            throw new Exception("Le nom et prénom doivent contenir au moins 2 lettres");
        }
        if (preg_match('/[0-9]/', $nom) || preg_match('/[0-9]/', $prenom)) {
            throw new Exception("Le nom et prénom ne doivent pas contenir de chiffres");
        }
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $nom) || !preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $prenom)) {
            throw new Exception("Le nom et prénom ne doivent contenir que des lettres");
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
        
        // Supprimer d'abord les enregistrements liés (ON DELETE ne fonctionne pas toujours)
        $db->prepare("DELETE FROM admin WHERE id_user = :id")->execute(['id' => $id_user]);
        $db->prepare("DELETE FROM agent WHERE id_user = :id")->execute(['id' => $id_user]);
        $db->prepare("DELETE FROM client WHERE id_user = :id")->execute(['id' => $id_user]);
        
        // Supprimer l'utilisateur
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
           // Notification blocage/déblocage
            $stmt2 = $db->prepare("SELECT email, nom FROM user WHERE id_user = :id");
            $stmt2->execute(['id' => $id_user]);
            $userData = $stmt2->fetch();

            try {
                $mailer = new Mailer();
                if ($newStatut === 'bloque') {
                    $mailer->sendCompteBloque($userData['email'], $userData['nom']);
                } else {
                    $mailer->sendCompteDebloque($userData['email'], $userData['nom']);
                }
            } catch (Exception $e) { /* silencieux */ }

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
        $sql = "SELECT nom, prenom, email, telephone, cin, role, statut, avatar, date_creation
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
        $sql = "SELECT u.nom, u.prenom, u.email, u.telephone, u.adresse, u.cin, u.avatar, u.date_naissance,
                       c.numero_client
                FROM user u
                LEFT JOIN client c ON u.id_user = c.id_user
                WHERE u.id_user = :id_user";
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
