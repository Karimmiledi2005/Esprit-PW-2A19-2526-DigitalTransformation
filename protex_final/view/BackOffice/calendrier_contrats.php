<?php
require_once __DIR__ . '/../../controller/ContratController.php';

$contratController = new ContratController();
$contrats = $contratController->getAll();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$events = [];
$totalEvents = 0;
$totalExpiringSoon = 0;
$totalExpired = 0;
$today = new DateTime('today');

foreach ($contrats as $contrat) {
    $id = (int)$contrat->getIdContrat();
    $numero = $contrat->getNumeroContrat() ?: ('CTR-' . $id);
    $client = trim(($contrat->getNomClient() ?? '') . ' ' . ($contrat->getPrenomClient() ?? ''));
    if ($client === '') {
        $client = 'Client';
    }

    $categorie = $contrat->getNomCategorie() ?: $contrat->getTypeContrat();
    $formule = $contrat->getNomFormule() ?: $contrat->getFormuleContrat();
    $dateDebut = $contrat->getDateDebutContrat();
    $dateFin = $contrat->getDateFinContrat();
    $statut = strtolower(trim($contrat->getStatutContrat()));

    if (!$dateFin) {
        continue;
    }

    $dateFinObj = DateTime::createFromFormat('Y-m-d', substr((string)$dateFin, 0, 10));
    if (!$dateFinObj) {
        continue;
    }

    $daysLeft = (int)$today->diff($dateFinObj)->format('%r%a');
    if ($daysLeft < 0) {
        $totalExpired++;
    } elseif ($daysLeft <= 30) {
        $totalExpiringSoon++;
    }

    $color = '#ef4444';
    $statusLabel = $contrat->getStatutContrat();

    if ($statut === 'actif') {
        $color = '#22c55e';
    } elseif ($statut === 'en attente' || $statut === 'en_attente') {
        $color = '#f59e0b';
    } elseif ($statut === 'résilié' || $statut === 'resilie' || $statut === 'refusé' || $statut === 'refuse') {
        $color = '#ef4444';
    } elseif ($statut === 'expiré' || $statut === 'expire') {
        $color = '#991b1b';
    }

    $events[] = [
        'id' => $id,
        'numero' => $numero,
        'title' => $numero . ' - ' . $client,
        'client' => $client,
        'categorie' => $categorie ?: '—',
        'formule' => $formule ?: '—',
        'statut' => $statusLabel ?: '—',
        'dateDebut' => substr((string)$dateDebut, 0, 10),
        'dateFin' => substr((string)$dateFin, 0, 10),
        'daysLeft' => $daysLeft,
        'color' => $color,
        'url' => 'showContrat.php?id=' . $id
    ];

    $totalEvents++;
}

$eventsJson = json_encode($events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendrier contrats — Protex Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="user/css/assets_contrats/css/variables.css">
  <link rel="stylesheet" href="user/css/assets_contrats/css/base.css">
  <link rel="stylesheet" href="user/css/assets_contrats/css/layout.css">
  <link rel="stylesheet" href="user/css/assets_contrats/css/contrats.css">

    <link rel="stylesheet" href="user/css/variables.css">
    <link rel="stylesheet" href="user/css/base.css">
    <link rel="stylesheet" href="user/css/layout.css">
    <link rel="stylesheet" href="user/css/admin-users.css">
    <link rel="stylesheet" href="user/css/validation.css">
    <link rel="stylesheet" href="user/css/animations.css">
  <script src="assets/js/validation.js"></script>
  <style>
    .calendar-shell {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 22px;
      padding: 20px;
      box-shadow: 0 18px 45px rgba(10, 25, 49, 0.18);
    }

    .calendar-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
      margin-bottom: 18px;
    }

    .calendar-title {
      font-size: 24px;
      font-weight: 800;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .calendar-subtitle {
      color: var(--text-secondary);
      font-size: 13px;
      margin-top: 4px;
    }

    .calendar-controls {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, minmax(115px, 1fr));
      gap: 10px;
    }

    .day-name {
      color: var(--text-secondary);
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 6px 8px;
    }

    .calendar-day {
      min-height: 128px;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 16px;
      padding: 10px;
      overflow: hidden;
    }

    .calendar-day.muted {
      opacity: 0.35;
    }

    .calendar-day.today {
      border-color: #FF6B1A;
      box-shadow: 0 0 0 2px rgba(255,107,26,0.18);
    }

    .day-number {
      color: #fff;
      font-weight: 800;
      font-size: 13px;
      margin-bottom: 8px;
    }

    .event-chip {
      display: block;
      width: 100%;
      border: 0;
      text-align: left;
      color: #fff;
      padding: 7px 8px;
      border-radius: 10px;
      margin-bottom: 6px;
      cursor: pointer;
      font-size: 11px;
      line-height: 1.25;
      box-shadow: 0 8px 18px rgba(0,0,0,0.16);
    }

    .event-chip strong {
      display: block;
      font-size: 11px;
      margin-bottom: 2px;
    }

    .event-chip span {
      display: block;
      opacity: 0.9;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .legend-row {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin: 14px 0 4px;
      color: var(--text-secondary);
      font-size: 12px;
    }

    .legend-item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 999px;
      padding: 6px 10px;
    }

    .legend-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      display: inline-block;
    }

    .modal-overlay.open {
      display: flex;
    }

    .event-detail-list {
      padding: 22px;
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
    }

    .event-detail-item {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 14px;
      padding: 12px;
    }

    .event-detail-label {
      color: var(--text-secondary);
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 5px;
    }

    .event-detail-value {
      color: #fff;
      font-weight: 750;
      font-size: 14px;
    }

    @media (max-width: 980px) {
      .calendar-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .day-name { display: none; }
    }
  </style>
