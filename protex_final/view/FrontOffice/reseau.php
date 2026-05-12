<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!defined('BASE_URL')) {
    $cfgPath = dirname(__DIR__, 2) . '/config.php';
    if (file_exists($cfgPath)) require_once $cfgPath;
}
?><!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Réseau — Protex</title>
    <script>
        if (window.location.protocol === 'file:') {
            const targetUrl = 'http://localhost/gestion_user_polished/view/FrontOffice/reseau.html' + window.location.search + window.location.hash;
            window.location.replace(targetUrl);
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="user/css/variables.css">
    <link rel="stylesheet" href="user/css/base.css">
    <link rel="stylesheet" href="user/css/layout.css">
    <link rel="stylesheet" href="user/css/animations.css">
    <!-- main.js loaded at bottom -->
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-body);
            color: var(--text-primary);
            background: #f0f4ff;
            min-height: 100vh;
        }


        /* ── PAGE LAYOUT ── */
        .page-body {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 24px 60px;
        }

        .page-header {
            margin-bottom: 28px;
        }

        .page-title {
            font-family: var(--font-display);
            font-size: 26px;
            font-weight: 800;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title i {
            color: var(--accent);
            font-size: 28px;
        }

        .page-sub {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .agence-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--accent);
            color: #fff;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 8px;
        }

        /* ── GRID ── */
        .net-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
            align-items: start;
        }

        @media (max-width: 820px) {
            .net-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── CARD ── */
        .card {
            background: #fff;
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            box-shadow: 0 2px 12px rgba(26, 58, 122, 0.06);
            overflow: hidden;
        }

        .card-header {
            padding: 18px 20px 14px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .card-title {
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i {
            color: var(--accent);
        }

        .card-body {
            padding: 16px 20px;
        }

        /* ── SEARCH BAR ── */
        .search-wrap {
            position: relative;
        }

        .search-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 15px;
        }

        .search-input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid var(--glass-border);
            border-radius: 50px;
            background: #f8f9ff;
            font-size: 13.5px;
            color: var(--text-primary);
            font-family: var(--font-body);
            transition: var(--transition);
            outline: none;
        }

        .search-input:focus {
            border-color: var(--accent);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(255, 107, 26, 0.10);
        }

        /* ── TABS ── */
        .tabs {
            display: flex;
            gap: 4px;
            padding: 12px 16px 0;
            background: rgba(26, 58, 122, 0.03);
            border-bottom: 1px solid var(--glass-border);
        }

        .tab-btn {
            flex: 1;
            padding: 8px 6px;
            border: none;
            border-radius: 8px 8px 0 0;
            background: transparent;
            color: var(--text-secondary);
            font-size: 11.5px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            font-family: var(--font-body);
        }

        .tab-btn.active {
            background: var(--accent);
            color: #fff;
        }

        .tab-btn:hover:not(.active) {
            background: rgba(255, 107, 26, 0.08);
            color: var(--accent);
        }

        .tab-badge {
            position: absolute;
            top: -4px;
            right: 2px;
            background: #e63946;
            color: #fff;
            border-radius: 50%;
            width: 17px;
            height: 17px;
            font-size: 9px;
            display: none;
            align-items: center;
            justify-content: center;
        }

        /* ── USER LIST ── */
        .user-list {
            max-height: 480px;
            overflow-y: auto;
        }

        .user-list::-webkit-scrollbar {
            width: 4px;
        }

        .user-list::-webkit-scrollbar-thumb {
            background: rgba(26, 58, 122, 0.15);
            border-radius: 2px;
        }

        .user-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 16px;
            border-bottom: 1px solid rgba(26, 58, 122, 0.06);
            transition: background 0.2s;
            animation: slideIn 0.25s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-item:last-child {
            border-bottom: none;
        }

        .user-item:hover {
            background: rgba(255, 107, 26, 0.03);
        }

        .avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .avatar-wrap img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(26, 58, 122, 0.10);
        }

        .online-dot {
            position: absolute;
            bottom: 1px;
            right: 1px;
            width: 11px;
            height: 11px;
            background: #2ed573;
            border: 2px solid #fff;
            border-radius: 50%;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 3px;
        }

        .role-pill {
            font-size: 9px;
            background: #1A3A7A;
            color: #fff;
            padding: 1px 7px;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-txt {
            font-size: 10px;
            color: var(--text-secondary);
        }

        .status-txt.online {
            color: #2ed573;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1.5px solid var(--glass-border);
            background: #f8f9ff;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(255, 107, 26, 0.06);
        }

        .action-btn.accent {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .action-btn.accent:hover {
            background: var(--accent-dark);
        }

        .action-btn.danger {
            border-color: #e63946;
            color: #e63946;
        }

        .action-btn.danger:hover {
            background: rgba(230, 57, 70, 0.08);
        }

        .action-btn.gold {
            color: #f5a623;
            border-color: #f5a623;
            background: rgba(245, 166, 35, 0.07);
        }

        .action-btn.gold.active {
            background: #f5a623;
            color: #fff;
            border-color: #f5a623;
        }

        .action-btn.chat-active {
            background: #1A3A7A;
            color: #fff;
            border-color: #1A3A7A;
            position: relative;
        }

        .unread-pip {
            position: absolute;
            top: -3px;
            right: -3px;
            background: #e63946;
            color: #fff;
            font-size: 8px;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            display: none;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            padding: 48px 24px;
            text-align: center;
        }

        .empty-icon {
            font-size: 48px;
            color: rgba(26, 58, 122, 0.15);
            margin-bottom: 12px;
        }

        .empty-txt {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* ── CHAT PANEL ── */
        .chat-panel {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 320px;
            background: #fff;
            border-radius: var(--radius-xl);
            box-shadow: 0 12px 40px rgba(26, 58, 122, 0.18);
            border: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            z-index: 500;
            transform: translateY(20px);
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            max-height: 480px;
        }

        .chat-panel.open {
            transform: translateY(0);
            opacity: 1;
            pointer-events: all;
        }

        .chat-header {
            background: #1A3A7A;
            padding: 12px 14px;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chat-header img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .chat-name {
            flex: 1;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
        }

        .chat-status {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.65);
        }

        .chat-close {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            font-size: 18px;
            cursor: pointer;
            padding: 2px;
        }

        .chat-close:hover {
            color: #fff;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-height: 200px;
            max-height: 300px;
        }

        .chat-messages::-webkit-scrollbar {
            width: 3px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(26, 58, 122, 0.15);
            border-radius: 2px;
        }

        .msg-bubble {
            max-width: 80%;
            padding: 8px 12px;
            border-radius: 14px;
            font-size: 12.5px;
            line-height: 1.45;
            word-wrap: break-word;
        }

        .msg-bubble.mine {
            background: var(--accent);
            color: #fff;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .msg-bubble.theirs {
            background: #f0f4ff;
            color: var(--text-primary);
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .msg-time {
            font-size: 9px;
            opacity: 0.6;
            margin-top: 2px;
            text-align: right;
        }

        .chat-footer {
            padding: 10px 12px;
            border-top: 1px solid var(--glass-border);
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .chat-input {
            flex: 1;
            padding: 8px 12px;
            border: 1.5px solid var(--glass-border);
            border-radius: 20px;
            font-size: 12.5px;
            outline: none;
            font-family: var(--font-body);
            transition: var(--transition);
            background: #f8f9ff;
        }

        .chat-input:focus {
            border-color: var(--accent);
        }

        .send-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--accent);
            color: #fff;
            border: none;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .send-btn:hover {
            background: var(--accent-dark);
        }

        /* ── RIGHT COLUMN ── */
        .right-col {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* SOS CARD */
        .sos-card {
            background: linear-gradient(135deg, #1A3A7A, #0f2556);
            border-radius: var(--radius-xl);
            padding: 20px;
            color: #fff;
        }

        .sos-title {
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .sos-sub {
            font-size: 11px;
            opacity: 0.7;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .trusted-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
            min-height: 40px;
        }

        .trusted-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: 10px;
            padding: 8px 10px;
        }

        .trusted-item img {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
        }

        .trusted-name {
            font-size: 12px;
            font-weight: 600;
            flex: 1;
        }

        .sos-trigger-btn {
            width: 100%;
            padding: 12px;
            background: #e63946;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: var(--font-display);
            transition: var(--transition);
            animation: sos-glow 2s infinite;
        }

        @keyframes sos-glow {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(230, 57, 70, 0.4);
            }

            50% {
                box-shadow: 0 0 0 8px rgba(230, 57, 70, 0);
            }
        }

        .sos-trigger-btn:hover {
            background: #c1121f;
        }

        .sos-empty {
            font-size: 11px;
            opacity: 0.6;
            text-align: center;
            padding: 10px 0;
        }

        /* TOAST */
        #toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(60px);
            background: #1A3A7A;
            color: #fff;
            padding: 10px 24px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            z-index: 9999;
            transition: transform 0.3s ease, opacity 0.3s ease;
            opacity: 0;
            pointer-events: none;
        }

        #toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        #toast.success {
            background: #2a9d8f;
        }

        #toast.danger {
            background: #e63946;
        }

        #toast.warning {
            background: #f5a623;
            color: #fff;
        }

        /* SOS MODAL */
        .sos-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 800;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .sos-modal-overlay.open {
            display: flex;
        }

        .sos-modal {
            background: #fff;
            border-radius: 24px;
            padding: 32px;
            max-width: 380px;
            width: 90%;
            text-align: center;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25);
        }

        .sos-modal-icon {
            font-size: 64px;
            margin-bottom: 12px;
            animation: sos-pulse 1s infinite;
        }

        @keyframes sos-pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .sos-modal-title {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 800;
            color: #e63946;
            margin-bottom: 8px;
        }

        .sos-modal-sub {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .sos-trusted-preview {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .sos-trusted-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f0f4ff;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #1A3A7A;
        }

        .sos-trusted-chip img {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            object-fit: cover;
        }

        .countdown-num {
            font-size: 56px;
            font-weight: 900;
            color: #e63946;
            line-height: 1;
            margin-bottom: 8px;
        }

        .gps-status {
            font-size: 11px;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        .modal-btns {
            display: flex;
            gap: 10px;
        }

        .modal-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            font-family: var(--font-display);
            transition: var(--transition);
        }

        .modal-btn.primary {
            background: #e63946;
            color: #fff;
        }

        .modal-btn.primary:hover {
            background: #c1121f;
        }

        .modal-btn.secondary {
            background: #f0f4ff;
            color: #1A3A7A;
        }

        .modal-btn.secondary:hover {
            background: #e0e8ff;
        }
    </style>
</head>

<body>
    <div class="background"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="layout">

        <!-- ===== NAVBAR ===== -->
        <?php require_once __DIR__.'/assets/includes/navbar.php'; ?>

        <!-- ===== BODY ===== -->
        <main class="page-body">

            <div class="page-header">
                <div class="page-title">
                    <i class="bi bi-people-fill"></i>
                    Mon Réseau Social Protex
                </div>
                <div class="page-sub">Connectez-vous avec les clients de votre agence, échangez des messages et activez
                    votre réseau SOS.</div>
                <div class="agence-badge" id="agenceBadge">
                    <i class="bi bi-building"></i> <span id="agenceName">Chargement...</span>
                </div>
            </div>

            <div class="net-grid">

                <!-- ── COLONNE GAUCHE : Recherche + Contacts ── -->
                <div>

                    <!-- RECHERCHE CLIENTS MÊME AGENCE -->
                    <div class="card" style="margin-bottom:16px;">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="bi bi-search"></i>
                                Chercher des clients dans mon agence
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="search-wrap">
                                <i class="bi bi-search"></i>
                                <input type="text" class="search-input" id="searchInput" placeholder="Nom ou prénom...">
                            </div>
                            <div id="searchResults" style="margin-top:12px;"></div>
                        </div>
                    </div>

                    <!-- MES CONTACTS (amis acceptés + invitations reçues) -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="bi bi-person-check"></i>
                                Mes contacts
                                <span id="contactCount" style="font-size:12px;opacity:0.5;font-weight:400;">(0)</span>
                            </div>
                        </div>

                        <div class="tabs">
                            <button class="tab-btn active" onclick="switchTab('friends')" id="tab-friends">
                                <i class="bi bi-people"></i> Amis
                            </button>
                            <button class="tab-btn" onclick="switchTab('pending')" id="tab-pending">
                                <i class="bi bi-clock"></i> Invitations
                                <span class="tab-badge" id="pendingBadge"></span>
                            </button>
                            <button class="tab-btn" onclick="switchTab('trusted')" id="tab-trusted">
                                ⭐ Confiance
                            </button>
                        </div>

                        <div id="contactsList" class="user-list">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="bi bi-hourglass-split"></i></div>
                                <div class="empty-txt">Chargement...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── COLONNE DROITE : SOS ── -->
                <div class="right-col">

                    <!-- CARTE SOS -->
                    <div class="sos-card">
                        <div class="sos-title">
                            <i class="bi bi-shield-fill-exclamation"></i>
                            Centre de Sécurité SOS
                        </div>
                        <div class="sos-sub">
                            En cas d'urgence, vos contacts de confiance (⭐) recevront immédiatement votre position GPS
                            et une alerte email.
                        </div>
                        <div
                            style="font-size:11px;opacity:0.6;text-transform:uppercase;font-weight:700;margin-bottom:8px;letter-spacing:0.5px;">
                            Mes contacts de confiance
                        </div>
                        <div class="trusted-list" id="trustedList">
                            <div class="sos-empty">Aucun contact de confiance.<br>Activez l'⭐ sur vos amis.</div>
                        </div>
                        <button class="sos-trigger-btn" onclick="openSOSModal()">
                            <i class="bi bi-exclamation-octagon-fill"></i> DÉCLENCHER UNE ALERTE SOS
                        </button>
                    </div>

                    <!-- STATS RÉSEAU -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><i class="bi bi-bar-chart"></i> Statistiques</div>
                        </div>
                        <div class="card-body">
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:13px;color:var(--text-secondary);">Amis totaux</span>
                                    <span style="font-size:18px;font-weight:800;color:var(--accent);"
                                        id="statFriends">0</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:13px;color:var(--text-secondary);">En ligne maintenant</span>
                                    <span style="font-size:18px;font-weight:800;color:#2ed573;" id="statOnline">0</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:13px;color:var(--text-secondary);">Contacts de
                                        confiance</span>
                                    <span style="font-size:18px;font-weight:800;color:#f5a623;"
                                        id="statTrusted">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- ── CHAT PANEL ── -->
    <div class="chat-panel" id="chatPanel">
        <div class="chat-header">
            <img src="" alt="" id="chatAvatar">
            <div>
                <div class="chat-name" id="chatName">—</div>
                <div class="chat-status" id="chatStatusTxt">—</div>
            </div>
            <button class="chat-close" onclick="closeChat()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div style="text-align:center;font-size:12px;color:var(--text-secondary);padding:20px;">Chargement des
                messages...</div>
        </div>
        <div class="chat-footer">
            <input type="text" class="chat-input" id="chatInput" placeholder="Écrire un message..." maxlength="500">
            <button class="send-btn" onclick="sendMessage()"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>

    <!-- ── SOS MODAL ── -->
    <div class="sos-modal-overlay" id="sosModalOverlay">
        <div class="sos-modal">
            <div class="sos-modal-icon">🆘</div>
            <div class="sos-modal-title">ALERTE SOS</div>
            <div class="sos-modal-sub" id="sosModalSub">
                Vos contacts de confiance vont recevoir votre position et une alerte immédiate.
            </div>
            <div class="sos-trusted-preview" id="sosTrustedChips"></div>
            <div class="countdown-num" id="sosCountdownNum">5</div>
            <div class="gps-status" id="sosGPSStatus">📍 Récupération de la position...</div>
            <div class="modal-btns">
                <button class="modal-btn primary" id="sosConfirmBtn" onclick="confirmSOS()" disabled>
                    <i class="bi bi-send-fill"></i> Confirmer SOS
                </button>
                <button class="modal-btn secondary" onclick="cancelSOS()">
                    <i class="bi bi-x-lg"></i> Annuler
                </button>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div id="toast"></div>

    <script>
        // ════════════════════════════════════════════
        //  UTILS
        // ════════════════════════════════════════════
        function getAvatarUrl(url) {
            if (!url || url === 'default.png') return 'logo.png';
            if (url.startsWith('http') || url.includes('/')) return url;
            return '../../uploads/avatars/' + url;
        }

        function showToast(msg, type = 'info') {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = `show ${type}`;
            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(() => t.className = '', 3200);
        }

        function fmt(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }

        // ════════════════════════════════════════════
        //  STATE
        // ════════════════════════════════════════════
        let networkData = { friends: [], pending: [], suggestions: [] };
        let currentTab = 'friends';
        let activeChatId = null;
        let chatPollTimer = null;
        let sosLocation = { lat: null, lng: null };
        let sosCountdownTimer = null;

        // ════════════════════════════════════════════
        //  AGENCE + INIT
        // ════════════════════════════════════════════
        async function init() {
            // Load agence name
            try {
                const r = await fetch('search_agency_users.php?action=my_agence');
                const d = await r.json();
                if (d.success) {
                    document.getElementById('agenceName').textContent = d.nom_agence;
                }
            } catch (e) { }

            // Load user avatar in nav
            try {
                const r = await fetch('get_user.php');
                let d = await r.json();
                if (d && d.success && d.user) d = d.user;
                if (d && !d.error) {
                    const initials = ((d.prenom[0] || '') + (d.nom[0] || '')).toUpperCase();
                    
                    let avatarSrc = '';
                    if (d.avatar_url) avatarSrc = d.avatar_url;
                    else if (d.avatar && d.avatar !== 'default.png') avatarSrc = d.avatar.includes('/') ? d.avatar : '../uploads/avatars/' + d.avatar;
                    else if (d.photo && d.photo !== 'default.png') avatarSrc = d.photo.includes('/') ? d.photo : '../uploads/avatars/' + d.photo;

                    const avatarInner = document.getElementById('avatarInitials');
                    const dropdownAvatar = document.getElementById('dropdownAvatar');
                    
                    if (avatarInner) {
                        avatarInner.innerHTML = avatarSrc ? `<img src="${avatarSrc}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.parentElement.textContent='${initials}'">` : initials;
                    }
                    if (dropdownAvatar) {
                        dropdownAvatar.innerHTML = avatarSrc ? `<img src="${avatarSrc}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.parentElement.textContent='${initials}'">` : initials;
                    }

                    document.getElementById('dropdownName').textContent = (d.prenom + ' ' + d.nom).trim();
                    document.getElementById('dropdownEmail').textContent = d.email;
                    if (d.role) document.getElementById('dropdownRole').textContent = d.role;
                }
            } catch (e) { }

            loadNetwork();
            // Auto-search on start (show all same-agency users)
            searchUsers('');
        }

        // ════════════════════════════════════════════
        //  SEARCH SAME-AGENCY USERS
        // ════════════════════════════════════════════
        let searchTimer = null;
        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => searchUsers(this.value.trim()), 300);
        });

        async function searchUsers(q) {
            const box = document.getElementById('searchResults');
            box.innerHTML = '<div style="text-align:center;padding:16px;font-size:12px;color:var(--text-secondary);"><i class="bi bi-arrow-repeat" style="animation:spin 1s linear infinite;display:inline-block;"></i> Recherche...</div>';

            try {
                const url = `search_agency_users.php?action=search&q=${encodeURIComponent(q)}`;
                const r = await fetch(url);
                const d = await r.json();

                if (!d.success) {
                    box.innerHTML = `<div class="empty-state"><div class="empty-icon"><i class="bi bi-exclamation-circle"></i></div><div class="empty-txt">${d.message}</div></div>`;
                    return;
                }

                if (!d.users || d.users.length === 0) {
                    box.innerHTML = `<div class="empty-state"><div class="empty-icon"><i class="bi bi-person-x"></i></div><div class="empty-txt">Aucun client trouvé${q ? ` pour "${q}"` : ''}</div></div>`;
                    return;
                }

                box.innerHTML = d.users.map(u => renderSearchUser(u)).join('');
            } catch (e) {
                box.innerHTML = '<div class="empty-state"><div class="empty-txt">Erreur de connexion.</div></div>';
            }
        }

        function renderSearchUser(u) {
            const online = u.is_online == 1;
            let actionHtml = '';

            if (u.rel_status === 'accepted') {
                actionHtml = `
                    <span style="font-size:11px;color:#2a9d8f;font-weight:700;display:flex;align-items:center;gap:4px;">
                        <i class="bi bi-check2-circle"></i> Ami
                    </span>
                    <button class="action-btn chat-active" title="Envoyer un message"
                        onclick="openChat(${u.id_user}, '${u.prenom}', '${getAvatarUrl(u.avatar_url)}', ${online})">
                        <i class="bi bi-chat-dots-fill"></i>
                    </button>`;
            } else if (u.rel_status === 'pending_sent') {
                actionHtml = `<span style="font-size:11px;color:var(--text-secondary);font-weight:700;"><i class="bi bi-hourglass-split"></i> En attente</span>`;
            } else if (u.rel_status === 'pending_recv') {
                actionHtml = `
                    <button class="action-btn accent" title="Accepter" onclick="handleFriend(${u.id_user}, 'accept')">
                        <i class="bi bi-check-lg"></i>
                    </button>
                    <button class="action-btn danger" title="Refuser" onclick="handleFriend(${u.id_user}, 'reject')">
                        <i class="bi bi-x-lg"></i>
                    </button>`;
            } else {
                actionHtml = `
                    <button class="action-btn accent" title="Envoyer une invitation"
                        onclick="handleFriend(${u.id_user}, 'add')">
                        <i class="bi bi-person-plus-fill"></i>
                    </button>`;
            }

            return `
            <div class="user-item" id="su-${u.id_user}">
                <div class="avatar-wrap">
                    <img src="${getAvatarUrl(u.avatar_url)}" alt="">
                    ${online ? '<div class="online-dot"></div>' : ''}
                </div>
                <div class="user-info">
                    <div class="user-name">${u.prenom} ${u.nom}</div>
                    <div class="user-meta">
                        <span class="role-pill">${u.role}</span>
                        <span class="status-txt ${online ? 'online' : ''}">${online ? 'En ligne' : 'Hors ligne'}</span>
                    </div>
                </div>
                <div class="user-actions">${actionHtml}</div>
            </div>`;
        }

        // ════════════════════════════════════════════
        //  NETWORK (mes contacts)
        // ════════════════════════════════════════════
        async function loadNetwork() {
            try {
                const r = await fetch('friends.php?action=list');
                const d = await r.json();
                if (!d.success) return;

                networkData = d;

                // Badges
                const pb = document.getElementById('pendingBadge');
                const ib = document.getElementById('invitationBadge');
                if (d.pending && d.pending.length > 0) {
                    pb.textContent = d.pending.length;
                    pb.style.display = 'flex';
                    ib.textContent = d.pending.length;
                    ib.style.display = 'flex';
                } else {
                    pb.style.display = 'none';
                    ib.style.display = 'none';
                }

                // Stats
                const friends = d.friends || [];
                const trusted = friends.filter(f => f.is_trusted == 1);
                const online = friends.filter(f => f.is_online == 1);
                document.getElementById('statFriends').textContent = friends.length;
                document.getElementById('statOnline').textContent = online.length;
                document.getElementById('statTrusted').textContent = trusted.length;
                document.getElementById('contactCount').textContent = `(${friends.length})`;

                // Trusted list (SOS card)
                updateTrustedCard(trusted);

                renderTab(currentTab);
            } catch (e) { }
        }

        function updateTrustedCard(trusted) {
            const box = document.getElementById('trustedList');
            if (!trusted || trusted.length === 0) {
                box.innerHTML = '<div class="sos-empty">Aucun contact de confiance.<br>Activez l\'⭐ sur vos amis.</div>';
                return;
            }
            box.innerHTML = trusted.map(u => `
                <div class="trusted-item">
                    <img src="${getAvatarUrl(u.avatar_url)}" alt="">
                    <span class="trusted-name">${u.prenom} ${u.nom}</span>
                    <span style="font-size:10px;color:${u.is_online == 1 ? '#2ed573' : 'rgba(255,255,255,0.4)'};">
                        ${u.is_online == 1 ? '● En ligne' : '● Hors ligne'}
                    </span>
                </div>`).join('');
        }

        function switchTab(tab) {
            currentTab = tab;
            ['friends', 'pending', 'trusted'].forEach(t => {
                document.getElementById(`tab-${t}`).classList.toggle('active', t === tab);
            });
            renderTab(tab);
        }

        function renderTab(tab) {
            const box = document.getElementById('contactsList');
            let users = [];

            if (tab === 'friends') users = networkData.friends || [];
            if (tab === 'pending') users = networkData.pending || [];
            if (tab === 'trusted') users = (networkData.friends || []).filter(f => f.is_trusted == 1);

            if (users.length === 0) {
                const msgs = {
                    friends: 'Aucun ami pour l\'instant. Cherchez des clients de votre agence ci-dessus !',
                    pending: 'Aucune invitation en attente.',
                    trusted: 'Aucun contact de confiance. Activez l\'⭐ sur vos amis pour activer le SOS.'
                };
                box.innerHTML = `<div class="empty-state"><div class="empty-icon"><i class="bi bi-inbox"></i></div><div class="empty-txt">${msgs[tab]}</div></div>`;
                return;
            }

            box.innerHTML = users.map(u => {
                if (tab === 'pending') return renderPendingItem(u);
                return renderFriendItem(u);
            }).join('');
        }

        function renderFriendItem(u) {
            const online = u.is_online == 1;
            const trusted = u.is_trusted == 1;
            return `
            <div class="user-item" data-userid="${u.id_user}" data-trusted="${u.is_trusted}">
                <div class="avatar-wrap">
                    <img src="${getAvatarUrl(u.avatar_url)}" alt="">
                    ${online ? '<div class="online-dot"></div>' : ''}
                </div>
                <div class="user-info">
                    <div class="user-name">${u.prenom} ${u.nom}</div>
                    <div class="user-meta">
                        <span class="role-pill">${u.role}</span>
                        <span class="status-txt ${online ? 'online' : ''}">${online ? 'En ligne' : 'Hors ligne'}</span>
                    </div>
                </div>
                <div class="user-actions">
                    <button class="action-btn gold ${trusted ? 'active' : ''}" title="${trusted ? 'Retirer de confiance' : 'Marquer de confiance (SOS)'}"
                        onclick="toggleTrust(${u.id_user})">
                        <i class="bi bi-star-fill"></i>
                    </button>
                    <button class="action-btn chat-active" title="Envoyer un message"
                        onclick="openChat(${u.id_user}, '${u.prenom}', '${getAvatarUrl(u.avatar_url)}', ${online})"
                        id="chatBtn-${u.id_user}">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span class="unread-pip" id="pip-${u.id_user}"></span>
                    </button>
                    <button class="action-btn danger" title="Supprimer de mes contacts"
                        onclick="if(confirm('Supprimer ce contact ?')) handleFriend(${u.id_user}, 'remove')">
                        <i class="bi bi-person-x"></i>
                    </button>
                </div>
            </div>`;
        }

        function renderPendingItem(u) {
            return `
            <div class="user-item">
                <div class="avatar-wrap">
                    <img src="${getAvatarUrl(u.avatar_url)}" alt="">
                </div>
                <div class="user-info">
                    <div class="user-name">${u.prenom} ${u.nom}</div>
                    <div class="user-meta">
                        <span style="font-size:11px;color:var(--accent);font-weight:600;">Invitation reçue</span>
                    </div>
                </div>
                <div class="user-actions">
                    <button class="action-btn accent" title="Accepter" onclick="handleFriend(${u.id_user}, 'accept')">
                        <i class="bi bi-check-lg"></i>
                    </button>
                    <button class="action-btn danger" title="Refuser" onclick="handleFriend(${u.id_user}, 'reject')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>`;
        }

        // ════════════════════════════════════════════
        //  FRIEND ACTIONS
        // ════════════════════════════════════════════
        async function handleFriend(id, action) {
            try {
                const r = await fetch('friends.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action, friend_id: id })
                });
                const d = await r.json();
                showToast(d.message, d.success ? 'success' : 'warning');
                if (d.success) {
                    await loadNetwork();
                    // Refresh search results to update button states
                    searchUsers(document.getElementById('searchInput').value.trim());
                }
            } catch (e) {
                showToast('Erreur réseau', 'danger');
            }
        }

        async function toggleTrust(id) {
            try {
                const r = await fetch('sos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'toggle_trust', friend_id: id })
                });
                const d = await r.json();
                showToast(d.message, d.success ? 'success' : 'warning');
                if (d.success) await loadNetwork();
            } catch (e) {
                showToast('Erreur réseau', 'danger');
            }
        }

        // ════════════════════════════════════════════
        //  CHAT
        // ════════════════════════════════════════════
        function openChat(userId, name, avatar, isOnline) {
            activeChatId = userId;
            document.getElementById('chatName').textContent = name;
            document.getElementById('chatAvatar').src = avatar;
            document.getElementById('chatStatusTxt').textContent = isOnline ? '🟢 En ligne' : '⚪ Hors ligne';
            document.getElementById('chatPanel').classList.add('open');
            loadMessages();
            clearInterval(chatPollTimer);
            chatPollTimer = setInterval(loadMessages, 4000);
            document.getElementById('chatInput').focus();
        }

        function closeChat() {
            document.getElementById('chatPanel').classList.remove('open');
            clearInterval(chatPollTimer);
            activeChatId = null;
        }

        async function loadMessages() {
            if (!activeChatId) return;
            const box = document.getElementById('chatMessages');
            box.innerHTML = '<div style="text-align:center;padding:30px;font-size:12px;color:var(--text-secondary);">Chargement des messages...</div>';
            try {
                const r = await fetch(`chat.php?action=fetch&friend_id=${activeChatId}`);
                const d = await r.json();
                if (!d.success) {
                    box.innerHTML = `<div style="text-align:center;padding:30px;font-size:12px;color:var(--text-secondary);">${d.message || 'Impossible de charger les messages.'}</div>`;
                    return;
                }

                const atBottom = box.scrollTop + box.clientHeight >= box.scrollHeight - 20;

                box.innerHTML = d.messages.length === 0
                    ? '<div style="text-align:center;padding:30px;font-size:12px;color:var(--text-secondary);">Commencez la conversation !</div>'
                    : d.messages.map(m => `
                        <div class="msg-bubble ${m.is_mine == 1 ? 'mine' : 'theirs'}">
                            ${escHtml(m.content)}
                            <div class="msg-time">${fmt(m.sent_at)}</div>
                        </div>`).join('');

                if (atBottom) box.scrollTop = box.scrollHeight;
            } catch (e) {
                box.innerHTML = '<div style="text-align:center;padding:30px;font-size:12px;color:var(--text-secondary);">Erreur de connexion.</div>';
            }
        }

        async function sendMessage() {
            if (!activeChatId) return;
            const input = document.getElementById('chatInput');
            const content = input.value.trim();
            if (!content) return;
            input.value = '';
            try {
                const r = await fetch('chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'send', friend_id: activeChatId, content })
                });
                const d = await r.json();
                if (d.success) loadMessages();
            } catch (e) { }
        }

        document.getElementById('chatInput').addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });

        function escHtml(s) {
            return String(s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // ════════════════════════════════════════════
        //  SOS
        // ════════════════════════════════════════════
        function openSOSModal() {
            const trusted = (networkData.friends || []).filter(f => f.is_trusted == 1);
            if (trusted.length === 0) {
                showToast('Aucun contact de confiance ! Activez l\'⭐ sur vos amis.', 'warning');
                return;
            }

            // Fill chips
            document.getElementById('sosTrustedChips').innerHTML = trusted.map(u => `
                <div class="sos-trusted-chip">
                    <img src="${getAvatarUrl(u.avatar_url)}" alt="">
                    ${u.prenom}
                </div>`).join('');

            document.getElementById('sosModalOverlay').classList.add('open');
            document.getElementById('sosConfirmBtn').disabled = true;
            document.getElementById('sosGPSStatus').textContent = '📍 Récupération de la position GPS...';

            // Get GPS
            sosLocation = { lat: null, lng: null };
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        sosLocation.lat = pos.coords.latitude;
                        sosLocation.lng = pos.coords.longitude;
                        document.getElementById('sosGPSStatus').textContent =
                            `📍 Position obtenue (précision ~${Math.round(pos.coords.accuracy)}m)`;
                    },
                    () => {
                        document.getElementById('sosGPSStatus').textContent = '⚠️ Position GPS indisponible — alerte sans localisation';
                    }
                );
            }

            // Countdown 5s
            let count = 5;
            document.getElementById('sosCountdownNum').textContent = count;
            clearInterval(sosCountdownTimer);
            sosCountdownTimer = setInterval(() => {
                count--;
                document.getElementById('sosCountdownNum').textContent = count;
                if (count <= 0) {
                    clearInterval(sosCountdownTimer);
                    document.getElementById('sosConfirmBtn').disabled = false;
                }
            }, 1000);
        }

        function cancelSOS() {
            clearInterval(sosCountdownTimer);
            document.getElementById('sosModalOverlay').classList.remove('open');
        }

        async function confirmSOS() {
            document.getElementById('sosConfirmBtn').disabled = true;
            try {
                const r = await fetch('sos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'trigger',
                        lat: sosLocation.lat,
                        lng: sosLocation.lng,
                        accuracy: null
                    })
                });
                const d = await r.json();
                cancelSOS();
                showToast(d.message, d.success ? 'success' : 'danger');
            } catch (e) {
                cancelSOS();
                showToast('Erreur lors de l\'envoi SOS', 'danger');
            }
        }

        // Close SOS modal on overlay click
        document.getElementById('sosModalOverlay').addEventListener('click', function (e) {
            if (e.target === this) cancelSOS();
        });

        // CSS spin keyframe
        const style = document.createElement('style');
        style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(style);

        // ════════════════════════════════════════════
        //  AVATAR DROPDOWN (handled by main.js)
        // ════════════════════════════════════════════


        // ════════════════════════════════════════════
        //  BOOT
        // ════════════════════════════════════════════
        init();
        // Refresh network every 30s
        setInterval(loadNetwork, 30000);
    </script>
<script src="assets_sinistre_traitement/js/main.js"></script>
</body>

</html>
