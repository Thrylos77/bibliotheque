<?php
/**
 * views/emprunts/supprimer.php
 * Page de confirmation de suppression d'un emprunt.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/emprunts.php';

auth_verifier_connexion();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: liste.php');
    exit;
}

$emprunt = db_emprunt_trouver($id);
if (!$emprunt) {
    ajouterFlash('danger', 'Emprunt introuvable.');
    header('Location: liste.php');
    exit;
}

$titrePage = 'Supprimer un emprunt';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verifier_token($_POST['csrf_token'] ?? '')) {
        ajouterFlash('danger', 'Token de sécurité invalide.');
    } elseif (emprunt_supprimer($id)) {
        ajouterFlash('success', 'L\'emprunt a été supprimé avec succès.');
    } else {
        ajouterFlash('danger', 'Impossible de supprimer cet emprunt.');
    }
    header('Location: liste.php');
    exit;
}

require __DIR__ . '/../_header.php';
?>

<div class="card p-4 mx-auto" style="max-width: 600px;">
    <div class="alert alert-warning">
        <h4><i class="bi bi-exclamation-triangle"></i> Confirmation de suppression</h4>
        <p>Êtes-vous sûr de vouloir supprimer cet emprunt ?</p>
        <ul>
            <li><strong>Livre :</strong> <?php echo e($emprunt['livre_titre']); ?></li>
            <li><strong>Étudiant :</strong> <?php echo e($emprunt['etudiant_nom'] . ' ' . $emprunt['etudiant_prenom']); ?></li>
            <li><strong>Date d'emprunt :</strong> <?php echo e($emprunt['date_emprunt']); ?></li>
            <li><strong>Statut :</strong> <?php echo e($emprunt['statut']); ?></li>
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