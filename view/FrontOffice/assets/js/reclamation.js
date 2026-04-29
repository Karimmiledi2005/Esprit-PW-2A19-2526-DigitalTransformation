// ── Date dans le header ──
const ds = new Date().toLocaleDateString('fr-FR', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
});
const dateEl = document.getElementById('currentDate');
if (dateEl) {
    dateEl.textContent = ds.charAt(0).toUpperCase() + ds.slice(1);
}

// ── Filtres live (reclamationList.php uniquement) ──
function filterCards() {
    const search = (document.getElementById('searchInput')?.value || '').toLowerCase();
    const statut = (document.getElementById('filterStatut')?.value || '').toLowerCase();
    const type   = (document.getElementById('filterType')?.value   || '').toLowerCase();

    document.querySelectorAll('.rec-card').forEach(card => {
        const cardText   = card.textContent.toLowerCase();
        const cardStatut = (card.dataset.statut || '').toLowerCase();
        const cardType   = (card.dataset.type   || '').toLowerCase();

        const matchSearch = !search || cardText.includes(search);
        const matchStatut = !statut || cardStatut === statut;
        const matchType   = !type   || cardType   === type;

        card.style.display = (matchSearch && matchStatut && matchType) ? '' : 'none';
    });
}

const searchInput   = document.getElementById('searchInput');
const filterStatut  = document.getElementById('filterStatut');
const filterType    = document.getElementById('filterType');

if (searchInput)  searchInput.addEventListener('input',   filterCards);
if (filterStatut) filterStatut.addEventListener('change', filterCards);
if (filterType)   filterType.addEventListener('change',   filterCards);
