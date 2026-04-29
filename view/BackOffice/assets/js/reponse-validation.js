/**
 * reponse-validation.js
 * Contrôle de saisie pour la partie Réponse (BackOffice)
 * ─────────────────────────────────────────────────────────
 * Couvre trois formulaires :
 *   1. Modal « Répondre »  → #mContenu        / #errMContenu
 *   2. Modal « Modifier »  → #modContenu       / #errModContenu
 *   3. Modal « Rejeter »   → #rejMotif         / #errRejMotif
 */

var ReponseValidation = (function () {

  /* ── Constantes ─────────────────────────────────────────────── */
  var CONTENU_MIN   = 10;     // nb de caractères minimum pour une réponse
  var CONTENU_MAX   = 1000;   // nb de caractères maximum pour une réponse
  var MOTIF_MIN     = 5;      // nb de caractères minimum pour un motif de rejet
  var MOTIF_MAX     = 500;    // nb de caractères maximum pour un motif de rejet

  /* ── Messages d'erreur ──────────────────────────────────────── */
  var MSG = {
    required  : 'Ce champ est requis.',
    tooShort  : function (min) { return 'Veuillez saisir au moins ' + min + ' caractères.'; },
    tooLong   : function (max) { return 'La saisie ne doit pas dépasser ' + max + ' caractères.'; },
    onlySpaces: 'La saisie ne peut pas contenir uniquement des espaces.',
    noHtml    : 'Les balises HTML ne sont pas autorisées.'
  };

  /* ── Helpers internes ───────────────────────────────────────── */

  /** Affiche un message d'erreur sous le champ */
  function showError(errId, msg) {
    var el = document.getElementById(errId);
    if (!el) return;
    el.textContent = msg || MSG.required;
    el.classList.add('show');
  }

  /** Efface le message d'erreur */
  function clearError(errId) {
    var el = document.getElementById(errId);
    if (!el) return;
    el.classList.remove('show');
  }

  /** Ajoute le feedback visuel d'erreur sur le textarea */
  function markInvalid(fieldId) {
    var el = document.getElementById(fieldId);
    if (el) el.classList.add('is-invalid');
  }

  /** Retire le feedback visuel d'erreur du textarea */
  function markValid(fieldId) {
    var el = document.getElementById(fieldId);
    if (el) el.classList.remove('is-invalid');
  }

  /** Valide une chaîne et retourne le message d'erreur ou null si valide */
  function validateText(value, min, max) {
    if (!value || value.length === 0) return MSG.required;
    if (/^\s+$/.test(value))          return MSG.onlySpaces;
    if (/<[^>]+>/.test(value))        return MSG.noHtml;
    if (value.length < min)           return MSG.tooShort(min);
    if (value.length > max)           return MSG.tooLong(max);
    return null; // valide
  }

  /** Attache un compteur de caractères sous un textarea */
  function attachCharCounter(fieldId, max, counterId) {
    var field   = document.getElementById(fieldId);
    var counter = document.getElementById(counterId);
    if (!field || !counter) return;

    function update() {
      var len = field.value.length;
      counter.textContent = len + ' / ' + max;
      counter.style.color = (len > max) ? 'var(--danger)' : 'var(--text-secondary)';
    }
    field.addEventListener('input', update);
    update();
  }

  /** Attache la validation en temps réel (on efface l'erreur dès que la saisie devient valide) */
  function attachLiveValidation(fieldId, errId, min, max) {
    var field = document.getElementById(fieldId);
    if (!field) return;
    field.addEventListener('input', function () {
      var val = field.value.trim();
      var err = validateText(val, min, max);
      if (err) {
        markInvalid(fieldId);
        showError(errId, err);
      } else {
        markValid(fieldId);
        clearError(errId);
      }
    });
  }

  /* ── API publique ───────────────────────────────────────────── */

  /**
   * Valide le formulaire « Répondre »
   * @returns {boolean} true si valide, false sinon
   */
  function validateRepondre() {
    var fieldId = 'mContenu';
    var errId   = 'errMContenu';
    var val     = (document.getElementById(fieldId) || {}).value;
    val = val ? val.trim() : '';

    var err = validateText(val, CONTENU_MIN, CONTENU_MAX);
    if (err) {
      markInvalid(fieldId);
      showError(errId, err);
      document.getElementById(fieldId).focus();
      return false;
    }
    markValid(fieldId);
    clearError(errId);
    return true;
  }

  /**
   * Valide le formulaire « Modifier »
   * @returns {boolean} true si valide, false sinon
   */
  function validateModifier() {
    var fieldId = 'modContenu';
    var errId   = 'errModContenu';
    var val     = (document.getElementById(fieldId) || {}).value;
    val = val ? val.trim() : '';

    var err = validateText(val, CONTENU_MIN, CONTENU_MAX);
    if (err) {
      markInvalid(fieldId);
      showError(errId, err);
      document.getElementById(fieldId).focus();
      return false;
    }
    markValid(fieldId);
    clearError(errId);
    return true;
  }

  /**
   * Valide le formulaire « Rejeter »
   * @returns {boolean} true si valide, false sinon
   */
  function validateRejeter() {
    var fieldId = 'rejMotif';
    var errId   = 'errRejMotif';
    var val     = (document.getElementById(fieldId) || {}).value;
    val = val ? val.trim() : '';

    var err = validateText(val, MOTIF_MIN, MOTIF_MAX);
    if (err) {
      markInvalid(fieldId);
      showError(errId, err);
      document.getElementById(fieldId).focus();
      return false;
    }
    markValid(fieldId);
    clearError(errId);
    return true;
  }

  /**
   * Réinitialise les erreurs d'un formulaire donné
   * @param {'repondre'|'modifier'|'rejeter'} form
   */
  function resetErrors(form) {
    var map = {
      repondre : { field: 'mContenu',   err: 'errMContenu'   },
      modifier : { field: 'modContenu', err: 'errModContenu' },
      rejeter  : { field: 'rejMotif',   err: 'errRejMotif'   }
    };
    var cfg = map[form];
    if (!cfg) return;
    markValid(cfg.field);
    clearError(cfg.err);
  }

  /**
   * Initialise les compteurs de caractères et la validation live.
   * À appeler une seule fois après le chargement du DOM.
   */
  function init() {
    /* Compteurs */
    attachCharCounter('mContenu',   CONTENU_MAX, 'charCountMContenu');
    attachCharCounter('modContenu', CONTENU_MAX, 'charCountModContenu');
    attachCharCounter('rejMotif',   MOTIF_MAX,   'charCountRejMotif');

    /* Validation live */
    attachLiveValidation('mContenu',   'errMContenu',   CONTENU_MIN, CONTENU_MAX);
    attachLiveValidation('modContenu', 'errModContenu', CONTENU_MIN, CONTENU_MAX);
    attachLiveValidation('rejMotif',   'errRejMotif',   MOTIF_MIN,   MOTIF_MAX);

    /* Style is-invalid : s'il n'existe pas dans les CSS, on l'injecte */
    if (!document.getElementById('rv-style')) {
      var style = document.createElement('style');
      style.id  = 'rv-style';
      style.textContent =
        '.modal-textarea.is-invalid{border-color:var(--danger)!important;' +
        'box-shadow:0 0 0 3px rgba(239,68,68,.12)!important;}' +
        '.char-counter{font-size:11px;text-align:right;margin-top:4px;' +
        'color:var(--text-secondary);transition:color .2s;}';
      document.head.appendChild(style);
    }
  }

  return {
    init            : init,
    validateRepondre: validateRepondre,
    validateModifier: validateModifier,
    validateRejeter : validateRejeter,
    resetErrors     : resetErrors
  };

})();

/* Auto-init au chargement du DOM */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', ReponseValidation.init);
} else {
  ReponseValidation.init();
}
