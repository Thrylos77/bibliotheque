<?php
/**
 * views/categories/liste.php
 * Gestion des categories de livres.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/categories.php';

auth_verifier_connexion();

$titrePage = 'Catégories';

// Traitement suppression en POST avec CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    if (!csrf_verifier_token($_POST['csrf_token'] ?? '')) {
        ajouterFlash('danger', 'Token de sécurité invalide. Veuillez réessayer.');
    } elseif ($_POST['action'] === 'supprimer') {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id && categorie_supprimer($id)) {
            ajouterFlash('success', 'La catégorie a été supprimée.');
        } else {
            ajouterFlash('danger', 'Impossible de supprimer cette catégorie.');
        }
    }
    header('Location: liste.php');
    exit;
}

$page      = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
$recherche = trim(filter_var($_GET['recherche'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));

$donneesListe = categorie_obtenir_liste($page, $recherche);
$categories   = $donneesListe['categories'];

require __DIR__ . '/../_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tags"></i> Gestion des catégories</h2>
    <a href="ajouter.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nouvelle catégorie
    </a>
</div>

<div class="card p-3 mb-3">
    <form method="GET" class="row g-2 form-recherche-auto">
        <div class="col-12">
            <input type="text" name="recherche" class="form-control"
                   placeholder="Rechercher une catégorie..."
                   value="<?php echo e($recherche); ?>">
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th class="text-center">Livres associés</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">Aucune catégorie trouvée.</td></tr>
                <?php endif; ?>
                <?php foreach ($categories as $categorie): ?>
                    <tr>
                        <td><strong><?php echo e($categorie['nom']); ?></strong></td>
                        <td class="text-center">
                            <span class="badge bg-info"><?php echo (int) $categorie['nb_livres']; ?></span>
                        </td>
                        <td class="text-end">
                            <a href="modifier.php?id=<?php echo (int) $categorie['id']; ?>"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="supprimer.php?id=<?php echo (int) $categorie['id']; ?>"
                               class="btn btn-sm btn-outline-danger btn-supprimer"
                               title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
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
                <a class="page-link" href="?page=<?php echo $donneesListe['page'] - 1; ?>&recherche=<?php echo urlencode($recherche); ?>">Précédent</a>
            </li>
            <?php for ($i = 1; $i <= $donneesListe['nombrePages']; $i++): ?>
                <li class="page-item <?php echo $i === $donneesListe['page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&recherche=<?php echo urlencode($recherche); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $donneesListe['page'] >= $donneesListe['nombrePages'] ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $donneesListe['page'] + 1; ?>&recherche=<?php echo urlencode($recherche); ?>">Suivant</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/../_footer.php'; ?>
