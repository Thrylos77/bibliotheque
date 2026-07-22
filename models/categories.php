<?php
/**
 * models/categories.php
 * Acces aux donnees de la table categories.
 */

require_once __DIR__ . '/../config/database.php';

function db_categorie_compter(string $recherche = ''): int
{
    $pdo = get_pdo();
    $sql = 'SELECT COUNT(*) AS total FROM categories';
    if ($recherche !== '') {
        $sql .= ' WHERE nom LIKE :r1';
    }
    $stmt = $pdo->prepare($sql);
    if ($recherche !== '') {
        $stmt->bindValue(':r1', '%' . $recherche . '%');
    }
    $stmt->execute();
    return (int) $stmt->fetch()['total'];
}

function db_categorie_lister(int $page = 1, int $parPage = 10, string $recherche = ''): array
{
    $pdo = get_pdo();
    $offset = ($page - 1) * $parPage;
    $sql = 'SELECT c.*, (SELECT COUNT(*) FROM livres WHERE id_categorie = c.id) AS nb_livres FROM categories c';
    if ($recherche !== '') {
        $sql .= ' WHERE c.nom LIKE :r1';
    }
    $sql .= ' ORDER BY c.nom ASC LIMIT :limite OFFSET :offset';
    $stmt = $pdo->prepare($sql);
    if ($recherche !== '') {
        $stmt->bindValue(':r1', '%' . $recherche . '%');
    }
    $stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function db_categorie_lister_toutes(): array
{
    $pdo = get_pdo();
    return $pdo->query('SELECT * FROM categories ORDER BY nom ASC')->fetchAll();
}

function db_categorie_trouver(int $id)
{
    $pdo = get_pdo();
    $sql = 'SELECT c.*, (SELECT COUNT(*) FROM livres WHERE id_categorie = c.id) AS nb_livres
            FROM categories c
            WHERE c.id = :id
            LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

function db_categorie_inserer(string $nom): bool
{
    $pdo = get_pdo();
    $sql = 'INSERT INTO categories (nom) VALUES (:nom)';
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':nom' => $nom]);
}

function db_categorie_modifier(int $id, string $nom): bool
{
    $pdo = get_pdo();
    $sql = 'UPDATE categories SET nom = :nom WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':nom' => $nom, ':id' => $id]);
}

function db_categorie_supprimer(int $id): bool
{
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}
