/**
 * Validation JavaScript pour les formulaires CRUD
 * Validation commune pour Catégories, Formules et Contrats
 */

// ============================================
// VALIDATION CATÉGORIES
// ============================================
function validateCategorieForm(form) {
    const nom = form.querySelector('[name="nom_categorie"]');
    const description = form.querySelector('[name="description_categorie"]');

    // Validation du nom
    if (!nom || nom.value.trim() === '') {
        showError('Le nom de la catégorie est obligatoire');
        nom?.focus();
        return false;
    }

    if (nom.value.trim().length < 3) {
        showError('Le nom doit contenir au moins 3 caractères');
        nom.focus();
        return false;
    }

    if (nom.value.trim().length > 100) {
        showError('Le nom ne doit pas dépasser 100 caractères');
        nom.focus();
        return false;
    }

    // Validation de la description
    if (description && description.value.trim().length > 500) {
        showError('La description ne doit pas dépasser 500 caractères');
        description.focus();
        return false;
    }

    return true;
}

// ============================================
// VALIDATION FORMULES
// ============================================
function validateFormuleForm(form) {
    const nom = form.querySelector('[name="nom_formule"]');
    const description = form.querySelector('[name="description_formule"]');
    const prix = form.querySelector('[name="prix_formule"]');
    const categorie = form.querySelector('[name="id_categorie"]');

    // Validation du nom
    if (!nom || nom.value.trim() === '') {
        showError('Le nom de la formule est obligatoire');
        nom?.focus();
        return false;
    }

    if (nom.value.trim().length < 3) {
        showError('Le nom doit contenir au moins 3 caractères');
        nom.focus();
        return false;
    }

    if (nom.value.trim().length > 100) {
        showError('Le nom ne doit pas dépasser 100 caractères');
        nom.focus();
        return false;
    }

    // Validation de la description
    if (description && description.value.trim().length > 1000) {
        showError('La description ne doit pas dépasser 1000 caractères');
        description.focus();
        return false;
    }

    // Validation du prix
    if (!prix || prix.value.trim() === '') {
        showError('Le prix est obligatoire');
        prix?.focus();
        return false;
    }

    const prixValue = parseFloat(prix.value);
    if (isNaN(prixValue) || prixValue < 0) {
        showError('Le prix doit être un nombre positif');
        prix.focus();
        return false;
    }

    if (prixValue > 999999.99) {
        showError('Le prix ne doit pas dépasser 999 999,99');
        prix.focus();
        return false;
    }

    // Validation de la catégorie
    if (categorie && (!categorie.value || categorie.value <= 0)) {
        showError('Veuillez sélectionner une catégorie');
        categorie.focus();
        return false;
    }

    return true;
}

// ============================================
// VALIDATION CONTRATS
// ============================================
function validateContratForm(form) {
    const numero = form.querySelector('[name="numero_contrat"]');
    const type = form.querySelector('[name="type_contrat"]');
    const client = form.querySelector('[name="id_client"]');
    const categorie = form.querySelector('[name="id_categorie"]');
    const prime = form.querySelector('[name="prime_contrat"]');
    const franchise = form.querySelector('[name="franchise_contrat"]');
    const dateDebut = form.querySelector('[name="date_debut_contrat"]');
    const dateFin = form.querySelector('[name="date_fin_contrat"]');
    const statut = form.querySelector('[name="statut_contrat"]');

    // Validation du numéro de contrat
    if (!numero || numero.value.trim() === '') {
        showError('Le numéro de contrat est obligatoire');
        numero?.focus();
        return false;
    }

    if (!/^[A-Z0-9\-]{5,20}$/.test(numero.value.trim())) {
        showError('Le numéro doit contenir 5-20 caractères alphanumériques et tirets');
        numero.focus();
        return false;
    }

    // Validation du type
    if (!type || type.value.trim() === '') {
        showError('Le type de contrat est obligatoire');
        type?.focus();
        return false;
    }

    // Validation du client
    if (!client || !client.value || client.value <= 0) {
        showError('Veuillez sélectionner un client');
        client?.focus();
        return false;
    }

    // Validation de la catégorie
    if (!categorie || !categorie.value || categorie.value <= 0) {
        showError('Veuillez sélectionner une catégorie');
        categorie?.focus();
        return false;
    }

    // Validation de la prime
    if (!prime || prime.value.trim() === '') {
        showError('La prime est obligatoire');
        prime?.focus();
        return false;
    }

    const primeValue = parseFloat(prime.value);
    if (isNaN(primeValue) || primeValue < 0) {
        showError('La prime doit être un nombre positif');
        prime.focus();
        return false;
    }

    // Validation de la franchise
    if (!franchise || franchise.value.trim() === '') {
        showError('La franchise est obligatoire');
        franchise?.focus();
        return false;
    }

    const franchiseValue = parseFloat(franchise.value);
    if (isNaN(franchiseValue) || franchiseValue < 0) {
        showError('La franchise doit être un nombre positif');
        franchise.focus();
        return false;
    }

    // Validation des dates
    if (!dateDebut || dateDebut.value.trim() === '') {
        showError('La date de début est obligatoire');
        dateDebut?.focus();
        return false;
    }

    if (!dateFin || dateFin.value.trim() === '') {
        showError('La date de fin est obligatoire');
        dateFin?.focus();
        return false;
    }

    const dateDebutObj = new Date(dateDebut.value);
    const dateFinObj = new Date(dateFin.value);

    if (dateDebutObj >= dateFinObj) {
        showError('La date de fin doit être après la date de début');
        dateFin.focus();
        return false;
    }

    // Validation du statut
    if (!statut || statut.value.trim() === '') {
        showError('Le statut est obligatoire');
        statut?.focus();
        return false;
    }

    return true;
}

