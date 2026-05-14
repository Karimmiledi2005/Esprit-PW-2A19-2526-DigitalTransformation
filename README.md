<div align="center">

<img src="assurance/welcome/img/icon/icon-02-primary.png" alt="Protex Logo" width="80"/>

# Protex — Digital Insurance Platform

**Plateforme d'assurance digitale complète — Esprit School of Engineering**

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Python](https://img.shields.io/badge/Python-3.10+-3776AB?style=flat-square&logo=python&logoColor=white)](https://python.org)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Flask](https://img.shields.io/badge/Flask-3.0-000000?style=flat-square&logo=flask&logoColor=white)](https://flask.palletsprojects.com)
[![Stripe](https://img.shields.io/badge/Stripe-Payments-635BFF?style=flat-square&logo=stripe&logoColor=white)](https://stripe.com)
[![License](https://img.shields.io/badge/License-Academic-orange?style=flat-square)](LICENSE)

*Projet Web — 2ème Année Ingénierie | 2025–2026*

</div>

---

## 📋 Présentation

**Protex** est une plateforme d'assurance digitale full-stack permettant aux clients de souscrire des contrats d'assurance, déclarer des sinistres, effectuer des paiements et soumettre des réclamations — entièrement en ligne, sans aucun déplacement physique.

La plateforme intègre de l'intelligence artificielle (OCR, reconnaissance faciale, détection de fraude, chatbot IA, recommandation de contrats) et des services tiers (Stripe, PHPMailer, Infobip SMS, GitHub OAuth).

### Trois espaces distincts

| Espace | Rôle | Accès |
|--------|------|-------|
| 🖥️ **Front-office** | Interface client intuitive et sécurisée | `/view/FrontOffice/client.php` |
| 🔧 **Back-office Agent** | Gestion des dossiers et traitement des sinistres | `/view/BackOffice/dashboard.php` |
| 👑 **Back-office Admin** | Dashboard global, statistiques et contrôle d'accès | `/view/BackOffice/admin.php` |

---

## ✨ Fonctionnalités

### 🔐 Module 1 — Identity & Access Engine *(Karim Miledi)*

**Authentification & Gestion des utilisateurs**
- Inscription client avec validation email et OTP
- Connexion / déconnexion sécurisée avec gestion de session (timeout 30min, `session_regenerate_id`)
- **Reconnaissance faciale** (Flask + OpenCV LBPH, port 5000) — login & inscription sans mot de passe
- **CAPTCHA personnalisé** (3 types de défis, port 5006) — protection anti-bot
- **GitHub OAuth 2.0** — connexion via compte GitHub avec état CSRF
- Réinitialisation de mot de passe par email (token sécurisé)
- Gestion de profil : avatar, informations personnelles, changement de mot de passe
- Système RBAC (SuperAdmin / Admin Agence / Agent / Client) avec guards de session
- Audit log des connexions, brute-force protection (table `login_attempts`)
- Tokens CSRF sur tous les formulaires POST
- Hachage bcrypt + PDO préparé (`ATTR_EMULATE_PREPARES = false`)

### 📄 Module 2 — Policy Management System *(Meriem Bouazizi)*

**Contrats & Souscriptions**
- Catalogue d'offres d'assurance : Auto, Santé, Habitation, Vie, Protection
- Souscription en ligne avec simulation de prime
- Contrats spécifiques : `contrat_auto.php`, `contrat_sante.php`, `contrat_habitation.php`, `contrat_protection.php`
- **Export PDF** des contrats signés (Dompdf)
- **QR Code** de vérification par contrat (endroid/qr-code)
- **Calendrier des contrats** avec alertes d'expiration
- Renouvellement de contrat en ligne
- **Recommandation IA** de contrat selon le profil client (`ai_contract/generate_need_contract.py`)
- Formules et garanties par catégorie
- Alerte SMS expiration (cron + Infobip)

### 🚨 Module 3 — Claim Processing Hub *(Abderrahmen Ben Abdallah)*

**Sinistres & Traitements**
- Déclaration de sinistre avec upload photo
- Suivi en temps réel (En attente / Validé / Refusé / Remboursé)
- **OCR** extraction automatique de documents (`ocr_engine.py`, port 5007, OpenCV preprocessing)
- **Détection de fraude IA** — scoring de risque (`FraudeController.php`, `FraudeService.php`)
- Panel de fraude avec analyse et statistiques
- Assignation automatique d'agent au sinistre
- Notifications email automatiques (PHPMailer, 6 triggers)
- Timeline de traitement dans l'espace client
- Commentaires sur sinistre
- Statistiques sinistres par période

### 💳 Module 4 — Payment & Quotation Engine *(Sadok Berrzouga)*

**Paiements & Devis**
- Demande de devis en ligne avec comparatif d'offres
- **Paiement sécurisé Stripe** (mode test, Checkout Session)
- Historique des transactions avec téléchargement de reçu PDF
- Suivi des devis : En attente / Accepté / Refusé
- Conversion devis → contrat → paiement
- Dashboard KPIs : revenus, taux de conversion, taux d'acceptation
- **Diagnostic IA** du tableau de bord (analyse automatique + recommandations)
- Alertes paiements en retard
- Export CSV/PDF des données

### 🏢 Module 5 — Agency & Workforce Manager *(Yessin Ben Hamza)*

**Agences & Personnel**
- Répertoire d'agences avec **carte interactive Leaflet/OpenStreetMap**
- Gestion CRUD des agences et postes (admin)
- Système de notation client/agent
- Contact direct avec les agents
- Dashboard de performance par agent
- Isolation des données par agence (admin agence ne voit que ses données)
- Messagerie interne entre utilisateurs (`MessagerieController.php`)
- Système SOS géolocalisé (`sos.php`)

### 📝 Module 6 — Claim & Complaint Manager *(Sabrine Mchabet)*

**Réclamations & Réponses**
- Soumission de réclamation en ligne avec suivi
- Réponse agent/admin avec mise à jour de statut
- **Classification automatique** des priorités par IA
- Historique complet des réclamations
- Suggestion de réponse automatique (`suggest_response.php`)
- Export des réclamations (CSV/PDF)
- Statistiques dashboard admin
- Système de commentaires et réponses imbriquées

### 🤖 Intelligence Artificielle

| Service | Technologie | Port | Description |
|---------|-------------|------|-------------|
| Reconnaissance faciale | Flask + OpenCV LBPH | 5000 | Login/inscription sans mot de passe |
| OCR documents | Flask + Tesseract + OpenCV | 5007 | Extraction automatique de données |
| CAPTCHA IA | Flask + Puzzle Engine | 5006 | Défi puzzle anti-bot |
| Détection fraude | PHP + Claude API | — | Scoring de risque sur sinistres |
| Chatbot assurance | Groq API (LLaMA) | — | Assistant virtuel client |
| Recommandation contrat | Python local | — | Proposition personnalisée |

### 🌱 Développement Durable
- Services 100% digitaux — zéro déplacement physique requis
- Contrats, reçus et réclamations entièrement dématérialisés
- Éco-score intégré dans les contrats automobile
- Offre "Contrat Vert" pour véhicules électriques

### 🎮 Fonctionnalités Bonus
- **Jeu Memory** et **Jeu Snake** pour fidélisation client (`jeux.php`, `memory.php`, `snake.php`)
- **Roulette des gains** avec envoi email du gain (`roulette.php`, `roulette_email.php`)
- **Réseau social** entre clients d'une même agence (`reseau.php`) : amis, invitations, posts, likes
- **Parrainage** avec système de points
- Notifications en temps réel

---

## 🏗️ Architecture

```
Protex/
├── 📁 controller/                    # Contrôleurs MVC
│   ├── AuthController.php            # Auth, login, register, GitHub OAuth
│   ├── DashboardController.php       # KPIs, diagnostic IA, stats
│   ├── SinistreController.php        # Gestion des sinistres
│   ├── ContratController.php         # Contrats et souscriptions
│   ├── DevisController.php           # Devis et conversions
│   ├── PaiementController.php        # Paiements et Stripe
│   ├── OffreController.php           # Catalogue d'offres
│   ├── ReclamationController.php     # Réclamations
│   ├── ReponseController.php         # Réponses aux réclamations
│   ├── AgenceController.php          # Agences et postes
│   ├── FraudeController.php          # Détection de fraude
│   ├── FraudeService.php             # Service IA fraude
│   ├── ChatbotController.php         # Chatbot Groq IA
│   ├── MessagerieController.php      # Messagerie interne
│   ├── RecommandationController.php  # Recommandation contrats
│   ├── JeuController.php             # Jeux gamification
│   ├── RouletteController.php        # Roulette gains
│   ├── StripePaymentController.php   # Intégration Stripe
│   ├── TraitementController.php      # Traitements sinistres
│   ├── FormuleController.php         # Formules assurance
│   ├── GarantieController.php        # Garanties
│   ├── CategorieController.php       # Catégories
│   ├── PosteController.php           # Postes d'agence
│   └── MailerService.php             # Service email PHPMailer
│
├── 📁 model/                         # Modèles PDO
│   ├── User.php                      # Utilisateurs
│   ├── Contrat.php                   # Contrats
│   ├── Sinistre.php                  # Sinistres
│   ├── Devis.php                     # Devis
│   ├── Paiement.php                  # Paiements
│   ├── Offre.php                     # Offres
│   ├── Formule.php                   # Formules
│   ├── Garantie.php                  # Garanties
│   ├── Categorie.php                 # Catégories
│   ├── Traitement.php                # Traitements
│   ├── ReclamationModel.php          # Réclamations
│   ├── ReponseModel.php              # Réponses
│   ├── Message.php                   # Messages
│   ├── JeuMemory.php                 # Jeu Memory
│   ├── JeuSnake.php                  # Jeu Snake
│   └── Roulette.php                  # Roulette
│
├── 📁 view/
│   ├── 📁 BackOffice/                # Interface administration
│   │   ├── dashboard.php             # Dashboard KPIs + Diagnostic IA
│   │   ├── admin.php                 # Gestion globale
│   │   ├── admin-users.php           # Gestion utilisateurs
│   │   ├── admin-agences.php         # Gestion agences
│   │   ├── sinistre_list.php         # Liste sinistres
│   │   ├── fraud_panel.html          # Panel anti-fraude
│   │   ├── contrats_back.php         # Gestion contrats
│   │   ├── devis/ liste.php          # Gestion devis
│   │   ├── paiements/ liste.php      # Gestion paiements
│   │   ├── offres/ liste.php         # Gestion offres
│   │   └── assets/                   # CSS/JS BackOffice
│   │
│   └── 📁 FrontOffice/               # Interface client
│       ├── client.php                # Dashboard client dynamique
│       ├── login.php                 # Connexion (+ Face ID + GitHub)
│       ├── registre.php              # Inscription
│       ├── contrat.php               # Mes contrats
│       ├── declarer-sinistre.php     # Déclaration sinistre + OCR
│       ├── paiement.php              # Paiement Stripe
│       ├── offres.php                # Catalogue offres
│       ├── reclamationList.php       # Mes réclamations
│       ├── reseau.php                # Réseau social
│       ├── chat.php                  # Messagerie
│       ├── chatbot.php               # Assistant IA
│       ├── roulette.php              # Roulette gains
│       ├── jeux.php                  # Espace jeux
│       ├── sos.php                   # Alerte SOS
│       └── api_client_dashboard.php  # API JSON dashboard
│
├── 📁 helpers/
│   ├── SessionGuard.php              # Gestion sessions + RBAC
│   ├── RoleHelper.php                # Helpers de rôle
│   └── CsrfHelper.php                # Protection CSRF
│
├── 📁 service/
│   ├── EmailService.php              # PHPMailer SMTP
│   ├── SmsService.php                # Infobip SMS
│   └── WhatsAppService.php           # WhatsApp API
│
├── 📁 face_api/                      # Microservice Python
│   └── face_engine.py                # Flask + OpenCV LBPH (port 5000)
│
├── 📁 ai_contract/
│   └── generate_need_contract.py     # Recommandation contrat IA
│
├── 📁 welcome/                       # Landing page publique
│   ├── index.html                    # Page d'accueil 3D (Three.js)
│   └── api_stats.php                 # Stats publiques
│
├── ocr_engine.py                     # OCR Flask (port 5007)
├── puzzle_engine.py                  # CAPTCHA Flask (port 5006)
├── config.php                        # Configuration centrale
├── config.env.php                    # Variables d'env (ne pas committer)
├── bootstrap.php                     # Autoload Composer
├── index.php                         # Point d'entrée
├── cron_sla_alert.php                # Cron alertes SLA
├── cron_sms_expiration.php           # Cron SMS expiration contrats
├── protex_full_seed.sql              # Données de test
├── requirements.txt                  # Dépendances Python
├── composer.json                     # Dépendances PHP
├── Lancer_Tous_Les_Moteurs.bat       # Démarrage tous les services
├── Lancer_Protex_FaceID.bat          # Démarrage Face ID
└── START_CAPTCHA.bat                 # Démarrage CAPTCHA
```

### Flux MVC

```
Navigateur
    │
    ▼
index.php ──► controller/XxxController.php
                    │
                    ├──► model/XxxModel.php ──► MySQL (PDO)
                    │
                    ├──► service/EmailService.php ──► SMTP Gmail
                    │         SmsService.php ──────► Infobip
                    │
                    ├──► face_api/face_engine.py ──► Flask:5000
                    │    ocr_engine.py ─────────────► Flask:5007
                    │    puzzle_engine.py ───────────► Flask:5006
                    │
                    └──► view/BackOffice/ ou FrontOffice/
```

---

## 🗄️ Base de données

**Tables principales :**

| Table | Description |
|-------|-------------|
| `user` | Utilisateurs (nom, prénom, email, rôle, avatar, face_registered, points) |
| `agence` | Agences avec coordonnées GPS |
| `poste` | Postes par agence |
| `offre` | Offres d'assurance (auto, santé, habitation, vie) |
| `formule` | Formules par offre |
| `garantie` | Garanties par formule |
| `categorie` | Catégories de contrats |
| `devis` | Demandes de devis |
| `contrat` | Contrats souscrits |
| `sinistre` | Déclarations de sinistres |
| `traitement` | Traitements des sinistres |
| `paiement` | Transactions de paiement |
| `reclamation` | Réclamations clients |
| `reponse` | Réponses aux réclamations |
| `message` | Messagerie interne |
| `notification` | Notifications temps réel |
| `login_attempts` | Protection brute-force |
| `audit_log` | Journal d'activité |

---

## 🛠️ Stack Technique

### Backend
| Technologie | Usage |
|-------------|-------|
| **PHP 8.0+** | Backend principal, MVC natif |
| **PDO + MySQL** | Base de données (ATTR_EMULATE_PREPARES = false) |
| **Python 3.10 + Flask** | Microservices IA (OCR, Face ID, CAPTCHA) |
| **Composer** | Autoload PSR-4 + dépendances |

### Frontend
| Technologie | Usage |
|-------------|-------|
| **HTML5 / CSS3** | Structure et style |
| **JavaScript (Vanilla)** | Dynamique, fetch API, DOM |
| **Three.js** | Hero 3D de la landing page |
| **Bootstrap Icons** | Iconographie |
| **Leaflet.js** | Carte interactive agences |
| **Chart.js** | Graphiques dashboard |

### Services Tiers & APIs
| Service | Usage |
|---------|-------|
| **Stripe** | Paiement sécurisé en ligne |
| **PHPMailer + SMTP Gmail** | Emails transactionnels (6 triggers) |
| **Infobip** | SMS d'alerte expiration |
| **GitHub OAuth 2.0** | Connexion sociale |
| **Groq API (LLaMA)** | Chatbot assistant client |
| **Claude API (Anthropic)** | Détection de fraude |
| **OpenCV LBPH** | Reconnaissance faciale |
| **Tesseract OCR** | Extraction de données documents |
| **Dompdf** | Génération PDF contrats/reçus |
| **endroid/qr-code** | QR Code sur contrats |

### Dépendances PHP (Composer)
```json
{
  "phpmailer/phpmailer": "^7.0",
  "endroid/qr-code": "^5.1",
  "vlucas/phpdotenv": "^5.6",
  "dompdf/dompdf": "^3.1",
  "thecodingmachine/safe": "^3.4"
}
```

### Dépendances Python (pip)
```
Flask==3.0.3
Flask-Cors==4.0.0
opencv-contrib-python==4.9.0.80
numpy
deepface
pytesseract
Pillow
tf-keras
```

---

## 🚀 Installation

### Prérequis
- XAMPP (Apache + MySQL)
- PHP >= 8.0
- Python >= 3.10
- Composer
- Navigateur moderne (Chrome, Firefox, Edge)

### 1. Cloner le dépôt

```bash
git clone https://github.com/Karimmiledi2005/Esprit-PW-2A19-2526-DigitalTransformation.git
cd Esprit-PW-2A19-2526-DigitalTransformation
```

### 2. Placer dans XAMPP

```
C:/xampp/htdocs/assurance/
```

### 3. Installer les dépendances PHP

```bash
composer install
```

### 4. Installer les dépendances Python

```bash
pip install -r requirements.txt
```

### 5. Configurer la base de données

- Ouvrir `http://localhost/phpmyadmin`
- Créer une base nommée `assurance`
- Importer `protex_full_seed.sql`

### 6. Configurer l'environnement

Créer/éditer `config.env.php` :

```php
return [
    'db_host'     => 'localhost',
    'db_user'     => 'root',
    'db_name'     => 'assurance',
    'db_pass'     => '',

    // SMTP Gmail
    'mail_host'     => 'smtp.gmail.com',
    'mail_port'     => 587,
    'mail_username' => 'votre-email@gmail.com',
    'mail_from'     => 'votre-email@gmail.com',

    // Stripe (test mode)
    'stripe_secret_key'      => 'sk_test_...',
    'stripe_publishable_key' => 'pk_test_...',

    // Infobip SMS
    'infobip_api_key'  => 'votre-cle',
    'infobip_base_url' => 'https://xxxx.api.infobip.com',

    // Groq (Chatbot)
    'groq_api_key' => 'gsk_...',
];
```

### 7. Démarrer les microservices IA

**Option A — Script automatique (Windows)**
```batch
# Tous les services
Lancer_Tous_Les_Moteurs.bat

# Ou individuellement
Lancer_Protex_FaceID.bat   # Face ID (port 5000)
START_CAPTCHA.bat           # CAPTCHA (port 5006)
```

**Option B — Manuel**
```bash
# Face ID
python face_api/face_engine.py       # → http://localhost:5000

# OCR
python ocr_engine.py                 # → http://localhost:5007

# CAPTCHA
python puzzle_engine.py              # → http://localhost:5006
```

### 8. Accéder à l'application

```
http://localhost/assurance/
```





## 🔑 Comptes de test

<<<<<<< HEAD
- **Esprit School of Engineering** for academic supervision and support
- Our supervisor **** for guidance throughout the project
- Official documentation for PHP
- GitHub Education Program

---

> *Protex — Smart, green and 100% digital insurance.*
> 
=======
| Rôle | Email | Mot de passe |
|------|-------|--------------|
| **SuperAdmin** | admin@protex.tn | admin123 |
| **Admin Agence** | adminagence@protex.tn | admin123 |
| **Agent** | agent@protex.tn | agent123 |
| **Client** | client@protex.tn | client123 |

---

## 🔒 Sécurité

- ✅ Sessions PHP avec timeout 30 minutes
- ✅ `session_regenerate_id(true)` après chaque login
- ✅ Tokens CSRF sur tous les formulaires POST
- ✅ Hachage `bcrypt` des mots de passe
- ✅ PDO préparé avec `ATTR_EMULATE_PREPARES = false`
- ✅ Protection brute-force (table `login_attempts`)
- ✅ Guards de session RBAC sur toutes les pages sensibles
- ✅ XSS prevention avec `htmlspecialchars()`
- ✅ Isolation des données par agence (admin agence)
- ✅ APIs JSON protégées par session obligatoire
- ✅ GitHub OAuth avec état CSRF

---

## 👥 Contributeurs

| Nom | GitHub | Module |
|-----|--------|--------|
| **Karim Miledi** | [@Karimmiledi2005](https://github.com/Karimmiledi2005) | Module 1 — Identity & Access Engine |
| **Meriem Bouazizi** | [@merybz](https://github.com/merybz) | Module 2 — Policy Management System |
| **Abderrahmen Ben Abdallah** | [@avdoll89](https://github.com/avdoll89) | Module 3 — Claim Processing Hub |
| **Sadok Berrzouga** | [@sadok09](https://github.com/sadok09) | Module 4 — Payment & Quotation Engine |
| **Yessin Ben Hamza** | — | Module 5 — Agency & Workforce Manager |
| **Sabrine Mchabet** | [@MchabetSabrine04](https://github.com/MchabetSabrine04) | Module 6 — Claim & Complaint Manager |

---

## 🎓 Contexte Académique

| | |
|---|---|
| **École** | Esprit School of Engineering — Tunisie |
| **Programme** | Technologies Web — 2ème Année Ingénierie |
| **Classe** | 2A19 |
| **Année académique** | 2025–2026 |
| **Superviseur** | MOUNIRA HMAYDA |
| **Thème** | Transformation Digitale des Services & Organisations |
| **Dépôt GitHub** | [Esprit-PW-2A19-2526-DigitalTransformation](https://github.com/Karimmiledi2005/Esprit-PW-2A19-2526-DigitalTransformation) |

---

## 📄 Licence

Projet académique développé à **Esprit School of Engineering**.
Usage éducatif uniquement.

---

<div align="center">

*Protex — Smart, green and 100% digital insurance.*

**Esprit School of Engineering | 2A19 | 2025–2026**

</div>
>>>>>>> 103fd3f (readme)
