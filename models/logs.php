<?php
/**
 * models/logs.php
 * Journalisation des actions utilisateur.
 */

require_once __DIR__ . '/../config/database.php';

function db_log_inserer(string $action, string $table, int $elementId, ?string $details = null): bool
{
    $pdo = get_pdo();
    $sql = 'INSERT INTO logs (action, table_cible, id_cible, details, ip, utilisateur_id)
            VALUES (:action, :table, :element_id, :details, :ip, :utilisateur_id)';
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':action'      => $action,
        ':table'       => $table,
        ':element_id'  => $elementId,
        ':details'     => $details,
        ':ip'          => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ':utilisateur_id' => $_SESSION['utilisateur_id'] ?? null,
    ]);
}

function db_log_lister(int $page = 1, int $parPage = 20, ?string $action = null, ?string $table = null): array
{
    $pdo = get_pdo();
    $offset = ($page - 1) * $parPage;

    $sql = 'SELECT l.*, u.nom AS utilisateur_nom
            FROM logs l
            LEFT JOIN utilisateurs u ON l.utilisateur_id = u.id
            WHERE 1=1';
    $params = [];

    if ($action !== null && $action !== '') {
        $sql .= ' AND l.action = :action';
        $params[':action'] = $action;
    }
    if ($table !== null && $table !== '') {
        $sql .= ' AND l.table_cible = :table';
        $params[':table'] = $table;
    }

    $sql .= ' ORDER BY l.created_at DESC LIMIT :limite OFFSET :offset';
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function db_log_compter(?string $action = null, ?string $table = null): int
{
    $pdo = get_pdo();
    $sql = 'SELECT COUNT(*) AS total FROM logs WHERE 1=1';
    $params = [];

    if ($action !== null && $action !== '') {
        $sql .= ' AND action = :action';
        $params[':action'] = $action;
    }
    if ($table !== null && $table !== '') {
        $sql .= ' AND table_cible = :table';
        $params[':table'] = $table;
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return (int) $stmt->fetch()['total'];
}