/**
 * reclamation-validation.js
 * Contrôle de saisie pour la partie Réclamation (FrontOffice)
 * ─────────────────────────────────────────────────────────────
 * Couvre deux formulaires :
 *   1. addReclamation.php    → ajout d'une réclamation
 *   2. updateReclamation.php → modification d'une réclamation
 *
 * Champs validés :
 *   #fObjet     / #objet_error
 *   #fType      (select — valeurs autorisées)
 *   #fPriorite  (select — valeurs autorisées)
 *   #fDesc      / #desc_error
 *
 * Note : l'email n'est plus saisi par le client — il est récupéré
 * automatiquement depuis la session lors de l'intégration du module auth.
 */

var ReclamationValidation = (function () {

  /* ── Constantes ─────────────────────────────────────────────── */
  var OBJET_MIN = 3;
  var OBJET_MAX = 100;
  var DESC_MIN  = 10;
  var DESC_MAX  = 1000;
  var EMAIL_MAX = 150;

  var TYPES_AUTORISES      = ['Santé', 'Auto', 'Habitation', 'Autre'];
  var PRIORITES_AUTORISEES = ['Normale', 'Urgente', 'Faible'];
  var REF_CONTRAT_PATTERN = /^REF-[A-Z0-9]{4}-[A-Z0-9]{3}$/i;
  var EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  /* ── Messages ───────────────────────────────────────────────── */
  var MSG = {
    required        : 'Ce champ est obligatoire.',
    objetLetters    : "L'objet doit contenir uniquement des lettres.",
    objetLength     : "L'objet doit contenir entre " + OBJET_MIN + " et " + OBJET_MAX + " caractères.",
    descTooShort    : 'Description trop courte (min. ' + DESC_MIN + ' caractères).',
    descTooLong     : 'Description trop longue (max. ' + DESC_MAX + ' caractères).',
    typeInvalid     : 'Type de réclamation invalide.',
    prioriteInvalid : 'Priorité invalide.',
    noHtml          : 'Les balises HTML ne sont pas autorisées.',
    onlySpaces      : 'Ce champ ne peut pas contenir uniquement des espaces.',
    refRequired : 'La référence contrat est obligatoire.',
    refFormat   : 'Format invalide. Exemple attendu : REF-2024-001',
    emailRequired   : "L'adresse email est obligatoire.",
    emailInvalid    : "L'adresse email est invalide (ex : client@exemple.com).",
    emailTooLong    : "L'adresse email est trop longue (max. " + EMAIL_MAX + " caractères).",
    ok              : ''
  };

  /* ── Helpers DOM ────────────────────────────────────────────── */
  function $(id) { return document.getElementById(id); }

  function showError(errEl, msg) {
    if (!errEl) return;
    errEl.textContent = '❌ ' + msg;
    errEl.style.color   = '';
    errEl.style.display = 'block';
  }

  function showOk(errEl, msg) {
    if (!errEl) return;
    errEl.textContent = '✅ ' + (msg || 'Valide');
    errEl.style.color   = 'var(--success, #22c55e)';
    errEl.style.display = 'block';
  }

  function markInvalid(field) { if (field) field.classList.add('is-invalid'); }
  function markValid(field)   { if (field) field.classList.remove('is-invalid'); }

  /* ── Compteur de caractères ─────────────────────────────────── */
  function attachCharCounter(fieldId, max, counterId) {
    var field   = $(fieldId);
    var counter = $(counterId);
    if (!field || !counter) return;
    function update() {
      var len = field.value.length;
      counter.textContent = len + ' / ' + max;
      counter.style.color = (len > max) ? 'var(--danger, #ef4444)' : 'var(--text-secondary, #94a3b8)';
    }
    field.addEventListener('input', update);
    update();
  }

  /* ── Règles de validation individuelles ─────────────────────── */

  function validateObjet(silent) {
    var field = $('fObjet');
    var errEl = $('objet_error');
    if (!field) return true;

    var val = field.value.trim();

    if (val === '') {
      markInvalid(field); if (!silent) showError(errEl, MSG.required); return false;
    }
    if (/^\s+$/.test(field.value)) {
      markInvalid(field); if (!silent) showError(errEl, MSG.onlySpaces); return false;
    }
    if (/<[^>]+>/.test(val)) {
      markInvalid(field); if (!silent) showError(errEl, MSG.noHtml); return false;
    }
    if (!/^[a-zA-ZÀ-ÿ\s]+$/u.test(val)) {
      markInvalid(field); if (!silent) showError(errEl, MSG.objetLetters); return false;
    }
    if (val.length < OBJET_MIN || val.length > OBJET_MAX) {
      markInvalid(field); if (!silent) showError(errEl, MSG.objetLength); return false;
    }

    markValid(field); showOk(errEl, 'Objet valide'); return true;
  }

  function validateType() {
    var field = $('fType');
    if (!field) return true;
    if (TYPES_AUTORISES.indexOf(field.value) === -1) {
      markInvalid(field); return false;
    }
    markValid(field); return true;
  }

  function validatePriorite() {
    var field = $('fPriorite');
    if (!field) return true;
    if (PRIORITES_AUTORISEES.indexOf(field.value) === -1) {
      markInvalid(field); return false;
    }
    markValid(field); return true;
  }

  function validateDesc(silent) {
    var field = $('fDesc');
    var errEl = $('desc_error');
    if (!field) return true;

    var val = field.value.trim();

    if (val === '') {
      markInvalid(field); if (!silent) showError(errEl, MSG.required); return false;
    }
    if (/^\s+$/.test(field.value)) {
      markInvalid(field); if (!silent) showError(errEl, MSG.onlySpaces); return false;
    }
    if (/<[^>]+>/.test(val)) {
      markInvalid(field); if (!silent) showError(errEl, MSG.noHtml); return false;
    }
    if (val.length < DESC_MIN) {
      markInvalid(field); if (!silent) showError(errEl, MSG.descTooShort); return false;
    }
    if (val.length > DESC_MAX) {
      markInvalid(field); if (!silent) showError(errEl, MSG.descTooLong); return false;
    }

    markValid(field); showOk(errEl, 'Description valide'); return true;
  }

  /* ── Validation complète du formulaire ──────────────────────── */
  function validateForm() {
    var ok = true;
    if (!validateEmail())      ok = false;
    if (!validateObjet())      ok = false;
    if (!validateType())       ok = false;
    if (!validatePriorite())   ok = false;
    if (!validateDesc())       ok = false;
    if (!validateRefContrat()) ok = false;

    if (!ok) {
      var firstInvalid = document.querySelector('.form-control.is-invalid');
      if (firstInvalid) firstInvalid.focus();
    }
    return ok;
  }

  /* ── Validation live ────────────────────────────────────────── */
  function attachLiveValidation() {
    var fEmail = $('fEmail');
    if (fEmail) {
      fEmail.addEventListener('input', function () { validateEmail(false); });
      fEmail.addEventListener('blur',  function () { validateEmail(false); });
    }

    var fObjet = $('fObjet');
    if (fObjet) {
      fObjet.addEventListener('input', function () { validateObjet(false); });
      fObjet.addEventListener('blur',  function () { validateObjet(false); });
    }

    var fDesc = $('fDesc');
    if (fDesc) {
      fDesc.addEventListener('input', function () { validateDesc(false); });
    }

    var fType = $('fType');
    if (fType) fType.addEventListener('change', function () { validateType(); });

    var fPriorite = $('fPriorite');
    if (fPriorite) fPriorite.addEventListener('change', function () { validatePriorite(); });
  
    var fRefContrat = $('fRefContrat');
    if (fRefContrat) {
       fRefContrat.addEventListener('input', function () { validateRefContrat(false); });
       fRefContrat.addEventListener('blur',  function () { validateRefContrat(false); });
    }
  }

  /* ── Style is-invalid ───────────────────────────────────────── */
  function injectStyle() {
    if (document.getElementById('rv-rec-style')) return;
    var style = document.createElement('style');
    style.id = 'rv-rec-style';
    style.textContent =
      '.form-control.is-invalid{border-color:var(--danger,#ef4444)!important;' +
      'box-shadow:0 0 0 3px rgba(239,68,68,.12)!important;}' +
      '.char-counter{font-size:11px;text-align:right;margin-top:4px;' +
      'color:var(--text-secondary,#94a3b8);transition:color .2s;}' +
      '.field-error{font-size:12px;margin-top:4px;}';
    document.head.appendChild(style);
  }

  function validateRefContrat(silent) {
    var field = $('fRefContrat');
    var errEl = $('ref_contrat_error');
    if (!field) return true;
    var val = field.value.trim();
    if (val === '') {
      markInvalid(field); if (!silent) showError(errEl, MSG.refRequired); return false;
    }
    if (!REF_CONTRAT_PATTERN.test(val)) {
      markInvalid(field); if (!silent) showError(errEl, MSG.refFormat); return false;
    }
    markValid(field); showOk(errEl, 'Référence valide'); return true;
}

  function validateEmail(silent) {
    var field = $('fEmail');
    var errEl = $('email_error');
    if (!field) return true;

    var val = field.value.trim();

    if (val === '') {
      markInvalid(field); if (!silent) showError(errEl, MSG.emailRequired); return false;
    }
    if (val.length > EMAIL_MAX) {
      markInvalid(field); if (!silent) showError(errEl, MSG.emailTooLong); return false;
    }
    if (!EMAIL_PATTERN.test(val)) {
      markInvalid(field); if (!silent) showError(errEl, MSG.emailInvalid); return false;
    }

    markValid(field); showOk(errEl, 'Email valide'); return true;
  }

  /* ── Init ───────────────────────────────────────────────────── */
  function init() {
    injectStyle();
    attachCharCounter('fObjet', OBJET_MAX, 'charCountObjet');
    attachCharCounter('fDesc',  DESC_MAX,  'charCountDesc');
    attachLiveValidation();
    window.validateReclamationForm = validateForm;
  }

  return {
    init         : init,
    validateForm : validateForm,
    validateObjet: validateObjet,
    validateDesc : validateDesc,
    validateEmail: validateEmail
  };

})();

/* Auto-init */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', ReclamationValidation.init);
} else {
  ReclamationValidation.init();
}
