<?php
/**
 * view/BackOffice/dashboard.php
 * Dashboard unifié BackOffice — Protex 2026
 */

if (!defined('BASE_URL')) define('BASE_URL', '/projet_web1/gestion_paiment_offre');
$base = '/projet_web1/gestion_paiment_offre';

function dE($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function dMoney($v): string { return number_format((float)$v, 3, '.', ' ') . ' DT'; }
function dDate($d): string { if (!$d) return '—'; try { return (new DateTime($d))->format('d/m/Y H:i'); } catch (Exception $e) { return dE($d); } }

$typeIcons = ['auto' => 'car-front', 'habitation' => 'house-door', 'sante' => 'heart-pulse', 'vie' => 'shield-heart'];
$typeColors = ['auto' => '#00c2ff', 'habitation' => '#ff9900', 'sante' => '#00d68f', 'vie' => '#a855f7'];
$statusBadges = [
    'en_attente' => '<span class="badge-warn"><i class="bi bi-hourglass-split"></i> En attente</span>',
    'valide'     => '<span class="badge-ok"><i class="bi bi-check-circle"></i> Validé</span>',
    'refuse'     => '<span class="badge-err"><i class="bi bi-x-circle"></i> Refusé</span>',
    'accepte'    => '<span class="badge-ok"><i class="bi bi-check-circle"></i> Accepté</span>',
    'en_cours'   => '<span class="badge-info"><i class="bi bi-arrow-repeat"></i> En cours</span>',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Protex Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>/view/BackOffice/assets/css/variables.css">
    <link rel="stylesheet" href="<?= $base ?>/view/BackOffice/assets/css/base.css">
    <link rel="stylesheet" href="<?= $base ?>/view/BackOffice/assets/css/layout.css">
    <link rel="stylesheet" href="<?= $base ?>/view/BackOffice/assets/css/admin-users.css">
    <style>
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .kpi-card {
            padding: 22px 24px; border-radius: 20px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.03);
            position: relative; overflow: hidden;
        }
        .kpi-card::before {
            content: ''; position: absolute; top: -20px; right: -20px;
            width: 80px; height: 80px; border-radius: 50%; opacity: .12;
        }
        .kpi-card.orange::before { background: var(--accent); }
        .kpi-card.green::before { background: #198754; }
        .kpi-card.blue::before { background: #0dcaf0; }
        .kpi-card.purple::before { background: #a855f7; }
        .kpi-icon { font-size: 24px; margin-bottom: 10px; }
        .kpi-value { font-size: 32px; font-weight: 900; color: #fff; margin-bottom: 4px; }
        .kpi-label { font-size: 12px; color: var(--text-secondary); font-weight: 600; text-transform: uppercase; letter-spacing: .08em; }
        .kpi-sub { font-size: 12px; color: var(--text-secondary); margin-top: 6px; }
        .kpi-sub strong { color: #fff; }

        .dash-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-bottom: 24px; }
        @media (max-width: 1100px) { .dash-grid { grid-template-columns: 1fr; } }

        .dash-card {
            border-radius: 22px; padding: 24px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.03);
        }
        .dash-card-head {
            font-size: 16px; font-weight: 800; color: #fff; margin-bottom: 18px;
            display: flex; align-items: center; justify-content: space-between;
            padding-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .dash-card-head i { color: var(--accent); margin-right: 8px; }

        .dash-link { font-size: 12px; color: var(--accent); text-decoration: none; font-weight: 700; }
        .dash-link:hover { text-decoration: underline; }

        .dash-table { width: 100%; border-collapse: collapse; }
        .dash-table th { text-align: left; padding: 10px 12px; font-size: 11px; color: var(--text-secondary); font-weight: 700; text-transform: uppercase; letter-spacing: .06em; border-bottom: 1px solid rgba(255,255,255,.06); }
        .dash-table td { padding: 12px; font-size: 13px; color: #fff; border-bottom: 1px solid rgba(255,255,255,.04); }
        .dash-table tr:last-child td { border-bottom: none; }
        .dash-table a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .dash-table a:hover { text-decoration: underline; }

        .badge-warn { padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: rgba(255,193,7,.12); color: #ffd66e; }
        .badge-ok   { padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: rgba(25,135,84,.12); color: #90f1bc; }
        .badge-err  { padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: rgba(220,53,69,.12); color: #ff9cab; }
        .badge-info { padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: rgba(13,202,240,.12); color: #8eeaff; }

        .perf-row { display: flex; align-items: center; gap: 14px; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,.06); }
        .perf-row:last-child { border-bottom: none; }
        .perf-bar-bg { flex: 1; height: 8px; border-radius: 4px; background: rgba(255,255,255,.06); overflow: hidden; }
        .perf-bar { height: 100%; border-radius: 4px; background: var(--accent); }
        .perf-name { font-size: 13px; color: #fff; font-weight: 700; min-width: 130px; }
        .perf-num  { font-size: 13px; color: var(--text-secondary); min-width: 50px; text-align: right; }

        .conversion-box {
            padding: 20px; border-radius: 18px;
            background: rgba(255,107,26,.06); border: 1px solid rgba(255,107,26,.18);
            text-align: center; margin-bottom: 18px;
        }
        .conversion-val { font-size: 40px; font-weight: 900; color: var(--accent); }
        .conversion-label { font-size: 12px; color: var(--text-secondary); margin-top: 4px; }

        .diag-card { border-radius: 22px; border: 1px solid rgba(255,255,255,.08); background: linear-gradient(135deg, rgba(255,255,255,.04), rgba(255,255,255,.02)); padding: 24px; margin-bottom: 22px; }
        .diag-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .diag-title { font-size: 16px; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 10px; }
        .diag-title i { color: var(--accent); font-size: 20px; }
        .score-circle { width: 72px; height: 72px; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 3px solid; }
        .score-num { font-size: 24px; font-weight: 900; color: #fff; line-height: 1; }
        .score-lbl { font-size: 8px; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,.6); margin-top: 2px; }

        .diag-alert { display: flex; gap: 14px; padding: 14px 16px; border-radius: 14px; margin-bottom: 10px; align-items: flex-start; }
        .diag-alert:last-child { margin-bottom: 0; }
        .diag-alert-danger { background: rgba(220,53,69,.08); border: 1px solid rgba(220,53,69,.15); }
        .diag-alert-warning { background: rgba(255,193,7,.08); border: 1px solid rgba(255,193,7,.15); }
        .diag-alert-info { background: rgba(13,202,240,.08); border: 1px solid rgba(13,202,240,.15); }
        .diag-alert-success { background: rgba(0,214,143,.08); border: 1px solid rgba(0,214,143,.15); }
        .diag-alert-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .diag-alert-danger .diag-alert-icon { background: rgba(220,53,69,.15); color: #ff9cab; }
        .diag-alert-warning .diag-alert-icon { background: rgba(255,193,7,.15); color: #ffd66e; }
        .diag-alert-info .diag-alert-icon { background: rgba(13,202,240,.15); color: #8eeaff; }
        .diag-alert-success .diag-alert-icon { background: rgba(0,214,143,.15); color: #90f1bc; }
        .diag-alert-body { flex: 1; }
        .diag-alert-title { font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 2px; }
        .diag-alert-msg { font-size: 12px; color: var(--text-secondary); line-height: 1.5; }

        .diag-rec { padding: 12px 16px; border-radius: 12px; background: rgba(255,255,255,.03); border-left: 3px solid var(--accent); margin-bottom: 8px; font-size: 13px; color: rgba(255,255,255,.85); line-height: 1.6; }
        .diag-rec:last-child { margin-bottom: 0; }
        .diag-rec i { color: var(--accent); margin-right: 8px; }

        .diag-trend { display: flex; gap: 10px; align-items: center; padding: 10px 14px; border-radius: 10px; background: rgba(255,255,255,.03); margin-bottom: 8px; }
        .diag-trend:last-child { margin-bottom: 0; }
        .diag-trend i { font-size: 16px; width: 28px; text-align: center; }
        .diag-trend-body { flex: 1; }
        .diag-trend-title { font-size: 12px; font-weight: 700; color: #fff; }
        .diag-trend-msg { font-size: 11px; color: var(--text-secondary); }

        @keyframes diagSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="layout">
    <?php include __DIR__ . '/assets/includes/sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <h1 class="topbar-title">📊 Tableau de bord</h1>
            <div style="font-size:13px;color:var(--text-secondary);"><?= date('d M Y') ?></div>
        </div>

        <div class="content">
            <!-- ═══ KPI CARDS ═══ -->
            <div class="kpi-grid">
                <div class="kpi-card orange">
                    <div class="kpi-icon" style="color:var(--accent);">📋</div>
                    <div class="kpi-value"><?= $kpi['devis_total'] ?></div>
                    <div class="kpi-label">Total devis</div>
                    <div class="kpi-sub"><strong><?= $kpi['devis_en_attente'] ?></strong> en attente</div>
                </div>
                <div class="kpi-card green">
                    <div class="kpi-icon" style="color:#198754;">✅</div>
                    <div class="kpi-value"><?= $kpi['devis_acceptes'] ?></div>
                    <div class="kpi-label">Devis acceptés</div>
                    <div class="kpi-sub">Taux <strong><?= $kpi['taux_acceptation'] ?>%</strong></div>
                </div>
                <div class="kpi-card blue">
                    <div class="kpi-icon" style="color:#0dcaf0;">💳</div>
                    <div class="kpi-value"><?= $kpi['paiements_total'] ?></div>
                    <div class="kpi-label">Paiements</div>
                    <div class="kpi-sub"><strong><?= $kpi['paiements_en_attente'] ?></strong> en attente</div>
                </div>
                <div class="kpi-card purple">
                    <div class="kpi-icon" style="color:#a855f7;">💰</div>
                    <div class="kpi-value"><?= dMoney($kpi['revenus']) ?></div>
                    <div class="kpi-label">Revenus validés</div>
                    <div class="kpi-sub"><strong><?= $kpi['paiements_valides'] ?></strong> paiements validés</div>
                </div>
            </div>

            <!-- ═══ AI DIAGNOSTIC ═══ -->
            <div class="dash-card" style="margin-bottom:22px;">
                <div class="dash-card-head">
                    <span><i class="bi bi-robot"></i> Diagnostic IA</span>
                    <button id="diagBtn" onclick="runDiagnostic()" style="padding:8px 20px;border-radius:12px;border:1px solid rgba(0,180,216,.4);background:linear-gradient(135deg,rgba(0,180,216,.2),rgba(0,180,216,.05));color:var(--accent);font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                        <i class="bi bi-play-circle"></i>
                        Analyser
                    </button>
                </div>
                <div id="diagLoading" style="display:none;text-align:center;padding:40px;">
                    <div style="font-size:32px;animation:diagSpin 1s linear infinite;">⚙️</div>
                    <div style="color:var(--text-secondary);font-size:13px;margin-top:12px;">Analyse en cours...</div>
                </div>
                <div id="diagResult"></div>
            </div>

            <!-- ═══ CONVERSION ═══ -->
            <div class="dash-grid">
                <div>
                    <div class="dash-card" style="margin-bottom:20px;">
                        <div class="dash-card-head">
                            <span><i class="bi bi-funnel"></i> Conversion Devis → Contrat</span>
                        </div>
                        <div class="conversion-box">
                            <div class="conversion-val"><?= $kpi['taux_conversion'] ?>%</div>
                            <div class="conversion-label">des devis acceptés convertis en contrat</div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center;">
                            <div>
                                <div style="font-size:24px;font-weight:900;color:#fff;"><?= $kpi['devis_acceptes'] ?></div>
                                <div style="font-size:11px;color:var(--text-secondary);">Devis acceptés</div>
                            </div>
                            <div>
                                <div style="font-size:24px;font-weight:900;color:#90f1bc;"><?= $kpi['devis_convertis'] ?></div>
                                <div style="font-size:11px;color:var(--text-secondary);">Convertis</div>
                            </div>
            <?php if ($kpi['devis_sans_paiement'] > 0): ?>
                            <div>
                                <div style="font-size:24px;font-weight:900;color:#ffd66e;"><?= $kpi['devis_sans_paiement'] ?></div>
                                <div style="font-size:11px;color:var(--text-secondary);">Sans contrat</div>
                            </div>
                            <?php else: ?>
                            <div>
                                <div style="font-size:24px;font-weight:900;color:#90f1bc;">0</div>
                                <div style="font-size:11px;color:var(--text-secondary);">Tout lié ✓</div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="dash-card-head">
                            <span><i class="bi bi-clock-history"></i> Derniers devis</span>
                            <a href="<?= $base ?>/controller/DevisController.php" class="dash-link">Voir tout →</a>
                        </div>
                        <table class="dash-table">
                            <thead><tr><th>Client</th><th>Offre</th><th>Statut</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentDevis as $d): ?>
                                <tr>
                                    <td><?= dE(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? '')) ?></td>
                                    <td><?= dE($d['nom_offre'] ?? '—') ?></td>
                                    <td><?= $statusBadges[$d['statut']] ?? dE($d['statut']) ?></td>
                                    <td style="color:var(--text-secondary);font-size:12px;"><?= dDate($d['date_demande']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <div class="dash-card" style="margin-bottom:20px;">
                        <div class="dash-card-head">
                            <span><i class="bi bi-receipt"></i> Derniers paiements</span>
                            <a href="<?= $base ?>/controller/PaiementController.php" class="dash-link">Voir tout →</a>
                        </div>
                        <table class="dash-table">
                            <thead><tr><th>Ref.</th><th>Client</th><th>Montant</th><th>Statut</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentPaiements as $p): ?>
                                <tr>
                                    <td><a href="<?= $base ?>/controller/PaiementController.php?action=detail&id=<?= (int)$p['id_paiement'] ?>"><?= dE($p['reference']) ?></a></td>
                                    <td><?= dE(($p['client_prenom'] ?? '') . ' ' . ($p['client_nom'] ?? '—')) ?></td>
                                    <td style="font-weight:700;"><?= dMoney($p['montant']) ?></td>
                                    <td><?= $statusBadges[$p['statut']] ?? dE($p['statut']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="dash-card">
                        <div class="dash-card-head">
                            <span><i class="bi bi-bar-chart"></i> Performance par offre</span>
                            <a href="<?= $base ?>/controller/OffreController.php" class="dash-link">Gérer →</a>
                        </div>
                        <?php
                        $maxRevenus = 0;
                        foreach ($offresPerf as $o) { $r = (float)($o['revenus'] ?? 0); if ($r > $maxRevenus) $maxRevenus = $r; }
                        ?>
                        <?php foreach ($offresPerf as $o): ?>
                            <?php $rev = (float)($o['revenus'] ?? 0); $pct = $maxRevenus > 0 ? ($rev / $maxRevenus) * 100 : 0; $type = $o['type_offre'] ?? 'auto'; $color = $typeColors[$type] ?? 'var(--accent)'; ?>
                            <div class="perf-row">
                                <div class="perf-name" style="color:<?= $color ?>;">
                                    <i class="bi bi-<?= $typeIcons[$type] ?? 'shield' ?>"></i>
                                    <?= dE($o['nom_offre']) ?>
                                </div>
                                <div class="perf-bar-bg"><div class="perf-bar" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div></div>
                                <div class="perf-num"><?= dMoney($rev) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="<?= $base ?>/view/BackOffice/assets/js/main.js"></script>
<script>
function runDiagnostic() {
    const btn = document.getElementById('diagBtn');
    const loading = document.getElementById('diagLoading');
    const result = document.getElementById('diagResult');

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Analyse...';
    loading.style.display = 'block';
    result.innerHTML = '';

    fetch('DashboardController.php?action=diagnostic')
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                result.innerHTML = '<div style="text-align:center;padding:20px;color:#ff9cab;"><i class="bi bi-exclamation-triangle"></i> ' + data.error + '</div>';
                return;
            }
            renderDiagnostic(data);
        })
        .catch(err => {
            result.innerHTML = '<div style="text-align:center;padding:20px;color:#ff9cab;"><i class="bi bi-exclamation-triangle"></i> Erreur: ' + err.message + '</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Relancer';
            loading.style.display = 'none';
        });
}

function renderDiagnostic(data) {
    const result = document.getElementById('diagResult');
    const diagTypes = {
        warning: 'diag-alert-warning',
        danger: 'diag-alert-danger',
        info: 'diag-alert-info',
        success: 'diag-alert-success'
    };

    let html = '';

    // Score header
    html += '<div class="diag-header">';
    html += '    <div class="diag-title"><i class="bi bi-robot"></i> Résultat de l\'analyse</div>';
    html += '    <div class="score-circle" style="border-color:' + data.score_color + ';">';
    html += '        <div class="score-num" style="color:' + data.score_color + ';">' + data.score + '</div>';
    html += '        <div class="score-lbl">' + data.score_label + '</div>';
    html += '    </div>';
    html += '</div>';

    // Trends
    if (data.trends && data.trends.length > 0) {
        html += '<div style="margin-top:18px;margin-bottom:18px;">';
        html += '    <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);letter-spacing:.08em;margin-bottom:10px;">';
        html += '        <i class="bi bi-graph-up" style="color:var(--accent);margin-right:4px;"></i> Tendances détectées';
        html += '    </div>';
        data.trends.forEach(function(t) {
            var icoColor = t.type === 'success' ? '#90f1bc' : (t.type === 'warning' ? '#ffd66e' : '#8eeaff');
            html += '    <div class="diag-trend">';
            html += '        <i class="bi bi-' + t.icon + '" style="color:' + icoColor + ';"></i>';
            html += '        <div class="diag-trend-body">';
            html += '            <div class="diag-trend-title">' + escHtml(t.title) + '</div>';
            html += '            <div class="diag-trend-msg">' + escHtml(t.message) + '</div>';
            html += '        </div>';
            html += '    </div>';
        });
        html += '</div>';
    }

    // Alerts
    if (data.alerts && data.alerts.length > 0) {
        html += '<div style="margin-bottom:18px;">';
        html += '    <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);letter-spacing:.08em;margin-bottom:10px;">';
        html += '        <i class="bi bi-exclamation-circle" style="color:#ff9cab;margin-right:4px;"></i> Alertes (' + data.alerts.length + ')';
        html += '    </div>';
        data.alerts.forEach(function(a) {
            var cls = diagTypes[a.type] || 'diag-alert-info';
            html += '    <div class="diag-alert ' + cls + '">';
            html += '        <div class="diag-alert-icon"><i class="bi bi-' + a.icon + '"></i></div>';
            html += '        <div class="diag-alert-body">';
            html += '            <div class="diag-alert-title">' + escHtml(a.title) + '</div>';
            html += '            <div class="diag-alert-msg">' + escHtml(a.message) + '</div>';
            html += '        </div>';
            html += '    </div>';
        });
        html += '</div>';
    }

    // Recommendations
    if (data.recommendations && data.recommendations.length > 0) {
        html += '<div>';
        html += '    <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);letter-spacing:.08em;margin-bottom:10px;">';
        html += '        <i class="bi bi-lightbulb" style="color:#ffd66e;margin-right:4px;"></i> Recommandations';
        html += '    </div>';
        data.recommendations.forEach(function(r) {
            html += '    <div class="diag-rec"><i class="bi bi-arrow-right-circle"></i>' + escHtml(r) + '</div>';
        });
        html += '</div>';
    }

    result.innerHTML = html;
}

function escHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>
</body>
</html>
