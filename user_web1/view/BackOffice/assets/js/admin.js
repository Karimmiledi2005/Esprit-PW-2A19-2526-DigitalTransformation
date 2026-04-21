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
    // CLIENT : numero_client est généré automatiquement côté serveur, ne pas l'envoyer

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

function saveUser() {
    if (!validate()) return;
    const btn = document.getElementById('btnSaveUser');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Enregistrement...';
    btn.disabled = true;

    if (editingId) {
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
        // CLIENT : numero_client ne peut pas être modifié

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

            // Count-up animation for stat values
            function animateValue(id, end, duration = 800) {
                const el = document.getElementById(id);
                if (!el) return;
                const start = 0;
                const startTime = performance.now();
                
                function update(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easeOut = 1 - Math.pow(1 - progress, 3);
                    const current = Math.floor(start + (end - start) * easeOut);
                    el.textContent = current;
                    
                    if (progress < 1) {
                        requestAnimationFrame(update);
                    }
                }
                requestAnimationFrame(update);
            }

            // principales avec animation
            animateValue("totalUsers", s.total);
            animateValue("actifs", s.actifs);
            animateValue("bloques", s.bloques);
            animateValue("agents", s.agents);

            // bonus (si tu les affiches)
            const admins = document.getElementById("admins");
            const clients = document.getElementById("clients");

            if (admins) admins.textContent = s.admins;
            if (clients) clients.textContent = s.clients;
        })
        .catch(err => console.error(err));
}