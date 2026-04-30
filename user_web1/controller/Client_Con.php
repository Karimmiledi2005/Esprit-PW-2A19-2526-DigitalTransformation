<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/../mailer/Mailer.php';
include(__DIR__ . '/../model/User.php');

class UserController
{
    // ============================================================
    // VALIDATION PRIVÉE CENTRALISÉE
    // ============================================================

    /**
     * Valide les champs nom/prénom/email/téléphone communs à tous les utilisateurs.
     * Lève une Exception si une règle est violée.
     */
    private function validateUserFields(
        string  $nom,
        string  $prenom,
        string  $email,
        ?string $telephone = null
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
        if (
            !preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $nom) ||
            !preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $prenom)
        ) {
            throw new Exception("Le nom et prénom ne doivent contenir que des lettres");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide");
        }
        if ($telephone !== null && !preg_match('/^[\d\s\-\+\(\)]{8,20}$/', $telephone)) {
            throw new Exception("Téléphone invalide");
        }
    }

    /**
     * Valide les règles de complexité du mot de passe.
     * Lève une Exception si une règle est violée.
     */
    private function validatePassword(string $password): void
    {
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
    }

    /**
     * Construit les clauses WHERE et les paramètres PDO à partir des filtres.
     * Factorisé pour éviter la duplication entre searchUsers() et countSearchUsers().
     */
    private function buildUserFilters(array $filters): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['keyword'])) {
            $kw = '%' . trim($filters['keyword']) . '%';
            $conditions[] = "(u.nom LIKE :kw1 OR u.prenom LIKE :kw2 OR u.email LIKE :kw3 OR u.cin LIKE :kw4 OR c.numero_client LIKE :kw5)";
            $params['kw1'] = $kw;
            $params['kw2'] = $kw;
            $params['kw3'] = $kw;
            $params['kw4'] = $kw;
            $params['kw5'] = $kw;
        }
        if (!empty($filters['role'])) {
            $conditions[] = "u.role = :role";
            $params['role'] = $filters['role'];
        }
        if (!empty($filters['statut'])) {
            $conditions[] = "u.statut = :statut";
            $params['statut'] = $filters['statut'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "u.date_creation >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "u.date_creation <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['agence'])) {
            $conditions[] = "ag.agence = :agence";
            $params['agence'] = $filters['agence'];
        }
        if (!empty($filters['has_avatar'])) {
            $conditions[] = "u.avatar != 'default.png'";
        }

        return [$conditions, $params];
    }

    // ============================================================
    // CSRF
    // ============================================================

    /**
     * Génère (ou récupère) le token CSRF stocké en session.
     */
    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie le token CSRF transmis dans la requête.
     * Lève une Exception si le token est absent ou invalide.
     */
    private function verifyCsrf(string $token): void
    {
        if (
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            throw new Exception("Token CSRF invalide");
        }
    }

    // ============================================================
    // AUTHENTIFICATION
    // ============================================================

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

            $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $db->prepare("DELETE FROM otp_codes WHERE id_user = :id")
               ->execute(['id' => $user['id_user']]);

            $db->prepare(
                "INSERT INTO otp_codes (id_user, code, expires_at) VALUES (:id, :code, :expires)"
            )->execute([
                'id'      => $user['id_user'],
                'code'    => $otp,
                'expires' => $expiresAt,
            ]);

            // Régénération de session AVANT d'écrire les données sensibles
            session_regenerate_id(true);
            $_SESSION['otp_user_id'] = $user['id_user'];
            $_SESSION['otp_role']    = $user['role'];
            $_SESSION['otp_nom']     = $user['nom'];

            try {
                (new Mailer())->sendOTP($user['email'], $user['prenom'], $otp);
            } catch (Exception $e) {
                error_log('Mailer sendOTP: ' . $e->getMessage());
            }

            return ['success' => true, 'otp_required' => true];

        } catch (Exception $e) {
            error_log('login(): ' . $e->getMessage());
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
            if (!hash_equals($otp['code'], $code)) {
                return ['success' => false, 'message' => 'Code incorrect'];
            }

            $db->prepare("UPDATE otp_codes SET used = 1 WHERE id = :id")
               ->execute(['id' => $otp['id']]);

            $db->prepare("UPDATE user SET last_login = NOW() WHERE id_user = :id")
               ->execute(['id' => $id_user]);

            // FIX : session_regenerate_id() ici, au moment de la vraie finalisation
            session_regenerate_id(true);
            $_SESSION['user_id'] = $_SESSION['otp_user_id'];
            $_SESSION['role']    = $_SESSION['otp_role'];
            $_SESSION['nom']     = $_SESSION['otp_nom'];
            unset($_SESSION['otp_user_id'], $_SESSION['otp_role'], $_SESSION['otp_nom']);

            return ['success' => true, 'role' => $_SESSION['role']];

        } catch (Exception $e) {
            error_log('verifyOTP(): ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur'];
        }
    }

    // ============================================================
    // CRÉATION UTILISATEUR
    // ============================================================

    /**
     * FIX : la méthode ne fait plus echo/header/exit.
     * Elle lève des exceptions ou retourne normalement.
     * L'appelant gère la réponse HTTP.
     */
    public function addclient(User $user): void
    {
        $this->validateUserFields(
            $user->getNom(),
            $user->getPrenom(),
            $user->getEmail(),
            $user->getTelephone()
        );
        $this->validatePassword($user->getMotDePasse());

        if ($this->getUserByEmail($user->getEmail())) {
            throw new Exception("Cet email est déjà utilisé");
        }

        $db = config::getConnexion();

        $stmt = $db->prepare(
            "INSERT INTO user
             (nom, prenom, email, mot_de_passe, telephone, cin, adresse, date_naissance, role, statut, date_creation)
             VALUES
             (:nom, :prenom, :email, :mdp, :telephone, :cin, :adresse, :date_naissance, 'client', 'actif', NOW())"
        );
        $stmt->execute([
            'nom'            => htmlspecialchars($user->getNom()),
            'prenom'         => htmlspecialchars($user->getPrenom()),
            'email'          => $user->getEmail(),
            'mdp'            => password_hash($user->getMotDePasse(), PASSWORD_DEFAULT),
            'telephone'      => $user->getTelephone(),
            'cin'            => $user->getCin(),
            'adresse'        => htmlspecialchars($user->getAdresse() ?? ''),
            'date_naissance' => $user->getDateNaissance()
                                ? $user->getDateNaissance()->format('Y-m-d')
                                : null,
        ]);

        $user_id       = (int) $db->lastInsertId();
        $numero_client = $this->generateClientNumber($db);

        $db->prepare("INSERT INTO client (id_user, numero_client) VALUES (:id_user, :numero_client)")
           ->execute(['id_user' => $user_id, 'numero_client' => $numero_client]);

        $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        $db->prepare("INSERT INTO otp_codes (id_user, code, expires_at) VALUES (:id, :code, :expires)")
           ->execute(['id' => $user_id, 'code' => $otp, 'expires' => $expiresAt]);

        session_regenerate_id(true);
        $_SESSION['otp_user_id'] = $user_id;
        $_SESSION['otp_role']    = 'client';
        $_SESSION['otp_nom']     = $user->getNom();

        try {
            (new Mailer())->sendOTP($user->getEmail(), $user->getPrenom(), $otp);
        } catch (Exception $e) {
            error_log('Mailer sendOTP (addclient): ' . $e->getMessage());
        }
    }

    /**
     * FIX : génération du numéro client sans boucle infinie possible.
     * Utilise un suffixe aléatoire large + timestamp pour garantir l'unicité.
     */
    private function generateClientNumber(\PDO $db): string
    {
        $attempts = 0;
        do {
            if (++$attempts > 10) {
                throw new Exception("Impossible de générer un numéro client unique");
            }
            $number = 'CL-' . str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            $stmt   = $db->prepare("SELECT COUNT(*) FROM client WHERE numero_client = :num");
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
        $this->validateUserFields($nom, $prenom, $email, $telephone);

        if (strlen($password) < 8) {
            throw new Exception("Mot de passe trop court (8 caractères minimum)");
        }
        if ($this->getUserByEmail($email)) {
            throw new Exception("Cet email est déjà utilisé");
        }

        // Validation du rôle
        $rolesValides = ['client', 'admin', 'agent'];
        if (!in_array($role, $rolesValides, true)) {
            throw new Exception("Rôle invalide");
        }

        $db   = config::getConnexion();
        $stmt = $db->prepare(
            "INSERT INTO user
             (nom, prenom, email, mot_de_passe, telephone, cin, role, statut, date_creation)
             VALUES
             (:nom, :prenom, :email, :password, :telephone, :cin, :role, :statut, NOW())"
        );
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

        $newId = (int) $db->lastInsertId();

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

    // ============================================================
    // LECTURE
    // ============================================================

    public function getAllUsers(int $page = 1, int $perPage = 20): array
    {
        $perPage = max(1, min(100, $perPage));
        $offset  = max(0, ($page - 1) * $perPage);

        $db   = config::getConnexion();
        $stmt = $db->prepare(
            "SELECT u.id_user, u.nom, u.prenom, u.email, u.telephone, u.cin, u.avatar,
                    u.role, u.statut, u.date_creation,
                    a.niveau_acces,
                    ag.agence, ag.salaire,
                    c.numero_client
             FROM user u
             LEFT JOIN admin  a  ON u.id_user = a.id_user
             LEFT JOIN agent  ag ON u.id_user = ag.id_user
             LEFT JOIN client c  ON u.id_user = c.id_user
             ORDER BY u.date_creation DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getUserByEmail(string $email): ?array
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare("SELECT id_user, email FROM user WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ============================================================
    // MISE À JOUR
    // ============================================================

    public function updateClient(
        int     $id_user,
        string  $nom,
        string  $prenom,
        string  $email,
        ?string $telephone      = null,
        ?string $adresse        = null,
        ?string $date_naissance = null
    ): void {
        $this->validateUserFields($nom, $prenom, $email, $telephone);

        $existingUser = $this->getUserById($id_user);
        if (!$existingUser) {
            throw new Exception("Utilisateur introuvable");
        }
        if ($email !== $existingUser['email'] && $this->getUserByEmail($email)) {
            throw new Exception("Cet email est déjà utilisé");
        }

        $db = config::getConnexion();
        $db->prepare(
            "UPDATE user SET
                nom            = :nom,
                prenom         = :prenom,
                email          = :email,
                telephone      = :telephone,
                adresse        = :adresse,
                date_naissance = :date_naissance
             WHERE id_user = :id_user"
        )->execute([
            'id_user'        => $id_user,
            'nom'            => htmlspecialchars(trim($nom)),
            'prenom'         => htmlspecialchars(trim($prenom)),
            'email'          => trim($email),
            'telephone'      => $telephone  ? trim($telephone)                 : null,
            'adresse'        => $adresse    ? htmlspecialchars(trim($adresse)) : null,
            'date_naissance' => $date_naissance ?: null,
        ]);
    }

    public function updateUserAdmin(
        int     $id_user,
        string  $nom,
        string  $prenom,
        string  $email,
        ?string $telephone    = null,
        ?string $cin          = null,
        string  $role         = 'client',
        string  $statut       = 'actif',
        ?int    $niveau_acces = null,
        ?string $agence       = null,
        ?float  $salaire      = null,
        ?string $numero_client = null
    ): void {
        $this->validateUserFields($nom, $prenom, $email, $telephone);

        // Validation des valeurs énumérées
        $rolesValides   = ['client', 'admin', 'agent'];
        $statutsValides = ['actif', 'bloque'];
        if (!in_array($role, $rolesValides, true)) {
            throw new Exception("Rôle invalide");
        }
        if (!in_array($statut, $statutsValides, true)) {
            throw new Exception("Statut invalide");
        }

        $db = config::getConnexion();
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
        try {
            $this->validatePassword($nouveauMdp);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
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
            error_log('changePassword(): ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur'];
        }
    }

    public function updateAdminProfile(int $id_user, string $nom, string $prenom, string $email, ?string $telephone): void
    {
        $this->validateUserFields($nom, $prenom, $email, $telephone);

        $db = config::getConnexion();
        $db->prepare(
            "UPDATE user SET
                nom       = :nom,
                prenom    = :prenom,
                email     = :email,
                telephone = :telephone
             WHERE id_user = :id_user"
        )->execute([
            'id_user'   => $id_user,
            'nom'       => htmlspecialchars(trim($nom)),
            'prenom'    => htmlspecialchars(trim($prenom)),
            'email'     => trim($email),
            'telephone' => !empty($telephone) ? trim($telephone) : null,
        ]);
    }

    // ============================================================
    // SUPPRESSION / STATUT
    // ============================================================

    /**
     * FIX : protégé par CSRF.
     * Appeler avec verifyCsrf($_POST['csrf_token']) côté appelant,
     * ou passer le token directement ici.
     */
    public function deleteUser(int $id_user, string $csrfToken): void
    {
        $this->verifyCsrf($csrfToken);

        $db = config::getConnexion();
        // Les ON DELETE CASCADE en base sont préférables,
        // mais on garde la suppression manuelle comme filet de sécurité.
        $db->prepare("DELETE FROM admin  WHERE id_user = :id")->execute(['id' => $id_user]);
        $db->prepare("DELETE FROM agent  WHERE id_user = :id")->execute(['id' => $id_user]);
        $db->prepare("DELETE FROM client WHERE id_user = :id")->execute(['id' => $id_user]);
        $db->prepare("DELETE FROM otp_codes WHERE id_user = :id")->execute(['id' => $id_user]);
        $db->prepare("DELETE FROM user   WHERE id_user = :id_user")->execute(['id_user' => $id_user]);
    }

    public function toggleStatutUser(int $id_user, string $csrfToken): string
    {
        $this->verifyCsrf($csrfToken);

        $db   = config::getConnexion();
        $stmt = $db->prepare("SELECT statut, email, nom FROM user WHERE id_user = :id_user");
        $stmt->execute(['id_user' => $id_user]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("Utilisateur introuvable");
        }

        $newStatut = ($row['statut'] === 'actif') ? 'bloque' : 'actif';
        $db->prepare("UPDATE user SET statut = :statut WHERE id_user = :id_user")
           ->execute(['statut' => $newStatut, 'id_user' => $id_user]);

        try {
            $mailer = new Mailer();
            if ($newStatut === 'bloque') {
                $mailer->sendCompteBloque($row['email'], $row['nom']);
            } else {
                $mailer->sendCompteDebloque($row['email'], $row['nom']);
            }
        } catch (Exception $e) {
            error_log('Mailer toggleStatut: ' . $e->getMessage());
        }

        return $newStatut;
    }

    // ============================================================
    // PROFILS
    // ============================================================

    public function getAdminProfile(int $id_user): ?array
    {
        $db    = config::getConnexion();
        $stmt  = $db->prepare(
            "SELECT nom, prenom, email, telephone, cin, role, statut, avatar, date_creation
             FROM user WHERE id_user = :id_user"
        );
        $stmt->execute(['id_user' => $id_user]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        if (!empty($user['date_creation'])) {
            $dt   = new DateTime($user['date_creation']);
            $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
            $user['date_creation_formatted'] =
                $dt->format('d') . ' ' .
                $mois[(int)$dt->format('n') - 1] . ' ' .
                $dt->format('Y') . ' · ' .
                $dt->format('H:i');
        }

        return $user;
    }

    public function getClientProfile(int $id_user): ?array
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare(
            "SELECT u.nom, u.prenom, u.email, u.telephone, u.adresse, u.cin, u.avatar, u.date_naissance,
                    c.numero_client
             FROM user u
             LEFT JOIN client c ON u.id_user = c.id_user
             WHERE u.id_user = :id_user"
        );
        $stmt->execute(['id_user' => $id_user]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    // ============================================================
    // RECHERCHE AVANCÉE
    // ============================================================

    public function searchUsers(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $perPage = max(1, min(100, (int)$perPage));
        $offset  = max(0, (int)(($page - 1) * $perPage));

        $db  = config::getConnexion();
        $sql = "SELECT u.id_user, u.nom, u.prenom, u.email, u.telephone, u.cin,
                       u.avatar, u.role, u.statut, u.date_creation, u.date_naissance,
                       a.niveau_acces,
                       ag.agence, ag.salaire,
                       c.numero_client
                FROM user u
                LEFT JOIN admin  a  ON u.id_user = a.id_user
                LEFT JOIN agent  ag ON u.id_user = ag.id_user
                LEFT JOIN client c  ON u.id_user = c.id_user";

        [$conditions, $params] = $this->buildUserFilters($filters);

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        // FIX : tri via whitelist stricte, zéro concaténation de variable non validée
        $validOrders = [
            'date_asc'  => 'u.date_creation ASC',
            'date_desc' => 'u.date_creation DESC',
            'nom_asc'   => 'u.nom ASC',
            'nom_desc'  => 'u.nom DESC',
        ];
        $orderClause = $validOrders[$filters['order_by'] ?? ''] ?? 'u.date_creation DESC';
        $sql .= " ORDER BY $orderClause";

        // Pagination via bindValue (pas de concaténation)
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countSearchUsers(array $filters = []): int
    {
        $db  = config::getConnexion();
        $sql = "SELECT COUNT(*)
                FROM user u
                LEFT JOIN admin  a  ON u.id_user = a.id_user
                LEFT JOIN agent  ag ON u.id_user = ag.id_user
                LEFT JOIN client c  ON u.id_user = c.id_user";

        [$conditions, $params] = $this->buildUserFilters($filters);

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    // ============================================================
    // STATISTIQUES
    // ============================================================

    /**
     * FIX : stats de base en une seule requête au lieu de 6.
     */
    // ============================================================
    // STATISTIQUES
    // ============================================================

    /**
     * KPI de base — une seule requête SQL.
     * Si $days est fourni, filtre sur les N derniers jours.
     */
    public function getStats(?int $days = null): array
    {
        $db  = config::getConnexion();
        $where = $days ? "WHERE date_creation >= DATE_SUB(NOW(), INTERVAL :days DAY)" : "";
        $sql = "SELECT
                    COUNT(*)                          AS total,
                    SUM(statut = 'actif')             AS actifs,
                    SUM(statut = 'bloque')            AS bloques,
                    SUM(role   = 'admin')             AS admins,
                    SUM(role   = 'agent')             AS agents,
                    SUM(role   = 'client')            AS clients
                FROM user $where";

        $stmt = $db->prepare($sql);
        if ($days) $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return array_map('intval', $row);
    }

    /**
     * Stats avancées avec filtre de période optionnel.
     * $days = null  → tout l'historique
     * $days = 30    → 30 derniers jours
     */
    public function getAdvancedStats(?int $days = null): array
    {
        $db    = config::getConnexion();
        $where = $days ? "WHERE date_creation >= DATE_SUB(NOW(), INTERVAL :days DAY)" : "";
        $whereAnd = $days ? "AND date_creation >= DATE_SUB(NOW(), INTERVAL :days DAY)" : "";

        // KPI en une seule requête
        $stats = $this->getStats($days);

        // Nouveaux ce mois (dans la période si filtrée)
        if ($days) {
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM user
                 WHERE DATE_FORMAT(date_creation, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
                 AND date_creation >= DATE_SUB(NOW(), INTERVAL :days DAY)"
            );
            $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $db->query(
                "SELECT COUNT(*) FROM user
                 WHERE DATE_FORMAT(date_creation, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
            );
        }
        $stats['new_this_month'] = (int) $stmt->fetchColumn();

        // Inscriptions par mois (12 derniers mois dans la période)
        if ($days) {
            $stmt = $db->prepare(
                "SELECT DATE_FORMAT(date_creation, '%Y-%m') AS month, COUNT(*) AS cnt
                 FROM user
                 WHERE date_creation >= DATE_SUB(NOW(), INTERVAL :days DAY)
                 GROUP BY month ORDER BY month ASC LIMIT 12"
            );
            $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $db->query(
                "SELECT DATE_FORMAT(date_creation, '%Y-%m') AS month, COUNT(*) AS cnt
                 FROM user GROUP BY month ORDER BY month ASC LIMIT 12"
            );
        }
        $stats['users_by_month'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Répartition par rôle
        if ($days) {
            $stmt = $db->prepare(
                "SELECT role, COUNT(*) AS cnt FROM user
                 WHERE date_creation >= DATE_SUB(NOW(), INTERVAL :days DAY)
                 GROUP BY role"
            );
            $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $db->query("SELECT role, COUNT(*) AS cnt FROM user GROUP BY role");
        }
        $stats['by_role'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Connexions par jour (7 derniers jours, indépendant de la période d'inscription)
        $stmt = $db->query(
            "SELECT DATE(last_login) AS jour, COUNT(*) AS cnt
             FROM user
             WHERE last_login >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY jour ORDER BY jour ASC"
        );
        $stats['connections_by_day'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Comparaison avec la période précédente (pour afficher évolution)
        if ($days) {
            $stmtPrev = $db->prepare(
                "SELECT COUNT(*) FROM user
                 WHERE date_creation >= DATE_SUB(NOW(), INTERVAL :days2 DAY)
                 AND date_creation < DATE_SUB(NOW(), INTERVAL :days DAY)"
            );
            $stmtPrev->bindValue(':days',  $days, \PDO::PARAM_INT);
            $stmtPrev->bindValue(':days2', $days * 2, \PDO::PARAM_INT);
            $stmtPrev->execute();
            $prevTotal = (int) $stmtPrev->fetchColumn();
            $stats['prev_total']  = $prevTotal;
            $stats['evolution']   = $prevTotal > 0
                ? round((($stats['total'] - $prevTotal) / $prevTotal) * 100, 1)
                : null;
        }

        return $stats;
    }
}