// ============================================
// UTILITAIRES
// ============================================
function showError(message) {
    alert('❌ Erreur de validation:\n\n' + message);
}

function showSuccess(message) {
    console.log('✅ ' + message);
}

function clearForm(form) {
    form.reset();
    removeAllErrors(form);
}

function removeAllErrors(form) {
    const errorElements = form.querySelectorAll('.error-message');
    errorElements.forEach(el => el.remove());

    const errorInputs = form.querySelectorAll('.input-error');
    errorInputs.forEach(el => el.classList.remove('input-error'));
}

function setFieldError(field, message) {
    if (!field) return;

    field.classList.add('input-error');

    const existingError = field.parentElement.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    const errorEl = document.createElement('small');
    errorEl.className = 'error-message';
    errorEl.style.color = '#d32f2f';
    errorEl.style.display = 'block';
    errorEl.textContent = message;

    field.parentElement.appendChild(errorEl);
}

// ============================================
// ATTACHMENT DES ÉVÉNEMENTS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            removeAllErrors(form);

            // Déterminer le type de formulaire
            const hasNomCategorie = form.querySelector('[name="nom_categorie"]');
            const hasNomFormule = form.querySelector('[name="nom_formule"]');
            const hasNumeroContrat = form.querySelector('[name="numero_contrat"]');

            let isValid = true;

            if (hasNomCategorie) {
                isValid = validateCategorieForm(form);
            } else if (hasNomFormule) {
                isValid = validateFormuleForm(form);
            } else if (hasNumeroContrat) {
                isValid = validateContratForm(form);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Validation en temps réel
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateFieldInRealTime(form, this);
            });
        });
    });
});

function validateFieldInRealTime(form, field) {
    const name = field.name;
    let isValid = true;
    let message = '';

    // Validation commune pour tous les champs texte
    if (field.type === 'text' || field.tagName === 'TEXTAREA') {
        if (field.hasAttribute('required') && field.value.trim() === '') {
            isValid = false;
            message = 'Ce champ est obligatoire';
        } else if (field.hasAttribute('minlength')) {
            const minLength = parseInt(field.getAttribute('minlength'));
            if (field.value.trim().length < minLength) {
                isValid = false;
                message = `Minimum ${minLength} caractères requis`;
            }
        } else if (field.hasAttribute('maxlength')) {
            const maxLength = parseInt(field.getAttribute('maxlength'));
            if (field.value.trim().length > maxLength) {
                isValid = false;
                message = `Maximum ${maxLength} caractères autorisés`;
            }
        }
    }

    // Validation pour les nombres
    if (field.type === 'number') {
        if (field.hasAttribute('required') && field.value.trim() === '') {
            isValid = false;
            message = 'Ce champ est obligatoire';
        } else if (field.value !== '') {
            const value = parseFloat(field.value);
            if (isNaN(value)) {
                isValid = false;
                message = 'Doit être un nombre valide';
            } else if (field.hasAttribute('min') && value < parseFloat(field.getAttribute('min'))) {
                isValid = false;
                message = `Minimum ${field.getAttribute('min')} requis`;
            } else if (field.hasAttribute('max') && value > parseFloat(field.getAttribute('max'))) {
                isValid = false;
                message = `Maximum ${field.getAttribute('max')} autorisé`;
            }
        }
    }

    // Validation pour les dates
    if (field.type === 'date') {
        if (field.hasAttribute('required') && field.value.trim() === '') {
            isValid = false;
            message = 'Ce champ est obligatoire';
        }
    }

    // Validation pour les selects
    if (field.tagName === 'SELECT' && field.hasAttribute('required')) {
        if (!field.value || field.value <= 0) {
            isValid = false;
            message = 'Veuillez sélectionner une option';
        }
    }

    if (!isValid) {
        field.classList.add('input-error');
        setFieldError(field, message);
    } else {
        field.classList.remove('input-error');
        const errorEl = field.parentElement.querySelector('.error-message');
        if (errorEl) errorEl.remove();
    }
}

// Confirmation avant suppression
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

// Formatage automatique des champs prix
document.addEventListener('DOMContentLoaded', function() {
    const priceInputs = document.querySelectorAll('input[name*="prix"], input[name*="prime"], input[name*="franchise"]');
    priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value !== '') {
                const value = parseFloat(this.value);
                if (!isNaN(value)) {
                    this.value = value.toFixed(2);
                }
            }
        });
    });
});
