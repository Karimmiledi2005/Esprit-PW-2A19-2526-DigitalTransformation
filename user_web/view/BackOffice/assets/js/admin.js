// =============================================
//  admin.js — Toutes les actions admin
// =============================================

// ===== AJOUTER UN UTILISATEUR =====
function saveUserAdd() {
    const data = new FormData();
    const role = document.getElementById('fRole').value;

    data.append('nom',       document.getElementById('fNom').value.trim());
    data.append('prenom',    document.getElementById('fPrenom').value.trim());
    data.append('email',     document.getElementById('fEmail').value.trim());
    data.append('telephone', document.getElementById('fTel').value.trim());
    data.append('cin',       document.getElementById('fCin').value.trim());
    data.append('role',      role);
    data.append('statut',    document.getElementById('fStatut').value);
    data.append('password',  document.getElementById('fPassword').value);

    if (role === 'ADMIN')  data.append('niveau_acces',  document.getElementById('fNiveau').value || 1);
    if (role === 'AGENT')  { data.append('agence', document.getElementById('fAgence').value); data.append('salaire', document.getElementById('fSalaire').value); }
    if (role === 'CLIENT') data.append('numero_client', document.getElementById('fNumClient').value);

    fetch('admin_add_user.php', { method: 'POST', body: data })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                showToast('Utilisateur ajouté avec succès', 'success');
                closeModal('modalAdd');
                loadUsers();
                loadStats();
            } else {
                alert('Erreur : ' + res.message);
            }
        })
        .catch(err => console.error(err));
}

// ===== SAUVEGARDER (ADD / EDIT) =====
function saveUser() {
    if (!validate()) return;
    const btn = document.getElementById('btnSaveUser');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Enregistrement...';
    btn.disabled = true;

    if (editingId) {
        // EDIT → admin_update_user.php
        const role = document.getElementById('fRole').value;
        const data = new FormData();
        data.append('id_user',   editingId);
        data.append('nom',       document.getElementById('fNom').value.trim());
        data.append('prenom',    document.getElementById('fPrenom').value.trim());
        data.append('email',     document.getElementById('fEmail').value.trim());
        data.append('telephone', document.getElementById('fTel').value.trim());
        data.append('cin',       document.getElementById('fCin').value.trim());
        data.append('role',      role);
        data.append('statut',    document.getElementById('fStatut').value);

        if (role === 'ADMIN')  data.append('niveau_acces',  document.getElementById('fNiveau').value || 1);
        if (role === 'AGENT')  { data.append('agence', document.getElementById('fAgence').value); data.append('salaire', document.getElementById('fSalaire').value); }
        if (role === 'CLIENT') data.append('numero_client', document.getElementById('fNumClient').value);

        fetch('admin_update_user.php', { method: 'POST', body: data })
            .then(res => res.json())
            .then(res => {
                btn.innerHTML = orig; btn.disabled = false;
                if (res.success) {
                    showToast('Utilisateur modifié avec succès', 'success');
                    closeModal('modalAdd');
                    loadUsers();
                    loadStats();
                } else {
                    alert('Erreur : ' + res.message);
                }
            })
            .catch(err => { btn.innerHTML = orig; btn.disabled = false; console.error(err); });
    } else {
        // ADD
        btn.innerHTML = orig; btn.disabled = false;
        saveUserAdd();
        loadStats();
    }
}

// ===== BLOQUER / DÉBLOQUER =====
function toggleStatut(id) {
    const data = new FormData();
    data.append('id_user', id);

    fetch('admin_toggle_statut.php', { method: 'POST', body: data })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                const u = users.find(u => u.id === id);
                if (u) u.statut = res.statut;
                showToast(res.statut === 'actif' ? 'Compte débloqué' : 'Compte bloqué',
                          res.statut === 'actif' ? 'success' : 'warning');
                render();
                loadStats();
            } else {
                alert('Erreur : ' + res.message);
            }
        })
        .catch(err => console.error(err));
}

// ===== SUPPRIMER =====
function deleteUser(id) {
    const u = users.find(u => u.id === id);
    if (!u) return;
    deletingId = id;
    document.getElementById('deleteMsg').innerHTML =
        `Vous êtes sur le point de supprimer <span class="delete-name">${u.prenom} ${u.nom}</span>.<br>Cette action est irréversible.`;
    openModal('modalDelete');
    loadStats();
}

function confirmDelete() {
    const btn = document.getElementById('btnConfirmDelete');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Suppression...';
    btn.disabled = true;

    const data = new FormData();
    data.append('id_user', deletingId);

    fetch('admin_delete_user.php', { method: 'POST', body: data })
        .then(res => res.json())
        .then(res => {
            btn.innerHTML = orig; btn.disabled = false;
            if (res.success) {
                users = users.filter(u => u.id !== deletingId);
                closeModal('modalDelete');
                showToast('Utilisateur supprimé', 'danger');
                render();
            } else {
                alert('Erreur : ' + res.message);
            }
        })
        .catch(err => { btn.innerHTML = orig; btn.disabled = false; console.error(err); });
        
}
function loadStats() {
    fetch('get_stats.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            const s = data.stats;

            // principales
            document.getElementById("totalUsers").textContent = s.total;
            document.getElementById("actifs").textContent = s.actifs;
            document.getElementById("bloques").textContent = s.bloques;
            document.getElementById("agents").textContent = s.agents;

            // bonus (si tu les affiches)
            const admins = document.getElementById("admins");
            const clients = document.getElementById("clients");

            if (admins) admins.textContent = s.admins;
            if (clients) clients.textContent = s.clients;
        })
        .catch(err => console.error(err));
}