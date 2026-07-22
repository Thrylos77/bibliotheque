<?php
/**
 * config.php
 * Configuration generale de l'application (constantes globales).
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Paramètres de connexion à la base de donnees
define('DB_HOST', 'localhost');
define('DB_NAME', 'bibliotheque');
define('DB_USER', 'root');
define('DB_PASS', '');

// Nombre d'elements affiches par page (pagination)
define('ELEMENTS_PAR_PAGE', 10);

// Chemin racine de l'application (utile pour les liens/redirections)
define('BASE_URL', '/bibliotheque/');