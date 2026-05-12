<?php

require_once __DIR__ . "/controller/ContratController.php";

header('Content-Type: text/plain; charset=utf-8');

try {
    $controller = new ContratController();

    $result = $controller->envoyerAlertesSmsExpiration(30);

    echo "Automatic SMS test result:\n\n";
    print_r($result);

} catch (Throwable $e) {
    echo "Test failed:\n\n";
    print_r([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