</head>
<body>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">
  <?php require_once __DIR__.'/assets/includes/sidebar.php'; ?>

  <main class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title">Calendrier des contrats</div>
        <div class="topbar-sub" id="topbarDate"></div>
      </div>
      <div class="topbar-actions">
        <a href="contrats_back.php" class="topbar-btn" title="Retour contrats"><i class="bi bi-arrow-left"></i></a>
        <a href="#" class="topbar-btn" title="Notifications"><i class="bi bi-bell"></i><span class="notif-dot"></span></a>
      </div>
    </div>

    <div class="content">
      <div class="page-header-bar">
        <div>
          <div class="page-title">Calendrier contrats</div>
          <div class="page-breadcrumb">
            <i class="bi bi-house"></i>
            <a href="#">Accueil</a>
            <i class="bi bi-chevron-right" style="font-size:10px;"></i>
            <span>Calendrier contrats</span>
          </div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
          <a href="contrats_back.php" class="btn btn-outline"><i class="bi bi-table"></i> Liste contrats</a>
          <a href="contrats_alertes_sms.php" class="btn btn-primary"><i class="bi bi-chat-dots"></i> Alertes SMS</a>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card blue">
          <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
          <div class="stat-value"><?= (int)$totalEvents ?></div>
          <div class="stat-label">Échéances</div>
          <div class="stat-trend trend-up"><i class="bi bi-calendar-check"></i> Dates fin contrat</div>
        </div>
        <div class="stat-card gold">
          <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
          <div class="stat-value"><?= (int)$totalExpiringSoon ?></div>
          <div class="stat-label">Dans 30 jours</div>
          <div class="stat-trend trend-warn"><i class="bi bi-clock"></i> À relancer</div>
        </div>
        <div class="stat-card red">
          <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
          <div class="stat-value"><?= (int)$totalExpired ?></div>
          <div class="stat-label">Déjà dépassés</div>
          <div class="stat-trend trend-down"><i class="bi bi-x-circle"></i> À vérifier</div>
        </div>
      </div>

      <div class="calendar-shell">
        <div class="calendar-top">
          <div>
            <div class="calendar-title"><i class="bi bi-calendar3"></i> <span id="monthTitle"></span></div>
            <div class="calendar-subtitle">Chaque événement représente la date fin d’un contrat. Cliquez sur un événement pour voir les détails.</div>
          </div>
          <div class="calendar-controls">
            <button class="btn btn-outline btn-sm" onclick="previousMonth()"><i class="bi bi-chevron-left"></i></button>
            <button class="btn btn-outline btn-sm" onclick="goToday()">Aujourd’hui</button>
            <button class="btn btn-outline btn-sm" onclick="nextMonth()"><i class="bi bi-chevron-right"></i></button>
          </div>
        </div>

        <div class="legend-row">
          <span class="legend-item"><span class="legend-dot" style="background:#22c55e;"></span> Actif</span>
          <span class="legend-item"><span class="legend-dot" style="background:#f59e0b;"></span> En attente</span>
          <span class="legend-item"><span class="legend-dot" style="background:#ef4444;"></span> Refusé / résilié</span>
          <span class="legend-item"><span class="legend-dot" style="background:#991b1b;"></span> Expiré</span>
        </div>

        <div class="calendar-grid" id="calendarGrid"></div>
      </div>
    </div>
  </main>
</div>

<div class="modal-overlay" id="modalEvent">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title"><i class="bi bi-calendar-event"></i> Détail échéance contrat</div>
      <button class="modal-close" onclick="closeModal('modalEvent')"><i class="bi bi-x"></i></button>
    </div>
    <div id="modalEventBody"></div>
    <div class="modal-footer">
      <a id="eventOpenLink" href="#" class="btn btn-primary"><i class="bi bi-eye"></i> Ouvrir le contrat</a>
      <button class="btn btn-outline" onclick="closeModal('modalEvent')">Fermer</button>
    </div>
  </div>
