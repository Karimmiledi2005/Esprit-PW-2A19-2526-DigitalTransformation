<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion Utilisateurs — Protex Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="/projet_web1/gestion_paiment_offre/view/BackOffice/assets/css/variables.css">
    <link rel="stylesheet" href="/projet_web1/gestion_paiment_offre/view/BackOffice/assets/css/base.css">
    <link rel="stylesheet" href="/projet_web1/gestion_paiment_offre/view/BackOffice/assets/css/layout.css">
    <link rel="stylesheet" href="/projet_web1/gestion_paiment_offre/view/BackOffice/assets/css/admin-users.css">

    <style>
        .sidebar-logo img {
            border-radius: 10px;
            object-fit: cover;
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="background"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="layout">

    <?php include __DIR__ . '/assets/includes/sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Gestion des utilisateurs</div>
                <div class="topbar-sub" id="topbarDate"></div>
            </div>
            <div class="topbar-actions">
                <a href="#" class="topbar-btn" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="notif-dot"></span>
                </a>
                <a href="#" class="topbar-btn" title="Aide">
                    <i class="bi bi-question-circle"></i>
                </a>
            </div>
        </div>

        <div class="content">
            <div class="page-header-bar">
                <div>
                    <div class="page-title">Utilisateurs</div>
                    <div class="page-breadcrumb">
                        <i class="bi bi-house"></i>
                        <a href="/projet_web1/gestion_paiment_offre/view/BackOffice/admin.html">Accueil</a>
                        <i class="bi bi-chevron-right" style="font-size:10px"></i>
                        <span>Utilisateurs</span>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="openModal('modalAdd')">
                    <i class="bi bi-person-plus"></i> Ajouter un utilisateur
                </button>
            </div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="bi bi-people"></i></div>
                    <div class="stat-value" id="statTotalUsers">24</div>
                    <div class="stat-label">Total utilisateurs</div>
                    <div class="stat-trend trend-up"><i class="bi bi-arrow-up"></i> +3 ce mois</div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon"><i class="bi bi-person-check"></i></div>
                    <div class="stat-value" id="statActifs">20</div>
                    <div class="stat-label">Comptes actifs</div>
                    <div class="stat-trend trend-up"><i class="bi bi-check-circle"></i> 83%</div>
                </div>

                <div class="stat-card gold">
                    <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
                    <div class="stat-value" id="statAgents">3</div>
                    <div class="stat-label">Agents</div>
                    <div class="stat-trend trend-up"><i class="bi bi-building"></i> 2 agences</div>
                </div>

                <div class="stat-card red">
                    <div class="stat-icon"><i class="bi bi-person-slash"></i></div>
                    <div class="stat-value" id="statBloques">4</div>
                    <div class="stat-label">Comptes bloqués</div>
                    <div class="stat-trend trend-down"><i class="bi bi-exclamation-triangle"></i> À vérifier</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-table"></i> Liste des utilisateurs
                    </div>
                    <button class="btn btn-outline btn-sm" onclick="exportUsers()">
                        <i class="bi bi-download"></i> Exporter
                    </button>
                </div>

                <div style="padding: 16px 24px; border-bottom: 1px solid var(--glass-border);">
                    <div class="toolbar">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchInput" placeholder="Rechercher par nom, email, CIN...">
                        </div>

                        <select class="filter-select" id="filterRole">
                            <option value="">Tous les rôles</option>
                            <option value="ADMIN">Admin</option>
                            <option value="AGENT">Agent</option>
                            <option value="CLIENT">Client</option>
                        </select>

                        <select class="filter-select" id="filterStatut">
                            <option value="">Tous les statuts</option>
                            <option value="actif">Actif</option>
                            <option value="bloque">Bloqué</option>
                        </select>

                        <button class="btn btn-outline btn-sm" onclick="resetFilters()">
                            <i class="bi bi-x-circle"></i> Réinitialiser
                        </button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>CIN</th>
                                <th>Téléphone</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Inscrit le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersBody"></tbody>
                    </table>
                </div>

                <div class="pagination">
                    <div class="pagination-info" id="paginationInfo"></div>
                    <div class="pagination-btns" id="paginationBtns"></div>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal-overlay" id="modalAdd">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title" id="modalAddTitle">
                <i class="bi bi-person-plus"></i> Ajouter un utilisateur
            </div>
            <button class="modal-close" onclick="closeModal('modalAdd')">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Nom *</label>
                <input type="text" class="form-control" id="fNom" placeholder="Ben Ali">
                <div class="form-error" id="errNom">Champ requis</div>
            </div>
            <div class="form-group">
                <label>Prénom *</label>
                <input type="text" class="form-control" id="fPrenom" placeholder="Ahmed">
                <div class="form-error" id="errPrenom">Champ requis</div>
            </div>
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" class="form-control" id="fEmail" placeholder="ahmed@example.com">
            <div class="form-error" id="errEmail">Email invalide</div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Téléphone</label>
                <input type="tel" class="form-control" id="fTel" placeholder="+216 XX XXX XXX">
            </div>
            <div class="form-group">
                <label>CIN</label>
                <input type="text" class="form-control" id="fCin" placeholder="12345678" maxlength="8">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Rôle *</label>
                <select class="form-control" id="fRole">
                    <option value="CLIENT">Client</option>
                    <option value="AGENT">Agent</option>
                    <option value="ADMIN">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select class="form-control" id="fStatut">
                    <option value="actif">Actif</option>
                    <option value="bloque">Bloqué</option>
                </select>
            </div>
        </div>

        <div class="form-group" id="pwdGroup">
            <label>Mot de passe *</label>
            <input type="password" class="form-control" id="fPassword" placeholder="Min. 8 caractères">
            <div class="form-hint">Minimum 8 caractères avec chiffres et lettres</div>
            <div class="form-error" id="errPassword">Mot de passe trop court (min 8 caractères)</div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalAdd')">Annuler</button>
            <button class="btn btn-primary" id="btnSaveUser" onclick="saveUser()">
                <i class="bi bi-save"></i> Enregistrer
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalView">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="bi bi-person-circle"></i> Fiche utilisateur</div>
            <button class="modal-close" onclick="closeModal('modalView')"><i class="bi bi-x"></i></button>
        </div>
        <div id="modalViewBody"></div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalView')">Fermer</button>
            <button class="btn btn-primary" id="btnEditFromView" onclick="editFromView()">
                <i class="bi bi-pencil"></i> Modifier
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay delete-modal" id="modalDelete">
    <div class="modal" style="text-align:center">
        <div class="delete-icon"><i class="bi bi-trash3"></i></div>
        <div class="delete-title">Supprimer l'utilisateur</div>
        <div class="delete-msg" id="deleteMsg"></div>
        <div class="modal-footer" style="justify-content:center; margin-top:28px">
            <button class="btn btn-outline" onclick="closeModal('modalDelete')">Annuler</button>
            <button class="btn btn-danger" id="btnConfirmDelete" onclick="confirmDelete()">
                <i class="bi bi-trash3"></i> Supprimer définitivement
            </button>
        </div>
    </div>
</div>

<script src="/projet_web1/gestion_paiment_offre/view/BackOffice/assets/js/main.js"></script>
<script>
const users = [
    { id:1,  nom:'Ben Ali',   prenom:'Ahmed',   email:'ahmed@example.com',     tel:'+216 20 123 456', cin:'12345678', role:'CLIENT', statut:'actif',  created:'2025-11-10' },
    { id:2,  nom:'Miledi',    prenom:'Karim',   email:'karim@protex.tn',       tel:'+216 55 987 654', cin:'87654321', role:'ADMIN',  statut:'actif',  created:'2025-10-01' },
    { id:3,  nom:'Trabelsi',  prenom:'Sarra',   email:'sarra@protex.tn',       tel:'+216 22 333 444', cin:'11223344', role:'AGENT',  statut:'actif',  created:'2025-11-15' },
    { id:4,  nom:'Chaabane',  prenom:'Yassine', email:'yassine@example.com',   tel:'+216 98 765 432', cin:'55667788', role:'CLIENT', statut:'bloque', created:'2025-12-03' },
    { id:5,  nom:'Mansouri',  prenom:'Leila',   email:'leila@example.com',     tel:'+216 23 456 789', cin:'44332211', role:'CLIENT', statut:'actif',  created:'2026-01-08' },
    { id:6,  nom:'Bouazizi',  prenom:'Mohamed', email:'med.bouazizi@gmail.com',tel:'+216 50 111 222', cin:'99887766', role:'AGENT',  statut:'actif',  created:'2026-01-20' },
    { id:7,  nom:'Hajji',     prenom:'Nour',    email:'nour.hajji@email.com',  tel:'+216 27 555 666', cin:'13572468', role:'CLIENT', statut:'actif',  created:'2026-02-05' },
    { id:8,  nom:'Ferchichi', prenom:'Slim',    email:'slim.f@email.com',      tel:'+216 20 999 000', cin:'24681357', role:'CLIENT', statut:'bloque', created:'2026-02-14' },
    { id:9,  nom:'Dridi',     prenom:'Amira',   email:'amira.dridi@email.com', tel:'+216 52 444 555', cin:'36925814', role:'CLIENT', statut:'actif',  created:'2026-03-01' },
    { id:10, nom:'Jendoubi',  prenom:'Hichem',  email:'hichem.j@protex.tn',    tel:'+216 25 888 999', cin:'14725836', role:'AGENT',  statut:'actif',  created:'2026-03-10' }
];

let nextId = 11;
let currentPage = 1;
const perPage = 6;
let editingId = null;
let deletingId = null;
let viewingId = null;

document.getElementById('topbarDate').textContent =
    new Date().toLocaleDateString('fr-FR', {
        weekday:'long',
        day:'numeric',
        month:'long',
        year:'numeric'
    });

function updateStats() {
    const total = users.length;
    const actifs = users.filter(u => u.statut === 'actif').length;
    const agents = users.filter(u => u.role === 'AGENT').length;
    const bloques = users.filter(u => u.statut === 'bloque').length;

    document.getElementById('statTotalUsers').textContent = total;
    document.getElementById('statActifs').textContent = actifs;
    document.getElementById('statAgents').textContent = agents;
    document.getElementById('statBloques').textContent = bloques;
}

function getFiltered() {
    const search = document.getElementById('searchInput').value.trim().toLowerCase();
    const role = document.getElementById('filterRole').value;
    const statut = document.getElementById('filterStatut').value;

    return users.filter(u => {
        const txt = `${u.nom} ${u.prenom} ${u.email} ${u.cin}`.toLowerCase();
        return (!search || txt.includes(search))
            && (!role || u.role === role)
            && (!statut || u.statut === statut);
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterRole').value = '';
    document.getElementById('filterStatut').value = '';
    currentPage = 1;
    render();
}

function initials(u) {
    return `${(u.prenom || '').charAt(0)}${(u.nom || '').charAt(0)}`.toUpperCase();
}

function escapeHtml(text) {
    return String(text ?? '').replace(/[&<>"']/g, c => ({
        '&':'&amp;',
        '<':'&lt;',
        '>':'&gt;',
        '"':'&quot;',
        "'":'&#39;'
    }[c]));
}

function formatDate(d) {
    const date = new Date(d);
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString('fr-FR', { day:'2-digit', month:'short', year:'numeric' });
}

function render() {
    const filtered = getFiltered();
    const total = filtered.length;
    const pages = Math.max(1, Math.ceil(total / perPage));

    if (currentPage > pages) currentPage = pages;

    const slice = filtered.slice((currentPage - 1) * perPage, currentPage * perPage);
    const tbody = document.getElementById('usersBody');

    tbody.innerHTML = slice.length === 0
        ? `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-secondary)">
                <i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:8px"></i>
                Aucun utilisateur trouvé
           </td></tr>`
        : slice.map(u => `
            <tr>
                <td>
                    <div class="user-cell">
                        <div class="user-avatar-sm">${escapeHtml(initials(u))}</div>
                        <div>
                            <div class="user-name-cell">${escapeHtml(u.prenom)} ${escapeHtml(u.nom)}</div>
                            <div class="user-email-cell">${escapeHtml(u.email)}</div>
                        </div>
                    </div>
                </td>
                <td style="color:var(--text-secondary)">${escapeHtml(u.cin)}</td>
                <td style="color:var(--text-secondary)">${escapeHtml(u.tel)}</td>
                <td><span class="badge badge-${u.role.toLowerCase()}">${escapeHtml(u.role)}</span></td>
                <td>
                    <span class="badge badge-${u.statut}">
                        <i class="bi bi-${u.statut === 'actif' ? 'check-circle' : 'slash-circle'}"></i>
                        ${u.statut.charAt(0).toUpperCase() + u.statut.slice(1)}
                    </span>
                </td>
                <td style="color:var(--text-secondary)">${formatDate(u.created)}</td>
                <td>
                    <div class="actions">
                        <button class="btn btn-outline btn-sm" title="Voir" onclick="viewUser(${u.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline btn-sm" title="Modifier" onclick="editUser(${u.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-${u.statut === 'actif' ? 'warning' : 'success'} btn-sm"
                                title="${u.statut === 'actif' ? 'Bloquer' : 'Débloquer'}"
                                onclick="toggleStatut(${u.id})">
                            <i class="bi bi-${u.statut === 'actif' ? 'lock' : 'unlock'}"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Supprimer" onclick="deleteUser(${u.id})">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

    const start = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
    const end = Math.min(currentPage * perPage, total);

    document.getElementById('paginationInfo').textContent =
        `Affichage ${start}–${end} sur ${total} utilisateur${total > 1 ? 's' : ''}`;

    const btns = document.getElementById('paginationBtns');
    btns.innerHTML = `
        <button class="page-btn" onclick="goPage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>
            <i class="bi bi-chevron-left"></i>
        </button>
        ${Array.from({length: pages}, (_, i) =>
            `<button class="page-btn ${i + 1 === currentPage ? 'active' : ''}" onclick="goPage(${i + 1})">${i + 1}</button>`
        ).join('')}
        <button class="page-btn" onclick="goPage(${currentPage + 1})" ${currentPage >= pages ? 'disabled' : ''}>
            <i class="bi bi-chevron-right"></i>
        </button>
    `;

    updateStats();
}

function goPage(p) {
    const filtered = getFiltered();
    const pages = Math.max(1, Math.ceil(filtered.length / perPage));
    if (p < 1 || p > pages) return;
    currentPage = p;
    render();
}

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('open');
    document.body.style.overflow = '';
    if (id === 'modalAdd') resetForm();
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(m => {
            m.classList.remove('open');
        });
        document.body.style.overflow = '';
    }
});

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) {
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
});

function resetForm() {
    ['fNom','fPrenom','fEmail','fTel','fCin','fPassword'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.value = '';
            el.classList.remove('error');
        }
    });

    document.getElementById('fRole').value = 'CLIENT';
    document.getElementById('fStatut').value = 'actif';
    document.querySelectorAll('.form-error').forEach(e => e.classList.remove('show'));
    document.getElementById('pwdGroup').style.display = '';
    document.getElementById('modalAddTitle').innerHTML = '<i class="bi bi-person-plus"></i> Ajouter un utilisateur';
    document.getElementById('btnSaveUser').innerHTML = '<i class="bi bi-save"></i> Enregistrer';
    editingId = null;
}

function showErr(inputId, errId, show) {
    const input = document.getElementById(inputId);
    const err = document.getElementById(errId);

    if (input) input.classList.toggle('error', show);
    if (err) err.classList.toggle('show', show);
}

function validate() {
    let ok = true;

    const nom = document.getElementById('fNom').value.trim();
    const prenom = document.getElementById('fPrenom').value.trim();
    const email = document.getElementById('fEmail').value.trim();
    const cin = document.getElementById('fCin').value.trim();
    const pwd = document.getElementById('fPassword').value;

    if (!nom) {
        showErr('fNom', 'errNom', true);
        ok = false;
    } else {
        showErr('fNom', 'errNom', false);
    }

    if (!prenom) {
        showErr('fPrenom', 'errPrenom', true);
        ok = false;
    } else {
        showErr('fPrenom', 'errPrenom', false);
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showErr('fEmail', 'errEmail', true);
        ok = false;
    } else {
        showErr('fEmail', 'errEmail', false);
    }

    if (cin && !/^\d{8}$/.test(cin)) {
        showToast('Le CIN doit contenir exactement 8 chiffres.', 'warning');
        ok = false;
    }

    if (!editingId && pwd.length < 8) {
        showErr('fPassword', 'errPassword', true);
        ok = false;
    } else {
        showErr('fPassword', 'errPassword', false);
    }

    return ok;
}

function saveUser() {
    if (!validate()) return;

    const btn = document.getElementById('btnSaveUser');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Enregistrement...';
    btn.disabled = true;

    setTimeout(() => {
        const data = {
            nom: document.getElementById('fNom').value.trim(),
            prenom: document.getElementById('fPrenom').value.trim(),
            email: document.getElementById('fEmail').value.trim(),
            tel: document.getElementById('fTel').value.trim() || '—',
            cin: document.getElementById('fCin').value.trim() || '—',
            role: document.getElementById('fRole').value,
            statut: document.getElementById('fStatut').value
        };

        if (editingId) {
            const idx = users.findIndex(u => u.id === editingId);
            if (idx !== -1) {
                users[idx] = { ...users[idx], ...data };
            }
            showToast('Utilisateur modifié avec succès', 'success');
        } else {
            users.unshift({
                id: nextId++,
                created: new Date().toISOString().split('T')[0],
                ...data
            });
            showToast('Utilisateur ajouté avec succès', 'success');
        }

        btn.innerHTML = orig;
        btn.disabled = false;
        closeModal('modalAdd');
        render();
    }, 700);
}

function editUser(id) {
    const u = users.find(u => u.id === id);
    if (!u) return;

    editingId = id;
    document.getElementById('fNom').value = u.nom;
    document.getElementById('fPrenom').value = u.prenom;
    document.getElementById('fEmail').value = u.email;
    document.getElementById('fTel').value = u.tel !== '—' ? u.tel : '';
    document.getElementById('fCin').value = u.cin !== '—' ? u.cin : '';
    document.getElementById('fRole').value = u.role;
    document.getElementById('fStatut').value = u.statut;
    document.getElementById('pwdGroup').style.display = 'none';
    document.getElementById('modalAddTitle').innerHTML = '<i class="bi bi-pencil"></i> Modifier l’utilisateur';
    document.getElementById('btnSaveUser').innerHTML = '<i class="bi bi-save"></i> Mettre à jour';

    openModal('modalAdd');
}

function editFromView() {
    closeModal('modalView');
    if (viewingId !== null) editUser(viewingId);
}

function field(icon, label, val) {
    return `
        <div style="background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:var(--radius-md);padding:14px">
            <div style="font-size:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px">
                <i class="bi ${icon}" style="margin-right:5px"></i>${label}
            </div>
            <div style="font-size:14px;color:#fff;font-weight:500">${escapeHtml(val)}</div>
        </div>
    `;
}

function viewUser(id) {
    const u = users.find(u => u.id === id);
    if (!u) return;

    viewingId = id;
    document.getElementById('modalViewBody').innerHTML = `
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--glass-border)">
            <div class="user-avatar-sm" style="width:56px;height:56px;font-size:18px">${escapeHtml(initials(u))}</div>
            <div>
                <div style="font-family:var(--font-display);font-size:17px;font-weight:700;color:#fff">${escapeHtml(u.prenom)} ${escapeHtml(u.nom)}</div>
                <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">${escapeHtml(u.email)}</div>
                <div style="margin-top:8px;display:flex;gap:8px">
                    <span class="badge badge-${u.role.toLowerCase()}">${escapeHtml(u.role)}</span>
                    <span class="badge badge-${u.statut}">
                        <i class="bi bi-${u.statut === 'actif' ? 'check-circle' : 'slash-circle'}"></i>
                        ${escapeHtml(u.statut)}
                    </span>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            ${field('bi-phone','Téléphone',u.tel)}
            ${field('bi-card-text','CIN',u.cin)}
            ${field('bi-calendar','Inscrit le',formatDate(u.created))}
            ${field('bi-shield-check','Statut',u.statut.charAt(0).toUpperCase() + u.statut.slice(1))}
        </div>
    `;

    openModal('modalView');
}

function toggleStatut(id) {
    const u = users.find(u => u.id === id);
    if (!u) return;

    u.statut = u.statut === 'actif' ? 'bloque' : 'actif';
    showToast(`Compte ${u.statut === 'actif' ? 'débloqué' : 'bloqué'}`, u.statut === 'actif' ? 'success' : 'warning');
    render();
}

function deleteUser(id) {
    const u = users.find(u => u.id === id);
    if (!u) return;

    deletingId = id;
    document.getElementById('deleteMsg').innerHTML =
        `Vous êtes sur le point de supprimer <span class="delete-name">${escapeHtml(u.prenom)} ${escapeHtml(u.nom)}</span>.<br>Cette action est irréversible.`;

    openModal('modalDelete');
}

function confirmDelete() {
    const btn = document.getElementById('btnConfirmDelete');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Suppression...';
    btn.disabled = true;

    setTimeout(() => {
        const idx = users.findIndex(u => u.id === deletingId);
        if (idx !== -1) {
            users.splice(idx, 1);
        }

        btn.innerHTML = orig;
        btn.disabled = false;
        closeModal('modalDelete');
        showToast('Utilisateur supprimé', 'danger');
        render();
    }, 700);
}

function exportUsers() {
    const filtered = getFiltered();
    const header = ['Nom', 'Prénom', 'Email', 'Téléphone', 'CIN', 'Rôle', 'Statut', 'Date'];
    const rows = filtered.map(u => [u.nom, u.prenom, u.email, u.tel, u.cin, u.role, u.statut, u.created]);
    const csv = [header, ...rows]
        .map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(';'))
        .join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'utilisateurs.csv';
    link.click();
    URL.revokeObjectURL(url);

    showToast('Export réalisé avec succès', 'success');
}

function showToast(message, type = 'success') {
    const icons = {
        success:'check-circle',
        warning:'exclamation-triangle',
        danger:'x-circle'
    };

    const t = document.createElement('div');
    t.className = `toast-notif toast-${type}`;
    t.innerHTML = `<i class="bi bi-${icons[type]}"></i><span>${escapeHtml(message)}</span>`;
    document.body.appendChild(t);

    setTimeout(() => t.classList.add('show'), 50);
    setTimeout(() => {
        t.classList.remove('show');
        setTimeout(() => t.remove(), 300);
    }, 3000);
}

function syncActiveNav() {
    const path = window.location.pathname.toLowerCase();

    if (path.includes('paiements')) {
        document.getElementById('navPaiements')?.classList.add('active');
    }

    if (path.includes('offres')) {
        document.getElementById('navOffres')?.classList.add('active');
    }

    if (path.includes('devis')) {
        document.getElementById('navDevis')?.classList.add('active');
    }

    if (path.includes('admin-users')) {
        document.getElementById('navUsers')?.classList.add('active');
    }
}

document.getElementById('searchInput').addEventListener('input', () => {
    currentPage = 1;
    render();
});

document.getElementById('filterRole').addEventListener('change', () => {
    currentPage = 1;
    render();
});

document.getElementById('filterStatut').addEventListener('change', () => {
    currentPage = 1;
    render();
});

syncActiveNav();
render();
</script>

</body>
</html>