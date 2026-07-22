<?php
declare(strict_types=1);

/**
 * controllers/livres.php
 * Logique metier des livres (validation, orchestration de la pagination)
 */

require_once __DIR__ . '/../models/livres.php';
require_once __DIR__ . '/../models/logs.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

/**
 * Prepare les donnees pour la page liste.php : livres pagines + infos de pagination.
 */
function livre_obtenir_liste(int $page, string $recherche, string $filtre = 'tout'): array
{
    $total       = db_livre_compter($recherche, $filtre);
    $nombrePages = max(1, (int) ceil($total / ELEMENTS_PAR_PAGE));
    $page        = max(1, min($page, $nombrePages));

    $livres = db_livre_lister($page, ELEMENTS_PAR_PAGE, $recherche, $filtre);

    return [
        'livres'      => $livres,
        'page'        => $page,
        'nombrePages' => $nombrePages,
        'total'       => $total,
        'recherche'   => $recherche,
        'filtre'      => $filtre,
    ];
}

/**
 * Valide et filtre les donnees d'un formulaire livre.
 * Retourne ['erreurs' => [...], 'donnees' => [...]]
 */
function livre_valider_donnees(array $post): array
{
    $erreurs = [];

    $titre        = trim((string) ($post['titre'] ?? ''));
    $auteur       = trim((string) ($post['auteur'] ?? ''));
    $isbn         = trim((string) ($post['isbn'] ?? ''));
    $annee        = filter_var($post['annee'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => -5000, 'max_range' => 2100]]);
    $quantite     = filter_var($post['quantite'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $id_categorie = filter_var($post['id_categorie'] ?? '', FILTER_VALIDATE_INT);

    if ($titre === '') {
        $erreurs[] = 'Le titre est obligatoire.';
    }
    if ($auteur === '') {
        $erreurs[] = 'L\'auteur est obligatoire.';
    }
    if ($annee === false) {
        $erreurs[] = 'L\'annee doit être un nombre valide (entre -5000 et 2100).';
    }
    if ($quantite === false) {
        $erreurs[] = 'La quantite doit être un nombre positif ou nul.';
    }
    // Validation basique ISBN-10 ou ISBN-13 si fourni
    if ($isbn !== '') {
        $isbnPropre = str_replace(['-', ' ', '.'], '', $isbn);
        $longueur = strlen($isbnPropre);
        if ($longueur !== 10 && $longueur !== 13) {
            $erreurs[] = 'L\'ISBN doit comporter 10 ou 13 chiffres.';
        } elseif (!ctype_digit($isbnPropre)) {
            $erreurs[] = 'L\'ISBN ne doit contenir que des chiffres (tirets autorises).';
        }
    }

    return [
        'erreurs' => $erreurs,
        'donnees' => [
            'titre'         => $titre,
            'auteur'        => $auteur,
            'isbn'          => $isbn,
            'annee'         => $annee !== false ? $annee : null,
            'quantite'      => $quantite !== false ? $quantite : 0,
            'id_categorie'  => $id_categorie ?: null,
        ],
    ];
}

/**
 * Traite l'ajout d'un livre.
 */
function livre_ajouter(array $post): array
{
    $resultat = livre_valider_donnees($post);
    
    if (empty($resultat['erreurs'])) {
        $id = db_livre_inserer($resultat['donnees']);
        if ($id) {
            db_log_inserer('ajout', 'livres', (int) $id, 'Ajout du livre : ' . $resultat['donnees']['titre']);
            $resultat['success'] = true;
        } else {
            $resultat['erreurs'][] = 'Erreur lors de l\'enregistrement.';
        }
    }
    
    return $resultat;
}

/**
 * Traite la modification d'un livre.
 */
function livre_modifier(int $id, array $post): array
{
    $resultat = livre_valider_donnees($post);
    
    if (empty($resultat['erreurs'])) {
        if (db_livre_modifier($id, $resultat['donnees'])) {
            db_log_inserer('modification', 'livres', $id, 'Modification du livre : ' . $resultat['donnees']['titre']);
            $resultat['success'] = true;
        } else {
            $resultat['erreurs'][] = 'Erreur lors de la mise à jour.';
        }
    }
    
    return $resultat;
}

/**
 * Traite la suppression d'un livre (admin uniquement).
 */
function livre_supprimer(int $id): bool
{
    if (!est_admin()) {
        return false;
    }
    
    $livre = db_livre_trouver($id);
    if (!$livre) {
        return false;
    }
    
    $supprime = db_livre_supprimer($id);
    if ($supprime) {
        db_log_inserer('suppression', 'livres', $id, 'Suppression du livre : ' . $livre['titre']);
    }
    
    return $supprime;
}
