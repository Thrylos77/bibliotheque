<?php
/**
 * views/categories/ajouter.php
 * Formulaire d'ajout d'une catégorie.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/categories.php';

auth_verifier_connexion();

$titrePage = 'Ajouter une catégorie';
$erreurs   = [];
$valeurs   = ['nom' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verifier_token($_POST['csrf_token'] ?? '')) {
        $erreurs[] = 'Token de sécurité invalide. Veuillez réessayer.';
    } else {
        $resultat = categorie_valider_donnees($_POST);
        $erreurs  = $resultat['erreurs'];
        $valeurs  = $resultat['donnees'];

        if (empty($erreurs)) {
            if (db_categorie_inserer($valeurs['nom'])) {
                ajouterFlash('success', 'La catégorie a été ajoutée avec succès.');
                header('Location: liste.php');
                exit;
            }
            $erreurs[] = 'Une erreur est survenue lors de l\'enregistrement.';
        }
    }
}

require __DIR__ . '/../_header.php';
?>

<h2 class="mb-4"><i class="bi bi-plus-circle"></i> Ajouter une catégorie</h2>

<div class="card p-4 mx-auto" style="max-width: 500px;">
    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?php echo e($erreur); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <?php echo csrf_champ_html(); ?>
        <div class="mb-3">
            <label class="form-label">Nom de la catégorie *</label>
            <input type="text" name="nom" class="form-control" required maxlength="50"
                   value="<?php echo e($valeurs['nom']); ?>" placeholder="ex: Roman, Science, Histoire...">
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="liste.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>