// ===== TRAITEMENT PAGE LOGIC =====

const TRAIT_LIST_API         = 'traitement_list.php';
const TRAIT_BY_SINISTRE_API  = 'traitement_list_sinistre.php';
const TRAIT_CHECK_API        = 'traitement_check_sinistre.php';
const TRAIT_CREATE_API       = 'traitement_create.php';
const TRAIT_UPDATE_API       = 'traitement_update.php';
const TRAIT_DELETE_API       = 'traitement_delete.php';
const SINISTRE_DETAILS_API   = 'sinistre_details.php';
let traitements = [];
let currentPage = 1, editingId = null, deletingId = null;
const perPage = 8;

// ── Sort state ───────────────────────────────────────────────────────────────
var sortColumn = null;    // 'id', 'sinistre', 'date', 'agent', 'decision', 'montant'
var sortDirection = null; // 'asc' or 'desc'

const DECISION_LABELS = { en_attente:'En attente', refuse:'Refusé', rembourse:'Remboursé' };
const DECISION_COLORS = { en_attente:'badge-agent', refuse:'badge-admin', rembourse:'badge-actif' };

function decisionBadge(d) {
  return '<span class="badge ' + (DECISION_COLORS[d]||'badge-agent') + '">' + (DECISION_LABELS[d]||d) + '</span>';
}
function initials(name) {
  if (!name) return '??';
  // Handle "Agent #1" style fallback
  var match = name.match(/^Agent #(\d+)$/);
  if (match) return 'A' + match[1];
  return name.split(' ').map(function(w){return w[0];}).join('').substring(0,2).toUpperCase();
}
function formatDate(d) {
  if (!d) return '—';
  var parts = d.split('-');
  return parts[2] + '/' + parts[1] + '/' + parts[0];
}

// ── Date topbar ──────────────────────────────────────────────────────────────
document.getElementById('topbarDate').textContent =
  new Date().toLocaleDateString('fr-FR', {weekday:'long', day:'numeric', month:'long', year:'numeric'});

// ── Load from DB ─────────────────────────────────────────────────────────────
async function loadTraitements() {
  try {
    var res  = await fetch(TRAIT_LIST_API + '?_=' + Date.now());
    var json = await res.json();
    if (json.success) {
      traitements = json.data.map(function(t) {
        return {
          id:            t.id_traitement,
          sinistre:      t.id_sinistre,
          sinType:       t.sinistre_type || '—',
          date:          t.date_traitement,
          agent:         t.nom_agent,
          decision:      t.decision,
          message_agent: t.message_agent,
          montant:       t.montant_indemnise !== null ? parseFloat(t.montant_indemnise) : null,
          statut:        t.statut,
        };
      });
      currentPage = 1;
      render();
    } else {
      showToast('Erreur: ' + json.message, 'danger');
    }
  } catch(e) {
    showToast('Impossible de contacter le serveur PHP.', 'danger');
  }
}

// ── Filters ──────────────────────────────────────────────────────────────────
function getFiltered() {
  var q   = document.getElementById('searchInput').value.toLowerCase();
  var dec = document.getElementById('filterDecision').value;
  var mon = document.getElementById('filterMontant').value;
  var filtered = traitements.filter(function(t) {
    var mQ = !q   || String(t.sinistre).includes(q) || String(t.agent||'').toLowerCase().includes(q) || String(t.decision||'').toLowerCase().includes(q);
    var mD = !dec || t.decision === dec;
    var mM = !mon || (mon === 'avec' ? (t.montant !== null && t.montant > 0) : (t.montant === null || t.montant === 0));
    return mQ && mD && mM;
  });

  // ── Apply sort ──
  if (sortColumn && sortDirection) {
    filtered.sort(function(a, b) {
      var valA, valB;
      switch(sortColumn) {
        case 'id':        valA = a.id;        valB = b.id;        break;
        case 'sinistre':  valA = a.sinistre;  valB = b.sinistre;  break;
        case 'date':      valA = a.date||'';  valB = b.date||'';  break;
        case 'agent':     valA = (a.agent||'').toLowerCase();     valB = (b.agent||'').toLowerCase();     break;
        case 'decision':  valA = (a.decision||'').toLowerCase();  valB = (b.decision||'').toLowerCase();  break;
        case 'montant':   valA = a.montant !== null ? a.montant : -1;  valB = b.montant !== null ? b.montant : -1;  break;
        default:          return 0;
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

function resetFilters() {
  ['searchInput','filterDecision','filterMontant'].forEach(function(id) {
    document.getElementById(id).value = '';
  });
  sortColumn = null; sortDirection = null;
  currentPage = 1;
  render();
}

// ── Sort toggle ──────────────────────────────────────────────────────────────
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

// ── Render table ─────────────────────────────────────────────────────────────
function render() {
  var filtered = getFiltered();
  var total    = filtered.length;
  var pages    = Math.ceil(total / perPage) || 1;
  if (currentPage > pages) currentPage = pages;
  var slice = filtered.slice((currentPage-1)*perPage, currentPage*perPage);

  var tbody = document.getElementById('traitBody');
  var empty = document.getElementById('emptyState');

  if (!slice.length) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
  } else {
    empty.style.display = 'none';
    tbody.innerHTML = slice.map(function(t) {
      var montantCell = t.montant !== null
        ? '<span class="amount-value">' + t.montant.toLocaleString('fr-FR') + ' DT</span>'
        : '<span class="amount-empty">—</span>';
      return '<tr>' +
        '<td><span style="font-family:monospace;font-size:12px;color:var(--accent);">TR-' + String(t.id).padStart(3,'0') + '</span></td>' +
        '<td><span style="color:var(--gold);font-weight:600;">#' + t.sinistre + '</span> <span style="font-size:11px;color:var(--text-secondary);">' + t.sinType + '</span></td>' +
        '<td style="color:var(--text-secondary);">' + formatDate(t.date) + '</td>' +
        '<td><div class="agent-cell"><div class="agent-avatar">' + initials(t.agent) + '</div><span style="font-size:13px;">' + t.agent + '</span></div></td>' +
        '<td>' + decisionBadge(t.decision) + '</td>' +
        '<td>' + montantCell + '</td>' +
        '<td><div class="actions">' +
          '<button class="btn btn-outline btn-sm" onclick="openViewModal(' + t.sinistre + ')" title="Voir le sinistre"><i class="bi bi-eye"></i></button>' +
          '<button class="btn btn-outline btn-sm" onclick="openEditModal(' + t.id + ')" title="Modifier"><i class="bi bi-pencil"></i></button>' +
          '<button class="btn btn-danger btn-sm" onclick="openDeleteModal(' + t.id + ')" title="Supprimer"><i class="bi bi-trash3"></i></button>' +
        '</div></td>' +
      '</tr>';
    }).join('');
  }

  var start = total === 0 ? 0 : (currentPage-1)*perPage + 1;
  var end   = Math.min(currentPage*perPage, total);
  document.getElementById('paginationInfo').textContent =
    'Affichage ' + start + '–' + end + ' sur ' + total + ' traitement' + (total > 1 ? 's' : '');

  var btnHtml = '<button class="page-btn" onclick="goPage(' + (currentPage-1) + ')" ' + (currentPage<=1?'disabled':'') + '><i class="bi bi-chevron-left"></i></button>';
  for (var i = 1; i <= pages; i++) {
    btnHtml += '<button class="page-btn ' + (i===currentPage?'active':'') + '" onclick="goPage(' + i + ')">' + i + '</button>';
  }
  btnHtml += '<button class="page-btn" onclick="goPage(' + (currentPage+1) + ')" ' + (currentPage>=pages?'disabled':'') + '><i class="bi bi-chevron-right"></i></button>';
  document.getElementById('paginationBtns').innerHTML = btnHtml;

  updateStats();
  updateSortHeaders();
}

function goPage(p) { currentPage = p; render(); }

function updateStats() {
  document.getElementById('statTotal').textContent = traitements.length;
  var total = traitements.filter(function(t){ return t.montant !== null; })
                         .reduce(function(s,t){ return s + t.montant; }, 0);
  document.getElementById('statMontant').textContent = total.toLocaleString('fr-FR');
  
  var rembourses = traitements.filter(function(t){ return t.decision === 'rembourse'; }).length;
  document.getElementById('statRembourses').textContent = rembourses;

  var refuses = traitements.filter(function(t){ return t.decision === 'refuse'; }).length;
  document.getElementById('statRefuses').textContent = refuses;
}

// ── Form helpers ─────────────────────────────────────────────────────────────

// ── Modal open/close ─────────────────────────────────────────────────────────
function openCreateModal() {
  editingId = null;
  document.getElementById('modalFormTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Ajouter un traitement';
  document.getElementById('btnSave').innerHTML = '<i class="bi bi-save"></i> Enregistrer';
  ['fSinistre','fDate','fAgent','fMontant','fMessage'].forEach(function(id){ document.getElementById(id).value = ''; });
  document.getElementById('fDecision').value = '';
  document.getElementById('fStatut').value   = 'en_cours';
  document.getElementById('fDate').value     = new Date().toISOString().split('T')[0];
  document.getElementById('sinistrePreview').style.display = 'none';
  clearAllErrors();
  openModal('modalForm');
}

function openEditModal(id) {
  var t = traitements.find(function(x){ return x.id == id; });
  if (!t) return;
  editingId = id;
  document.getElementById('modalFormTitle').innerHTML = '<i class="bi bi-pencil"></i> Modifier le traitement';
  document.getElementById('btnSave').innerHTML = '<i class="bi bi-save"></i> Mettre à jour';
  document.getElementById('fSinistre').value = t.sinistre;
  document.getElementById('fDate').value     = t.date;
  document.getElementById('fAgent').value    = t.agent;
  document.getElementById('fDecision').value = t.decision;
  document.getElementById('fMessage').value  = t.message_agent || '';
  document.getElementById('fMontant').value  = t.montant !== null ? t.montant : '';
  document.getElementById('fStatut').value   = t.statut || 'en_cours';
  document.getElementById('sinistrePreview').style.display = 'none';
  clearAllErrors();
  openModal('modalForm');
}

// ── Save (create or update) ───────────────────────────────────────────────────
async function saveTraitement() {
  var ok = true;

  // 1. Sinistre ID — basic numeric check only (DB check is advisory, not blocking)
  var sinVal = document.getElementById('fSinistre').value.trim();
  var sinId  = parseInt(sinVal);
  if (!sinVal || isNaN(sinId) || sinId <= 0) {
    showErr('fSinistre', 'errSinistre', "L'ID du sinistre est requis (nombre entier positif).");
    ok = false;
  } else {
    clearErr('fSinistre', 'errSinistre');
    var sinValid = await validateSinistreId(); // blocks if duplicate or not found
    if (!sinValid) ok = false;
  }

  // 2. Date
  var date = document.getElementById('fDate').value;
  if (!date) { showErr('fDate','errDate','La date est requise.'); ok = false; }
  else clearErr('fDate','errDate');

  // 3. Agent (advisory only — backend uses session user)
  clearErr('fAgent','errAgent');

  // 4. Décision
  var decVal = document.getElementById('fDecision').value;
  if (!decVal) { showErr('fDecision','errDecision','Choisissez une décision.'); ok = false; }
  else clearErr('fDecision','errDecision');

  // 5. Montant (required if rembourse)
  var montantV = document.getElementById('fMontant').value.trim();
  var montant  = montantV ? parseFloat(montantV) : null;
  if (decVal === 'rembourse' && !montantV) {
    showErr('fMontant','errMontant','Le montant est obligatoire pour un remboursement.');
    ok = false;
  } else if (montantV && (isNaN(montant) || montant < 0)) {
    showErr('fMontant','errMontant','Montant invalide.');
    ok = false;
  } else {
    clearErr('fMontant','errMontant');
  }

  if (!ok) return;

  var btn  = document.getElementById('btnSave');
  var orig = btn.innerHTML;
  btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Enregistrement...';
  btn.disabled  = true;

  var sinistre = document.getElementById('fSinistre').value.trim();
  var agent    = document.getElementById('fAgent').value.trim();
  var message  = document.getElementById('fMessage').value.trim();
  var statut   = document.getElementById('fStatut').value;

  var params = 'id_sinistre=' + encodeURIComponent(sinistre) +
               '&nom_agent='  + encodeURIComponent(agent) +
               '&decision='   + encodeURIComponent(decVal) +
               '&message_agent=' + encodeURIComponent(message) +
               '&montant='    + encodeURIComponent(montantV) +
               '&statut='     + encodeURIComponent(statut);
  if (editingId) params += '&id=' + editingId;

  var url = editingId ? TRAIT_UPDATE_API : TRAIT_CREATE_API;

  try {
    var res  = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: params
    });
    var json = await res.json();
    if (json.success) {
      closeModal('modalForm');
      await loadTraitements();
      showToast(editingId ? 'Traitement modifié.' : 'Traitement ajouté avec succès.', 'success');
    } else {
      showToast(json.message, 'danger');
    }
  } catch(e) {
    showToast('Erreur réseau.', 'danger');
  } finally {
    btn.innerHTML = orig;
    btn.disabled  = false;
  }
}

// ── Delete ────────────────────────────────────────────────────────────────────
function openDeleteModal(id) {
  deletingId = id;
  document.getElementById('deleteMsg').innerHTML =
    'Vous êtes sur le point de supprimer le traitement <strong>TR-' + String(id).padStart(3,'0') + '</strong>. Cette action est irréversible.';
  openModal('modalDelete');
}
async function confirmDelete() {
  try {
    var res  = await fetch(TRAIT_DELETE_API + '?id=' + deletingId, { method: 'GET' });
    var json = await res.json();
    closeModal('modalDelete');
    if (json.success) {
      await loadTraitements();
      showToast('Traitement supprimé.', 'danger');
    } else {
      showToast(json.message, 'danger');
    }
  } catch(e) {
    showToast('Erreur réseau.', 'danger');
  }
}

// ── View Sinistre ─────────────────────────────────────────────────────────────
async function openViewModal(sinistreId) {
  openModal('modalView');
  document.getElementById('viewContent').innerHTML = 
    '<div style="text-align:center;padding:40px;">' +
      '<i class="bi bi-hourglass" style="font-size:32px;opacity:0.5;display:block;margin-bottom:10px;"></i>' +
      '<p>Chargement...</p>' +
    '</div>';
  
  try {
    var res  = await fetch(SINISTRE_DETAILS_API + '?id=' + sinistreId);
    var json = await res.json();
    
    if (json.success) {
      var s = json.data;
      var photoHtml = '';
      if (s.photo_url) {
        photoHtml = '<img src="../../' + s.photo_url + '" style="width:100%;max-height:400px;border-radius:8px;object-fit:cover;margin-bottom:20px;" alt="Photo du sinistre">';
      } else {
        photoHtml = '<div style="width:100%;height:300px;background:var(--glass-bg);border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;border:1px solid var(--glass-border);"><i class="bi bi-image" style="font-size:48px;opacity:0.3;"></i></div>';
      }
      
      var statutColors = { en_attente: '#f4a261', rembourse: '#2ec4b6', refuse: '#e63946' };
      var statutLabels = { en_attente: 'En attente', rembourse: 'Remboursé', refuse: 'Refusé' };
      
      document.getElementById('modalViewTitle').innerHTML = '<i class="bi bi-shield-check"></i> Sinistre #' + s.id_sinistre;
      document.getElementById('viewContent').innerHTML = 
        '<div style="padding:0 20px 20px 20px;">' +
          photoHtml +
          '<div style="background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:8px;padding:16px;margin-bottom:16px;">' +
            '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;font-size:13px;">' +
              '<div><span style="color:var(--text-secondary);">Type:</span> <strong style="color:#fff;display:block;margin-top:4px;">' + s.type + '</strong></div>' +
              '<div><span style="color:var(--text-secondary);">Statut:</span> <strong style="color:' + (statutColors[s.statut]||'#fff') + ';display:block;margin-top:4px;">' + (statutLabels[s.statut]||s.statut) + '</strong></div>' +
              '<div><span style="color:var(--text-secondary);">Date:</span> <strong style="color:#fff;display:block;margin-top:4px;">' + formatDate(s.date_declaration) + '</strong></div>' +
            '</div>' +
          '</div>' +
          '<div>' +
            '<div style="margin-bottom:8px;font-size:12px;color:var(--text-secondary);">Description:</div>' +
            '<div style="background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:8px;padding:12px;font-size:13px;line-height:1.6;color:#fff;word-wrap:break-word;">' + (s.description || '—') + '</div>' +
          '</div>' +
        '</div>';
    } else {
      document.getElementById('viewContent').innerHTML = 
        '<div style="text-align:center;padding:40px;color:var(--text-secondary);">' +
          '<i class="bi bi-exclamation-triangle" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.5;"></i>' +
          '<p>' + json.message + '</p>' +
        '</div>';
    }
  } catch(e) {
    document.getElementById('viewContent').innerHTML = 
      '<div style="text-align:center;padding:40px;color:var(--text-secondary);">' +
        '<i class="bi bi-exclamation-circle" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.5;"></i>' +
        '<p>Erreur lors du chargement des détails.</p>' +
      '</div>';
  }
}

// ── Export CSV ────────────────────────────────────────────────────────────────
function exportCSV() {
  var rows = [['ID','Sinistre','Date','Agent','Decision','Montant (DT)']];
  traitements.forEach(function(t) {
    rows.push(['TR-'+String(t.id).padStart(3,'0'), '#'+t.sinistre, t.date, t.agent, t.decision, t.montant !== null ? t.montant : '']);
  });
  var csv = rows.map(function(r){ return r.map(function(v){ return '"'+v+'"'; }).join(','); }).join('\n');
  var a = document.createElement('a');
  a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
  a.download = 'traitements.csv';
  a.click();
  showToast('Export CSV téléchargé.', 'success');
}

// ── Modal helpers ─────────────────────────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(function(m) {
      m.classList.remove('open'); document.body.style.overflow = '';
    });
  }
});
document.querySelectorAll('.modal-overlay').forEach(function(o) {
  o.addEventListener('click', function(e) {
    if (e.target === o) { o.classList.remove('open'); document.body.style.overflow = ''; }
  });
});


document.getElementById('searchInput').addEventListener('input', function(){ currentPage=1; render(); });
document.getElementById('filterDecision').addEventListener('change', function(){ currentPage=1; render(); });
document.getElementById('filterMontant').addEventListener('change', function(){ currentPage=1; render(); });

document.addEventListener('DOMContentLoaded', function() { loadTraitements(); });
