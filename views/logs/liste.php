<?php
/**
 * views/logs/liste.php
 * Journal des actions utilisateur.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/logs.php';

auth_verifier_connexion();

$titrePage = 'Journal d\'activité';

$action = $_GET['action'] ?? null;
$table  = $_GET['table'] ?? null;
$page   = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

$donneesListe = logs_obtenir_liste($page, $action, $table);
$logs         = $donneesListe['logs'];

require __DIR__ . '/../_header.php';
?>

<h2 class="mb-4"><i class="bi bi-journal-text"></i> Journal d'activité</h2>

<div class="card p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-4">
            <select name="action" class="form-select">
                <option value="">-- Toutes les actions --</option>
                <option value="ajout" <?php echo $action === 'ajout' ? 'selected' : ''; ?>>Ajout</option>
                <option value="modification" <?php echo $action === 'modification' ? 'selected' : ''; ?>>Modification</option>
                <option value="suppression" <?php echo $action === 'suppression' ? 'selected' : ''; ?>>Suppression</option>
                <option value="connexion" <?php echo $action === 'connexion' ? 'selected' : ''; ?>>Connexion</option>
            </select>
        </div>
        <div class="col-md-4">
            <select name="table" class="form-select">
                <option value="">-- Toutes les tables --</option>
                <option value="livres" <?php echo $table === 'livres' ? 'selected' : ''; ?>>Livres</option>
                <option value="etudiants" <?php echo $table === 'etudiants' ? 'selected' : ''; ?>>Étudiants</option>
                <option value="emprunts" <?php echo $table === 'emprunts' ? 'selected' : ''; ?>>Emprunts</option>
                <option value="categories" <?php echo $table === 'categories' ? 'selected' : ''; ?>>Catégories</option>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-outline-primary w-100">
                <i class="bi bi-search"></i> Filtrer
            </button>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>ID</th>
                    <th>Détails</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun log trouvé.</td></tr>
                <?php endif; ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo e($log['created_at']); ?></td>
                        <td><?php echo e($log['utilisateur_nom'] ?? 'System'); ?></td>
                        <td>
                            <?php
                                $badgeClass = match($log['action']) {
                                    'ajout' => 'bg-success',
                                    'modification' => 'bg-warning',
                                    'suppression' => 'bg-danger',
                                    'connexion' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo e($log['action']); ?></span>
                        </td>
                        <td><?php echo e($log['table_cible']); ?></td>
                        <td><?php echo (int) ($log['id_cible'] ?? 0); ?></td>
                        <td><?php echo e($log['details'] ?? '-'); ?></td>
                        <td><?php echo e($log['ip'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($donneesListe['nombrePages'] > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $donneesListe['page'] <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $donneesListe['page'] - 1; ?>&action=<?php echo urlencode($action ?? ''); ?>&table=<?php echo urlencode($table ?? ''); ?>">Précédent</a>
            </li>
            <?php for ($i = 1; $i <= $donneesListe['nombrePages']; $i++): ?>
                <li class="page-item <?php echo $i === $donneesListe['page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&action=<?php echo urlencode($action ?? ''); ?>&table=<?php echo urlencode($table ?? ''); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $donneesListe['page'] >= $donneesListe['nombrePages'] ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $donneesListe['page'] + 1; ?>&action=<?php echo urlencode($action ?? ''); ?>&table=<?php echo urlencode($table ?? ''); ?>">Suivant</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/../_footer.php'; ?>