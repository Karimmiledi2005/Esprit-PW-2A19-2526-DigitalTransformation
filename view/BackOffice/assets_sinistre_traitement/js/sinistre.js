// Sinistre page logic

// API endpoints
const SINISTRE_LIST_API         = 'sinistre_list.php';
const SINISTRE_UPDATE_STATUT_API = 'sinistre_update_statut.php';
const SINISTRE_CREATE_API       = 'sinistre_create.php';
const SINISTRE_DELETE_API       = 'sinistre_delete.php';
const FRAUD_GET_API             = 'fraud_get.php';
const FRAUD_ANALYSE_API         = 'fraud_analyse.php';

let sinistres = [];   // chargé depuis la BDD
let nextNum = 1000, currentPage = 1, deletingId = null;
const perPage = 8;

// Sort state
let sortColumn = null;   // 'id', 'contrat', 'type', 'client', 'date', 'statut'
let sortDirection = null; // 'asc' or 'desc'

const STATUT_LABELS = { en_attente:'En attente', rembourse:'Remboursé', refuse:'Refusé' };
const STATUT_BADGE  = { en_attente:'badge-agent', rembourse:'badge-actif', refuse:'badge-admin' };
const TYPE_ICONS    = { 
    'Accident auto': 'bi-car-front',
    'Vol de véhicule': 'bi-car-front',
    'Bris de glace': 'bi-window',
    'Incendie véhicule': 'bi-fire',
    'Incendie': 'bi-fire',
    'Cambriolage / Vol': 'bi-lock',
    'Dégât des eaux': 'bi-droplet',
    'Catastrophe naturelle': 'bi-cloud-lightning-rain',
    'Décès': 'bi-heartbreak',
    'Invalidité': 'bi-person-wheelchair',
    'Hospitalisation': 'bi-hospital',
    'Accident corporel': 'bi-bandaid',
    'Maladie grave': 'bi-activity',
    'Vol / Vandalisme': 'bi-shield-slash'
};

// Load data from DB
async function loadSinistres() {
    try {
        const res  = await fetch(SINISTRE_LIST_API + '?_=' + Date.now());
        const json = await res.json();
        if (json.success) {
            sinistres = json.data.map(s => ({
                id:             s.id_sinistre,
                contrat:        s.numero_contrat,
                type:           s.type,
                date:           s.date_declaration,
                statut:         s.statut,
                description:    s.description,
                client:         s.client_nom || '—',
                fraudScore:     s.fraud_score  !== undefined ? s.fraud_score  : null,
                fraudNiveau:    s.fraud_niveau !== undefined ? s.fraud_niveau : null,
                fraudSuggestion:s.fraud_suggestion !== undefined ? s.fraud_suggestion : null,
            }));
            currentPage = 1;
            render();
            updateChart();
        } else {
            showToast('Erreur chargement: ' + json.message, 'danger');
        }
    } catch(e) {
        showToast('Impossible de contacter le serveur PHP.', 'danger');
    }
}

// Chart.js integration
let sinistresChart = null;

function updateChart() {
    const ctx = document.getElementById('sinistresChart');
    if (!ctx) return;

    // Group by month (YYYY-MM)
    const counts = {};
    sinistres.forEach(s => {
        if (!s.date) return;
        const month = s.date.substring(0, 7);
        counts[month] = (counts[month] || 0) + 1;
    });

    // Sort months chronologically
    const labels = Object.keys(counts).sort();
    const data = labels.map(label => counts[label]);

    // Format labels nicely (e.g. "Oct 2023")
    const niceLabels = labels.map(label => {
        const d = new Date(label + '-01');
        return d.toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' });
    });

    if (sinistresChart) {
        sinistresChart.data.labels = niceLabels;
        sinistresChart.data.datasets[0].data = data;
        sinistresChart.update();
    } else {
        sinistresChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: niceLabels,
                datasets: [{
                    label: 'Sinistres déclarés',
                    data: data,
                    borderColor: '#00b4d8',
                    backgroundColor: 'rgba(0, 180, 216, 0.15)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#0077b6',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(10, 15, 30, 0.9)',
                        titleFont: { size: 13, family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, family: "'Inter', sans-serif" },
                        padding: 12,
                        displayColors: false
                    }
                }
            }
        });
    }
}

