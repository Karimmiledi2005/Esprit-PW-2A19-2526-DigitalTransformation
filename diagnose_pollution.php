<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['id_user'] = 1; // Assuming user 1 exists

// Buffer output to catch any pollution
ob_start();
require 'view/FrontOffice/sinistre_list_user.php';
$output = ob_get_clean();

echo "--- START OUTPUT ---\n";
echo $output;
echo "\n--- END OUTPUT ---\n";

$json = json_decode($output, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON ERROR: " . json_last_error_msg() . "\n";
    // Find where the non-JSON part starts
    if (preg_match('/^[^{\[]+/', $output, $matches)) {
        echo "POLLUTION AT START: " . bin2hex($matches[0]) . " ('{$matches[0]}')\n";
    }
} else {
    echo "JSON IS VALID\n";
    print_r($json);
}
?>
