<?php
declare(strict_types=1);

/**
 * database.php
 * Connexion PDO centralisee.
 * La variable statique à l'interieur de la fonction garantit qu'une
 * seule connexion physique est ouverte, même si get_pdo() est appelee
 * plusieurs fois au cours du script.
 */

require_once __DIR__ . '/config.php';

function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de donnees : ' . $e->getMessage());
        }
    }

    return $pdo;
}