async function changeStatut(id, newStatut) {
    const body = new URLSearchParams({ id, statut: newStatut });
    const res  = await fetch(SINISTRE_UPDATE_STATUT_API, { method:'POST', body });
    const json = await res.json();
    if (json.success) {
        const s = sinistres.find(x => x.id == id);
        if (s) { s.statut = newStatut; render(); }
        showToast(`Statut → ${STATUT_LABELS[newStatut]}`, newStatut==='refuse'?'danger':'success');
    } else {
        showToast(json.message, 'danger');
    }
}

async function saveSinistre() {
    const contrat = document.getElementById('fContrat').value.trim();
    const date    = document.getElementById('fDate').value;
    const desc    = document.getElementById('fDescription').value.trim();
    let ok = true;
    if(!contrat){showErr('fContrat','errContrat',true);ok=false;}else showErr('fContrat','errContrat',false);
    if(!date)   {showErr('fDate','errDate',true);ok=false;}      else showErr('fDate','errDate',false);
    if(!desc)   {showErr('fDescription','errDescription',true);ok=false;} else showErr('fDescription','errDescription',false);
    if(!ok) return;

    const btn = document.getElementById('btnCreate');
    btn.innerHTML='<i class="bi bi-arrow-repeat spin"></i> Enregistrement...'; btn.disabled=true;

    // id_contrat et id_user: pour la démo on envoie 1 — en production, utilisez la session
    const formData = new FormData();
    formData.append('id_contrat',  contrat);
    formData.append('type',        document.getElementById('fType').value);
    formData.append('description', desc);
    formData.append('id_user',     1); // remplacer par $_SESSION['user_id'] côté PHP

    try {
        const res  = await fetch(SINISTRE_CREATE_API, { method:'POST', body: formData });
        const json = await res.json();
        if (json.success) {
            closeModal('modalCreate');
            await loadSinistres();
            showToast('Sinistre déclaré avec succès.', 'success');
        } else {
            showToast(json.message, 'danger');
        }
    } catch(e) {
        showToast('Erreur réseau.', 'danger');
    } finally {
        btn.innerHTML='<i class="bi bi-save"></i> Enregistrer'; btn.disabled=false;
    }
}

// Topbar date
const now = new Date();
document.getElementById('topbarDate').textContent =
  now.toLocaleDateString('fr-FR',{weekday:'long',day:'numeric',month:'long',year:'numeric'});

function getFiltered() {
  const q    = document.getElementById('searchInput').value.toLowerCase();
  const stat = document.getElementById('filterStatut').value;
  const type = document.getElementById('filterType').value;
  const date = document.getElementById('filterDate').value;
  var filtered = sinistres.filter(s =>
    (!q    || String(s.id).includes(q) || String(s.contrat||'').toLowerCase().includes(q) || String(s.type||'').toLowerCase().includes(q) || String(s.client||'').toLowerCase().includes(q)) &&
    (!stat || s.statut === stat) &&
    (!type || s.type === type) &&
    (!date || s.date === date)
  );

  // ── Apply sort ──
  if (sortColumn && sortDirection) {
    filtered.sort(function(a, b) {
      var valA, valB;
      switch(sortColumn) {
        case 'id':      valA = a.id;      valB = b.id;      break;
        case 'contrat': valA = (a.contrat||'').toLowerCase(); valB = (b.contrat||'').toLowerCase(); break;
        case 'type':    valA = (a.type||'').toLowerCase();    valB = (b.type||'').toLowerCase();    break;
        case 'client':  valA = (a.client||'').toLowerCase();  valB = (b.client||'').toLowerCase();  break;
        case 'date':    valA = a.date||'';  valB = b.date||'';  break;
        case 'statut':  valA = (a.statut||'').toLowerCase();  valB = (b.statut||'').toLowerCase();  break;
        default:        return 0;
      }
      var cmp = 0;
      if (typeof valA === 'number' && typeof valB === 'number') {
        cmp = valA - valB;
      } else {
        cmp = String(valA).localeCompare(String(valB), 'fr');
      }
      return sortDirection === 'desc' ? -cmp : cmp;
    });
  }

  return filtered;
}

function resetFilters(){
  ['searchInput','filterStatut','filterType','filterDate'].forEach(id=>document.getElementById(id).value='');
  sortColumn = null; sortDirection = null;
  currentPage=1; render();
}

