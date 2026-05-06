<?php
/**
 * view/BackOffice/contrats/detail.php
 * Détail d'un contrat — BackOffice Protex 2026
 */

if (!defined('BASE_URL')) define('BASE_URL', '/projet_web1/gestion_paiment_offre');
$base = '/projet_web1/gestion_paiment_offre';

function cE($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }

$ref = $contrat['numero_contrat'] ?? '';
$idDevis = $contrat['id_devis'] ?? 0;
$statut = $contrat['statut_contrat'] ?? '';
$statutClasses = ['actif' => 'status-actif', 'expire' => 'status-expire', 'resilie' => 'status-resilie', 'en_attente' => 'status-en_attente'];
$statutLabels  = ['actif' => 'Actif', 'expire' => 'Expiré', 'resilie' => 'Résilé', 'en_attente' => 'En attente'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat <?= cE($ref) ?> — Protex Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>/view/BackOffice/assets/css/variables.css">
    <link rel="stylesheet" href="<?= $base ?>/view/BackOffice/assets/css/base.css">
    <link rel="stylesheet" href="<?= $base ?>/view/BackOffice/assets/css/layout.css">
    <link rel="stylesheet" href="<?= $base ?>/view/BackOffice/assets/css/admin-users.css">
    <style>
        .detail-hero { position: relative; overflow: hidden; padding: 30px; border-radius: 26px; background: radial-gradient(circle at 80% 20%, rgba(0,214,143,.14), transparent 30%), radial-gradient(circle at 20% 80%, rgba(0,194,255,.08), transparent 30%), linear-gradient(135deg, rgba(255,255,255,.06), rgba(255,255,255,.02)); border: 1px solid rgba(255,255,255,.08); margin-bottom: 24px; display: flex; align-items: flex-start; gap: 24px; flex-wrap: wrap; }
        .detail-hero::before { content: ""; position: absolute; width: 200px; height: 200px; right: -50px; top: -50px; border-radius: 50%; background: rgba(0,214,143,.06); filter: blur(60px); }
        .hero-avatar { width: 64px; height: 64px; border-radius: 20px; background: linear-gradient(135deg, rgba(0,214,143,.9), rgba(0,166,126,.8)); display: grid; place-items: center; font-size: 22px; font-weight: 800; color: #fff; flex-shrink: 0; position: relative; z-index: 1; }
        .hero-info { flex: 1; min-width: 200px; position: relative; z-index: 1; }
        .hero-ref { font-size: 13px; color: var(--text-secondary); margin-bottom: 4px; font-family: monospace; }
        .hero-client { font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 8px; }
        .hero-meta { display: flex; gap: 16px; flex-wrap: wrap; }
        .hero-meta-item { font-size: 12px; color: var(--text-secondary); display: flex; align-items: center; gap: 5px; }
        .hero-meta-item i { color: var(--accent); }
        .hero-actions { display: flex; gap: 10px; flex-wrap: wrap; position: relative; z-index: 1; }

        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        @media(max-width:900px) { .detail-grid { grid-template-columns: 1fr; } }

        .detail-card { border-radius: 22px; padding: 24px; border: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.03); }
        .detail-card-title { font-size: 15px; font-weight: 800; color: #fff; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; padding-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,.06); }
        .detail-card-title i { color: var(--accent); font-size: 17px; }
        .detail-pairs { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .detail-pair { padding: 14px; border-radius: 14px; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.06); }
        .detail-pair-label { color: var(--text-secondary); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 7px; }
        .detail-pair-value { color: #fff; font-size: 14px; font-weight: 700; }

        .amount-display { text-align: center; padding: 24px; border-radius: 16px; background: rgba(25,135,84,.08); border: 1px solid rgba(25,135,84,.2); }
        .amount-label { color: var(--text-secondary); font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
        .amount-value { font-size: 36px; font-weight: 900; color: #90f1bc; }
        .amount-sub { color: var(--text-secondary); font-size: 12px; margin-top: 4px; }

        .status-badge-d { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 700; }
        .status-actif { background: rgba(0,214,143,.12); border: 1px solid rgba(0,214,143,.24); color: #90f1bc; }
        .status-expire { background: rgba(255,107,26,.12); border: 1px solid rgba(255,107,26,.24); color: #ffd6a0; }
        .status-resilie { background: rgba(220,53,69,.12); border: 1px solid rgba(220,53,69,.24); color: #ff9cab; }
        .status-en_attente { background: rgba(255,193,7,.12); border: 1px solid rgba(255,193,7,.24); color: #ffd66e; }
    </style>
</head>
<body>
<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
<div class="layout">
    <?php include __DIR__ . '/../assets/includes/sidebar.php'; ?>
    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Détail du contrat</div>
                <div class="topbar-sub"><?= cE($ref) ?> — <?= htmlspecialchars(date('d/m/Y')) ?></div>
            </div>
            <div class="topbar-actions">
                <a href="<?= $base ?>/controller/ContratController.php" class="topbar-btn" title="Retour"><i class="bi bi-arrow-left"></i></a>
            </div>
        </div>
        <div class="content">

            <div class="detail-hero">
                <div class="hero-avatar">
                    <i class="bi bi-file-earmark-check"></i>
                </div>
                <div class="hero-info">
                    <div class="hero-ref"><i class="bi bi-upc-scan"></i> <?= cE($ref) ?></div>
                    <div class="hero-client"><?= cE(($contrat['prenom'] ?? '') . ' ' . ($contrat['nom'] ?? '')) ?></div>
                    <div class="hero-meta">
                        <div class="hero-meta-item"><i class="bi bi-envelope"></i> <?= cE($contrat['email'] ?? '—') ?></div>
                        <div class="hero-meta-item"><i class="bi bi-telephone"></i> <?= cE($contrat['telephone'] ?? '—') ?></div>
                        <div class="hero-meta-item"><i class="bi bi-calendar3"></i> <?= date('d/m/Y', strtotime($contrat['date_debut_contrat'] ?? 'now')) ?></div>
                    </div>
                </div>
                <div class="hero-actions">
                    <a href="<?= $base ?>/controller/ContratController.php" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left"></i> Liste</a>
                    <a href="<?= $base ?>/controller/ContratController.php?action=generer_pdf&id=<?= (int)$contrat['id_contrat'] ?>" target="_blank" class="btn btn-primary btn-sm"><i class="bi bi-file-earmark-pdf-fill"></i> Générer le contrat PDF</a>
                    <?php if ($idDevis): ?>
                    <a href="<?= $base ?>/controller/DevisController.php?action=details&id=<?= (int)$idDevis ?>" class="btn btn-outline btn-sm"><i class="bi bi-file-earmark-text"></i> Voir devis</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-card">
                    <div class="detail-card-title"><i class="bi bi-file-earmark-check"></i> Informations du contrat</div>
                    <div class="detail-pairs">
                        <div class="detail-pair">
                            <div class="detail-pair-label">Statut</div>
                            <div class="detail-pair-value">
                                <span class="status-badge-d <?= $statutClasses[$statut] ?? '' ?>">
                                    <i class="bi bi-circle-fill" style="font-size:6px;"></i>
                                    <?= $statutLabels[$statut] ?? cE($statut) ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-pair">
                            <div class="detail-pair-label">Périodicité</div>
                            <div class="detail-pair-value"><?= ucfirst($contrat['type_contrat'] ?? '') ?></div>
                        </div>
                        <div class="detail-pair">
                            <div class="detail-pair-label">Date début</div>
                            <div class="detail-pair-value"><?= date('d/m/Y', strtotime($contrat['date_debut_contrat'] ?? 'now')) ?></div>
                        </div>
                        <div class="detail-pair">
                            <div class="detail-pair-label">Date fin</div>
                            <div class="detail-pair-value"><?= date('d/m/Y', strtotime($contrat['date_fin_contrat'] ?? 'now')) ?></div>
                        </div>
                        <div class="detail-pair">
                            <div class="detail-pair-label">Franchise</div>
                            <div class="detail-pair-value"><?= number_format((float)($contrat['franchise_contrat'] ?? 0), 3, '.', ' ') ?> DT</div>
                        </div>
                        <div class="detail-pair">
                            <div class="detail-pair-label">Type assurance</div>
                            <div class="detail-pair-value"><?= ucfirst(cE($contrat['type_assurance'] ?? '—')) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($contrat['notes'])): ?>
                    <div style="margin-top:16px;padding:14px;border-radius:12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);">
                        <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:6px;">Notes</div>
                        <div style="font-size:14px;color:#fff;line-height:1.6;"><?= nl2br(cE($contrat['notes'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="detail-card">
                    <div class="detail-card-title"><i class="bi bi-person"></i> Client</div>
                    <div class="detail-pairs">
                        <div class="detail-pair">
                            <div class="detail-pair-label">Nom complet</div>
                            <div class="detail-pair-value"><?= cE(($contrat['prenom'] ?? '') . ' ' . ($contrat['nom'] ?? '')) ?></div>
                        </div>
                        <div class="detail-pair">
                            <div class="detail-pair-label">Email</div>
                            <div class="detail-pair-value"><?= cE($contrat['email'] ?? '—') ?></div>
                        </div>
                        <div class="detail-pair">
                            <div class="detail-pair-label">Téléphone</div>
                            <div class="detail-pair-value"><?= cE($contrat['telephone'] ?? '—') ?></div>
                        </div>
                    </div>

                    <div class="amount-display" style="margin-top:18px;">
                        <div class="amount-label">Prime du contrat</div>
                        <div class="amount-value"><?= number_format((float)($contrat['prime_contrat'] ?? 0), 3, '.', ' ') ?> DT</div>
                        <div class="amount-sub">par <?= ($contrat['type_contrat'] ?? 'mensuel') === 'annuel' ? 'an' : 'mois' ?></div>
                    </div>
                </div>
            </div>

            <div class="detail-card" style="margin-bottom:20px;">
                <div class="detail-card-title"><i class="bi bi-tag"></i> Offre</div>
                <div class="detail-pairs">
                    <div class="detail-pair">
                        <div class="detail-pair-label">Nom de l'offre</div>
                        <div class="detail-pair-value"><?= cE($contrat['nom_offre'] ?? '—') ?></div>
                    </div>
                    <div class="detail-pair">
                        <div class="detail-pair-label">Couverture</div>
                        <div class="detail-pair-value"><?= cE($contrat['couverture'] ?? '—') ?></div>
                    </div>
                </div>
            </div>

            <?php if ($statut === 'actif'): ?>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="btn btn-danger btn-sm" onclick="changeStatut('resilie')"><i class="bi bi-x-circle"></i> Résilier</button>
                <button class="btn btn-outline btn-sm" onclick="changeStatut('expire')" style="border-color:rgba(255,193,7,.3);color:#ffd66e;"><i class="bi bi-clock-history"></i> Marquer expiré</button>
            </div>
            <?php elseif ($statut === 'en_attente'): ?>
            <button class="btn btn-outline btn-sm" onclick="changeStatut('actif')" style="border-color:rgba(0,214,143,.3);color:#90f1bc;"><i class="bi bi-check-circle"></i> Activer le contrat</button>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
function changeStatut(statut) {
    if (!confirm('Changer le statut du contrat ?')) return;
    fetch('<?= $base ?>/controller/ContratController.php?action=changer_statut', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_contrat=<?= (int)$contrat['id_contrat'] ?>&statut=' + statut
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert('Erreur: ' + (d.error || 'inconnue'));
    });
}
</script>
</body>
</html>
