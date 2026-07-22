# Bibliothèque Universitaire — Application PHP / PDO (version procédurale)

Mini-projet intégrateur : gestion d'une bibliothèque universitaire (livres, étudiants,
emprunts, catégories) en **PHP avec PDO**.
Authentification, gestion des rôles, CRUD, recherche multicritère, pagination, export CSV,
journal d'audit, tableau de bord statistique et securisation.
Interface Bootstrap 5.

---

## Installation (WAMP / Laragon)

1. Placer le dossier `bibliotheque/` dans le répertoire web racine (`www` pour WAMP, `www` pour Laragon).
2. Démarrer le serveur web (Apache) et la base de données (MySQL).
3. Importer `sql/database.sql` via phpMyAdmin ou en ligne de commande :
   ```bash
   mysql -u root -p < sql/database.sql
   ```
4. Vérifier et éventuellement modifier les identifiants de connexion dans `config/config.php`.
5. S'assurer que le dossier `uploads/couvertures/` est accessible en écriture :
   ```bash
   chmod 755 uploads/couvertures/
   ```
6. Ouvrir `http://localhost/bibliotheque/login.php` dans le navigateur.

---

## Comptes utilisateurs

| Rôle          | Email                          | Mot de passe         |
|---------------|--------------------------------|----------------------|
| Administrateur | `admin@bibliotheque.local`     | `admin123`           |
| Gestionnaire   | `gestionnaire@bibliotheque.local` | `gestionnaire123` |

---

## Structure du projet

```
bibliotheque/
├── config/
│   ├── config.php       (constantes globales : DB, session, BASE_URL)
│   ├── database.php     (connexion PDO centralisée — fonction get_pdo())
│   └── helpers.php      (fonctions utilitaires : flash, échappement, CSRF, pagination, uploads, rôles)
├── controllers/         (logique métier — fonctions, aucune classe)
│   ├── auth.php         (authentification, session, vérification rôles)
│   ├── livres.php       (CRUD livres + validation)
│   ├── etudiants.php    (CRUD étudiants + validation)
│   ├── emprunts.php     (CRUD emprunts + retour de livre)
│   ├── categories.php   (CRUD catégories)
│   ├── export.php       (export CSV : livres, étudiants, emprunts, retards)
│   ├── logs.php         (journal d'audit — liste des actions)
│   └── statistiques.php (tableau de bord : totaux, top livres, recommandations)
├── models/              (accès aux données — fonctions préfixées db_..., aucune classe)
│   ├── utilisateurs.php (recherche par email / id)
│   ├── livres.php       (lister, compter, insérer, modifier, supprimer, stock)
│   ├── etudiants.php    (lister, compter, insérer, modifier, supprimer)
│   ├── emprunts.php     (lister, compter, insérer, modifier, supprimer, retard)
│   ├── categories.php   (lister, compter, insérer, modifier, supprimer)
│   └── logs.php         (insérer, lister, compter les logs)
├── views/
│   ├── _header.php / _footer.php
│   ├── auth/connexion.php
│   ├── livres/          (liste, _liste_contenu, ajouter, modifier, supprimer)
│   ├── etudiants/       (liste, _liste_contenu, ajouter, modifier, supprimer)
│   ├── emprunts/        (liste, _liste_contenu, ajouter, modifier, supprimer)
│   ├── categories/      (liste, ajouter, modifier, supprimer)
│   ├── logs/liste.php
│   └── statistiques/
├── assets/
│   ├── css/style.css
│   ├── img/bibliotheque-bg.svg
│   ├── js/script.js
│   ├── js/htmx.min.js
│   └── uploads/         (couvertures de livres)
├── sql/database.sql
├── index.php            (tableau de bord — page d'accueil protégée)
├── login.php
└── logout.php
```

---

## Fonctionnalités

### Authentification & sécurité
- Authentification par session avec `password_hash()` / `password_verify()`.
- Gestion des rôles : **admin** et **gestionnaire** (contrôle d'accès sur les actions sensibles).
- Protection **CSRF** sur tous les formulaires (token généré, vérifié et régénéré).
- Échappement HTML systématique via `htmlspecialchars()` (`function e()`).
- Requêtes préparées partout (PDO) — injection SQL bloquée.
- Confirmation JavaScript avant suppression.
- Journal d'audit (`logs`) : chaque action (création, modification, suppression, emprunt, retour)
  est enregistrée avec l'IP et l'utilisateur.

### Gestion des livres
- CRUD complet (créer, lire, modifier, supprimer).
- Catégorisation des livres (relation `livres.id_categorie` → `categories.id`).
- Upload de couverture de livre (stocké dans `uploads/couvertures/`).
- Recherche multicritère (titre, auteur, ISBN, catégorie).
- Filtres : tous / disponibles / catégorie.
- Pagination (10 résultats par page).

### Gestion des étudiants
- CRUD complet (nom, prénom, email, téléphone, filière).
- Recherche multicritère (nom, prénom, email, filière).
- Filtres : tous / par filière.
- Pagination.

### Gestion des emprunts
- Emprunt avec **transaction PDO** : décrémente le stock du livre, empêche les quantités
  négatives, réincrémente le stock au moment du retour.
- Retour de livre (marquage `Retourne` + remise à jour du stock).
- Détection automatique des emprunts **en retard**.
- Recherche multicritère (livre, étudiant, statut).
- Filtres : tous / en cours / en retard / retournés.
- Pagination.

### Catégories
- CRUD complet des catégories de livres.
- Utilisé dans la gestion des livres (sélection dans un `<select>`).

### Tableau de bord (index.php)
- Statistiques globales : nombre de livres, d'étudiants, d'emprunts en cours, d'emprunts en retard.
- Top 5 des livres les plus empruntés.
- Livres recommandés (derniers ajouts disponibles).
- Total des emprunts depuis la création.

### Export CSV
- Export des **livres**, **étudiants**, **emprunts** et **emprunts en retard** en CSV
  (compatible Excel — BOM UTF-8, séparateur `;`).

### Journal d'audit (logs)
- Liste paginée de toutes les actions effectuées dans l'application.
- Filtrable par type d'action et par table cible.

---

## Base de données

Le script `sql/database.sql` crée 6 tables :

| Table          | Description                                      |
|----------------|--------------------------------------------------|
| `utilisateurs` | Comptes admin/gestionnaire (email, rôle, hash MDP) |
| `categories`   | Catégories de livres (nom unique)                |
| `livres`       | Livres (titre, auteur, ISBN, année, quantité, catégorie, couverture) |
| `etudiants`    | Étudiants (nom, prénom, email, téléphone, filière) |
| `emprunts`     | Emprunts (livre, étudiant, dates, statut)        |
| `logs`         | Journal d'audit (action, table, élément, IP, utilisateur, date) |

Le script inclut également :
- 2 comptes utilisateurs (admin + gestionnaire).
- 10 catégories de démonstration.
- 30 livres de démonstration.
- 15 étudiants de démonstration.
- 27 emprunts de démonstration (retournés, en retard, en cours).

---

## Technologies

| Couche         | Technologie                          |
|----------------|--------------------------------------|
| Backend        | PHP 8+ / PDO (procédural)            |
| Base de données| MySQL / MariaDB                      |
| Frontend       | Bootstrap 5, Bootstrap Icons         |
| JavaScript     | Vanilla JS + HTMX                    |
| Sécurité       | password_hash, CSRF, htmlspecialchars, requêtes préparées |
| Police         | Playfair Display, Work Sans (Google Fonts) |
