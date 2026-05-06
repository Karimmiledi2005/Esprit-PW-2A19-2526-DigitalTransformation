# 📧 Installation PHPMailer — Protex Assurance

## Étape 1 — Installer PHPMailer avec Composer

Ouvrez un terminal dans le **dossier racine du projet** (`Version_Final_avecEmail/`) et exécutez :

```bash
composer require phpmailer/phpmailer
```

Cela crée automatiquement le dossier `vendor/` avec PHPMailer et son autoloader.

---

## Étape 2 — Configurer le fichier `.env`

Ouvrez le fichier `.env` à la racine du projet et remplissez les variables SMTP :

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre.email@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_FROM=votre.email@gmail.com
MAIL_FROM_NAME=Protex Assurance
```

### 🔑 Comment obtenir le mot de passe Gmail (App Password)

1. Allez sur [myaccount.google.com](https://myaccount.google.com)
2. **Sécurité** → activez la **Validation en 2 étapes**
3. Revenez dans **Sécurité** → **Mots de passe des applications**
4. Choisissez "Autre (nom personnalisé)" → tapez `Protex`
5. Cliquez **Générer** → copiez le mot de passe de 16 caractères (sans espaces)
6. Collez-le dans `MAIL_PASSWORD` du fichier `.env`

---

## Étape 3 — Vérifier la structure finale

```
Version_Final_avecEmail/
├── composer.json          ✅ déjà présent
├── vendor/                ✅ créé après composer install
│   └── autoload.php
├── .env                   ✅ à configurer
├── config/
│   └── env.php            ✅ lit automatiquement le .env
├── view/BackOffice/
│   └── addreponse.php     ✅ utilise PHPMailer
└── ...
```

---

## 🧪 Comment tester

1. Connectez-vous en tant qu'**admin** → partie BackOffice → Réclamations
2. Cliquez **Répondre** sur une réclamation
3. Rédigez votre réponse → cliquez **Envoyer**
4. L'email arrive automatiquement dans la boîte du client

---

## ❗ Si l'email n'arrive pas

- Vérifiez les logs PHP : `error_log` dans `php_error.log` (XAMPP : `C:/xampp/php/logs/`)
- Cherchez les lignes `[Protex Mail]` pour voir l'erreur exacte
- Vérifiez que le mot de passe d'application Gmail est correct (sans espaces)
- Vérifiez que la validation en 2 étapes est bien activée sur votre compte Google