// Sort toggle
function toggleSort(col) {
  if (sortColumn === col) {
    if (sortDirection === 'asc')       sortDirection = 'desc';
    else if (sortDirection === 'desc') { sortColumn = null; sortDirection = null; }
  } else {
    sortColumn = col;
    sortDirection = 'asc';
  }
  currentPage = 1;
  render();
}

function updateSortHeaders() {
  document.querySelectorAll('thead th.sortable').forEach(function(th) {
    th.classList.remove('sort-asc', 'sort-desc');
    if (th.dataset.sort === sortColumn) {
      th.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
    }
  });
}


// Antifraud helpers
const FRAUD_NIVEAU_LABELS = { faible:'Faible', normal:'Normal', fraude:'FRAUDE' };

function renderFraudBadge(score, niveau) {
  if (score === null || niveau === null) {
    return '<span class="fraud-badge none"><i class="bi bi-shield"></i> —</span>';
  }
  const icons = { faible:'bi-shield-check', normal:'bi-shield-exclamation', fraude:'bi-shield-fill' };
  const icon  = icons[niveau] || 'bi-shield';
  const label = FRAUD_NIVEAU_LABELS[niveau] || niveau;
  return `<span class="fraud-badge ${niveau}"><i class="bi ${icon}"></i> ${label} (${score})</span>`;
}

