<?php
/**
 * models/livres.php
 * Accès aux donnees de la table livres.
 * Toutes les requêtes utilisent PDO avec des requêtes preparees.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Compte le nombre total de livres correspondant à une recherche (pour la pagination)
 */
function db_livre_compter(string $recherche = '', string $filtre = 'tout'): int
{
    $pdo = get_pdo();

    $sql = 'SELECT COUNT(*) AS total FROM livres
            LEFT JOIN categories ON livres.id_categorie = categories.id';
    if ($recherche !== '') {
        $sql .= ' WHERE ' . db_livre_condition_recherche($filtre);
    }

    $stmt = $pdo->prepare($sql);

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $stmt->bindValue(':recherche', $motif);
    }

    $stmt->execute();
    return (int) $stmt->fetch()['total'];
}

/**
 * Retourne une liste paginee de livres, avec recherche optionnelle selon un filtre.
 */
function db_livre_lister(int $page = 1, int $parPage = 10, string $recherche = '', string $filtre = 'tout'): array
{
    $pdo = get_pdo();
    $offset = ($page - 1) * $parPage;

    $sql = 'SELECT livres.*, categories.nom AS categorie_nom
            FROM livres
            LEFT JOIN categories ON livres.id_categorie = categories.id';

    if ($recherche !== '') {
        $sql .= ' WHERE ' . db_livre_condition_recherche($filtre);
    }

    $sql .= ' ORDER BY livres.id DESC LIMIT :limite OFFSET :offset';

    $stmt = $pdo->prepare($sql);

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $stmt->bindValue(':recherche', $motif);
    }

    $stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function db_livre_condition_recherche(string $filtre): string
{
    switch ($filtre) {
        case 'auteur':
            return 'livres.auteur COLLATE utf8mb4_general_ci LIKE :recherche';
        case 'date':
            return 'CAST(livres.annee AS CHAR) COLLATE utf8mb4_general_ci LIKE :recherche';
        case 'tout':
            return "CONCAT(
                COALESCE(livres.titre, ''), ' ',
                COALESCE(livres.auteur, ''), ' ',
                COALESCE(livres.isbn, ''), ' ',
                COALESCE(CAST(livres.annee AS CHAR), ''), ' ',
                COALESCE(CAST(livres.quantite AS CHAR), ''), ' ',
                COALESCE(categories.nom, '')
            ) COLLATE utf8mb4_general_ci LIKE :recherche";
        case 'titre':
        default:
            return 'livres.titre COLLATE utf8mb4_general_ci LIKE :recherche';
    }
}

/**
 * Retourne uniquement les livres disponibles (quantite > 0) - Exercice 2
 */
function db_livre_lister_disponibles(): array
{
    $pdo = get_pdo();
    $sql = 'SELECT livres.*, categories.nom AS categorie_nom
            FROM livres
            LEFT JOIN categories ON livres.id_categorie = categories.id
            WHERE quantite > 0 ORDER BY titre ASC';
    return $pdo->query($sql)->fetchAll();
}

function db_livre_trouver(int $id)
{
    $pdo = get_pdo();
    $sql = 'SELECT livres.*, categories.nom AS categorie_nom
            FROM livres
            LEFT JOIN categories ON livres.id_categorie = categories.id
            WHERE livres.id = :id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

function db_livre_inserer(array $donnees): bool
{
    $pdo = get_pdo();
    $sql = 'INSERT INTO livres (titre, auteur, isbn, annee, quantite, id_categorie, couverture)
            VALUES (:titre, :auteur, :isbn, :annee, :quantite, :id_categorie, :couverture)';
    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':titre'         => $donnees['titre'],
        ':auteur'        => $donnees['auteur'],
        ':isbn'          => $donnees['isbn'],
        ':annee'         => $donnees['annee'],
        ':quantite'      => $donnees['quantite'],
        ':id_categorie'  => !empty($donnees['id_categorie']) ? (int) $donnees['id_categorie'] : null,
        ':couverture'    => !empty($donnees['couverture']) ? $donnees['couverture'] : null,
    ]);
}

function db_livre_modifier(int $id, array $donnees): bool
{
    $pdo = get_pdo();
    $sql = 'UPDATE livres
            SET titre = :titre, auteur = :auteur, isbn = :isbn,
                annee = :annee, quantite = :quantite, id_categorie = :id_categorie';

    if (array_key_exists('couverture', $donnees)) {
        $sql .= ', couverture = :couverture';
    }

    $sql .= '
            WHERE id = :id';
    $stmt = $pdo->prepare($sql);

    $params = [
        ':titre'         => $donnees['titre'],
        ':auteur'        => $donnees['auteur'],
        ':isbn'          => $donnees['isbn'],
        ':annee'         => $donnees['annee'],
        ':quantite'      => $donnees['quantite'],
        ':id_categorie'  => !empty($donnees['id_categorie']) ? (int) $donnees['id_categorie'] : null,
        ':id'            => $id,
    ];

    if (array_key_exists('couverture', $donnees)) {
        $params[':couverture'] = !empty($donnees['couverture']) ? $donnees['couverture'] : null;
    }

    return $stmt->execute($params);
}

/**
 * Supprime un livre. Retourne false (au lieu de lever une exception fatale)
 * si le livre est encore reference par un emprunt (contrainte de cle etrangère).
 */
function db_livre_supprimer(int $id): bool
{
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('DELETE FROM livres WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Decremente la quantite disponible d'un livre (utilise lors d'un emprunt).
 */
function db_livre_decrementer(int $id): bool
{
    $pdo = get_pdo();
    $sql = 'UPDATE livres SET quantite = quantite - 1 WHERE id = :id AND quantite > 0';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->rowCount() > 0;
}

/**
 * Incremente la quantite disponible d'un livre (utilise lors d'un retour).
 */
function db_livre_incrementer(int $id): bool
{
    $pdo = get_pdo();
    $sql = 'UPDATE livres SET quantite = quantite + 1 WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':id' => $id]);
}
