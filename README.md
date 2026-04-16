# Protex – Digital Insurance Platform

## Overview

This project was developed as part of the **Technologies Web – 2nd Year Engineering Program** at **Esprit School of Engineering** (Academic Year 2025–2026).

**Protex** is a full-stack web application that allows clients to subscribe to insurance contracts, declare claims, make payments and submit complaints — entirely online, without any physical displacement.

The platform provides three distinct spaces:
- **Front-office** — intuitive and secure client interface
- **Agent back-office** — case management and claim processing
- **Admin back-office** — global dashboard, statistics and access control

---

## Features

### Module 1 — Identity & Access Engine
- Client registration, login and logout
- Profile management and password reset
- Role attribution (admin / agent / client)
- Account activation and deactivation
- Activity logs (connection history)

###  Module 2 — Policy Management System
- Browse and simulate insurance contracts
- Online subscription (auto, health, home)
- PDF export of signed contracts
- Agent validation of subscription requests
- AI-powered contract recommendation

### Module 3 — Claim Processing Hub
- Claim declaration with document upload
- Real-time status tracking (pending / validated / refused)
- Agent claim processing and status updates
- AI-based fraud detection (risk scoring)
- Automatic email notifications to client

###  Module 4 — Payment & Quotation Engine
- Quotation consultation and comparison
- Secure online premium payment
- Transaction history and PDF receipt download
- Late payment tracking and automatic reminders

###  Module 5 — Agency & Workforce Manager
- Agency directory with interactive map
- Direct agent contact
- Client / agent rating system
- CRUD management of agents and agencies (admin)
- Performance dashboard per agent

###  Module 6 — Claim & Complaint Manager
- Online complaint submission and tracking
- Agent / admin response with status update
- AI-based automatic priority classification
- Full complaint history
- Complaint statistics dashboard (admin)

### Artificial Intelligence
- Contract recommendation based on client profile (Module 2)
- Anti-fraud scoring on claim declarations (Module 3)
- Automatic priority classification of complaints (Module 6)

### Sustainable Development
- 100% digital services — zero physical displacement required
- Fully dematerialized contracts, receipts and complaints
- Eco-score integrated into automobile contracts
- "Green contract" offer for electric vehicles

---

## Tech Stack

### Frontend
- HTML5 / CSS3
- JavaScript 

### Backend
- PHP 8

### Tools & Environment
- XAMPP (Apache + MySQL local server)
- GitHub
- Visual Studio Code

---

## Architecture



## Contributors

| Name | GitHub | Module |
|------|--------|--------|
| Karim Miledi | [@Karimmiledi2005](https://github.com/Karimmiledi2005) | Module 1 — Identity & Access Engine |
| Meriem Bouazizi | [@merybz ](https://github.com/merybz) | Module 2 — Policy Management System |
| Abderrahmen Ben Abdallah | [@avdoll89 ](https://github.com/avdoll89) | Module 3 — Claim Processing Hub |
| Sadok Berrzouga | [@sadok09](https://github.com/sadok09) | Module 4 — Payment & Quotation Engine |
| Yessin Ben Hamza | [@username](https://github.com/) | Module 5 — Agency & Workforce Manager |
| Sabrine Mchabet | [@MchabetSabrine04](https://github.com/MchabetSabrine04) | Module 6 — Claim & Complaint Manager |

---

## Academic Context

Developed at **Esprit School of Engineering – Tunisia**

- **Program** : Technologies Web — 2nd Year Engineering
- **Class** : 2A19
- **Academic Year** : 2025–2026
- **Supervisor** : 
- **Theme** : Digital Transformation of Services & Organizations

---

## Getting Started

### Prerequisites
- XAMPP installed (Apache + MySQL)
- PHP >= 8.0
- Modern web browser (Chrome, Firefox, Edge)

### Installation

1. **Clone the repository:**
```bash
git clone https://github.com/Karimmiledi2005/Esprit-PW-2A19-2526-DigitalTransformation.git
cd Esprit-PW-2A19-2526-DigitalTransformation
```

2. **Place the project in the XAMPP folder:**
```
C:/xampp/htdocs/Protex/
```

3. **Start Apache and MySQL** from the XAMPP Control Panel.

4. **Import the database:**
   - Open `http://localhost/phpmyadmin`
   - Create a database named `protex_db`
   - Import the file `database/protex_db.sql`

5. **Configure the connection** in `includes/db.php`:
```php
$host     = "localhost";
$dbname   = "protex_db";
$user     = "root";
$password = "";
```

6. **Access the application:**
```
http://localhost/Protex/
```

### Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@protex.tn | admin123 |
| Agent | agent@protex.tn | agent123 |
| Client | client@protex.tn | client123 |

## Gestion Réclamation
| Todo | In Progress | Done |
|------|------------|------|
| ⬜ Le fonctionalités avancées  | 🔄Amélioration de l'interface | ✅ CRUD complet "Réclamation"  |
| ⬜ Le fonctionalités innovantes | 🔄 CRUD "Réponse" | ✅ Base de données |
|  | | ✅ la jointure associée |
|  | | ✅ integration de les interfaces |
## USER
| Todo | In Progress | Done |
|------|------------|------|
| ⬜ Le fonctionalités de bases  | 🔄Amélioration de l'interface | ✅ CRUD complet  |
| ⬜ Le fonctionalités innovantes | 🔄 Forget password | ✅ Base de données |
|  | | ✅ la jointure associée |
|  | | ✅ integration de les interfaces |
## Gestion Offre & Paiement
| Todo | In Progress | Done |
|------|------------|------|
| ⬜ Le fonctionalités avancées  | 🔄Amélioration de l'interface | ✅ CRUD complet "Offre"  |
| ⬜ Le fonctionalités innovantes | 🔄 CRUD "Paiement" | ✅ Base de données |
|  | | ✅ la jointure associée |
|  | | ✅ integration de les interfaces |
## Gestion Sinistre & Traitement
| 📝 Todo | 🔄 In Progress | ✅ Done |
|--------|--------------|--------|
| Fonctionnalités avancées (statistiques, recherche filtrée) | Amélioration de l’interface | CRUD |
|  |  | Base de données |
|  |  | Jointure Sinistre–Traitement |
|  |  | Intégration des interfaces |

## Gestion Contrat & Garantie

| 📝 Todo | 🔄 In Progress | ✅ Done |
|--------|--------------|--------|
| Fonctionnalités avancées (statistiques, filtrage des contrats) | Ajout & gestion des garanties | Ajout & gestion des contrats |
|  | CRUD "Garantie" | Affichage dynamique FrontOffice |
|  |  | Base de données |
|  |  | Intégration Front/Back |
|  |  | Intégration des interfaces |
|  |  | CRUD complet "Contrat" |



## Acknowledgments

- **Esprit School of Engineering** for academic supervision and support
- Our supervisor **** for guidance throughout the project
- Official documentation for PHP
- GitHub Education Program

---

> *Protex — Smart, green and 100% digital insurance.*
> 
