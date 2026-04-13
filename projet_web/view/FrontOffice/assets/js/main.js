/* =============================================
   main.js — Scripts globaux Protex
   FrontOffice — Version corrigée & améliorée
   ============================================= */

document.addEventListener('DOMContentLoaded', function () {

    // ===== DATE DYNAMIQUE BREADCRUMB =====
    const breadcrumbSpan = document.querySelector('.page-breadcrumb span');
    if (breadcrumbSpan) {
        const now = new Date();
        const dateStr = now.toLocaleDateString('fr-FR', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
        // Capitalise la première lettre
        breadcrumbSpan.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
    }

    // ===== NAVIGATION ACTIVE (basée sur l'URL courante) =====
    const currentPage = location.pathname.split('/').pop() || 'client.html';
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });

    // ===== AVATAR DROPDOWN =====
    const avatarBtn = document.getElementById('avatarBtn');
const avatarDropdown = document.getElementById('avatarDropdown');

if (avatarBtn && avatarDropdown) {
    avatarBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        avatarDropdown.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
        if (!avatarDropdown.contains(e.target) && e.target !== avatarBtn) {
            avatarDropdown.classList.remove('open');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            avatarDropdown.classList.remove('open');
        }
    });
}

    // ===== UPLOAD PHOTO PROFIL =====
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const fileName = document.querySelector('.file-name');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                // Mettre à jour le nom du fichier affiché
                if (fileName) fileName.textContent = file.name;

                // Créer un img dynamiquement dans la div preview
                const reader = new FileReader();
                reader.onload = (ev) => {
                    avatarPreview.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = ev.target.result;
                    img.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:inherit;';
                    avatarPreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ===== TOGGLE MOT DE PASSE =====
    window.togglePassword = function () {
        const pwd = document.getElementById('password');
        const icon = document.querySelector('[onclick="togglePassword()"] i');
        if (!pwd) return;
        if (pwd.type === 'password') {
            pwd.type = 'text';
            if (icon) { icon.className = 'bi bi-eye-slash'; }
        } else {
            pwd.type = 'password';
            if (icon) { icon.className = 'bi bi-eye'; }
        }
    };

    // ===== SAUVEGARDE PROFIL (validation + fetch PHP) =====
    const saveProfileBtn = document.getElementById('saveProfile');
    if (saveProfileBtn) {
        saveProfileBtn.addEventListener('click', function (e) {
            e.preventDefault();

            const emailInput = document.getElementById('email');
            const telInput   = document.getElementById('phone');
            let valid = true;

            if (emailInput) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value.trim())) {
                    emailInput.style.borderColor = 'var(--danger)';
                    showToast('Email invalide', 'danger');
                    valid = false;
                } else {
                    emailInput.style.borderColor = '';
                }
            }

            if (telInput && telInput.value.trim() !== '') {
                const telRegex = /^(\+216\s?)?[2-9]\d{7}$/;
                const telClean = telInput.value.replace(/\s/g, '');
                if (!telRegex.test(telClean)) {
                    telInput.style.borderColor = 'var(--danger)';
                    showToast('Numéro de téléphone invalide', 'danger');
                    valid = false;
                } else {
                    telInput.style.borderColor = '';
                }
            }

            if (!valid) return;

            const originalHTML = saveProfileBtn.innerHTML;
            saveProfileBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Enregistrement...';
            saveProfileBtn.disabled = true;

            const data = {
                nom:       document.getElementById('nom').value.trim(),
                prenom:    document.getElementById('prenom').value.trim(),
                email:     document.getElementById('email').value.trim(),
                telephone: document.getElementById('phone')?.value.trim() || '',
                adresse:   document.getElementById('address')?.value.trim() || ''
            };

            fetch('../../controller/update_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                saveProfileBtn.innerHTML = originalHTML;
                saveProfileBtn.disabled = false;
                if (result.success) {
                    showToast('Profil mis à jour avec succès', 'success');
                } else {
                    showToast(result.message || 'Erreur lors de la mise à jour', 'danger');
                }
            })
            .catch(() => {
                saveProfileBtn.innerHTML = originalHTML;
                saveProfileBtn.disabled = false;
                showToast('Erreur réseau, réessayez', 'danger');
            });
        });
    }

    // ===== FERMER ALERTES =====
    document.querySelectorAll('.alert-close').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('.alert-banner').remove();
        });
    });

    // ===== ANIMATION PROGRESS BARS =====
    const bars = document.querySelectorAll('.progress-fill[data-width]');
    setTimeout(() => {
        bars.forEach(bar => {
            bar.style.width = bar.getAttribute('data-width') + '%';
        });
    }, 300);

    document.querySelectorAll('.progress-fill:not([data-width])').forEach(bar => {
        const w = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => { bar.style.width = w; }, 400);
    });

    // ===== TOAST NOTIFICATION =====
    window.showToast = function (message, type = 'success') {
        const icons = { success: 'check-circle', warning: 'exclamation-triangle', danger: 'x-circle' };
        const toast = document.createElement('div');
        toast.className = `toast-notif toast-${type}`;
        toast.innerHTML = `
            <i class="bi bi-${icons[type] || 'info-circle'}"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 50);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    };
    //document.addEventListener("DOMContentLoaded", function () {

    //fetch("http://localhost/projet_web/controller/get_user.php")
    //.then(res => res.json())
    //.then(data => {
        //document.getElementById("welcome").innerText =
            //"Bonjour, " + data.nom + " 👋";
    //});

    //});
    function deleteMyAccount() {
        fetch("../controller/delete_user.php", {
            method: "POST",
            credentials: "include"
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Compte supprimé");
                window.location.href = "../login.html";
            } else {
                alert(data.message);
            }
        });
    }
    // 🔹 Charger les données utilisateur
    fetch("../../controller/get_user.php")
    .then(res => res.json())
    .then(user => {
        document.getElementById("nom").value = user.nom;
        document.getElementById("prenom").value = user.prenom;
        document.getElementById("email").value = user.email;
        document.getElementById("phone").value = user.telephone;
        document.getElementById("address").value = user.adresse;
        document.getElementById("cin").value = user.cin;
        document.getElementById("date_naissance").value = user.date_naissance;
    })
    .catch(() => { /* page sans formulaire profil, on ignore */ });


});