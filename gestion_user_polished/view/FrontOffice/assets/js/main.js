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

    //fetch("../../controller/get_user.php")
    //.then(res => res.json())
    //.then(data => {
        //document.getElementById("welcome").innerText =
            //"Bonjour, " + data.nom + " 👋";
    //});

    //});




});