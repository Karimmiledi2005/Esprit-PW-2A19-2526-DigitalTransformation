<?php
/**
 * chatbot.php — Point d'entrée AJAX du chatbot
 * POST { message: string, email: string }
 */
header('Content-Type: application/json; charset=utf-8');

// Sécurité : désactiver l'affichage des erreurs PHP en production
ini_set('display_errors', '0');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

require_once __DIR__ . '/../../controller/ChatbotController.php';

try {
    $raw  = file_get_contents('php://input');
    $body = ($raw !== false && $raw !== '') ? (json_decode($raw, true) ?? []) : $_POST;

    $message = trim($body['message'] ?? '');
    $email   = trim($body['email']   ?? 'client@protex.tn');

    if ($message === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message vide.']);
        exit;
    }
    if (mb_strlen($message) > 500) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message trop long (500 caractères max).']);
        exit;
    }

    $ctrl   = new ChatbotController();
    $result = $ctrl->handleMessage($message, $email);

    if (!$result['success']) {
        http_response_code(200); // On retourne 200 même en cas d'erreur métier pour que le JS traite le message
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('chatbot.php fatal: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '❌ Erreur serveur inattendue. Contactez l\'administrateur.']);
}
