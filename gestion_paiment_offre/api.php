<?php
// gestion_paiment_offre/api.php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

include(__DIR__ . '/config.php');

try {
    $db = config::getConnexion();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Connexion BDD échouée : ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {

    // ── Offres actives par type (pour devis.html front) ──
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

    // ── Liste tous les devis (pour devis.html back-office) ──
    case 'devis_liste':
        $stmt = $db->query(
            "SELECT d.id_devis, d.nom, d.prenom, d.email, d.telephone,
                    d.type_assurance, d.statut, d.montant_estime,
                    d.date_demande, d.reponse_admin, d.id_offre,
                    o.nom_offre
             FROM devis d
             LEFT JOIN offre o ON d.id_offre = o.id_offre
             ORDER BY d.date_demande DESC"
        );
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // ── Soumettre un nouveau devis (depuis devis.html front) ──
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

            // Insertion principale dans devis
            $stmt = $db->prepare(
                "INSERT INTO devis (id_offre, nom, prenom, email, telephone, type_assurance, statut)
                 VALUES (:id_offre, :nom, :prenom, :email, :telephone, :type_assurance, 'en_attente')"
            );
            $stmt->execute([
                ':id_offre'       => $data['id_offre']       ?? null,
                ':nom'            => trim($data['nom']        ?? ''),
                ':prenom'         => trim($data['prenom']     ?? ''),
                ':email'          => trim($data['email']      ?? ''),
                ':telephone'      => trim($data['telephone']  ?? ''),
                ':type_assurance' => $data['type_assurance']  ?? 'auto',
            ]);
            $id = (int) $db->lastInsertId();

            // Insertion dans la table de détails selon le type
            $type = strtolower(trim($data['type_assurance'] ?? ''));

            if ($type === 'auto') {
                $db->prepare(
                    "INSERT INTO devis_auto
                     (id_devis, marque, modele, annee, immatriculation, puissance, carburant, valeur_vehicule, usage_vehicule)
                     VALUES (?,?,?,?,?,?,?,?,?)"
                )->execute([
                    $id,
                    $data['marque']            ?? '',
                    $data['modele']            ?? '',
                    $data['annee']             ?? null,
                    $data['immatriculation']   ?? '',
                    $data['puissance'] ?? null,
                    $data['carburant']         ?? '',
                    $data['valeur_vehicule']   ?? null,
                    $data['usage_vehicule']    ?? '',
                ]);
            } elseif ($type === 'habitation') {
                $db->prepare(
                    "INSERT INTO devis_habitation
                     (id_devis, type_habitation, adresse, superficie, nombre_pieces, valeur_bien, statut_occupation)
                     VALUES (?,?,?,?,?,?,?)"
                )->execute([
                    $id,
                    $data['type_habitation']  ?? '',
                    $data['adresse']          ?? '',
                    $data['superficie']       ?? null,
                    $data['nombre_pieces']    ?? null,
                    $data['valeur_bien']      ?? null,
                    $data['statut_occupation']?? '',
                ]);
            } elseif ($type === 'sante') {
                $db->prepare(
                    "INSERT INTO devis_sante
                     (id_devis, age, situation_familiale, nombre_beneficiaires, antecedents_medicaux, couverture_souhaitee, profession)
                     VALUES (?,?,?,?,?,?,?)"
                )->execute([
                    $id,
                    $data['age']                  ?? null,
                    $data['situation_familiale']   ?? '',
                    $data['nombre_beneficiaires']  ?? 1,
                    $data['antecedents_medicaux']  ?? '',
                    $data['couverture_souhaitee']  ?? '',
                    $data['profession']            ?? '',
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

    // ── Modifier statut/montant/réponse d'un devis (back-office) ──
    case 'devis_modifier':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); break;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare(
            "UPDATE devis SET statut = ?, montant_estime = ?, reponse_admin = ? WHERE id_devis = ?"
        );
        $stmt->execute([
            $data['statut']          ?? 'en_attente',
            $data['montant_estime']  ?? null,
            $data['reponse_admin']   ?? null,
            (int)($data['id_devis'] ?? 0),
        ]);
        echo json_encode(['success' => true]);
        break;

    // ── Supprimer un devis (back-office) ──
    case 'devis_supprimer':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error' => 'ID invalide']); break; }
        // Les FK CASCADE suppriment automatiquement devis_auto/habitation/sante
        $db->prepare("DELETE FROM devis WHERE id_devis = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action inconnue : ' . $action]);
}
?>