<?php
/**
 * dashboard_stats.php
 * API endpoint to fetch advanced dashboard analytics.
 */

error_reporting(0);
require_once __DIR__ . '/../config/database.php';

function json(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = config::getConnexion();

    // 1. Total claims this month
    $stmt1 = $db->query("
        SELECT COUNT(*) as count 
        FROM sinistre 
        WHERE MONTH(date_declaration) = MONTH(CURRENT_DATE()) 
        AND YEAR(date_declaration) = YEAR(CURRENT_DATE())
    ");
    $claimsThisMonth = (int)$stmt1->fetchColumn();

    // 2. Average AI Fraud Score
    $stmt2 = $db->query("SELECT ROUND(AVG(score_global), 1) FROM fraud_analysis");
    $avgFraudScore = (float)$stmt2->fetchColumn();

    // 3. Total Refunded Money
    $stmt3 = $db->query("SELECT SUM(montant_indemnise) FROM traitement WHERE statut = 'accepte'");
    $totalRefunded = (float)$stmt3->fetchColumn();

    // 4. Total Saved Money (From refused claims)
    $stmt4 = $db->query("SELECT SUM(montant_indemnise) FROM traitement WHERE statut = 'refuse'");
    $totalSaved = (float)$stmt4->fetchColumn();

    // 5. Chart 1: Refunded vs Saved per month (Last 6 months)
    $stmt5 = $db->query("
        SELECT 
            DATE_FORMAT(date_traitement, '%Y-%m') as month,
            SUM(CASE WHEN statut = 'accepte' THEN montant_indemnise ELSE 0 END) as refunded,
            SUM(CASE WHEN statut = 'refuse' THEN montant_indemnise ELSE 0 END) as saved
        FROM traitement
        WHERE date_traitement >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(date_traitement, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthlyData = $stmt5->fetchAll(PDO::FETCH_ASSOC);

    // 6. Chart 2: Fraud Risk Distribution
    $stmt6 = $db->query("
        SELECT niveau_risque, COUNT(*) as count 
        FROM fraud_analysis 
        GROUP BY niveau_risque
    ");
    
    $fraudDist = [
        'faible' => 0,
        'moyen' => 0,
        'eleve' => 0,
        'critique' => 0
    ];
    
    foreach ($stmt6->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $niveau = strtolower($row['niveau_risque']);
        if (isset($fraudDist[$niveau])) {
            $fraudDist[$niveau] = (int)$row['count'];
        }
    }

    json([
        'success' => true,
        'data' => [
            'kpis' => [
                'claims_this_month' => $claimsThisMonth,
                'avg_fraud_score' => $avgFraudScore,
                'total_refunded' => $totalRefunded,
                'total_saved' => $totalSaved
            ],
            'charts' => [
                'monthly_financials' => $monthlyData,
                'fraud_distribution' => $fraudDist
            ]
        ]
    ]);

} catch (Exception $e) {
    json(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()], 500);
}
