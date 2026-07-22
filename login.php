<?php
/**
 * login.php
 * Point d'entree : affiche le formulaire de connexion et traite sa soumission.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/controllers/auth.php';

// Si l'utilisateur est dejà connecte, on le redirige directement vers l'accueil
if (!empty($_SESSION['utilisateur_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$erreur = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erreur = auth_connecter($_POST['email'] ?? '', $_POST['mot_de_passe'] ?? '');
    // Si auth_connecter() reussit, elle redirige elle-même et exit() ; on n'arrive ici qu'en cas d'erreur
}

require __DIR__ . '/views/auth/connexion.php';
