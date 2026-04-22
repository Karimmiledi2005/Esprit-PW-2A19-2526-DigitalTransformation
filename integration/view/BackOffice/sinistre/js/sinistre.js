// ===== SINISTRE PAGE LOGIC =====

// ── API ──────────────────────────────────────────────────────────────────────
const SINISTRE_LIST_API         = 'sinistre_list.php';
const SINISTRE_UPDATE_STATUT_API = 'sinistre_update_statut.php';
const SINISTRE_CREATE_API       = 'sinistre_create.php';
const SINISTRE_DELETE_API       = 'sinistre_delete.php';

let sinistres = [];   // chargé depuis la BDD
let nextNum = 1000, currentPage = 1, deletingId = null;
const perPage = 8;

const STATUT_LABELS = { en_attente:'En attente', rembourse:'Remboursé', refuse:'Refusé' };
const STATUT_BADGE  = { en_attente:'badge-agent', rembourse:'badge-actif', refuse:'badge-admin' };
const TYPE_ICONS    = { 'Accident auto':'bi-car-front','Incendie':'bi-fire','Vol':'bi-lock','Degat des eaux':'bi-droplet' };

// ── Chargement depuis la BDD ─────────────────────────────────────────────────
async function loadSinistres() {
    try {
        const res  = await fetch(SINISTRE_LIST_API + '?_=' + Date.now());
        const json = await res.json();
        if (json.success) {
            sinistres = json.data.map(s => ({
                id:          s.id_sinistre,
                contrat:     s.numero_contrat,
                type:        s.type,
                date:        s.date_declaration,
                statut:      s.statut,
                description: s.description,
                client:      s.client_nom || '—',
            }));
            currentPage = 1;
            render();
        } else {
            showToast('Erreur chargement: ' + json.message, 'danger');
        }
    } catch(e) {
        showToast('Impossible de contacter le serveur PHP.', 'danger');
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

// Date topbar
const now = new Date();
document.getElementById('topbarDate').textContent =
  now.toLocaleDateString('fr-FR',{weekday:'long',day:'numeric',month:'long',year:'numeric'});

function getFiltered() {
  const q    = document.getElementById('searchInput').value.toLowerCase();
  const stat = document.getElementById('filterStatut').value;
  const type = document.getElementById('filterType').value;
  const date = document.getElementById('filterDate').value;
  return sinistres.filter(s =>
    (!q    || String(s.id).includes(q) || s.contrat.toLowerCase().includes(q) || s.type.toLowerCase().includes(q) || (s.client||'').toLowerCase().includes(q)) &&
    (!stat || s.statut === stat) &&
    (!type || s.type === type) &&
    (!date || s.date === date)
  );
}

function resetFilters(){
  ['searchInput','filterStatut','filterType','filterDate'].forEach(id=>document.getElementById(id).value='');
  currentPage=1; render();
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
        <td>
          <div class="actions">
            <button class="btn btn-outline btn-sm" onclick="viewSinistre(${s.id})" title="Voir"><i class="bi bi-eye"></i></button>
            <button class="btn btn-danger  btn-sm" onclick="openDeleteModal(${s.id})" title="Supprimer"><i class="bi bi-trash3"></i></button>
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
    </div>`;
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