async function reanalyserFraud(idSinistre) {
  const btn = document.getElementById('fraudReanalyseBtn');
  if (btn) { btn.classList.add('loading'); btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Analyse...'; }
  try {
    const fd = new FormData(); fd.append('id_sinistre', idSinistre);
    const res  = await fetch(FRAUD_ANALYSE_API, { method: 'POST', body: fd });
    const json = await res.json();
    if (json.success) {
      renderFraudPanel(json.data, idSinistre);
      // Mettre à jour le badge dans le tableau
      const s = sinistres.find(x => x.id == idSinistre);
      if (s) { 
        s.fraudScore = json.data.score_global; 
        s.fraudNiveau = json.data.niveau_risque; 
        s.fraudSuggestion = json.data.suggestion_ia; 
        if (json.data.auto_refused) {
            s.statut = 'refuse';
            showToast('Sinistre REFUSÉ automatiquement (Risque IA Critique).', 'danger');
            // Mettre à jour le badge de statut dans le modal ouvert
            var badge = document.querySelector('#modalDetail .badge');
            if (badge) {
              badge.className = 'badge badge-admin';
              badge.textContent = 'Refusé';
            }
        } else {
            showToast('Analyse antifraud terminée.', 'success');
        }
        render(); 
      }
    } else {
      showToast(json.message || 'Erreur analyse.', 'danger');
    }
  } catch(e) { showToast('Erreur réseau lors de l\'analyse.', 'danger'); }
  finally { if (btn) { btn.classList.remove('loading'); btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Relancer l\'analyse'; } }
}

function renderFraudPanel(data, idSinistre) {
  const panel = document.getElementById('fraudPanel');
  if (!panel) return;
  panel.style.display = '';
  const niveauMap = { faible:'Faible', normal:'Normal', fraude:'FRAUDE' };
  const suggMap   = { accepter:'Accepter', investiguer:'Investiguer', refuser:'Refuser' };
  document.getElementById('fraudScoreNum').textContent  = data.score_global;
  document.getElementById('fraudCircle').className      = 'fraud-score-circle ' + data.niveau_risque;
  document.getElementById('fraudNiveauLabel').className = 'niveau-label ' + data.niveau_risque;
  document.getElementById('fraudNiveauLabel').textContent = niveauMap[data.niveau_risque] || data.niveau_risque;
  const pill = document.getElementById('fraudSuggestionPill');
  pill.innerHTML = `<span class="suggestion-pill ${data.suggestion_ia}"><i class="bi bi-lightning-charge"></i> ${suggMap[data.suggestion_ia] || data.suggestion_ia}</span>`;

  // Barres sous-scores
  const sd = data.scores_detail || {};
  document.getElementById('barTexteVal').textContent   = (sd.texte ?? '—') + '/100';
  document.getElementById('barComportVal').textContent = (sd.comportement ?? '—') + '/100';
  document.getElementById('barContratVal').textContent = (sd.contrat ?? '—') + '/100';
  document.getElementById('barTexte').style.width   = (sd.texte  || 0) + '%';
  document.getElementById('barComport').style.width = (sd.comportement || 0) + '%';
  document.getElementById('barContrat').style.width = (sd.contrat || 0) + '%';

  // Flags
  const flagDefs = [
    { key:'description_vague',   label:'Description vague',     icon:'bi-chat-square-dots' },
    { key:'sinistres_multiples', label:'Sinistres multiples',   icon:'bi-exclamation-triangle' },
    { key:'contrat_recent',      label:'Contrat récent',        icon:'bi-calendar-x' },
    { key:'montant_eleve',       label:'Montant élevé',         icon:'bi-cash-stack' },
    { key:'image_suspecte',      label:'Image suspecte',        icon:'bi-image' },
  ];
  const flags = data.flags || {};
  document.getElementById('fraudFlags').innerHTML = flagDefs.map(f => {
    const active = !!flags[f.key];
    return `<span class="fraud-flag ${active ? 'active' : 'inactive'}"><i class="bi ${f.icon}"></i> ${f.label}</span>`;
  }).join('');

  // Recommandation
  const rec = document.getElementById('fraudRecommandation');
  if (rec) rec.textContent = data.recommandation || '—';

  // Bouton relancer
  const btn = document.getElementById('fraudReanalyseBtn');
  if (btn) btn.setAttribute('onclick', `reanalyserFraud(${idSinistre})`);

  // Date
  const dt = document.getElementById('fraudAnalyseDate');
  if (dt && data.date_analyse) dt.textContent = '— ' + data.date_analyse.substring(0,10);
}

async function loadFraudPanel(idSinistre) {
  const panel = document.getElementById('fraudPanel');
  if (!panel) return;
  panel.style.display = '';
  try {
    const res  = await fetch(FRAUD_GET_API + '?id_sinistre=' + idSinistre);
    const json = await res.json();
    if (json.success && json.data) {
      renderFraudPanel(json.data, idSinistre);
    } else {
      // Pas encore analysé : simple message sans bouton
      panel.innerHTML += '<div style="padding:14px 18px;font-size:13px;color:var(--text-secondary);"><i class="bi bi-info-circle"></i> Aucune analyse disponible. Veuillez passer par l\'onglet "Traitements" pour analyser ce sinistre.</div>';
    }
  } catch(e) { /* silencieux */ }
}

function render() {
  const filtered=getFiltered(), total=filtered.length;
  const pages=Math.ceil(total/perPage)||1;
  if(currentPage>pages) currentPage=pages;
  const slice=filtered.slice((currentPage-1)*perPage,currentPage*perPage);

  const tbody=document.getElementById('sinistreBody');
  const empty=document.getElementById('emptyState');

  if(!slice.length){ tbody.innerHTML=''; empty.style.display='block'; }
  else {
    empty.style.display='none';
    tbody.innerHTML=slice.map(s=>{
      const icon=TYPE_ICONS[s.type]||'bi-shield';
      return `<tr>
        <td><span style="font-family:monospace;font-size:12px;color:var(--accent);">#${s.id}</span></td>
        <td><span style="color:var(--gold);font-weight:600;">${s.contrat}</span></td>
        <td><div class="type-cell"><div class="type-icon"><i class="bi ${icon}"></i></div>${s.type}</div></td>
        <td style="color:var(--text-secondary);">${s.client||'—'}</td>
        <td style="color:var(--text-secondary);">${formatDate(s.date)}</td>
        <td>
          <select class="status-select ${s.statut}" onchange="changeStatut(${s.id},this.value)">
            <option value="en_attente" ${s.statut==='en_attente'?'selected':''}>En attente</option>
            <option value="rembourse"  ${s.statut==='rembourse' ?'selected':''}>Remboursé</option>
            <option value="refuse"     ${s.statut==='refuse'    ?'selected':''}>Refusé</option>
          </select>
        </td>
        <td>${renderFraudBadge(s.fraudScore, s.fraudNiveau)}</td>
        <td>
          <div class="actions">
            <button class="btn btn-outline btn-sm" onclick="viewSinistre(${s.id})" title="Voir"><i class="bi bi-eye"></i></button>
            <button class="btn btn-danger  btn-sm btn-delete-sinistre" onclick="openDeleteModal(${s.id})" title="Supprimer"><i class="bi bi-trash3"></i></button>
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  const start=total===0?0:(currentPage-1)*perPage+1;
  const end=Math.min(currentPage*perPage,total);
  document.getElementById('paginationInfo').textContent=`Affichage ${start}–${end} sur ${total} sinistre${total>1?'s':''}`;
  document.getElementById('paginationBtns').innerHTML=`
    <button class="page-btn" onclick="goPage(${currentPage-1})" ${currentPage<=1?'disabled':''}><i class="bi bi-chevron-left"></i></button>
    ${Array.from({length:pages},(_,i)=>`<button class="page-btn ${i+1===currentPage?'active':''}" onclick="goPage(${i+1})">${i+1}</button>`).join('')}
    <button class="page-btn" onclick="goPage(${currentPage+1})" ${currentPage>=pages?'disabled':''}><i class="bi bi-chevron-right"></i></button>`;

  updateStats();
  updateSortHeaders();
}

function goPage(p){currentPage=p;render();}
function formatDate(d){if(!d)return'—';const[y,m,day]=d.split('-');return`${day}/${m}/${y}`;}

function updateStats(){
  document.getElementById('statTotal').textContent   = sinistres.length;
  document.getElementById('statAttente').textContent = sinistres.filter(s=>s.statut==='en_attente').length;
  document.getElementById('statValides').textContent = sinistres.filter(s=>s.statut==='rembourse').length;
  document.getElementById('statRefuses').textContent = sinistres.filter(s=>s.statut==='refuse').length;
}


function viewSinistre(id){
  const s=sinistres.find(x=>x.id==id); if(!s)return;
  const icon=TYPE_ICONS[s.type]||'bi-shield';
  const bc=STATUT_BADGE[s.statut]||'badge-agent';
  document.getElementById('modalDetailBody').innerHTML=`
    <div class="sinistre-modal-header">
      <div class="sinistre-modal-icon"><i class="bi ${icon}"></i></div>
      <div style="flex:1;">
        <div class="sinistre-modal-type">${s.type}</div>
        <div class="sinistre-modal-id">Dossier #${s.id} · Contrat ${s.contrat}</div>
      </div>
      <span class="badge ${bc}">${STATUT_LABELS[s.statut]}</span>
    </div>
    <div class="detail-grid">
      <div class="detail-field"><div class="detail-field-label"><i class="bi bi-hash"></i> ID</div><div class="detail-field-value" style="font-family:monospace;color:var(--accent);">#${s.id}</div></div>
      <div class="detail-field"><div class="detail-field-label"><i class="bi bi-file-earmark-text"></i> Contrat</div><div class="detail-field-value" style="color:var(--gold);">${s.contrat}</div></div>
      <div class="detail-field"><div class="detail-field-label"><i class="bi bi-person"></i> Client</div><div class="detail-field-value">${s.client||'—'}</div></div>
      <div class="detail-field"><div class="detail-field-label"><i class="bi bi-calendar3"></i> Date</div><div class="detail-field-value">${formatDate(s.date)}</div></div>
      <div class="detail-field full"><div class="detail-field-label"><i class="bi bi-chat-left-text"></i> Description</div><div class="detail-field-value" style="color:var(--text-secondary);">${s.description}</div></div>
    </div>
    <!-- ANTIFRAUD PANEL -->
    <div class="fraud-panel" id="fraudPanel" style="display:none;">
      <div class="fraud-panel-header">
        <div class="fraud-panel-title"><i class="bi bi-shield-shaded"></i> Analyse Antifraud IA <span id="fraudAnalyseDate" style="font-weight:400;font-size:11px;color:var(--text-secondary);margin-left:4px;"></span></div>
      </div>
      <div class="fraud-score-row">
        <div class="fraud-score-circle" id="fraudCircle"><span class="fraud-score-num" id="fraudScoreNum">—</span><span class="fraud-score-denom">/100</span></div>
        <div class="fraud-score-meta">
          <div class="niveau-label" id="fraudNiveauLabel">—</div>
          <div id="fraudSuggestionPill"></div>
          <div style="font-size:12px;color:var(--text-secondary);">Score de risque global calculé par l'IA</div>
        </div>
      </div>
      <div class="fraud-bars">
        <div class="fraud-bar-row"><div class="fraud-bar-label"><span><i class="bi bi-chat-text"></i> Analyse textuelle</span><span id="barTexteVal">—</span></div><div class="fraud-bar-track"><div class="fraud-bar-fill bar-texte" id="barTexte" style="width:0%"></div></div></div>
        <div class="fraud-bar-row"><div class="fraud-bar-label"><span><i class="bi bi-person-lines-fill"></i> Comportement client</span><span id="barComportVal">—</span></div><div class="fraud-bar-track"><div class="fraud-bar-fill bar-comportement" id="barComport" style="width:0%"></div></div></div>
        <div class="fraud-bar-row" style="margin-bottom:0;"><div class="fraud-bar-label"><span><i class="bi bi-file-earmark-text"></i> Profil contrat</span><span id="barContratVal">—</span></div><div class="fraud-bar-track"><div class="fraud-bar-fill bar-contrat" id="barContrat" style="width:0%"></div></div></div>
      </div>
      <div class="fraud-flags" id="fraudFlags"></div>
      <div class="fraud-recommandation" id="fraudRecommandation"></div>
    </div>`;
  loadFraudPanel(s.id);
  openModal('modalDetail');
}

function openDeleteModal(id){
  deletingId=id;
  document.getElementById('deleteMsg').textContent=`Vous êtes sur le point de supprimer le dossier #${id}. Cette action est irréversible.`;
  openModal('modalDelete');
}
async function confirmDelete(){
  const res  = await fetch(SINISTRE_DELETE_API + '?id=' + deletingId, { method:'GET' });
  const json = await res.json();
  closeModal('modalDelete');
  if (json.success) {
    sinistres = sinistres.filter(x=>x.id!=deletingId);
    render(); showToast('Sinistre supprimé.','danger');
  } else {
    showToast(json.message,'danger');
  }
}

function openCreateModal(){
  document.getElementById('fContrat').value='';
  document.getElementById('fDescription').value='';
  document.getElementById('fDate').value=new Date().toISOString().split('T')[0];
  document.getElementById('fType').value='Accident auto';
  document.getElementById('fStatut').value='en_attente';
  document.querySelectorAll('.form-error').forEach(e=>e.classList.remove('show'));
  document.querySelectorAll('.form-control').forEach(e=>e.classList.remove('error'));
  openModal('modalCreate');
}


function exportCSV(){
  const rows=[['ID','Contrat','Type','Date','Statut']];
  sinistres.forEach(s=>rows.push([s.id,s.contrat,s.type,s.date,STATUT_LABELS[s.statut]]));
  const csv=rows.map(r=>r.map(v=>`"${v}"`).join(',')).join('\n');
  const a=document.createElement('a');
  a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(csv);
  a.download='sinistres.csv'; a.click();
  showToast('Export CSV téléchargé.','success');
}

function openModal(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';}
function closeModal(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}

document.addEventListener('keydown',e=>{
  if(e.key==='Escape') document.querySelectorAll('.modal-overlay.open').forEach(m=>{m.classList.remove('open');document.body.style.overflow='';});
});
document.querySelectorAll('.modal-overlay').forEach(o=>{
  o.addEventListener('click',e=>{if(e.target===o){o.classList.remove('open');document.body.style.overflow='';}});
});


document.getElementById('searchInput').addEventListener('input',()=>{currentPage=1;render();});
document.getElementById('filterStatut').addEventListener('change',()=>{currentPage=1;render();});
document.getElementById('filterType').addEventListener('change',()=>{currentPage=1;render();});
document.getElementById('filterDate').addEventListener('change',()=>{currentPage=1;render();});

document.addEventListener('DOMContentLoaded', () => loadSinistres());
async function loadPermissions() {
    try {
        const res = await fetch('get_permissions.php');
        const perms = await res.json();

        if (!perms.canDeleteSinistre) {
            document.querySelectorAll('.btn-delete-sinistre').forEach(b => b.remove());
        }
        if (!perms.canModifySinistre) {
            document.querySelectorAll('.btn-edit-sinistre').forEach(b => b.remove());
        }
        if (!perms.canAssignSinistre) {
            document.querySelectorAll('.btn-assign-sinistre').forEach(b => b.remove());
        }
        if (!perms.canSeeFraudScore) {
            document.querySelectorAll('.fraud-score-col').forEach(el => el.remove());
        }
        if (!perms.canExportSinistres) {
            document.querySelectorAll('.btn-export').forEach(b => b.remove());
        }
    } catch (e) { console.error('Erreur chargement permissions:', e); }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    loadPermissions();
    loadSinistres();
});
