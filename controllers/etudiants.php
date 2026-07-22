<?php
declare(strict_types=1);

/**
 * controllers/etudiants.php
 * Logique metier des etudiants.
 */

require_once __DIR__ . '/../models/etudiants.php';
require_once __DIR__ . '/../models/logs.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

function etudiant_obtenir_liste(int $page, string $recherche, string $filtre = 'tout'): array
{
    $total       = db_etudiant_compter($recherche, $filtre);
    $nombrePages = max(1, (int) ceil($total / ELEMENTS_PAR_PAGE));
    $page        = max(1, min($page, $nombrePages));

    $etudiants = db_etudiant_lister($page, ELEMENTS_PAR_PAGE, $recherche, $filtre);

    return [
        'etudiants'   => $etudiants,
        'page'        => $page,
        'nombrePages' => $nombrePages,
        'total'       => $total,
        'recherche'   => $recherche,
        'filtre'      => $filtre,
    ];
}

function etudiant_valider_donnees(array $post): array
{
    $erreurs = [];
    $nom        = trim((string) ($post['nom'] ?? ''));
    $prenom     = trim((string) ($post['prenom'] ?? ''));
    $email      = trim((string) ($post['email'] ?? ''));
    $telephone  = trim((string) ($post['telephone'] ?? ''));
    $filiere    = trim((string) ($post['filiere'] ?? ''));

    if ($nom === '') {
        $erreurs[] = 'Le nom est obligatoire.';
    }
    if ($prenom === '') {
        $erreurs[] = 'Le prenom est obligatoire.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'email est invalide.';
    }

    return [
        'erreurs' => $erreurs,
        'donnees' => [
            'nom'       => $nom,
            'prenom'    => $prenom,
            'email'     => $email,
            'telephone' => $telephone,
            'filiere'   => $filiere,
        ],
    ];
}

function etudiant_ajouter(array $post): array
{
    $resultat = etudiant_valider_donnees($post);
    
    if (empty($resultat['erreurs'])) {
        $id = db_etudiant_inserer($resultat['donnees']);
        if ($id) {
            db_log_inserer('ajout', 'etudiants', (int) $id, 'Ajout de l\'étudiant : ' . $resultat['donnees']['nom'] . ' ' . $resultat['donnees']['prenom']);
            $resultat['success'] = true;
        } else {
            $resultat['erreurs'][] = 'Erreur lors de l\'enregistrement.';
        }
    }
    
    return $resultat;
}

function etudiant_modifier(int $id, array $post): array
{
    $resultat = etudiant_valider_donnees($post);
    
    if (empty($resultat['erreurs'])) {
        if (db_etudiant_modifier($id, $resultat['donnees'])) {
            db_log_inserer('modification', 'etudiants', $id, 'Modification de l\'étudiant : ' . $resultat['donnees']['nom']);
            $resultat['success'] = true;
        } else {
            $resultat['erreurs'][] = 'Erreur lors de la mise à jour.';
        }
    }
    
    return $resultat;
}

function etudiant_supprimer(int $id): bool
{
    if (!est_admin()) {
        return false;
    }
    
    $etudiant = db_etudiant_trouver($id);
    if (!$etudiant) {
        return false;
    }
    
    $supprime = db_etudiant_supprimer($id);
    if ($supprime) {
        db_log_inserer('suppression', 'etudiants', $id, 'Suppression de l\'étudiant : ' . $etudiant['nom']);
    }
    
    return $supprime;
}
