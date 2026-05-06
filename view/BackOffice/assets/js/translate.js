/* ═══════════════════════════════════════════════════════════
   translate.js — Traduction FR ↔ EN (MyMemory, sans clé)
   ═══════════════════════════════════════════════════════════ */

/**
 * Traduit un texte via MyMemory API (gratuit, sans clé)
 * @param {string} text  - texte à traduire
 * @param {string} pair  - "fr|en" ou "en|fr"
 * @returns {Promise<string>}
 */
async function translateText(text, pair) {
  if (!text || !text.trim()) return text;
  // MyMemory limite à 500 chars par requête — on tronque si besoin
  const safe = text.length > 480 ? text.slice(0, 480) + '…' : text;
  const url = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(safe)}&langpair=${pair}`;
  const res  = await fetch(url);
  const data = await res.json();
  if (data.responseStatus === 200) return data.responseData.translatedText;
  throw new Error(data.responseDetails || 'Erreur de traduction');
}

/**
 * Attache un bouton FR↔EN à un élément texte (span/div/p).
 * Gère correctement les éléments avec du HTML (nl2br etc.).
 * Cloner le bouton à chaque appel pour éviter l'accumulation de listeners.
 *
 * @param {HTMLElement} btnEl   - le bouton <button>
 * @param {HTMLElement} textEl  - l'élément qui contient le texte
 * @returns {HTMLElement}       - le nouveau bouton (clone)
 */
function attachTranslateToggle(btnEl, textEl) {
  // Cloner pour supprimer les anciens listeners (cas modals réouvertes)
  const newBtn = btnEl.cloneNode(true);
  btnEl.parentNode.replaceChild(newBtn, btnEl);

  let originalHTML = null;
  let translatedText = null;
  let lang = 'fr';

  newBtn.addEventListener('click', async () => {
    // Extraire le texte brut visible (innerText gère les <br> → \n)
    const currentText = textEl.innerText.trim();
    if (!currentText || currentText === '—') return;

    newBtn.disabled = true;
    newBtn.innerHTML = '<i class="bi bi-hourglass-split" style="animation:spin .8s linear infinite"></i>';

    try {
      if (lang === 'fr') {
        if (!originalHTML) originalHTML = textEl.innerHTML;
        if (!translatedText) translatedText = await translateText(currentText, 'fr|en');
        // Afficher la traduction en préservant les sauts de ligne
        textEl.innerHTML = translatedText.replace(/\n/g, '<br>');
        lang = 'en';
        newBtn.innerHTML = '<i class="bi bi-translate"></i> FR';
        newBtn.title = 'Afficher en français';
      } else {
        textEl.innerHTML = originalHTML;
        lang = 'fr';
        newBtn.innerHTML = '<i class="bi bi-translate"></i> EN';
        newBtn.title = 'Translate to English';
      }
    } catch (e) {
      newBtn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Erreur';
      setTimeout(() => {
        newBtn.innerHTML = lang === 'fr'
          ? '<i class="bi bi-translate"></i> EN'
          : '<i class="bi bi-translate"></i> FR';
      }, 2000);
    }

    newBtn.disabled = false;
  });

  return newBtn;
}

/**
 * Attache un bouton FR↔EN à un <textarea>.
 * Cloner le bouton à chaque appel pour éviter l'accumulation de listeners.
 *
 * @param {HTMLElement} btnEl
 * @param {HTMLElement} taEl  - le <textarea>
 * @returns {HTMLElement}     - le nouveau bouton (clone)
 */
function attachTranslateTextarea(btnEl, taEl) {
  // Cloner pour supprimer les anciens listeners
  const newBtn = btnEl.cloneNode(true);
  btnEl.parentNode.replaceChild(newBtn, btnEl);

  let lang = 'fr';

  newBtn.addEventListener('click', async () => {
    const current = taEl.value.trim();
    if (!current) return;

    newBtn.disabled = true;
    const pair = lang === 'fr' ? 'fr|en' : 'en|fr';
    newBtn.innerHTML = '<i class="bi bi-hourglass-split" style="animation:spin .8s linear infinite"></i>';

    try {
      const result = await translateText(current, pair);
      taEl.value = result;
      lang = lang === 'fr' ? 'en' : 'fr';
      newBtn.innerHTML = lang === 'fr'
        ? '<i class="bi bi-translate"></i> EN'
        : '<i class="bi bi-translate"></i> FR';
    } catch (e) {
      newBtn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Erreur';
      setTimeout(() => {
        newBtn.innerHTML = lang === 'fr'
          ? '<i class="bi bi-translate"></i> EN'
          : '<i class="bi bi-translate"></i> FR';
      }, 2000);
    }
    newBtn.disabled = false;
  });

  return newBtn;
}