</div>

<script>
  const events = <?= $eventsJson ?: '[]' ?>;
  const calendarGrid = document.getElementById('calendarGrid');
  const monthTitle = document.getElementById('monthTitle');
  let currentDate = new Date();

  document.getElementById('topbarDate').textContent =
    new Date().toLocaleDateString('fr-FR', {
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    });

  function normalizeDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  function escapeHTML(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function renderCalendar() {
    calendarGrid.innerHTML = '';

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const todayKey = normalizeDate(new Date());

    monthTitle.textContent = new Date(year, month, 1).toLocaleDateString('fr-FR', {
      month: 'long', year: 'numeric'
    });

    const dayNames = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    dayNames.forEach(day => {
      const item = document.createElement('div');
      item.className = 'day-name';
      item.textContent = day;
      calendarGrid.appendChild(item);
    });

    const firstDay = new Date(year, month, 1);
    const startOffset = (firstDay.getDay() + 6) % 7;
    const startDate = new Date(year, month, 1 - startOffset);

    for (let i = 0; i < 42; i++) {
      const date = new Date(startDate);
      date.setDate(startDate.getDate() + i);
      const key = normalizeDate(date);
      const dayEvents = events.filter(event => event.dateFin === key);

      const dayBox = document.createElement('div');
      dayBox.className = 'calendar-day';
      if (date.getMonth() !== month) dayBox.classList.add('muted');
      if (key === todayKey) dayBox.classList.add('today');

      dayBox.innerHTML = `<div class="day-number">${date.getDate()}</div>`;

      dayEvents.slice(0, 3).forEach(event => {
        const btn = document.createElement('button');
        btn.className = 'event-chip';
        btn.style.background = event.color || '#2563eb';
        btn.innerHTML = `<strong>${escapeHTML(event.numero)}</strong><span>${escapeHTML(event.client)}</span>`;
        btn.onclick = () => openEvent(event);
        dayBox.appendChild(btn);
      });

      if (dayEvents.length > 3) {
        const more = document.createElement('button');
        more.className = 'event-chip';
        more.style.background = '#334155';
        more.innerHTML = `<strong>+${dayEvents.length - 3}</strong><span>autres contrats</span>`;
        more.onclick = () => alert(dayEvents.map(e => e.numero + ' - ' + e.client).join('\n'));
        dayBox.appendChild(more);
      }

      calendarGrid.appendChild(dayBox);
    }
  }

  function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
  }

  function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
  }

  function goToday() {
    currentDate = new Date();
    renderCalendar();
  }

  function openModal(id) {
    document.getElementById(id).classList.add('open');
  }

  function closeModal(id) {
    document.getElementById(id).classList.remove('open');
  }

  function openEvent(event) {
    const daysText = Number(event.daysLeft) < 0
      ? `Dépassé depuis ${Math.abs(Number(event.daysLeft))} jour(s)`
      : `Expire dans ${Number(event.daysLeft)} jour(s)`;

    document.getElementById('modalEventBody').innerHTML = `
      <div class="event-detail-list">
        <div class="event-detail-item"><div class="event-detail-label">N° Contrat</div><div class="event-detail-value">${escapeHTML(event.numero)}</div></div>
        <div class="event-detail-item"><div class="event-detail-label">Client</div><div class="event-detail-value">${escapeHTML(event.client)}</div></div>
        <div class="event-detail-item"><div class="event-detail-label">Catégorie</div><div class="event-detail-value">${escapeHTML(event.categorie)}</div></div>
        <div class="event-detail-item"><div class="event-detail-label">Formule</div><div class="event-detail-value">${escapeHTML(event.formule)}</div></div>
        <div class="event-detail-item"><div class="event-detail-label">Date début</div><div class="event-detail-value">${escapeHTML(event.dateDebut || '—')}</div></div>
        <div class="event-detail-item"><div class="event-detail-label">Date fin</div><div class="event-detail-value">${escapeHTML(event.dateFin)}</div></div>
        <div class="event-detail-item"><div class="event-detail-label">Statut</div><div class="event-detail-value">${escapeHTML(event.statut)}</div></div>
        <div class="event-detail-item"><div class="event-detail-label">Échéance</div><div class="event-detail-value">${escapeHTML(daysText)}</div></div>
      </div>
    `;

    document.getElementById('eventOpenLink').href = event.url;
    openModal('modalEvent');
  }

  renderCalendar();
</script>

</body>
</html>


