<?php
/**
 * statsType.php
 * GET → JSON { success, stats: [{type, total, percent}] }
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../controller/ReponseController.php';

try {
    $ctrl  = new ReponseController();
    $rows  = $ctrl->getStatsByType();

    $grand = array_sum(array_column($rows, 'total'));

    $stats = array_map(function($row) use ($grand) {
        return [
            'type'    => $row['type'],
            'total'   => (int)$row['total'],
            'percent' => $grand > 0 ? round($row['total'] / $grand * 100, 1) : 0
        ];
    }, $rows);

    echo json_encode(['success' => true, 'stats' => $stats, 'total' => (int)$grand]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
