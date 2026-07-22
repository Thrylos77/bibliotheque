<?php
/**
 * models/etudiants.php
 * Accès aux donnees de la table etudiants.
 */

require_once __DIR__ . '/../config/database.php';

function db_etudiant_compter(string $recherche = '', string $filtre = 'tout'): int
{
    $pdo = get_pdo();
    $sql = 'SELECT COUNT(*) AS total FROM etudiants';

    if ($recherche !== '') {
        $sql .= ' WHERE ' . db_etudiant_condition_recherche($filtre);
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
 * Liste paginee avec recherche multicritère (nom, prenom, email, filière) - Exercice 1
 */
function db_etudiant_lister(int $page = 1, int $parPage = 10, string $recherche = '', string $filtre = 'tout'): array
{
    $pdo = get_pdo();
    $offset = ($page - 1) * $parPage;
    $sql = 'SELECT * FROM etudiants';

    if ($recherche !== '') {
        $sql .= ' WHERE ' . db_etudiant_condition_recherche($filtre);
    }

    $sql .= ' ORDER BY id DESC LIMIT :limite OFFSET :offset';
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

function db_etudiant_condition_recherche(string $filtre): string
{
    switch ($filtre) {
        case 'nom':
            return 'etudiants.nom COLLATE utf8mb4_general_ci LIKE :recherche';
        case 'prenom':
            return 'etudiants.prenom COLLATE utf8mb4_general_ci LIKE :recherche';
        case 'email':
            return 'etudiants.email COLLATE utf8mb4_general_ci LIKE :recherche';
        case 'filiere':
            return 'etudiants.filiere COLLATE utf8mb4_general_ci LIKE :recherche';
        case 'tout':
        default:
            return "CONCAT(
                COALESCE(etudiants.nom, ''), ' ',
                COALESCE(etudiants.prenom, ''), ' ',
                COALESCE(etudiants.email, ''), ' ',
                COALESCE(etudiants.filiere, '')
            ) COLLATE utf8mb4_general_ci LIKE :recherche";
    }
}

function db_etudiant_trouver(int $id)
{
    $pdo = get_pdo();
    $sql = 'SELECT * FROM etudiants WHERE id = :id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

function db_etudiant_inserer(array $donnees): bool
{
    $pdo = get_pdo();
    $sql = 'INSERT INTO etudiants (nom, prenom, email, telephone, filiere)
            VALUES (:nom, :prenom, :email, :telephone, :filiere)';
    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':nom'       => $donnees['nom'],
        ':prenom'    => $donnees['prenom'],
        ':email'     => $donnees['email'],
        ':telephone' => $donnees['telephone'],
        ':filiere'   => $donnees['filiere'],
    ]);
}

function db_etudiant_modifier(int $id, array $donnees): bool
{
    $pdo = get_pdo();
    $sql = 'UPDATE etudiants
            SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, filiere = :filiere
            WHERE id = :id';
    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':nom'       => $donnees['nom'],
        ':prenom'    => $donnees['prenom'],
        ':email'     => $donnees['email'],
        ':telephone' => $donnees['telephone'],
        ':filiere'   => $donnees['filiere'],
        ':id'        => $id,
    ]);
}

/**
 * Supprime un etudiant. Retourne false (au lieu de lever une exception fatale)
 * si l'etudiant est encore reference par un emprunt (contrainte de cle etrangère).
 */
function db_etudiant_supprimer(int $id): bool
{
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('DELETE FROM etudiants WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Retourne la liste de tous les etudiants (utile pour les listes deroulantes)
 */
function db_etudiant_lister_tous(): array
{
    $pdo = get_pdo();
    $sql = 'SELECT * FROM etudiants ORDER BY nom ASC';
    return $pdo->query($sql)->fetchAll();
}