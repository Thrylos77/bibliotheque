<?php
/**
 * views/categories/supprimer.php
 * Page de confirmation de suppression d'une catégorie.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/categories.php';

auth_verifier_connexion();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: liste.php');
    exit;
}

$categorie = db_categorie_trouver($id);
if (!$categorie) {
    ajouterFlash('danger', 'Catégorie introuvable.');
    header('Location: liste.php');
    exit;
}

$titrePage = 'Supprimer une catégorie';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verifier_token($_POST['csrf_token'] ?? '')) {
        ajouterFlash('danger', 'Token de sécurité invalide.');
    } elseif (categorie_supprimer($id)) {
        ajouterFlash('success', 'La catégorie a été supprimée avec succès.');
    } else {
        ajouterFlash('danger', 'Impossible de supprimer cette catégorie : elle contient des livres.');
    }
    header('Location: liste.php');
    exit;
}

require __DIR__ . '/../_header.php';
?>

<div class="card p-4 mx-auto" style="max-width: 600px;">
    <div class="alert alert-warning">
        <h4><i class="bi bi-exclamation-triangle"></i> Confirmation de suppression</h4>
        <p>Êtes-vous sûr de vouloir supprimer cette catégorie ?</p>
        <ul>
            <li><strong>Nom :</strong> <?php echo e($categorie['nom']); ?></li>
            <li><strong>Livres associés :</strong> <?php echo (int) $categorie['nb_livres']; ?></li>
        </ul>
        <p class="mb-0 text-warning">Cette action est irréversible.</p>
    </div>

    <form method="POST">
        <?php echo csrf_champ_html(); ?>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash"></i> Confirmer la suppression
            </button>
            <a href="liste.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Annuler
            </a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>