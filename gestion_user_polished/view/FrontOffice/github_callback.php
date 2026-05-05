<?php
session_start();
require_once __DIR__ . '/../../controller/Client_Con.php';

$client_id = "Ov23liGC8ESkcViBlU00";
$client_secret = "ec2d6bc6062a88ac6c2bd342298ff568046cf550";
$redirect_uri = "http://localhost/user_web1_v2/view/FrontOffice/github_callback.php";

$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

if (!$code || $state !== ($_SESSION['github_oauth_state'] ?? '')) {
    die("Échec de l'authentification GitHub.");
}

// 1. Échanger le code contre un access token
$ch = curl_init("https://github.com/login/oauth/access_token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'code' => $code,
    'redirect_uri' => $redirect_uri
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

$access_token = $data['access_token'] ?? '';

if (!$access_token) {
    die("Erreur lors de la récupération de l'access token GitHub.");
}

// 2. Récupérer les infos de l'utilisateur
$ch = curl_init("https://api.github.com/user");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: token ' . $access_token,
    'User-Agent: Protex-App'
]);
$github_user = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($github_user['id'])) {
    die("Erreur lors de la récupération des infos utilisateur GitHub.");
}

// 3. Récupérer l'email (si non présent dans /user)
if (empty($github_user['email'])) {
    $ch = curl_init("https://api.github.com/user/emails");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: token ' . $access_token,
        'User-Agent: Protex-App'
    ]);
    $emails = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    foreach ($emails as $email) {
        if ($email['primary'] && $email['verified']) {
            $github_user['email'] = $email['email'];
            break;
        }
    }
}

// 4. Connecter ou créer l'utilisateur dans Protex
$controller = new UserController();
$result = $controller->findOrCreateGithubUser($github_user);

if ($result['success']) {
    header("Location: client.html");
} else {
    die("Erreur lors de la connexion Protex : " . $result['message']);
}
exit;
