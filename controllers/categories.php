<?php
/**
 * controllers/categories.php
 * Logique metier des categories de livres.
 */

require_once __DIR__ . '/../models/categories.php';
require_once __DIR__ . '/../models/logs.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

function categorie_obtenir_liste(int $page, string $recherche): array
{
    $total       = db_categorie_compter($recherche);
    $nombrePages = max(1, (int) ceil($total / ELEMENTS_PAR_PAGE));
    $page        = max(1, min($page, $nombrePages));

    $categories = db_categorie_lister($page, ELEMENTS_PAR_PAGE, $recherche);

    return [
        'categories'  => $categories,
        'page'        => $page,
        'nombrePages' => $nombrePages,
        'total'       => $total,
        'recherche'   => $recherche,
    ];
}

function categorie_valider_donnees(array $post): array
{
    $erreurs = [];
    $nom = trim((string) ($post['nom'] ?? ''));

    if ($nom === '') {
        $erreurs[] = 'Le nom de la categorie est obligatoire.';
    } elseif (mb_strlen($nom) > 50) {
        $erreurs[] = 'Le nom ne doit pas depasser 50 caracteres.';
    }

    // Verifier unicite
    if (empty($erreurs)) {
        $existantes = db_categorie_lister_toutes();
        foreach ($existantes as $cat) {
            if (strtolower($cat['nom']) === strtolower($nom)) {
                // Si modification, on ignore la categorie elle-meme
                $idExistant = (int) ($post['id'] ?? 0);
                if ((int) $cat['id'] !== $idExistant) {
                    $erreurs[] = 'Une categorie avec ce nom existe deja.';
                    break;
                }
            }
        }
    }

    return [
        'erreurs' => $erreurs,
        'donnees' => ['nom' => $nom],
    ];
}

function categorie_ajouter(array $post): array
{
    $resultat = categorie_valider_donnees($post);
    
    if (empty($resultat['erreurs'])) {
        $id = db_categorie_inserer($resultat['donnees']['nom']);
        if ($id) {
            db_log_inserer('ajout', 'categories', (int) $id, 'Ajout de la catégorie : ' . $resultat['donnees']['nom']);
            $resultat['success'] = true;
        } else {
            $resultat['erreurs'][] = 'Erreur lors de l\'enregistrement.';
        }
    }
    
    return $resultat;
}

function categorie_modifier(int $id, array $post): array
{
    $resultat = categorie_valider_donnees($post);
    
    if (empty($resultat['erreurs'])) {
        if (db_categorie_modifier($id, $resultat['donnees']['nom'])) {
            db_log_inserer('modification', 'categories', $id, 'Modification de la catégorie : ' . $resultat['donnees']['nom']);
            $resultat['success'] = true;
        } else {
            $resultat['erreurs'][] = 'Erreur lors de la mise à jour.';
        }
    }
    
    return $resultat;
}

function categorie_supprimer(int $id): bool
{
    if (!est_gestionnaire()) {
        return false;
    }
    
    $categorie = db_categorie_trouver($id);
    if (!$categorie) {
        return false;
    }

    if ((int) $categorie['nb_livres'] > 0) {
        return false;
    }
    
    $supprime = db_categorie_supprimer($id);
    if ($supprime) {
        db_log_inserer('suppression', 'categories', $id, 'Suppression de la catégorie : ' . $categorie['nom']);
    }
    
    return $supprime;
}
