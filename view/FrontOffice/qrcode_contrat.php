<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    exit('ID contrat invalide');
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

$url = $scheme . '://' . $_SERVER['HTTP_HOST']
     . '/gestion_user/view/FrontOffice/contratshow.php?id=' . $id;

try {
    $result = Builder::create()
        ->writer(new SvgWriter())
        ->data($url)
        ->size(220)
        ->margin(10)
        ->build();

    header('Content-Type: image/svg+xml');
    echo $result->getString();
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Erreur QR: ' . $e->getMessage();
}
exit;