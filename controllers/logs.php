<?php
/**
 * controllers/logs.php
 * Logique metier de la journalisation.
 */

require_once __DIR__ . '/../models/logs.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

function logs_obtenir_liste(int $page, ?string $action = null, ?string $table = null): array
{
    $total       = db_log_compter($action, $table);
    $nombrePages = max(1, (int) ceil($total / 20));
    $page        = max(1, min($page, $nombrePages));

    $logs = db_log_lister($page, 20, $action, $table);

    return [
        'logs'        => $logs,
        'page'        => $page,
        'nombrePages' => $nombrePages,
        'total'       => $total,
        'action'      => $action,
        'table'       => $table,
    ];
}