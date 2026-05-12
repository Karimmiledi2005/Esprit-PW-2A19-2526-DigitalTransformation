# 📍 AGENCES - COORDONNÉES ET COLONNES

## 🔧 COLONNES AJOUTÉES À LA TABLE `agence`

| Colonne | Type | Valeur par défaut | Description |
|---------|------|-------------------|-------------|
| `id_agence` | INT (PRIMARY KEY, AUTO_INCREMENT) | - | Identifiant unique de l'agence |
| `nom_agence` | VARCHAR(255) | - | Nom de l'agence |
| `adresse` | VARCHAR(255) | NULL | Adresse complète de l'agence |
| `pays` | VARCHAR(100) | 'Tunisie' | Pays de l'agence |
| `tel` | VARCHAR(20) | - | Numéro de téléphone |
| `email` | VARCHAR(255) | - | Email de l'agence |
| `statut` | ENUM('actif','inactif') | 'actif' | Statut de l'agence |
| `date_creation` | TIMESTAMP | CURRENT_TIMESTAMP | Date de création |

---

## 📍 COORDONNÉES DES 3 AGENCES

### 🏢 1. AGENCE TUNIS
```sql
INSERT INTO agence (nom_agence, adresse, pays, tel, email, statut, date_creation) VALUES
('Agence Tunis', '123 Avenue Bourguiba, Tunis 1000', 'Tunisie', '+216 71 123 456', 'tunis@protex.tn', 'actif', NOW());
```

**Détails:**
- **Nom:** Agence Tunis
- **Adresse:** 123 Avenue Bourguiba, Tunis 1000
- **Pays:** Tunisie
- **Tél:** +216 71 123 456
- **Email:** tunis@protex.tn
- **Statut:** Actif

---

### 🏢 2. AGENCE SFAX
```sql
INSERT INTO agence (nom_agence, adresse, pays, tel, email, statut, date_creation) VALUES
('Agence Sfax', '456 Rue Mongi Slim, Sfax 3000', 'Tunisie', '+216 74 234 567', 'sfax@protex.tn', 'actif', NOW());
```

**Détails:**
- **Nom:** Agence Sfax
- **Adresse:** 456 Rue Mongi Slim, Sfax 3000
- **Pays:** Tunisie
- **Tél:** +216 74 234 567
- **Email:** sfax@protex.tn
- **Statut:** Actif

---

### 🏢 3. AGENCE SOUSSE
```sql
INSERT INTO agence (nom_agence, adresse, pays, tel, email, statut, date_creation) VALUES
('Agence Sousse', '789 Avenue de la Côte, Sousse 4000', 'Tunisie', '+216 73 345 678', 'sousse@protex.tn', 'actif', NOW());
```

**Détails:**
- **Nom:** Agence Sousse
- **Adresse:** 789 Avenue de la Côte, Sousse 4000
- **Pays:** Tunisie
- **Tél:** +216 73 345 678
- **Email:** sousse@protex.tn
- **Statut:** Actif

---

## 🔄 REQUÊTE SQL DE MIGRATION

```sql
-- Ajouter colonnes manquantes à agence
ALTER TABLE agence ADD COLUMN IF NOT EXISTS adresse VARCHAR(255) DEFAULT NULL;
ALTER TABLE agence ADD COLUMN IF NOT EXISTS pays VARCHAR(100) DEFAULT 'Tunisie';
ALTER TABLE agence ADD COLUMN IF NOT EXISTS statut ENUM('actif','inactif') DEFAULT 'actif';
```

---

## 📊 EXEMPLE DE REQUÊTE POUR VOIR LES AGENCES

```sql
SELECT 
    id_agence,
    nom_agence,
    adresse,
    pays,
    tel,
    email,
    statut,
    date_creation
FROM agence;
```

---

## ✅ VÉRIFICATION APRÈS IMPORT

Après l'import de `seed_test_data.sql`, vous pouvez vérifier les données:

```sql
SELECT * FROM agence;
```

**Résultat attendu:**
- 3 agences crées (Tunis, Sfax, Sousse)
- Chaque agence avec adresse, pays, statut actif
- Tous les contacts présents
