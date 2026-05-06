<?php
/**
 * view/BackOffice/contrats/liste.php
 * Liste des contrats — BackOffice Protex 2026
 */

if (!defined('BASE_URL')) define('BASE_URL', '/projet_web1/gestion_paiment_offre');
$base = '/projet_web1/gestion_paiment_offre';

function cE($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function cRef($id): string { return 'CTR-2026-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT); }

$total = (int)($stats['total'] ?? 0);
$actifs = (int)($stats['actifs'] ?? 0);
$enAttente = (int)($stats['en_attente'] ?? 0);
$montantActif = (float)($stats['montant_total_actif'] ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrats — Protex Admin</title>
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
        .avatar-cell {
            width: 42px; height: 42px; border-radius: 14px;
            display: grid; place-items: center; font-weight: 800; font-size: 13px; color: #fff;
            background: linear-gradient(135deg, rgba(255,107,26,.95), rgba(255,140,66,.85));
            flex-shrink: 0;
        }
        .client-cell { display: flex; align-items: center; gap: 12px; }
        .client-name { font-weight: 700; color: #fff; line-height: 1.2; }
        .client-email { color: var(--text-secondary); font-size: 12px; margin-top: 2px; }
        .ref-cell { display: inline-flex; align-items: center; gap: 7px; font-size: 12px; color: var(--text-secondary); }
        .amount { font-weight: 800; color: #fff; }

        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 22px; }
        .kpi-card { position: relative; overflow: hidden; border-radius: 24px; background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02)); border: 1px solid rgba(255,255,255,.08); padding: 18px; box-shadow: 0 20px 40px rgba(0,0,0,.14); }
        .kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
        .kpi-icon { width: 48px; height: 48px; border-radius: 16px; display: grid; place-items: center; font-size: 20px; color: #fff; border: 1px solid rgba(255,255,255,.10); }
        .kpi-blue .kpi-icon  { background: linear-gradient(135deg, rgba(0,194,255,.85), rgba(16,85,255,.75)); }
        .kpi-gold .kpi-icon  { background: linear-gradient(135deg, rgba(255,166,0,.9), rgba(255,107,26,.85)); }
        .kpi-green .kpi-icon { background: linear-gradient(135deg, rgba(0,214,143,.9), rgba(0,166,126,.8)); }
        .kpi-purple .kpi-icon { background: linear-gradient(135deg, rgba(137,100,255,.9), rgba(88,80,236,.85)); }
        .kpi-value { font-size: 28px; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 4px; }
        .kpi-label { color: var(--text-secondary); font-size: 13px; font-weight: 600; }
        .kpi-trend { margin-top: 10px; font-size: 12px; color: var(--text-secondary); display: inline-flex; align-items: center; gap: 6px; }

        .page-header-flex { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
        .page-title { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: #fff; }
        .header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        .toolbar-grid { display: grid; grid-template-columns: 1.3fr 1fr auto; gap: 12px; align-items: center; padding: 16px 24px; border-bottom: 1px solid var(--glass-border); }
        .search-box { position: relative; }
        .search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 14px; pointer-events: none; }
        .search-box input { width: 100%; padding: 9px 14px 9px 40px; border-radius: 12px; border: 1px solid var(--glass-border); background: rgba(255,255,255,.05); color: #fff; font-size: 13px; outline: none; transition: .2s; }
        .search-box input:focus { border-color: var(--accent); }
        .filter-select { padding: 9px 14px; border-radius: 12px; border: 1px solid var(--glass-border); background: rgba(255,255,255,.05); color: #fff; font-size: 13px; outline: none; cursor: pointer; }
        .filter-select option { background: var(--navy); color: #fff; }

        .table-wrap { overflow-x: auto; }
        .table-wrap table { width: 100%; border-collapse: collapse; }
        .table-wrap thead th { padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-secondary); border-bottom: 1px solid rgba(255,255,255,.06); }
        .table-wrap tbody tr { border-bottom: 1px solid rgba(255,255,255,.04); transition: background .15s; }
        .table-wrap tbody tr:hover { background: rgba(255,255,255,.03); }
        .table-wrap tbody td { padding: 14px 16px; font-size: 13px; }

        .status-badge-ctr { display: inline-flex; align-items: center; gap: 7px; padding: 7px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .status-actif { background: rgba(0,214,143,.12); border: 1px solid rgba(0,214,143,.24); color: #90f1bc; }
        .status-expire { background: rgba(255,107,26,.12); border: 1px solid rgba(255,107,26,.24); color: #ffd6a0; }
        .status-resilie { background: rgba(220,53,69,.12); border: 1px solid rgba(220,53,69,.24); color: #ff9cab; }
        .status-en_attente { background: rgba(255,193,7,.12); border: 1px solid rgba(255,193,7,.24); color: #ffd66e; }

        .actions { display: flex; gap: 6px; }

        .empty-state { text-align: center; padding: 52px 18px; color: var(--text-secondary); }
        .empty-state i { display: block; font-size: 36px; margin-bottom: 12px; color: rgba(255,255,255,.3); }

        .export-wrapper-ctr { position: relative; }
        .export-menu-ctr { position: absolute; top: calc(100% + 6px); right: 0; min-width: 200px; background: #0e1c33; border: 1px solid rgba(255,255,255,.12); border-radius: 12px; padding: 6px; box-shadow: 0 16px 40px rgba(0,0,0,.5); z-index: 100; opacity: 0; visibility: hidden; transform: translateY(-6px); transition: .2s ease; }
        .export-menu-ctr.show { opacity: 1 !important; visibility: visible !important; transform: translateY(0) !important; }
        .export-menu-ctr button { width: 100%; padding: 10px 12px; border-radius: 8px; border: none; background: transparent; color: #fff; text-align: left; cursor: pointer; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 10px; transition: .15s; }
        .export-menu-ctr button:hover { background: rgba(255,255,255,.06); }

        @media(max-width:1300px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
        @media(max-width:900px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } .toolbar-grid { grid-template-columns: 1fr 1fr; } }
        @media(max-width:640px) { .kpi-grid, .toolbar-grid { grid-template-columns: 1fr; } }
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
                <div class="topbar-title">Suivi des contrats</div>
                <div class="topbar-sub"><?= htmlspecialchars(date('d/m/Y')) ?></div>
            </div>
            <div class="topbar-actions">
                <a href="#" class="topbar-btn" title="Notifications"><i class="bi bi-bell"></i><span class="notif-dot"></span></a>
                <a href="#" class="topbar-btn" title="Aide"><i class="bi bi-question-circle"></i></a>
            </div>
        </div>
        <div class="content">

            <div class="page-header-flex">
                <div>
                    <div class="page-title">Contrats</div>
                    <div class="page-breadcrumb">
                        <i class="bi bi-house"></i>
                        <a href="<?= $base ?>/view/BackOffice/admin.html">Accueil</a>
                        <i class="bi bi-chevron-right" style="font-size:10px"></i>
                        <span>Contrats</span>
                    </div>
                </div>
                <div class="header-actions">
                    <span style="padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.05);color:var(--text-secondary);border:1px solid rgba(255,255,255,.07);font-size:12px;font-weight:700;">
                        <i class="bi bi-file-earmark-check"></i> Gestion contrats
                    </span>
                    <a href="<?= $base ?>/controller/ContratController.php?action=index" class="btn btn-outline">
                        <i class="bi bi-arrow-clockwise"></i> Actualiser
                    </a>
                </div>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card kpi-blue">
                    <div class="kpi-top">
                        <div>
                            <div class="kpi-value"><?= $total ?></div>
                            <div class="kpi-label">Total contrats</div>
                        </div>
                        <div class="kpi-icon"><i class="bi bi-file-earmark-check"></i></div>
                    </div>
                    <div class="kpi-trend"><i class="bi bi-stack"></i> Ensemble des contrats</div>
                </div>

                <div class="kpi-card kpi-green">
                    <div class="kpi-top">
                        <div>
                            <div class="kpi-value"><?= $actifs ?></div>
                            <div class="kpi-label">Actifs</div>
                        </div>
                        <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
                    </div>
                    <div class="kpi-trend"><i class="bi bi-graph-up-arrow"></i> En cours</div>
                </div>

                <div class="kpi-card kpi-gold">
                    <div class="kpi-top">
                        <div>
                            <div class="kpi-value"><?= $enAttente ?></div>
                            <div class="kpi-label">En attente</div>
                        </div>
                        <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
                    </div>
                    <div class="kpi-trend"><i class="bi bi-clock-history"></i> À traiter</div>
                </div>

                <div class="kpi-card kpi-purple">
                    <div class="kpi-top">
                        <div>
                            <div class="kpi-value"><?= number_format($montantActif, 0) ?></div>
                            <div class="kpi-label">Prime active DT</div>
                        </div>
                        <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
                    </div>
                    <div class="kpi-trend"><i class="bi bi-coin"></i> Total primes actives</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="bi bi-table"></i> Liste des contrats</div>
                    <span style="font-size:12px;color:var(--text-secondary);"><i class="bi bi-info-circle"></i> Utilisez les filtres puis le bouton Exporter</span>
                </div>

                <div class="toolbar-grid">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchInput" placeholder="Rechercher par client, référence...">
                    </div>

                    <select class="filter-select" id="filterStatut">
                        <option value="">Tous les statuts</option>
                        <option value="actif" <?= ($_GET['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="en_attente" <?= ($_GET['statut'] ?? '') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="expire" <?= ($_GET['statut'] ?? '') === 'expire' ? 'selected' : '' ?>>Expiré</option>
                        <option value="resilie" <?= ($_GET['statut'] ?? '') === 'resilie' ? 'selected' : '' ?>>Résilé</option>
                    </select>

                    <div style="display:flex;gap:10px;align-items:center;">
                        <div class="export-wrapper-ctr">
                            <button class="btn btn-outline btn-sm" type="button" onclick="toggleExportCtr()" style="display:inline-flex;align-items:center;gap:6px;">
                                <i class="bi bi-download"></i> Exporter <i class="bi bi-chevron-down" style="font-size:10px"></i>
                            </button>
                            <div class="export-menu-ctr" id="exportMenuCtr">
                                <button type="button" onclick="exportCtrData('csv')"><i class="bi bi-filetype-csv" style="color:#86efac"></i> Exporter en CSV</button>
                                <button type="button" onclick="exportCtrData('excel')"><i class="bi bi-file-earmark-excel-fill" style="color:#6ee7b7"></i> Exporter en Excel</button>
                                <button type="button" onclick="exportCtrData('pdf')"><i class="bi bi-file-earmark-pdf-fill" style="color:#fca5a5"></i> Exporter en PDF</button>
                            </div>
                        </div>
                        <a href="<?= $base ?>/controller/DevisController.php?action=index" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left"></i> Devis</a>
                    </div>
                </div>

                <?php if (empty($contrats)): ?>
                    <div class="empty-state">
                        <i class="bi bi-file-earmark-x"></i>
                        <p style="font-size:16px;font-weight:600;margin:0 0 8px;">Aucun contrat trouvé</p>
                        <p style="font-size:13px;margin:0;">Convertissez un devis accepté en contrat pour le voir apparaître ici.</p>
                    </div>
                <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Client</th>
                                <th>Offre</th>
                                <th>Montant</th>
                                <th>Période</th>
                                <th>Statut</th>
                                <th>Date début</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contrats as $c):
                            $clientName = mb_strtolower(trim(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? '')));
                            $searchText = mb_strtolower(trim(
                                ($c['numero_contrat'] ?? '') . ' ' .
                                ($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? '') . ' ' .
                                ($c['nom_offre'] ?? '') . ' ' . ($c['statut_contrat'] ?? '')
                            ), 'UTF-8');
                            $initiales = strtoupper(substr($c['prenom'] ?? 'C', 0, 1) . substr($c['nom'] ?? '', 0, 1));
                            ?>
                            <tr data-search="<?= htmlspecialchars($searchText, ENT_QUOTES, 'UTF-8') ?>" data-reference="<?= cE($c['numero_contrat']) ?>" data-client="<?= htmlspecialchars($clientName) ?>" data-offre="<?= cE($c['nom_offre'] ?? '') ?>" data-montant="<?= (float)($c['prime_contrat'] ?? 0) ?>" data-statut="<?= cE($c['statut_contrat']) ?>" data-date="<?= cE($c['date_debut_contrat']) ?>" data-periodicite="<?= cE($c['type_contrat']) ?>">
                                <td>
                                    <span class="ref-cell">
                                        <i class="bi bi-upc-scan"></i>
                                        <?= cE($c['numero_contrat']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="client-cell">
                                        <div class="avatar-cell"><?= $initiales ?></div>
                                        <div>
                                            <div class="client-name"><?= cE(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? '')) ?></div>
                                            <div class="client-email"><?= cE($c['email'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:700;color:#fff;font-size:13px;"><?= cE($c['nom_offre'] ?? '—') ?></div>
                                </td>
                                <td><span class="amount"><?= number_format((float)($c['prime_contrat'] ?? 0), 3, '.', ' ') ?> DT</span></td>
                                <td><?= cE(ucfirst($c['type_contrat'] ?? '')) ?></td>
                                <td>
                                    <span class="status-badge-ctr status-<?= cE($c['statut_contrat']) ?>">
                                        <i class="bi bi-circle-fill" style="font-size:6px;"></i>
                                        <?= ucfirst(str_replace('_', ' ', $c['statut_contrat'])) ?>
                                    </span>
                                </td>
                                <td style="color:var(--text-secondary);font-size:13px;"><?= date('d/m/Y', strtotime($c['date_debut_contrat'])) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="<?= $base ?>/controller/ContratController.php?action=details&id=<?= (int)$c['id_contrat'] ?>" class="btn btn-outline btn-sm" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= $base ?>/controller/ContratController.php?action=generer_pdf&id=<?= (int)$c['id_contrat'] ?>" target="_blank" class="btn btn-outline btn-sm" title="Générer le contrat PDF" style="border-color:rgba(0,214,143,.3);color:#90f1bc;">
                                            <i class="bi bi-file-earmark-pdf-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
    const val = this.value;
    document.querySelectorAll('tbody tr').forEach(r => {
        r.style.display = r.dataset.search && r.dataset.search.includes(val.toLowerCase()) ? '' : 'none';
    });
});
document.getElementById('filterStatut')?.addEventListener('change', function() {
    const s = this.value;
    const url = new URL(window.location.href);
    if (s) url.searchParams.set('statut', s); else url.searchParams.delete('statut');
    window.location.href = url.toString();
});

function toggleExportCtr() { document.getElementById('exportMenuCtr')?.classList.toggle('show'); }
document.addEventListener('click', function(e) {
    const wrap = document.querySelector('.export-wrapper-ctr');
    if (wrap && !wrap.contains(e.target)) document.getElementById('exportMenuCtr')?.classList.remove('show');
});

function getVisibleCtrData() {
    const data = [];
    document.querySelectorAll('tbody tr').forEach(row => {
        if (row.style.display === 'none') return;
        data.push({
            reference: row.dataset.reference || '',
            client: row.dataset.client || '',
            offre: row.dataset.offre || '',
            montant: row.dataset.montant || '0',
            periodicite: row.dataset.periodicite || '',
            statut: row.dataset.statut || '',
            date: row.dataset.date || '',
        });
    });
    return data;
}

function exportCtrData(type) {
    document.getElementById('exportMenuCtr')?.classList.remove('show');
    const data = getVisibleCtrData();
    if (!data.length) return;
    const ts = Math.floor(Date.now() / 1000);
    if (type === 'csv') exportCtrCSV(data, ts);
    else if (type === 'excel') exportCtrExcel(data, ts);
    else if (type === 'pdf') exportCtrPDF(data, ts);
}

function exportCtrCSV(data, ts) {
    let csv = 'Reference,Client,Offre,Montant,Periodicite,Statut,Date Debut\n';
    data.forEach(d => {
        csv += `"${d.reference}","${d.client}","${d.offre}","${parseFloat(d.montant).toFixed(3)}","${ucfirst(d.periodicite)}","${ucfirst(d.statut)}","${formatDate(d.date)}"\n`;
    });
    downloadCtrFile(csv, `contrats_protex_${ts}.csv`, 'text/csv');
}

function exportCtrExcel(data, ts) {
    let html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"></head><body><table>';
    html += '<tr><th>Référence</th><th>Client</th><th>Offre</th><th>Montant</th><th>Périodicité</th><th>Statut</th><th>Date début</th></tr>';
    data.forEach(d => {
        html += `<tr><td>${d.reference}</td><td>${d.client}</td><td>${d.offre}</td><td>${parseFloat(d.montant).toFixed(3)} DT</td><td>${ucfirst(d.periodicite)}</td><td>${ucfirst(d.statut)}</td><td>${formatDate(d.date)}</td></tr>`;
    });
    html += '</table></body></html>';
    downloadCtrFile(html, `contrats_protex_${ts}.xls`, 'application/vnd.ms-excel');
}

function exportCtrPDF(data, ts) {
    let rowsHtml = '';
    data.forEach(d => {
        rowsHtml += `<tr>
            <td>${d.reference}</td>
            <td>${d.client}</td>
            <td>${d.offre}</td>
            <td style="text-align:right">${parseFloat(d.montant).toFixed(3)} DT</td>
            <td>${ucfirst(d.periodicite)}</td>
            <td>${ucfirst(d.statut)}</td>
            <td>${formatDate(d.date)}</td>
        </tr>`;
    });
    const content = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Export Contrats</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; color: #000; }
.print-header { border-bottom: 3px solid #FF6B1A; padding-bottom: 12px; margin-bottom: 18px; }
.print-brand { font-size: 24px; font-weight: 800; color: #1A3A7A; margin: 0; }
.print-brand span { color: #FF6B1A; }
.print-info { color: #666; font-size: 12px; margin-top: 4px; }
table { width: 100%; border-collapse: collapse; font-size: 11px; }
th, td { border: 1px solid #999; padding: 7px 9px; }
th { background: #FF6B1A; color: #fff; text-align: left; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
@media print { body { padding: 0; } }
</style></head><body>
<div class="print-header">
    <h1 class="print-brand">PROTEX <span>Assurance</span></h1>
    <p class="print-info">Rapport des contrats — Export du ${new Date().toLocaleString('fr-FR')} — ${data.length} contrat(s)</p>
</div>
<table><thead><tr>
    <th>Référence</th><th>Client</th><th>Offre</th><th>Montant</th>
    <th>Périodicité</th><th>Statut</th><th>Date début</th>
</tr></thead><tbody>${rowsHtml}</tbody></table>
<p style="margin-top:20px;font-size:10px;color:#888;text-align:center;">
    Document généré automatiquement par Protex Admin — ${new Date().toLocaleDateString('fr-FR')}
</p>
<script>window.onload = function() { window.print(); }<\/script>
</body></html>`;
    const win = window.open('', '_blank');
    if (win) { win.document.write(content); win.document.close(); }
}

function downloadCtrFile(content, filename, mime) {
    const blob = new Blob(["\uFEFF" + content], { type: mime + ';charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = filename;
    document.body.appendChild(a); a.click();
    document.body.removeChild(a); URL.revokeObjectURL(url);
}

function ucfirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
function formatDate(d) { if (!d) return '—'; try { return new Date(d).toLocaleDateString('fr-FR'); } catch { return d; } }
</script>
</body>
</html>
