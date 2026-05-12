# Protex — Module 1 v2 : Changelog

## Nouveautés

### Rôles
| Rôle | Avant | Après |
|------|-------|-------|
| superadmin | ❌ inexistant | ✅ ajouté |
| admin | role='admin' sans distinction | admin_agence avec id_agence |
| agent | agence en varchar libre | id_agence FK vers table agence |
| client | identique | identique |

### controller/Client_Con.php
- `requireRole(array)` — middleware d'accès centralisé
- `isSuperAdmin()` / `isAdminAgence()` — helpers de rôle
- `getSessionAgence()` — retourne l'agence de l'admin connecté
- `addUserAdmin()` — règles de création selon rôle (superadmin > admin > agent > client)
- `updateUserAdmin()` — bloque un admin_agence de modifier un superadmin/admin
- `deleteUser()` — bloque un admin_agence de supprimer un superadmin/admin
- `toggleStatutUser()` — même protection
- `verifyOTP()` — charge `id_agence` en session après OTP validé
- `getAllAgences()` / `addAgence()` / `toggleStatutAgence()` — gestion agences (superadmin only)
- `buildUserFilters()` — isolation automatique par agence pour admin_agence
- `getStats()` — stats filtrées par agence pour admin_agence

### view/BackOffice/
| Fichier | Rôles autorisés |
|---------|----------------|
| admin_add_user.php | superadmin, admin, agent |
| admin_delete_user.php | superadmin, admin |
| admin_toggle_statut.php | superadmin, admin |
| admin_update_user.php | superadmin, admin |
| get_all_users.php | superadmin, admin, agent |
| get_stats.php | superadmin, admin |
| get_advanced_stats.php | superadmin, admin |
| search_users.php | superadmin, admin |
| **get_agences.php** *(nouveau)* | superadmin |
| **add_agence.php** *(nouveau)* | superadmin |
| **toggle_agence.php** *(nouveau)* | superadmin |

### view/FrontOffice/check_session.php
- Expose maintenant `role` et `id_agence` dans la réponse JSON

## Fichiers à ne pas modifier
- model/User.php — inchangé
- mailer/Mailer.php — inchangé
- connexion.php — inchangé
- FrontOffice/* — inchangés sauf check_session.php

## Migration BDD
Exécuter `protex_patch_v3.sql` dans phpMyAdmin avant de déployer.
