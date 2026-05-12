<?php
session_start();

$client_id = "Ov23liGC8ESkcViBlU00";
$redirect_uri = "http://localhost/user_web1_v2/view/FrontOffice/github_callback.php";
$scope = "user:email";
$state = bin2hex(random_bytes(16));
$_SESSION['github_oauth_state'] = $state;

$url = "https://github.com/login/oauth/authorize?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'scope' => $scope,
    'state' => $state
]);

header("Location: $url");
exit;
