/**
 * chatbot-assurance.js
 * Widget chatbot IA — Domaine Assurance Protex uniquement
 * ────────────────────────────────────────────────────────
 * Déclenché par le bouton #btnOpenChat dans la page.
 * Panneau latéral droit avec prompt strictement spécialisé.
 */
(function () {
  'use strict';

  var EMAIL    = window.PROTEX_EMAIL || '';
  var API_URL  = 'chatbot.php';
  var isOpen   = false;
  var isTyping = false;

  /* ── Pré-filtre client : mots-clés hors domaine ─────────────── */
  var OFF_TOPIC = [
    'météo','sport','foot','football','film','cinéma','musique',
    'cuisine','recette','politique','jeux','blague','histoire',
    'géographie','math','mathématiques','programmation','code',
    'javascript','python','java','voyage','hotel','restaurant',
    'shopping','mode','actualité','news','people'
  ];
  function isOffTopic(msg) {
    var m = msg.toLowerCase();
    return OFF_TOPIC.some(function (kw) { return m.indexOf(kw) !== -1; });
  }

  /* ── CSS complet du panneau ─────────────────────────────────── */
  var CSS = `
    #chat-overlay {
      position:fixed; inset:0; z-index:8000;
      background:rgba(15,25,50,.50); backdrop-filter:blur(4px);
      opacity:0; pointer-events:none; transition:opacity .3s;
    }
    #chat-overlay.open { opacity:1; pointer-events:all; }

    #chat-panel {
      position:fixed; top:0; right:0; bottom:0; z-index:8001;
      width:420px; max-width:100vw;
      background:#fff; display:flex; flex-direction:column;
      box-shadow:-12px 0 50px rgba(31,47,77,.20);
      transform:translateX(100%);
      transition:transform .32s cubic-bezier(.4,0,.2,1);
    }
    #chat-panel.open { transform:translateX(0); }

    /* ── Header ── */
    #chat-head {
      flex-shrink:0;
      background:linear-gradient(135deg,#23458f 0%,#1d3c82 100%);
      padding:18px 20px; display:flex; align-items:center; gap:13px;
    }
    .chat-logo {
      width:44px; height:44px; border-radius:14px; flex-shrink:0;
      background:linear-gradient(135deg,#ff7a1a,#ef6b0a);
      display:flex; align-items:center; justify-content:center;
      font-size:20px; color:#fff; font-weight:900;
      box-shadow:0 4px 14px rgba(239,107,10,.40);
    }
    .chat-head-info { flex:1; }
    .chat-head-name { font-size:15px; font-weight:800; color:#fff; line-height:1.2; }
    .chat-head-sub  { font-size:11.5px; color:rgba(255,255,255,.65); margin-top:3px; display:flex; align-items:center; gap:5px; }
    .chat-online-dot { width:7px; height:7px; border-radius:50%; background:#22c55e; animation:onlinePulse 2s ease-in-out infinite; }
    @keyframes onlinePulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    #chat-status { color:rgba(255,255,255,.70); font-size:11.5px; }
    #chat-close-btn {
      width:34px; height:34px; border-radius:10px; border:none; flex-shrink:0;
      background:rgba(255,255,255,.12); color:#fff; cursor:pointer;
      display:flex; align-items:center; justify-content:center; font-size:18px; transition:background .2s;
    }
    #chat-close-btn:hover { background:rgba(255,255,255,.24); }

    /* ── Badge domaine ── */
    .chat-domain-badge {
      flex-shrink:0; margin:12px 16px 0;
      background:rgba(35,69,143,.07); border:1px solid rgba(35,69,143,.15);
      border-radius:10px; padding:9px 14px;
      display:flex; align-items:center; gap:8px;
      font-size:12.5px; color:#23458f; font-weight:600;
    }
    .chat-domain-badge i { font-size:15px; color:#ff7a1a; }

    /* ── Messages ── */
    #chat-msgs {
      flex:1; overflow-y:auto; padding:14px 16px 8px;
      display:flex; flex-direction:column; gap:12px; scroll-behavior:smooth;
    }
    #chat-msgs::-webkit-scrollbar { width:4px; }
    #chat-msgs::-webkit-scrollbar-thumb { background:#dde5f0; border-radius:4px; }

    .cmsg { display:flex; gap:9px; animation:msgIn .22s ease; }
    @keyframes msgIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
    .cmsg.bot  { align-items:flex-end; }
    .cmsg.user { flex-direction:row-reverse; align-items:flex-end; }
    .cmsg-avatar {
      width:30px; height:30px; border-radius:50%; flex-shrink:0;
      background:linear-gradient(135deg,#23458f,#1d3c82);
      display:flex; align-items:center; justify-content:center;
      font-size:12px; font-weight:800; color:#fff;
    }
    .cmsg-bubble {
      max-width:80%; padding:11px 15px; border-radius:17px;
      font-size:13.5px; line-height:1.58; word-break:break-word;
    }
    .cmsg.bot  .cmsg-bubble { background:#f2f5fb; color:#1f2f4d; border-bottom-left-radius:4px; }
    .cmsg.user .cmsg-bubble { background:linear-gradient(135deg,#23458f,#1d3c82); color:#fff; border-bottom-right-radius:4px; }
    .cmsg-bubble.hors-domaine { background:#fff5f5; color:#b91c1c; border:1px solid rgba(239,68,68,.2); }
    .cmsg-bubble.msg-erreur   { background:#fffbeb; color:#92400e; border:1px solid rgba(245,158,11,.25); font-size:12.5px; }
    .cmsg-time { font-size:10px; color:#9aa7bd; margin-top:4px; text-align:right; }

    /* ── Typing ── */
    .ctyping { display:flex; gap:5px; align-items:center; padding:11px 15px; }
    .ctyping span { width:7px; height:7px; border-radius:50%; background:#9aa7bd; animation:cDot 1.2s ease-in-out infinite; }
    .ctyping span:nth-child(2){animation-delay:.2s}
    .ctyping span:nth-child(3){animation-delay:.4s}
    @keyframes cDot { 0%,80%,100%{transform:scale(.75);opacity:.45} 40%{transform:scale(1.1);opacity:1} }

    /* ── Questions rapides ── */
    .chat-quick-wrap { flex-shrink:0; padding:8px 16px 10px; display:flex; flex-wrap:wrap; gap:7px; }
    .chat-quick {
      font-size:12px; padding:6px 13px; border-radius:20px;
      border:1.5px solid #e0e7f0; background:#f8fafd;
      color:#23458f; cursor:pointer; font-weight:600; transition:all .18s; white-space:nowrap;
    }
    .chat-quick:hover { background:#fff0e5; border-color:#ff7a1a; color:#ef6b0a; }

    /* ── Footer ── */
    #chat-footer {
      flex-shrink:0; padding:12px 14px 14px;
      border-top:1px solid #edf2f8; display:flex; gap:9px; align-items:flex-end;
    }
    #chat-input {
      flex:1; border:1.5px solid #e0e7f0; border-radius:14px;
      padding:10px 14px; font-size:13.5px; color:#1f2f4d;
      resize:none; outline:none; max-height:110px; min-height:42px;
      font-family:inherit; line-height:1.5; background:#f8fafd;
      transition:border-color .2s, background .2s;
    }
    #chat-input:focus { border-color:#23458f; background:#fff; }
    #chat-input::placeholder { color:#b0bac8; }
    #chat-send {
      width:42px; height:42px; border-radius:50%; border:none; flex-shrink:0;
      background:linear-gradient(135deg,#23458f,#1d3c82); color:#fff; cursor:pointer;
      display:flex; align-items:center; justify-content:center;
      transition:opacity .2s, transform .2s; box-shadow:0 3px 12px rgba(35,69,143,.35);
    }
    #chat-send:hover:not(:disabled) { opacity:.88; transform:scale(1.07); }
    #chat-send:disabled { opacity:.38; cursor:not-allowed; box-shadow:none; }
    #chat-send svg { width:17px; height:17px; fill:none; stroke:#fff; stroke-width:2.2; stroke-linecap:round; stroke-linejoin:round; }
    #chat-powered { flex-shrink:0; text-align:center; font-size:10.5px; color:#b0bac8; padding-bottom:10px; letter-spacing:.3px; }
  `;

  /* ── Questions rapides ─────────────────────────────────────── */
  var QUICK = [
    '📋 Statut de mes réclamations',
    '💬 Y a-t-il une réponse à ma réclamation ?',
    '🔍 Qu\'est-ce qu\'une franchise ?',
    '🏠 Comment fonctionne l\'assurance habitation ?',
    '🚗 Comment déclarer un sinistre auto ?',
    '💊 Que couvre l\'assurance santé ?',
    '⏱️ Délai de traitement d\'une réclamation ?',
    '📝 Quels sont mes droits en tant qu\'assuré ?',
  ];

  /* ── Construire le DOM ────────────────────────────────────── */
  function build() {
    var s = document.createElement('style');
    s.textContent = CSS;
    document.head.appendChild(s);

    var overlay = document.createElement('div');
    overlay.id = 'chat-overlay';
    overlay.onclick = closeChat;
    document.body.appendChild(overlay);

    var panel = document.createElement('div');
    panel.id = 'chat-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Assistant IA Assurance Protex');
    panel.innerHTML =
      '<div id="chat-head">' +
        '<div class="chat-logo">P</div>' +
        '<div class="chat-head-info">' +
          '<div class="chat-head-name">Assistant IA Protex</div>' +
          '<div class="chat-head-sub"><span class="chat-online-dot"></span><span id="chat-status">Spécialisé assurance · En ligne</span></div>' +
        '</div>' +
        '<button id="chat-close-btn" aria-label="Fermer">&#x2715;</button>' +
      '</div>' +
      '<div class="chat-domain-badge"><i class="bi bi-shield-check"></i>Assurance Protex &amp; Questions générales d\'assurance</div>' +
      '<div id="chat-msgs"></div>' +
      '<div class="chat-quick-wrap" id="chat-quick"></div>' +
      '<div id="chat-footer">' +
        '<textarea id="chat-input" placeholder="Ex : Quel est le statut de ma réclamation ?" rows="1" maxlength="500"></textarea>' +
        '<button id="chat-send" aria-label="Envoyer"><svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></button>' +
      '</div>' +
      '<div id="chat-powered">Propulsé par Claude AI · Protex Assurance</div>';
    document.body.appendChild(panel);

    /* Questions rapides */
    var qc = document.getElementById('chat-quick');
    QUICK.forEach(function (q) {
      var btn = document.createElement('button');
      btn.className = 'chat-quick';
      btn.textContent = q;
      btn.onclick = function () { sendMsg(q.replace(/^[^\s]+\s/, '')); };
      qc.appendChild(btn);
    });

    /* Message d'accueil */
    addBot(
      'Bonjour ! Je suis l\'assistant IA de Protex Assurance. 🛡️\n\n' +
      'Je suis spécialisé dans :\n' +
      '• Vos réclamations (statut, suivi, réponses)\n' +
      '• Les sinistres (auto, santé, habitation)\n' +
      '• Vos contrats et garanties\n' +
      '• Les procédures et délais\n\n' +
      'Comment puis-je vous aider ?'
    );

    /* Événements */
    document.getElementById('chat-close-btn').addEventListener('click', closeChat);
    document.getElementById('chat-send').addEventListener('click', onSend);
    document.getElementById('chat-input').addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); onSend(); }
    });
    document.getElementById('chat-input').addEventListener('input', autoResize);

    /* Lier le bouton de la page */
    var btn = document.getElementById('btnOpenChat');
    if (btn) btn.addEventListener('click', openChat);
  }

  /* ── Open / Close ────────────────────────────────────────── */
  function openChat() {
    isOpen = true;
    document.getElementById('chat-overlay').classList.add('open');
    document.getElementById('chat-panel').classList.add('open');
    setTimeout(function () { document.getElementById('chat-input').focus(); }, 340);
  }
  function closeChat() {
    isOpen = false;
    document.getElementById('chat-overlay').classList.remove('open');
    document.getElementById('chat-panel').classList.remove('open');
  }

  /* ── Envoi ──────────────────────────────────────────────── */
  function onSend() {
    var input = document.getElementById('chat-input');
    var msg   = input.value.trim();
    if (!msg || isTyping) return;
    input.value = ''; autoResize.call(input);
    sendMsg(msg);
  }

  function sendMsg(msg) {
    addUser(msg);
    document.getElementById('chat-quick').style.display = 'none';

    /* Pré-filtre côté client */
    if (isOffTopic(msg)) {
      addBot(
        'Je suis spécialisé dans le domaine de l\'assurance ' +
        '(réclamations, sinistres, contrats, garanties, types d\'assurance, droits des assurés…).\n\n' +
        'Je ne peux pas répondre à cette question. Puis-je vous aider ' +
        'avec une question sur l\'assurance ou vos réclamations Protex ?',
        false, true
      );
      return;
    }

    showTyping(true);
    setStatus('En train d\'analyser…');

    fetch(API_URL, {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify({ message: msg, email: EMAIL })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      showTyping(false);
      setStatus('Spécialisé assurance · En ligne');
      if (data.success) {
        addBot(data.reply);
      } else {
        addBot(data.message || 'Une erreur est survenue. Veuillez réessayer.', false, true);
      }
    })
    .catch(function () {
      showTyping(false);
      setStatus('Spécialisé assurance · En ligne');
      addBot('🌐 Connexion au serveur impossible. Vérifiez que le serveur PHP est actif.', false, true);
    });
  }

  /* ── Messages ──────────────────────────────────────────── */
  function addBot(text, horsdomaine, isError) {
    var box = document.getElementById('chat-msgs');
    var hm  = now();
    var cls = horsdomaine ? ' hors-domaine' : (isError ? ' msg-erreur' : '');
    var div = document.createElement('div');
    div.className = 'cmsg bot';
    div.innerHTML =
      '<div class="cmsg-avatar">P</div>' +
      '<div><div class="cmsg-bubble' + cls + '">' + escHtml(text) + '</div>' +
      '<div class="cmsg-time">' + hm + '</div></div>';
    box.appendChild(div); scrollBot();
  }

  function addUser(text) {
    var box = document.getElementById('chat-msgs');
    var div = document.createElement('div');
    div.className = 'cmsg user';
    div.innerHTML =
      '<div><div class="cmsg-bubble">' + escHtml(text) + '</div>' +
      '<div class="cmsg-time">' + now() + '</div></div>';
    box.appendChild(div); scrollBot();
  }

  /* ── Typing indicator ──────────────────────────────────── */
  var typingEl = null;
  function showTyping(show) {
    isTyping = show;
    document.getElementById('chat-send').disabled = show;
    var box = document.getElementById('chat-msgs');
    if (show) {
      typingEl = document.createElement('div');
      typingEl.className = 'cmsg bot';
      typingEl.innerHTML =
        '<div class="cmsg-avatar">P</div>' +
        '<div class="cmsg-bubble ctyping"><span></span><span></span><span></span></div>';
      box.appendChild(typingEl); scrollBot();
    } else if (typingEl) { typingEl.remove(); typingEl = null; }
  }

  /* ── Helpers ───────────────────────────────────────────── */
  function now() {
    var d = new Date();
    return d.getHours() + ':' + (d.getMinutes() < 10 ? '0' : '') + d.getMinutes();
  }
  function scrollBot()  { var b = document.getElementById('chat-msgs'); b.scrollTop = b.scrollHeight; }
  function setStatus(t) { var el = document.getElementById('chat-status'); if (el) el.textContent = t; }
  function autoResize() { this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 110) + 'px'; }
  function escHtml(s) {
    return String(s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')
      .replace(/\n/g,'<br>');
  }

  /* ── Init ──────────────────────────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', build);
  } else { build(); }

})();
