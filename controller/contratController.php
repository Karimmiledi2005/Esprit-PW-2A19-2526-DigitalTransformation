<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/Contrat.php';
require_once __DIR__ . '/../service/SmsService.php';

class ContratController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    private function columnExists(string $table, string $column): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table
                AND COLUMN_NAME = :column";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['table' => $table, 'column' => $column]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function selectSql(string $where = ''): string
    {
        $formuleSelect = $this->columnExists('contrat', 'id_formule') ? ', f.nom_formule, f.prix_formule, f.franchise_formule' : ', NULL AS nom_formule, NULL AS prix_formule, NULL AS franchise_formule';
        $formuleJoin = $this->columnExists('contrat', 'id_formule') ? 'LEFT JOIN formule f ON c.id_formule = f.id_formule' : '';

        return "SELECT c.*, cat.nom_categorie, u.nom, u.prenom, u.email $formuleSelect
                FROM contrat c
                LEFT JOIN categorie cat ON c.id_categorie = cat.id_categorie
                LEFT JOIN user u ON c.id_client = u.id_user
                $formuleJoin
                $where";
    }

    private function hydrate(array $row): Contrat
    {
        $contrat = new Contrat(
            $row['numero_contrat'],
            $row['type_contrat'],
            (int)$row['id_client'],
            (int)$row['id_categorie'],
            (float)$row['prime_contrat'],
            (float)$row['franchise_contrat'],
            $row['date_debut_contrat'],
            $row['date_fin_contrat'],
            $row['statut_contrat'],
            $row['id_formule'] ?? null,
            $row['formule_contrat'] ?? ($row['nom_formule'] ?? null),
            $row['details_contrat'] ?? null
        );

        $contrat->setIdContrat($row['id_contrat']);
        $contrat->setNomCategorie($row['nom_categorie'] ?? '—');
        $contrat->setNomFormule($row['nom_formule'] ?? ($row['formule_contrat'] ?? '—'));
        $contrat->setNomClient($row['nom'] ?? '');
        $contrat->setPrenomClient($row['prenom'] ?? '');
        $contrat->setEmailClient($row['email'] ?? '');
        return $contrat;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query($this->selectSql('ORDER BY c.id_contrat DESC'));
        return array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getByClient(int $userId): array
    {
        if (!$userId) return [];
        $stmt = $this->db->prepare($this->selectSql('WHERE c.id_client = :id_client ORDER BY c.id_contrat DESC'));
        $stmt->execute(['id_client' => $userId]);
        return array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?Contrat
    {
        $row = $this->getById($id);
        return $row ? $this->hydrate($row) : null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare($this->selectSql('WHERE c.id_contrat = :id LIMIT 1'));
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getFirstClientId(): ?int
    {
        $stmt = $this->db->query("SELECT id_user FROM client ORDER BY id_user ASC LIMIT 1");
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public function getAllFormules(): array
    {
        $stmt = $this->db->query("SELECT f.*, c.nom_categorie FROM formule f LEFT JOIN categorie c ON c.id_categorie = f.id_categorie ORDER BY c.nom_categorie ASC, f.id_formule ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFormulesByCategorie(int $idCategorie): array
    {
        $stmt = $this->db->prepare("SELECT * FROM formule WHERE id_categorie = :cat ORDER BY id_formule ASC");
        $stmt->execute(['cat' => $idCategorie]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFormuleById(int $idFormule): ?array
    {
        $stmt = $this->db->prepare("SELECT f.*, c.nom_categorie FROM formule f LEFT JOIN categorie c ON c.id_categorie = f.id_categorie WHERE f.id_formule = :id LIMIT 1");
        $stmt->execute(['id' => $idFormule]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getFormuleByNameAndCategorie(string $formuleName, int $idCategorie): ?array
    {
        $stmt = $this->db->prepare("SELECT f.*, c.nom_categorie FROM formule f LEFT JOIN categorie c ON c.id_categorie = f.id_categorie WHERE f.nom_formule = :nom AND f.id_categorie = :cat LIMIT 1");
        $stmt->execute(['nom' => $formuleName, 'cat' => $idCategorie]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function generateNumero(): string
    {
        do {
            $numero = 'CTR-' . date('Y') . '-' . str_pad((string)random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM contrat WHERE numero_contrat = :numero");
            $stmt->execute(['numero' => $numero]);
        } while ((int)$stmt->fetchColumn() > 0);
        return $numero;
    }

    public function addContrat($contrat): bool
    {
        $columns = "numero_contrat, type_contrat, id_client, id_categorie, prime_contrat, franchise_contrat, date_debut_contrat, date_fin_contrat, statut_contrat";
        $values  = ":numero_contrat, :type_contrat, :id_client, :id_categorie, :prime_contrat, :franchise_contrat, :date_debut_contrat, :date_fin_contrat, :statut_contrat";
        $params = [
            'numero_contrat' => $contrat->getNumeroContrat(),
            'type_contrat' => $contrat->getTypeContrat(),
            'id_client' => $contrat->getIdClient(),
            'id_categorie' => $contrat->getIdCategorie(),
            'prime_contrat' => $contrat->getPrimeContrat(),
            'franchise_contrat' => $contrat->getFranchiseContrat(),
            'date_debut_contrat' => $contrat->getDateDebutContrat(),
            'date_fin_contrat' => $contrat->getDateFinContrat(),
            'statut_contrat' => $contrat->getStatutContrat()
        ];

        if ($this->columnExists('contrat', 'id_formule')) {
            $columns .= ", id_formule";
            $values .= ", :id_formule";
            $params['id_formule'] = $contrat->getIdFormule();
        }
        if ($this->columnExists('contrat', 'formule_contrat')) {
            $columns .= ", formule_contrat";
            $values .= ", :formule_contrat";
            $params['formule_contrat'] = $contrat->getFormuleContrat();
        }
        if ($this->columnExists('contrat', 'details_contrat')) {
            $columns .= ", details_contrat";
            $values .= ", :details_contrat";
            $params['details_contrat'] = $contrat->getDetailsContrat();
        }

        $query = $this->db->prepare("INSERT INTO contrat ($columns) VALUES ($values)");
        return $query->execute($params);
    }

    public function updateContrat(int $id, $contrat): bool
    {
        $set = "numero_contrat = :numero_contrat,
                type_contrat = :type_contrat,
                id_client = :id_client,
                id_categorie = :id_categorie,
                prime_contrat = :prime_contrat,
                franchise_contrat = :franchise_contrat,
                date_debut_contrat = :date_debut_contrat,
                date_fin_contrat = :date_fin_contrat,
                statut_contrat = :statut_contrat";
        $params = [
            'id' => $id,
            'numero_contrat' => $contrat->getNumeroContrat(),
            'type_contrat' => $contrat->getTypeContrat(),
            'id_client' => $contrat->getIdClient(),
            'id_categorie' => $contrat->getIdCategorie(),
            'prime_contrat' => $contrat->getPrimeContrat(),
            'franchise_contrat' => $contrat->getFranchiseContrat(),
            'date_debut_contrat' => $contrat->getDateDebutContrat(),
            'date_fin_contrat' => $contrat->getDateFinContrat(),
            'statut_contrat' => $contrat->getStatutContrat()
        ];
        if ($this->columnExists('contrat', 'id_formule')) {
            $set .= ", id_formule = :id_formule";
            $params['id_formule'] = $contrat->getIdFormule();
        }
        if ($this->columnExists('contrat', 'formule_contrat')) {
            $set .= ", formule_contrat = :formule_contrat";
            $params['formule_contrat'] = $contrat->getFormuleContrat();
        }
        if ($this->columnExists('contrat', 'details_contrat')) {
            $set .= ", details_contrat = :details_contrat";
            $params['details_contrat'] = $contrat->getDetailsContrat();
        }
        $query = $this->db->prepare("UPDATE contrat SET $set WHERE id_contrat = :id");
        return $query->execute($params);
    }

public function updateStatut(int $id, string $statut): bool
{
    $allowed = ['en attente', 'actif', 'expiré', 'résilié', 'refusé'];

    if (!in_array($statut, $allowed, true)) {
        return false;
    }

    // 1) Update status
    $stmt = $this->db->prepare("
        UPDATE contrat
        SET statut_contrat = :statut
        WHERE id_contrat = :id
    ");

    $updated = $stmt->execute([
        'statut' => $statut,
        'id' => $id
    ]);

    if (!$updated) {
        return false;
    }

    // 2) Get contract
    $stmt = $this->db->prepare("
        SELECT *
        FROM contrat
        WHERE id_contrat = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $contrat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contrat) {
        return true;
    }

    // 3) Get phone from details_contrat JSON
    $details = json_decode($contrat['details_contrat'] ?? '', true);

    if (!is_array($details) || empty($details['telephone'])) {
        return true;
    }

    $telephone = trim((string)$details['telephone']);
    $client = trim(($details['prenom'] ?? '') . ' ' . ($details['nom'] ?? ''));

    if ($client === '') {
        $client = 'cher client';
    }

    $numeroContrat = $contrat['numero_contrat'] ?? '';

    // 4) SMS message
    $message = "Bonjour $client, le statut de votre contrat d'assurance $numeroContrat a été mis à jour : $statut.";

    // 5) Send real SMS
    $smsResult = SmsService::sendSms($telephone, $message);

    $response = $smsResult['response'] ?? [];
    $firstMessage = $response['messages'][0] ?? [];

    $smsStatus = $firstMessage['status']['name'] ?? 'unknown';
    $messageId = $firstMessage['messageId'] ?? null;
    $bulkId = $response['bulkId'] ?? null;

    // 6) Save in sms_alerts with correct type_alert
    $typeAlert = 'changement_statut_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $statut);

    $stmtLog = $this->db->prepare("
        INSERT INTO sms_alerts (
            id_contrat,
            id_client,
            telephone,
            message,
            type_alert,
            statut,
            infobip_message_id,
            infobip_bulk_id,
            response_json,
            date_envoi
        ) VALUES (
            :id_contrat,
            :id_client,
            :telephone,
            :message,
            :type_alert,
            :statut,
            :message_id,
            :bulk_id,
            :response_json,
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            telephone = VALUES(telephone),
            message = VALUES(message),
            statut = VALUES(statut),
            infobip_message_id = VALUES(infobip_message_id),
            infobip_bulk_id = VALUES(infobip_bulk_id),
            response_json = VALUES(response_json),
            date_envoi = NOW()
    ");

    $stmtLog->execute([
        'id_contrat' => $id,
        'id_client' => $contrat['id_client'] ?? null,
        'telephone' => $telephone,
        'message' => $message,
        'type_alert' => $typeAlert,
        'statut' => $smsStatus,
        'message_id' => $messageId,
        'bulk_id' => $bulkId,
        'response_json' => json_encode($smsResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    ]);

    // 7) File log
    $logDir = __DIR__ . '/../logs';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    file_put_contents(
        $logDir . '/sms_expiration.log',
        "[" . date("Y-m-d H:i:s") . "] STATUS CHANGE SMS\n" .
        "Contract: $numeroContrat\n" .
        "New Status: $statut\n" .
        "Client: $client\n" .
        "Phone: $telephone\n" .
        "Message: $message\n" .
        "SMS Status: $smsStatus\n" .
        "Type Alert: $typeAlert\n" .
        "Message ID: " . ($messageId ?? 'N/A') . "\n" .
        "Bulk ID: " . ($bulkId ?? 'N/A') . "\n" .
        "----------------------------------------\n",
        FILE_APPEND
    );

    return true;
}

    private function logSmsStatusChange(array $contrat, string $newStatus, string $telephone, string $message, string $smsStatus, ?string $messageId, ?string $bulkId, array $response): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        file_put_contents(
            $logDir . '/sms_expiration.log',
            "[" . date('Y-m-d H:i:s') . "] STATUS CHANGE SMS\n" .
            "Contract: " . ($contrat['numero_contrat'] ?? '') . "\n" .
            "New Status: " . $newStatus . "\n" .
            "Phone: " . ($telephone ?: 'N/A') . "\n" .
            "Message: " . ($message ?: 'N/A') . "\n" .
            "SMS Status: " . $smsStatus . "\n" .
            "Message ID: " . ($messageId ?? 'N/A') . "\n" .
            "Bulk ID: " . ($bulkId ?? 'N/A') . "\n" .
            "Response: " . json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n" .
            "----------------------------------------\n",
            FILE_APPEND
        );
    }

    public function deleteContrat(int $id): bool
    {
        $query = $this->db->prepare("DELETE FROM contrat WHERE id_contrat = :id");
        return $query->execute(['id' => $id]);
    }

    public function countContrats(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM contrat")->fetchColumn();
    }

    public function getContratsSortedByPrime(string $order = 'ASC'): array
    {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $stmt = $this->db->query(
            $this->selectSql("ORDER BY c.prime_contrat $order")
        );

        return array_map(
            fn($row) => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function getContratsSortedByDateDebut(string $order = 'DESC'): array
    {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $stmt = $this->db->query(
            $this->selectSql("ORDER BY c.date_debut_contrat $order")
        );

        return array_map(
            fn($row) => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function searchContrats(string $keyword): array
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return $this->getAll();
        }

        $sql = $this->selectSql("
            WHERE c.numero_contrat LIKE :keyword
               OR c.type_contrat LIKE :keyword
               OR cat.nom_categorie LIKE :keyword
               OR u.nom LIKE :keyword
               OR u.prenom LIKE :keyword
               OR u.email LIKE :keyword
               OR f.nom_formule LIKE :keyword
            ORDER BY c.id_contrat DESC
        ");

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'keyword' => '%' . $keyword . '%'
        ]);

        return array_map(
            fn($row) => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function getContratsByStatut(string $statut): array
    {
        $allowed = ['en attente', 'actif', 'expiré', 'résilié', 'refusé'];

        if (!in_array($statut, $allowed, true)) {
            return [];
        }

        $stmt = $this->db->prepare(
            $this->selectSql("WHERE c.statut_contrat = :statut ORDER BY c.id_contrat DESC")
        );

        $stmt->execute([
            'statut' => $statut
        ]);

        return array_map(
            fn($row) => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function countContratsByStatut(string $statut): int
    {
        $allowed = ['en attente', 'actif', 'expiré', 'résilié', 'refusé'];

        if (!in_array($statut, $allowed, true)) {
            return 0;
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM contrat
            WHERE statut_contrat = :statut
        ");

        $stmt->execute([
            'statut' => $statut
        ]);

        return (int)$stmt->fetchColumn();
    }


    public function getGarantiesByContrat(int $idContrat): array
    {
        if ($idContrat <= 0) {
            return [];
        }

        $hasIdFormule = $this->columnExists('contrat', 'id_formule');
        $hasFormuleContrat = $this->columnExists('contrat', 'formule_contrat');

        $joinCondition = $hasIdFormule
            ? "c.id_formule = f.id_formule"
            : "1 = 0";

        if ($hasFormuleContrat) {
            $joinCondition .= " OR (c.formule_contrat = f.nom_formule AND c.id_categorie = f.id_categorie)";
        }

        $sql = "
            SELECT DISTINCT
                g.id_garantie,
                g.nom_garantie,
                g.description_garantie,
                g.plafond_couvert_garantie,
                fg.niveau_couvert_garantie,
                f.nom_formule
            FROM contrat c
            INNER JOIN formule f ON ($joinCondition)
            INNER JOIN formule_garantie fg ON f.id_formule = fg.id_formule
            INNER JOIN garantie g ON fg.id_garantie = g.id_garantie
            WHERE c.id_contrat = :id_contrat
            ORDER BY
                CASE
                    WHEN fg.niveau_couvert_garantie = 'basique' THEN 1
                    WHEN fg.niveau_couvert_garantie = 'option' THEN 2
                    ELSE 3
                END,
                g.nom_garantie ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_contrat' => $idContrat
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    public function getContratsExpirantBientot(int $days = 30): array
    {
        $days = max(1, min($days, 365));

        $telephoneSelect = $this->columnExists('user', 'telephone')
            ? ', u.telephone AS telephone_client'
            : ', NULL AS telephone_client';

        $formuleSelect = $this->columnExists('contrat', 'id_formule')
            ? ', f.nom_formule, f.prix_formule, f.franchise_formule'
            : ', NULL AS nom_formule, NULL AS prix_formule, NULL AS franchise_formule';

        $formuleJoin = $this->columnExists('contrat', 'id_formule')
            ? 'LEFT JOIN formule f ON c.id_formule = f.id_formule'
            : '';

        $sql = "
            SELECT
                c.*,
                cat.nom_categorie,
                u.nom,
                u.prenom,
                u.email
                $telephoneSelect
                $formuleSelect
            FROM contrat c
            LEFT JOIN categorie cat ON c.id_categorie = cat.id_categorie
            LEFT JOIN user u ON c.id_client = u.id_user
            $formuleJoin
            WHERE c.statut_contrat = 'actif'
              AND c.date_fin_contrat >= CURDATE()
              AND c.date_fin_contrat < DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY c.date_fin_contrat ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['telephone_final'] = $this->extractTelephoneFromContratRow($row);
            $row['jours_restants'] = $this->daysUntil($row['date_fin_contrat'] ?? null);
        }

        return $rows;
    }

    private function extractTelephoneFromContratRow(array $row): string
    {
        $details = [];
        if (!empty($row['details_contrat'])) {
            $decoded = json_decode((string)$row['details_contrat'], true);
            if (is_array($decoded)) {
                $details = $decoded;
            }
        }

        $possibleKeys = ['telephone', 'tel', 'phone', 'telephone_client', 'client_telephone'];
        foreach ($possibleKeys as $key) {
            if (!empty($details[$key]) && !is_array($details[$key])) {
                return trim((string)$details[$key]);
            }
        }

        $telephone = trim((string)($row['telephone_client'] ?? ''));
        if ($telephone !== '') {
            return $telephone;
        }

        return '';
    }

    private function daysUntil(?string $date): ?int
    {
        if (!$date) {
            return null;
        }

        try {
            $today = new DateTime(date('Y-m-d'));
            $target = new DateTime($date);
            return (int)$today->diff($target)->format('%r%a');
        } catch (Exception $e) {
            return null;
        }
    }

    private function ensureSmsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS sms_alerts (
                id_alert INT AUTO_INCREMENT PRIMARY KEY,
                id_contrat INT NOT NULL,
                id_client INT NULL,
                telephone VARCHAR(30) NOT NULL,
                message TEXT NOT NULL,
                type_alert VARCHAR(100) NOT NULL DEFAULT 'expiration_contrat',
                statut VARCHAR(80) NOT NULL,
                infobip_message_id VARCHAR(120) NULL,
                infobip_bulk_id VARCHAR(120) NULL,
                response_json LONGTEXT NULL,
                date_envoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_sms_expiration (id_contrat, type_alert)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";

        $this->db->exec($sql);
    }

    public function smsAlertAlreadySent(int $idContrat, string $typeAlert = 'expiration_contrat'): bool
    {
        $this->ensureSmsTable();

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM sms_alerts
            WHERE id_contrat = :id_contrat
              AND type_alert = :type_alert
              AND statut NOT IN ('failed', 'echec')
        ");

        $stmt->execute([
            'id_contrat' => $idContrat,
            'type_alert' => $typeAlert
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function saveSmsAlert(
        int $idContrat,
        ?int $idClient,
        string $telephone,
        string $message,
        string $typeAlert = 'expiration_contrat',
        string $statut = 'sent',
        ?string $messageId = null,
        ?string $bulkId = null,
        ?array $response = null
    ): bool {
        $this->ensureSmsTable();

        $stmt = $this->db->prepare("
            INSERT INTO sms_alerts (
                id_contrat,
                id_client,
                telephone,
                message,
                type_alert,
                statut,
                infobip_message_id,
                infobip_bulk_id,
                response_json,
                date_envoi
            ) VALUES (
                :id_contrat,
                :id_client,
                :telephone,
                :message,
                :type_alert,
                :statut,
                :infobip_message_id,
                :infobip_bulk_id,
                :response_json,
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                id_client = VALUES(id_client),
                telephone = VALUES(telephone),
                message = VALUES(message),
                statut = VALUES(statut),
                infobip_message_id = VALUES(infobip_message_id),
                infobip_bulk_id = VALUES(infobip_bulk_id),
                response_json = VALUES(response_json),
                date_envoi = NOW()
        ");

        return $stmt->execute([
            'id_contrat' => $idContrat,
            'id_client' => $idClient,
            'telephone' => $telephone,
            'message' => $message,
            'type_alert' => $typeAlert,
            'statut' => $statut,
            'infobip_message_id' => $messageId,
            'infobip_bulk_id' => $bulkId,
            'response_json' => $response ? json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null
        ]);
    }

    public function envoyerAlertesSmsExpiration(int $days = 30): array
    {
        $this->ensureSmsTable();

        $contrats = $this->getContratsExpirantBientot($days);

        $result = [
            'total_detectes' => count($contrats),
            'envoyes' => 0,
            'deja_envoyes' => 0,
            'sans_telephone' => 0,
            'erreurs' => 0,
            'details' => []
        ];

        foreach ($contrats as $contrat) {
            $idContrat = (int)$contrat['id_contrat'];
            $telephone = trim((string)($contrat['telephone_final'] ?? ''));

            if ($this->smsAlertAlreadySent($idContrat)) {
                $result['deja_envoyes']++;
                $result['details'][] = [
                    'numero' => $contrat['numero_contrat'] ?? '',
                    'status' => 'deja_envoye',
                    'message' => 'Alerte déjà envoyée pour ce contrat.'
                ];
                continue;
            }

            if ($telephone === '') {
                $result['sans_telephone']++;
                $result['details'][] = [
                    'numero' => $contrat['numero_contrat'] ?? '',
                    'status' => 'sans_telephone',
                    'message' => 'Aucun numéro de téléphone trouvé pour ce client.'
                ];
                continue;
            }

            $jours = (int)($contrat['jours_restants'] ?? 0);
            $client = trim(($contrat['prenom'] ?? '') . ' ' . ($contrat['nom'] ?? ''));
            if ($client === '') {
                $client = 'cher client';
            }

            $numeroContrat = $contrat['numero_contrat'] ?? '';
            $dateFin = date('d/m/Y', strtotime($contrat['date_fin_contrat']));

            $message = "Bonjour $client, votre contrat d'assurance $numeroContrat expire le $dateFin, dans $jours jour(s). Merci de le renouveler avant son expiration.";

            $smsResult = SmsService::send($telephone, $message);
            $response = $smsResult['response'] ?? [];
            $firstMessage = $response['messages'][0] ?? [];

            $messageId = $firstMessage['messageId'] ?? null;
            $bulkId = $response['bulkId'] ?? null;
            $statusName = $firstMessage['status']['name'] ?? null;

            $success = !empty($smsResult['success']);
            $statutSms = $success ? ($statusName ?: 'sent') : 'failed';

            $this->saveSmsAlert(
                $idContrat,
                isset($contrat['id_client']) ? (int)$contrat['id_client'] : null,
                $telephone,
                $message,
                'expiration_contrat',
                $statutSms,
                $messageId,
                $bulkId,
                $smsResult
            );

            if ($success) {
                $result['envoyes']++;
            } else {
                $result['erreurs']++;
            }

            $result['details'][] = [
                'numero' => $numeroContrat,
                'telephone' => $telephone,
                'message' => $message,
                'status' => $statutSms,
                'message_id' => $messageId,
                'bulk_id' => $bulkId,
                'error' => $smsResult['error'] ?? null
            ];
        }

        return $result;
    }

    public function getSmsAlerts(): array
    {
        $this->ensureSmsTable();

        $stmt = $this->db->query("
            SELECT
                sa.*,
                c.numero_contrat,
                c.date_fin_contrat,
                c.statut_contrat
            FROM sms_alerts sa
            LEFT JOIN contrat c ON c.id_contrat = sa.id_contrat
            ORDER BY sa.date_envoi DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function renewContrat(int $idContrat): ?int
    {
        $old = $this->getById($idContrat);

        if (!$old) {
            return null;
        }

        $statut = strtolower((string)($old['statut_contrat'] ?? ''));
        if (!in_array($statut, ['actif', 'expiré', 'résilié'], true)) {
            return null;
        }

        $oldDateFin = $old['date_fin_contrat'] ?? date('Y-m-d');
        $startBase = strtotime($oldDateFin) >= strtotime(date('Y-m-d'))
            ? strtotime($oldDateFin . ' +1 day')
            : strtotime(date('Y-m-d'));

        $dateDebut = date('Y-m-d', $startBase);
        $dateFin = date('Y-m-d', strtotime($dateDebut . ' +1 year'));

        $details = [];
        if (!empty($old['details_contrat'])) {
            $decoded = json_decode((string)$old['details_contrat'], true);
            if (is_array($decoded)) {
                $details = $decoded;
            }
        }

        $details['renouvellement_de'] = $old['numero_contrat'] ?? ('#' . $idContrat);
        $details['date_demande_renouvellement'] = date('Y-m-d H:i:s');

        $newContrat = new Contrat(
            $this->generateNumero(),
            $old['type_contrat'],
            (int)$old['id_client'],
            (int)$old['id_categorie'],
            (float)$old['prime_contrat'],
            (float)$old['franchise_contrat'],
            $dateDebut,
            $dateFin,
            'en attente',
            isset($old['id_formule']) ? (int)$old['id_formule'] : null,
            $old['formule_contrat'] ?? ($old['nom_formule'] ?? null),
            json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $ok = $this->addContrat($newContrat);
        if (!$ok) {
            return null;
        }

        return (int)$this->db->lastInsertId();
    }

}
