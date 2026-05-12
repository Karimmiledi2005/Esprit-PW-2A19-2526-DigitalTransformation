<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!defined('BASE_URL')) {
    $cfgPath = dirname(__DIR__, 2) . '/config.php';
    if (file_exists($cfgPath)) require_once $cfgPath;
}
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Paiements — Protex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="user/css/variables.css">
    <link rel="stylesheet" href="user/css/base.css">
    <link rel="stylesheet" href="user/css/layout.css">
    <link rel="stylesheet" href="user/css/client.css">

    <!-- FrontOffice unifie - surcharge thème camarades dark-navy -->
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/light-theme.css">
    <link rel="stylesheet" href="assets/css/client.css"></head>
<body>
<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">
    <!-- ===== NAVBAR ===== -->
    <?php require_once __DIR__.'/assets/includes/navbar.php'; ?>


    <main class="main">
        <div class="page-header">
            <div>
                <div class="page-title-main">Mes Paiements</div>
                <div class="page-breadcrumb"><i class="bi bi-house"></i> Accueil <i class="bi bi-chevron-right"></i> Paiements</div>
            </div>
        </div>
        <div class="content">
            <div class="section-header"><div><div class="section-title">Historique des paiements</div><div class="section-sub">Vos transactions récentes</div></div></div>
            <p style="color:var(--text-secondary);text-align:center;padding:60px;">Page en cours de développement...</p>
        </div>
    </main>
</div>
<script src="assets_sinistre_traitement/js/main.js"></script>
</body>
</html>


