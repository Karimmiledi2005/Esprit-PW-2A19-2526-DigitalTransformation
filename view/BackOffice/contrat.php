<?php
require_once '../../Controller/ContratController.php';

$contratC = new ContratController();
$list = $contratC->listContrats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office Contrats - Protex</title>
    <meta name="description" content="3D Glassmorphism Dashboard Template by TemplateMo">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/templatemo-glass-admin-style.css">
    <!--

TemplateMo 607 Glass Admin

https://templatemo.com/tm-607-glass-admin

-->
</head>
<body>
    <!-- Animated Background -->
    <div class="background"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="logo.png" alt="logo" width="50" height="50">              
                  <span class="logo-text">Protex</span>
            </div>

            <ul class="nav-menu">
    <li class="nav-section">
        <span class="nav-section-title">Main Menu</span>
        <ul>
            <li class="nav-item">
                <a href="index.html" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a href="contrats-admin.html" class="nav-link active">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Contrats
                </a>
            </li>

            <li class="nav-item">
                <a href="garanties-admin.html" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Garanties
                </a>
            </li>

            <li class="nav-item">
                <a href="settings.html" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                    Settings
                </a>
            </li>
        </ul>
    </li>
</ul>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">TM</div>
                    <div class="user-info">
                       <div class="user-name">Admin Protex</div>
                    <div class="user-role">Gestionnaire</div>
                    </div>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navbar -->
           <nav class="navbar">
    <div class="page-header">
       <h1 class="page-title">Gestion des contrats</h1>
<div class="page-breadcrumb">
    <a href="index.html">Dashboard</a>
    <span>/</span>
    <span>Contrats</span>
</div>
    </div>

    <div class="navbar-right">
        ...
    </div>
</nav>

            <!-- Stats Cards -->
           <section class="stats-grid">
    <div class="glass-card glass-card-3d stat-card">
        <div class="stat-card-inner">
            <div class="stat-info">
                <h3>Total demandes</h3>
                <div class="stat-value">128</div>
                <span class="stat-change positive">+12%</span>
            </div>
            <div class="stat-icon cyan">📄</div>
        </div>
    </div>

    <div class="glass-card glass-card-3d stat-card">
        <div class="stat-card-inner">
            <div class="stat-info">
                <h3>En attente</h3>
                <div class="stat-value">17</div>
                <span class="stat-change negative">-3%</span>
            </div>
            <div class="stat-icon magenta">⏳</div>
        </div>
    </div>

    <div class="glass-card glass-card-3d stat-card">
        <div class="stat-card-inner">
            <div class="stat-info">
                <h3>Contrats validés</h3>
                <div class="stat-value">92</div>
                <span class="stat-change positive">+8%</span>
            </div>
            <div class="stat-icon purple">✅</div>
        </div>
    </div>

    <div class="glass-card glass-card-3d stat-card">
        <div class="stat-card-inner">
            <div class="stat-info">
                <h3>Contrats refusés</h3>
                <div class="stat-value">19</div>
                <span class="stat-change negative">-2%</span>
            </div>
            <div class="stat-icon success">❌</div>
        </div>
    </div>
</section>

                
            </section>

         
            <section class="content-grid" style="grid-template-columns: 1fr;">

    <!-- TABLE CONTRATS -->
    <div class="glass-card table-card" style="grid-column: span 1; margin-bottom: 24px;">
        <div class="card-header">
            <div>
                <h2 class="card-title">Gestion des contrats</h2>
                <p class="card-subtitle">Validation et suivi des demandes de souscription du front office</p>
            </div>
            <div class="card-actions">
                <button class="card-btn">Filtrer</button>
                <button class="card-btn">Export</button>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>N° Contrat</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Prime</th>
                        <th>Franchise</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

<tbody>

<?php foreach ($list as $contrat) { ?>

<tr>
    <td><?php echo $contrat['numero_contrat']; ?></td>
    <td>Client</td>

    <td><?php echo $contrat['type_contrat']; ?></td>

    <td><?php echo $contrat['date_debut']; ?></td>
    <td><?php echo $contrat['date_fin']; ?></td>

    <td><?php echo $contrat['montant_prime']; ?> DT</td>
    <td><?php echo $contrat['franchise']; ?> DT</td>

    <td>
        <span class="status-badge 
        <?php
            if ($contrat['statut'] == 'en attente') echo 'pending';
            elseif ($contrat['statut'] == 'actif') echo 'completed';
            elseif ($contrat['statut'] == 'refuse') echo 'processing';
        ?>">
            <?php echo $contrat['statut']; ?>
        </span>
    </td>

    <td>
        <a href="validerContrat.php?id=<?php echo $contrat['id_contrat']; ?>" class="card-btn success-btn">Valider</a>

        <a href="deleteContrat.php?id=<?php echo $contrat['id_contrat']; ?>" class="card-btn danger-btn">Supprimer</a>
    </td>
</tr>

<?php } ?>

</tbody>
            </table>
        </div>
    </div>

   

</section>
        </main>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <!-- Footer -->
    <footer class="site-footer">
        <p>Copyright © 2026 Your Company. Designed by <a href="https://templatemo.com" target="_blank" rel="nofollow">TemplateMo</a></p>
    </footer>

    <script src="js/templatemo-glass-admin-script.js"></script>
    <!-- TemplateMo 607 Glass Admin -->
</body>
</html>