<?php

require_once __DIR__ . "/service/WhatsAppService.php";

$result = WhatsAppService::sendText(
    "216XXXXXXXX",
    "Bonjour, votre contrat d'assurance arrive bientôt à expiration."
);

echo "<pre>";
print_r($result);
echo "</pre>";