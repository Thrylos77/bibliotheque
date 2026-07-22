<?php
/**
 * models/emprunts.php
 * Accès aux donnees de la table emprunts.
 * Gère les jointures avec livres et etudiants pour l'affichage.
 */

require_once __DIR__ . '/../config/database.php';

function db_emprunt_requete_base(): string
{
    return 'SELECT emprunts.*, livres.titre AS livre_titre,
                   etudiants.nom AS etudiant_nom, etudiants.prenom AS etudiant_prenom
            FROM emprunts
            INNER JOIN livres ON emprunts.id_livre = livres.id
            INNER JOIN etudiants ON emprunts.id_etudiant = etudiants.id';
}

function db_emprunt_compter(string $recherche = '', string $filtre = 'tout'): int
{
    $pdo = get_pdo();

    $sql = 'SELECT COUNT(*) AS total FROM emprunts
            INNER JOIN livres ON emprunts.id_livre = livres.id
            INNER JOIN etudiants ON emprunts.id_etudiant = etudiants.id';

    if ($recherche !== '') {
        $sql .= ' WHERE ' . db_emprunt_condition_recherche($filtre);
    }

    $stmt = $pdo->prepare($sql);

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $stmt->bindValue(':recherche', $motif);
    }

    $stmt->execute();
    return (int) $stmt->fetch()['total'];
}

function db_emprunt_lister(int $page = 1, int $parPage = 10, string $recherche = '', string $filtre = 'tout'): array
{
    $pdo = get_pdo();
    $offset = ($page - 1) * $parPage;
    $sql = db_emprunt_requete_base();

    if ($recherche !== '') {
        $sql .= ' WHERE ' . db_emprunt_condition_recherche($filtre);
    }

    $sql .= ' ORDER BY emprunts.id DESC LIMIT :limite OFFSET :offset';
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

function db_emprunt_condition_recherche(string $filtre): string
{
    switch ($filtre) {
        case 'livre':
            return 'livres.titre COLLATE utf8mb4_general_ci LIKE :recherche';
        case 'etudiant':
            return "CONCAT(
                COALESCE(etudiants.nom, ''), ' ',
                COALESCE(etudiants.prenom, '')
            ) COLLATE utf8mb4_general_ci LIKE :recherche";
        case 'date':
            return "CONCAT(
                COALESCE(emprunts.date_emprunt, ''), ' ',
                COALESCE(emprunts.date_retour_prevue, ''), ' ',
                COALESCE(emprunts.date_retour, '')
            ) COLLATE utf8mb4_general_ci LIKE :recherche";
        case 'statut':
            return 'emprunts.statut COLLATE utf8mb4_general_ci LIKE :recherche';
        case 'tout':
        default:
            return "CONCAT(
                COALESCE(livres.titre, ''), ' ',
                COALESCE(etudiants.nom, ''), ' ',
                COALESCE(etudiants.prenom, ''), ' ',
                COALESCE(emprunts.statut, ''), ' ',
                COALESCE(emprunts.date_emprunt, ''), ' ',
                COALESCE(emprunts.date_retour_prevue, ''), ' ',
                COALESCE(emprunts.date_retour, '')
            ) COLLATE utf8mb4_general_ci LIKE :recherche";
    }
}

function db_emprunt_trouver(int $id)
{
    $pdo = get_pdo();
    $sql = db_emprunt_requete_base() . ' WHERE emprunts.id = :id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

function db_emprunt_inserer(array $donnees): bool
{
    $pdo = get_pdo();
    $sql = 'INSERT INTO emprunts (id_livre, id_etudiant, date_emprunt, date_retour_prevue, statut)
            VALUES (:id_livre, :id_etudiant, :date_emprunt, :date_retour_prevue, :statut)';
    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':id_livre'           => $donnees['id_livre'],
        ':id_etudiant'        => $donnees['id_etudiant'],
        ':date_emprunt'       => $donnees['date_emprunt'],
        ':date_retour_prevue' => $donnees['date_retour_prevue'],
        ':statut'             => 'En cours',
    ]);
}

function db_emprunt_marquer_retourne(int $id): bool
{
    $pdo = get_pdo();
    $sql = "UPDATE emprunts SET statut = 'Retourne', date_retour = CURDATE() WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

function db_emprunt_modifier(int $id, array $donnees): bool
{
    $pdo = get_pdo();
    $sql = 'UPDATE emprunts
            SET id_livre = :id_livre, id_etudiant = :id_etudiant,
                date_emprunt = :date_emprunt, date_retour_prevue = :date_retour_prevue
            WHERE id = :id';
    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':id_livre'           => $donnees['id_livre'],
        ':id_etudiant'        => $donnees['id_etudiant'],
        ':date_emprunt'       => $donnees['date_emprunt'],
        ':date_retour_prevue' => $donnees['date_retour_prevue'],
        ':id'                 => $id,
    ]);
}

function db_emprunt_supprimer(int $id): bool
{
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('DELETE FROM emprunts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/** Exercice 3 : liste des emprunts en retard */
function db_emprunt_lister_en_retard(): array
{
    $pdo = get_pdo();
    $sql = db_emprunt_requete_base() . "
            WHERE emprunts.statut = 'En cours' AND emprunts.date_retour_prevue < CURDATE()
            ORDER BY emprunts.date_retour_prevue ASC";
    return $pdo->query($sql)->fetchAll();
}

/** Exercice 4 : nombre total d'emprunts enregistres */
function db_emprunt_compter_total(): int
{
    $pdo = get_pdo();
    $sql = 'SELECT COUNT(*) AS total FROM emprunts';
    return (int) $pdo->query($sql)->fetch()['total'];
}

/** Nombre d'emprunts actuellement en cours */
function db_emprunt_compter_en_cours(): int
{
    $pdo = get_pdo();
    $sql = "SELECT COUNT(*) AS total FROM emprunts WHERE statut = 'En cours'";
    return (int) $pdo->query($sql)->fetch()['total'];
}
