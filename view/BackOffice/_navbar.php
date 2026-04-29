<?php
// Partial — Sidebar + Topbar (same style as traitement.html)
// Usage: include __DIR__ . '/_navbar.php';
// Set $activeNav = 'reclamations' (or other key) before including.
$activeNav = $activeNav ?? '';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/variables.css">
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/layout.css">
<link rel="stylesheet" href="assets/css/admin-users.css">

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">🛡️</div>
    <div>
      <div class="logo-text">Protex</div>
      <div class="logo-sub">Back-Office</div>
    </div>
  </div>

  <div class="sidebar-user">
    <div class="user-avatar">AD</div>
    <div>
      <div class="user-name">Agent Admin</div>
      <span class="user-role">Administrateur</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">Principal</div>
    <a class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>" href="#">
      <i class="bi bi-grid-1x2"></i> Tableau de bord
    </a>

    <div class="nav-section">Gestion</div>
    <a class="nav-item <?= $activeNav === 'sinistres' ? 'active' : '' ?>" href="sinsiter.html">
      <i class="bi bi-shield-exclamation"></i> Sinistres
      <span class="nav-badge accent">New</span>
    </a>
    <a class="nav-item <?= $activeNav === 'traitements' ? 'active' : '' ?>" href="traitement.html">
      <i class="bi bi-file-earmark-text"></i> Traitements
    </a>
    <a class="nav-item <?= $activeNav === 'utilisateurs' ? 'active' : '' ?>" href="admin-users.html">
      <i class="bi bi-people"></i> Utilisateurs
      <span class="nav-badge">24</span>
    </a>
    <a class="nav-item <?= $activeNav === 'contrats' ? 'active' : '' ?>" href="#">
      <i class="bi bi-file-earmark-check"></i> Contrats
    </a>
    <a class="nav-item <?= $activeNav === 'paiements' ? 'active' : '' ?>" href="paiements_back.html">
      <i class="bi bi-credit-card"></i> Paiements
    </a>
    <a class="nav-item <?= $activeNav === 'offres' ? 'active' : '' ?>" href="offres_back.html">
      <i class="bi bi-tags"></i> Offres
    </a>
    <a class="nav-item <?= $activeNav === 'reclamations' ? 'active' : '' ?>" href="reponse.html">
      <i class="bi bi-chat-dots"></i> Réclamations
    </a>
    <a class="nav-item <?= $activeNav === 'agences' ? 'active' : '' ?>" href="#">
      <i class="bi bi-geo-alt"></i> Agences
    </a>

    <div class="nav-section">Compte</div>
    <a class="nav-item <?= $activeNav === 'profil' ? 'active' : '' ?>" href="adminprofile.html">
      <i class="bi bi-person-gear"></i> Mon profil
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="#" class="logout-btn">
      <i class="bi bi-box-arrow-left"></i> Se déconnecter
    </a>
  </div>
</aside>
