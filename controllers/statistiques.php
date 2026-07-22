<?php
declare(strict_types=1);

/**
 * controllers/statistiques.php
 * Donnees agregees pour le tableau de bord.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/livres.php';
require_once __DIR__ . '/../models/etudiants.php';
require_once __DIR__ . '/../models/emprunts.php';

function statistiques_obtenir_dashboard(): array
{
    return [
        'nombre_livres'       => db_livre_compter(),
        'nombre_etudiants'    => db_etudiant_compter(),
        'emprunts_en_cours'   => db_emprunt_compter_en_cours(),
        'emprunts_en_retard'  => count(db_emprunt_lister_en_retard()),
        'emprunts_total'      => db_emprunt_compter_total(),
        'livres_recommandes'  => statistiques_livres_recommandes(),
        'top_livres'          => statistiques_top_livres(),
    ];
}

function statistiques_livres_recommandes(int $limite = 5): array
{
    $pdo = get_pdo();
    $sql = 'SELECT livres.id, livres.titre, livres.auteur, livres.couverture, categories.nom AS categorie
            FROM livres
            LEFT JOIN categories ON livres.id_categorie = categories.id
            WHERE livres.quantite > 0
            ORDER BY livres.id DESC
            LIMIT :limite';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function statistiques_top_livres(int $limite = 5): array
{
    $pdo = get_pdo();
    $sql = 'SELECT livres.id, livres.titre, livres.auteur, livres.couverture, COUNT(emprunts.id) AS nb_emprunts
            FROM emprunts
            INNER JOIN livres ON emprunts.id_livre = livres.id
            GROUP BY livres.id, livres.titre, livres.auteur, livres.couverture
            ORDER BY nb_emprunts DESC, livres.titre ASC
            LIMIT :limite';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}
