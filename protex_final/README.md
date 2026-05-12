# Protex Assurance — Projet Intégration Esprit
**PHP 8 · MVC · MySQL · XAMPP**

---

## Démarrage rapide

### 1. Prérequis
- XAMPP avec Apache (port **8081**) + MySQL (port **3306**)
- PHP 8.1+

### 2. Base de données
```sql
-- Dans phpMyAdmin : créer la base "assurance" puis importer :
assurance_complete.sql
```

### 3. Configuration
Copier et remplir le fichier de credentials :
```
cp config.env.php config.env.local.php
```
Éditer `config.env.local.php` :
```php
define('DB_HOST',     '127.0.0.1');
define('DB_NAME',     'assurance');
define('DB_USER',     'root');
define('DB_PASS',     '');          // ← votre mot de passe MySQL

define('SMTP_HOST',   'smtp.gmail.com');
define('SMTP_USER',   'votre@email.com');
define('SMTP_PASS',   'votre_app_password');

define('STRIPE_SECRET_KEY',      'sk_test_...');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');

define('INFOBIP_API_KEY',  '...');
define('INFOBIP_BASE_URL', 'https://xxxxx.api.infobip.com');

define('CLAUDE_API_KEY', 'sk-ant-...');
```

Puis dans `config.php`, ajouter avant la fin :
```php
if (file_exists(__DIR__ . '/config.env.local.php'))
    require_once __DIR__ . '/config.env.local.php';
```

### 4. Dossier uploads
```bash
mkdir -p uploads/messages uploads/sinistres uploads/recus
chmod 755 uploads -R
```

### 5. Accès
| Rôle | URL | Email | Mot de passe |
|---|---|---|---|
| SuperAdmin | `/view/BackOffice/connexion.php` | medkarimmiledi@gmail.com | Azerty123! |
| Admin | `/view/BackOffice/connexion.php` | medkarimmiledi123@gmail.com | Azerty123! |
| Agent | `/view/BackOffice/connexion.php` | muledikarim@gmail.com | Azerty123! |
| Client | `/view/BackOffice/connexion.php` | sansbibi@gmail.com | Azerty123! |

---

## Structure du projet

```
protex_final/
├── config.php                  BASE_URL dynamique, PDO singleton
├── config.env.php              Template credentials (ne pas modifier)
├── bootstrap.php               Chargement modèles + session CSRF
├── api.php                     API JSON (auth guard + RBAC)
├── assurance_complete.sql      Base de données complète (30 tables)
│
├── helpers/
│   ├── RoleHelper.php          Lecture session dual-key (user_id/id_user…)
│   └── CsrfHelper.php          Protection CSRF
│
├── controller/
│   ├── AuthController.php      Login (bcrypt) + logout + double session
│   ├── ContratController.php   Gestion contrats (BackOffice + FrontOffice)
│   ├── DevisController.php     Gestion devis
│   ├── OffreController.php     Gestion offres
│   ├── PaiementController.php  Paiements BackOffice
│   ├── RouletteController.php  Roulette de fidélité
│   ├── MessagerieController.php Messagerie interne
│   ├── DashboardController.php Tableau de bord admin
│   ├── EmailService.php        PHPMailer (vendor ou lib/ fallback)
│   └── FrontOffice/
│       └── PaiementController.php  Stripe + paiement client
│
├── model/
│   ├── Contrat.php             Modèle unifié (id_user/id_client détecté auto)
│   ├── Devis.php, Offre.php, Paiement.php
│   ├── ReclamationModel.php    Réclamations
│   ├── ReponseModel.php        Réponses réclamations
│   ├── Message.php             Messagerie interne
│   ├── JeuSnake.php, JeuMemory.php
│   └── …
│
├── view/
│   ├── FrontOffice/            Interface client
│   │   ├── client.html         Dashboard client
│   │   ├── ajoutdevis.php      Formulaire devis
│   │   ├── mes_devis.php       Mes devis
│   │   ├── contrat_list_client.php  Mes contrats
│   │   ├── sinistre_list_user.php   Mes sinistres
│   │   ├── paiement.php        Paiement
│   │   ├── stripe-payment.php  Paiement Stripe
│   │   ├── reclamationList.php Réclamations
│   │   ├── roulette.php        Roulette fidélité
│   │   ├── jeux.php            Hub jeux
│   │   ├── snake.php, memory.php
│   │   └── assets/includes/navbar.php
│   │
│   └── BackOffice/             Interface admin/agent
│       ├── connexion.php       Page de connexion
│       ├── devis.php           Gestion devis
│       ├── offres.php          Gestion offres
│       ├── paiements.php       Paiements
│       ├── sinistres_back.php  Sinistres
│       ├── reclamations_back.php Réclamations
│       ├── messagerie.php      Chat interne
│       ├── fraud_analyse.php   Antifraud IA
│       ├── admin-users.php     Gestion utilisateurs
│       └── assets/includes/sidebar.php
│
├── migrations/
│   ├── 001_link_devis_paiement.sql
│   ├── 002_messagerie_interne.sql
│   ├── 003_contrats.sql
│   ├── 004_jeu_snake.sql
│   └── 005_jeu_memory.sql
│
├── lib/
│   ├── PHPMailer/              Fallback si vendor/ absent
│   └── stripe-php/
│
├── uploads/                    Fichiers uploadés (messages, sinistres, reçus)
└── service/                    Services tiers (Infobip SMS, Claude AI)
```

---

## Modules intégrés

| Module | Source | Responsable |
|---|---|---|
| Gestion paiements / offres | mon_dossier | Module A |
| Roulette de fidélité | mon_dossier | Module A |
| Messagerie interne admin | mon_dossier | Module A |
| Jeux (Snake, Memory) | mon_dossier | Module A |
| Gestion sinistres | dossier_camarades | Module B |
| Gestion réclamations | dossier_camarades | Module B |
| Antifraud IA (Claude) | dossier_camarades | Module B |
| Gestion utilisateurs / agences | dossier_camarades | Module B |
| Traitement sinistres | dossier_camarades | Module B |

---

## Points techniques clés

### BASE_URL dynamique
`config.php` calcule automatiquement le chemin depuis `DOCUMENT_ROOT` :
```php
define('BASE_URL', rtrim(substr(realpath(__DIR__), strlen(realpath($_SERVER['DOCUMENT_ROOT']))), '/'));
// Résultat : /integration/protex_final
```

### Session unifiée
Chaque module utilisait des clés session différentes. `AuthController::writeSession()` écrit les deux :
```php
$_SESSION['user_id'] = $_SESSION['id_user'] = $id;
$_SESSION['role']    = $_SESSION['user_role'] = $role;
$_SESSION['nom']     = $_SESSION['user_nom']  = $nom;
```

### Contrat — double schéma
La table `contrat` utilise `id_user` (schéma camarades). `ContratController::getUserColumn()` détecte la colonne présente via `information_schema` pour compatibilité.

---

## Dépendances optionnelles

| Dépendance | Usage | Fallback |
|---|---|---|
| `vendor/` (Composer) | PHPMailer via autoload | `lib/PHPMailer/` |
| Stripe PHP SDK | Paiements carte | Désactivé si clé absente |
| Infobip API | SMS alerts | Log en base, pas d'envoi |
| Claude API | Analyse antifraud | Message d'erreur gracieux |
