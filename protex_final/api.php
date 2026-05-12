<?php
/**
 * api.php — API REST interne Protex Assurance
 * FIX P-21 : Authentification requise sur tous les endpoints
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Démarrage session avant tout check
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config.php';

// FIX P-21 : Vérification d'authentification
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié. Veuillez vous connecter.']);
    exit;
}

try {
    $db = config::getConnexion();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Connexion BDD échouée : ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {

    // ── Offres actives par type (pour devis FrontOffice) ──
    case 'offres':
        $type = $_GET['type'] ?? '';
        if ($type) {
            $stmt = $db->prepare(
                "SELECT id_offre, nom_offre, type_offre, prix_mensuel, prix_annuel, couverture, plafond
                 FROM offre WHERE type_offre = ? AND statut = 'active' ORDER BY prix_annuel ASC"
            );
            $stmt->execute([$type]);
        } else {
            $stmt = $db->query(
                "SELECT id_offre, nom_offre, type_offre, prix_mensuel, prix_annuel, couverture, plafond
                 FROM offre WHERE statut = 'active' ORDER BY type_offre, prix_annuel ASC"
            );
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // ── Liste tous les devis (BackOffice) ──
    case 'devis_liste':
        // Restriction rôle : seuls admin/agent/superadmin voient tous les devis
        $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'client';
        if ($role === 'client') {
            // Un client ne voit que ses propres devis (via email en session)
            $email = $_SESSION['user_email'] ?? '';
            $stmt  = $db->prepare(
                "SELECT d.*, o.nom_offre FROM devis d
                 LEFT JOIN offre o ON d.id_offre = o.id_offre
                 WHERE d.email = ? ORDER BY d.date_demande DESC"
            );
            $stmt->execute([$email]);
        } else {
            $stmt = $db->query(
                "SELECT d.*, o.nom_offre FROM devis d
                 LEFT JOIN offre o ON d.id_offre = o.id_offre
                 ORDER BY d.date_demande DESC"
            );
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // ── Soumettre un nouveau devis (FrontOffice client) ──
    case 'devis_ajouter':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            break;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            break;
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare(
                "INSERT INTO devis (id_offre, nom, prenom, email, telephone, type_assurance, statut)
                 VALUES (:id_offre, :nom, :prenom, :email, :telephone, :type_assurance, 'en_attente')"
            );
            $stmt->execute([
                ':id_offre'       => $data['id_offre']      ?? null,
                ':nom'            => trim($data['nom']       ?? ''),
                ':prenom'         => trim($data['prenom']    ?? ''),
                ':email'          => trim($data['email']     ?? ''),
                ':telephone'      => trim($data['telephone'] ?? ''),
                ':type_assurance' => $data['type_assurance'] ?? 'auto',
            ]);
            $id = (int)$db->lastInsertId();

            $type = strtolower(trim($data['type_assurance'] ?? ''));

            if ($type === 'auto') {
                $db->prepare(
                    "INSERT INTO devis_auto
                     (id_devis, marque, modele, annee, immatriculation, puissance, carburant, valeur_vehicule, usage_vehicule)
                     VALUES (?,?,?,?,?,?,?,?,?)"
                )->execute([
                    $id,
                    $data['marque']          ?? '',
                    $data['modele']          ?? '',
                    $data['annee']           ?? null,
                    $data['immatriculation'] ?? '',
                    $data['puissance']       ?? null,
                    $data['carburant']       ?? '',
                    $data['valeur_vehicule'] ?? null,
                    $data['usage_vehicule']  ?? '',
                ]);
            } elseif ($type === 'habitation') {
                $db->prepare(
                    "INSERT INTO devis_habitation
                     (id_devis, type_habitation, adresse, superficie, nombre_pieces, valeur_bien, statut_occupation)
                     VALUES (?,?,?,?,?,?,?)"
                )->execute([
                    $id,
                    $data['type_habitation']   ?? '',
                    $data['adresse']           ?? '',
                    $data['superficie']        ?? null,
                    $data['nombre_pieces']     ?? null,
                    $data['valeur_bien']       ?? null,
                    $data['statut_occupation'] ?? '',
                ]);
            } elseif ($type === 'sante') {
                $db->prepare(
                    "INSERT INTO devis_sante
                     (id_devis, age, situation_familiale, nombre_beneficiaires, antecedents_medicaux, couverture_souhaitee, profession)
                     VALUES (?,?,?,?,?,?,?)"
                )->execute([
                    $id,
                    $data['age']                 ?? null,
                    $data['situation_familiale'] ?? '',
                    $data['nombre_beneficiaires']?? 1,
                    $data['antecedents_medicaux']?? '',
                    $data['couverture_souhaitee']?? '',
                    $data['profession']          ?? '',
                ]);
            }

            $db->commit();

            echo json_encode([
                'success'   => true,
                'id'        => $id,
                'reference' => 'DEV-2026-' . str_pad($id, 4, '0', STR_PAD_LEFT),
            ]);

        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    // ── Modifier statut/montant/réponse d'un devis (BackOffice) ──
    case 'devis_modifier':
        $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'client';
        if ($role === 'client') {
            http_response_code(403);
            echo json_encode(['error' => 'Accès refusé']);
            break;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); break;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare(
            "UPDATE devis SET statut = ?, montant_estime = ?, reponse_admin = ? WHERE id_devis = ?"
        );
        $stmt->execute([
            $data['statut']         ?? 'en_attente',
            $data['montant_estime'] ?? null,
            $data['reponse_admin']  ?? null,
            (int)($data['id_devis'] ?? 0),
        ]);
        echo json_encode(['success' => true]);
        break;

    // ── Supprimer un devis (BackOffice) ──
    case 'devis_supprimer':
        $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'client';
        if ($role === 'client') {
            http_response_code(403);
            echo json_encode(['error' => 'Accès refusé']);
            break;
        }
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error' => 'ID invalide']); break; }
        $db->prepare("DELETE FROM devis WHERE id_devis = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action inconnue : ' . htmlspecialchars($action)]);
}
