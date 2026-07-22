<?php
declare(strict_types=1);

/**
 * controllers/auth.php
 * Logique metier de l'authentification.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/utilisateurs.php';
require_once __DIR__ . '/../models/logs.php';

/**
 * Verifie les identifiants de connexion.
 * Retourne les donnees de l'utilisateur si valides, sinon false.
 */
function auth_verifier_identifiants(string $email, string $motDePasse)
{
    $utilisateur = db_utilisateur_trouver_par_email($email);

    if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
        return $utilisateur;
    }

    return false;
}

/**
 * Traite la soumission du formulaire de connexion.
 * Retourne un message d'erreur (string) en cas d'echec, ou redirige (et exit) si succès.
 */
function auth_connecter(string $email, string $motDePasse): ?string
{
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if ($email === '' || $motDePasse === '') {
        return 'Veuillez renseigner votre email et votre mot de passe.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Adresse email invalide.';
    }

    $utilisateur = auth_verifier_identifiants($email, $motDePasse);

    if ($utilisateur === false) {
        return 'Email ou mot de passe incorrect.';
    }

    // Creation de la session
    session_regenerate_id(true); // protège contre la fixation de session
    $_SESSION['utilisateur_id']  = $utilisateur['id'];
    $_SESSION['utilisateur_nom'] = htmlspecialchars($utilisateur['nom'], ENT_QUOTES, 'UTF-8');
    $_SESSION['utilisateur_role'] = $utilisateur['role'] ?? 'gestionnaire';

    // Logger la connexion
    db_log_inserer('connexion', 'utilisateurs', (int) $utilisateur['id'], 'Connexion réussie');

    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

/**
 * Detruit la session et redirige vers la page de connexion.
 */
function auth_deconnecter(): void
{
    // Logger la déconnexion
    if (!empty($_SESSION['utilisateur_id'])) {
        db_log_inserer('connexion', 'utilisateurs', (int) $_SESSION['utilisateur_id'], 'Déconnexion');
    }
    
    $_SESSION = [];
    session_unset();
    session_destroy();

    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

/**
 * Verifie que l'utilisateur est connecte. Si non, redirige vers login.php.
 * À appeler en début de chaque page protégée.
 */
function auth_verifier_connexion(): void
{
    if (empty($_SESSION['utilisateur_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }

    if (empty($_SESSION['utilisateur_role'])) {
        $utilisateur = db_utilisateur_trouver_par_id((int) $_SESSION['utilisateur_id']);
        if (!$utilisateur) {
            auth_deconnecter();
        }

        $_SESSION['utilisateur_nom'] = htmlspecialchars($utilisateur['nom'], ENT_QUOTES, 'UTF-8');
        $_SESSION['utilisateur_role'] = $utilisateur['role'] ?? 'gestionnaire';
    }
}
