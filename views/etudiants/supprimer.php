<?php
/**
 * views/etudiants/supprimer.php
 * Page de confirmation de suppression d'un étudiant.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/etudiants.php';

auth_verifier_connexion();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: liste.php');
    exit;
}

$etudiant = db_etudiant_trouver($id);
if (!$etudiant) {
    ajouterFlash('danger', 'Étudiant introuvable.');
    header('Location: liste.php');
    exit;
}

$titrePage = 'Supprimer un étudiant';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verifier_token($_POST['csrf_token'] ?? '')) {
        ajouterFlash('danger', 'Token de sécurité invalide.');
    } elseif (etudiant_supprimer($id)) {
        ajouterFlash('success', 'L\'étudiant a été supprimé avec succès.');
    } else {
        ajouterFlash('danger', 'Impossible de supprimer cet étudiant : il est encore lié à un ou plusieurs emprunts.');
    }
    header('Location: liste.php');
    exit;
}

require __DIR__ . '/../_header.php';
?>

<div class="card p-4 mx-auto" style="max-width: 600px;">
    <div class="alert alert-warning">
        <h4><i class="bi bi-exclamation-triangle"></i> Confirmation de suppression</h4>
        <p>Êtes-vous sûr de vouloir supprimer cet étudiant ?</p>
        <ul>
            <li><strong>Nom :</strong> <?php echo e($etudiant['nom'] . ' ' . $etudiant['prenom']); ?></li>
            <li><strong>Email :</strong> <?php echo e($etudiant['email']); ?></li>
            <li><strong>Filière :</strong> <?php echo e($etudiant['filiere'] ?? '-'); ?></li>
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