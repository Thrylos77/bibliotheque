<?php
/**
 * models/utilisateurs.php
 * Accès aux donnees de la table utilisateurs.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Recherche un utilisateur par son email. Retourne un tableau associatif ou false.
 */
function db_utilisateur_trouver_par_email(string $email)
{
    $pdo = get_pdo();
    $sql = 'SELECT * FROM utilisateurs WHERE email = :email LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    return $stmt->fetch();
}

function db_utilisateur_trouver_par_id(int $id)
{
    $pdo = get_pdo();
    $sql = 'SELECT * FROM utilisateurs WHERE id = :id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch();
}
