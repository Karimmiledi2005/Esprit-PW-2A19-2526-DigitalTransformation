<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$url = 'http://' . $_SERVER['HTTP_HOST'] . '/integ/view/FrontOffice/contratshow.php?id=' . $id;

try {
    $result = Builder::create()
        ->writer(new SvgWriter())
        ->data($url)
        ->size(220)
        ->margin(10)
        ->build();

    header('Content-Type: image/svg+xml');
    echo $result->getString();
} catch (Exception $e) {
    echo "Erreur QR: " . $e->getMessage();
}

exit;