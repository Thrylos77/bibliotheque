<?php
declare(strict_types=1);

/**
 * controllers/emprunts.php
 * Logique metier des emprunts.
 */

require_once __DIR__ . '/../models/emprunts.php';
require_once __DIR__ . '/../models/logs.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

function emprunt_obtenir_liste(int $page, string $recherche, string $filtre = 'tout'): array
{
    $total       = db_emprunt_compter($recherche, $filtre);
    $nombrePages = max(1, (int) ceil($total / ELEMENTS_PAR_PAGE));
    $page        = max(1, min($page, $nombrePages));

    $emprunts = db_emprunt_lister($page, ELEMENTS_PAR_PAGE, $recherche, $filtre);

    return [
        'emprunts'    => $emprunts,
        'page'        => $page,
        'nombrePages' => $nombrePages,
        'total'       => $total,
        'recherche'   => $recherche,
        'filtre'      => $filtre,
    ];
}

function emprunt_valider_donnees(array $post): array
{
    $erreurs = [];
    $idLivre     = filter_var($post['id_livre'] ?? '', FILTER_VALIDATE_INT);
    $idEtudiant  = filter_var($post['id_etudiant'] ?? '', FILTER_VALIDATE_INT);
    $dateEmprunt = trim((string) ($post['date_emprunt'] ?? ''));
    $dateRetour  = trim((string) ($post['date_retour_prevue'] ?? ''));

    if (!$idLivre) {
        $erreurs[] = 'Le livre est obligatoire.';
    }
    if (!$idEtudiant) {
        $erreurs[] = 'L\'étudiant est obligatoire.';
    }
    if ($dateEmprunt === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateEmprunt)) {
        $erreurs[] = 'La date d\'emprunt est invalide.';
    }
    if ($dateRetour === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateRetour)) {
        $erreurs[] = 'La date de retour prévue est invalide.';
    }

    return [
        'erreurs' => $erreurs,
        'donnees' => [
            'id_livre'        => $idLivre,
            'id_etudiant'     => $idEtudiant,
            'date_emprunt'    => $dateEmprunt,
            'date_retour_prevue' => $dateRetour,
        ],
    ];
}

function emprunt_ajouter(array $post): array
{
    $resultat = emprunt_valider_donnees($post);
    
    if (empty($resultat['erreurs'])) {
        $id = db_emprunt_inserer($resultat['donnees']);
        if ($id) {
            db_log_inserer('ajout', 'emprunts', (int) $id, 'Nouvel emprunt - Livre ID: ' . $resultat['donnees']['id_livre']);
            $resultat['success'] = true;
        } else {
            $resultat['erreurs'][] = 'Erreur lors de l\'enregistrement.';
        }
    }
    
    return $resultat;
}

function emprunt_modifier(int $id, array $post): array
{
    $resultat = emprunt_valider_donnees($post);
    
    if (empty($resultat['erreurs'])) {
        if (db_emprunt_modifier($id, $resultat['donnees'])) {
            db_log_inserer('modification', 'emprunts', $id, 'Modification de l\'emprunt ID: ' . $id);
            $resultat['success'] = true;
        } else {
            $resultat['erreurs'][] = 'Erreur lors de la mise à jour.';
        }
    }
    
    return $resultat;
}

function emprunt_supprimer(int $id): bool
{
    if (!est_gestionnaire()) {
        return false;
    }
    
    $emprunt = db_emprunt_trouver($id);
    if (!$emprunt) {
        return false;
    }
    
    $supprime = db_emprunt_supprimer($id);
    if ($supprime) {
        db_log_inserer('suppression', 'emprunts', $id, 'Suppression de l\'emprunt ID: ' . $id);
    }
    
    return $supprime;
}

function emprunt_retourner_livre(int $id): bool
{
    $emprunt = db_emprunt_trouver($id);
    if (!$emprunt || $emprunt['statut'] !== 'En cours') {
        return false;
    }
    
    $retourne = db_emprunt_marquer_retourne($id);
    if ($retourne) {
        db_log_inserer('modification', 'emprunts', $id, 'Retour du livre - Emprunt ID: ' . $id);
    }
    
    return $retourne;
}
