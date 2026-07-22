<?php
/**
 * views/categories/modifier.php
 * Formulaire de modification d'une catégorie.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/categories.php';

auth_verifier_connexion();

$titrePage = 'Modifier une catégorie';

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

$erreurs = [];
$valeurs = ['nom' => $categorie['nom']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verifier_token($_POST['csrf_token'] ?? '')) {
        $erreurs[] = 'Token de sécurité invalide. Veuillez réessayer.';
    } else {
        $_POST['id'] = $id; // Pour la vérification d'unicité
        $resultat = categorie_valider_donnees($_POST);
        $erreurs  = $resultat['erreurs'];
        $valeurs  = $resultat['donnees'];

        if (empty($erreurs)) {
            if (db_categorie_modifier($id, $valeurs['nom'])) {
                ajouterFlash('success', 'La catégorie a été modifiée avec succès.');
                header('Location: liste.php');
                exit;
            }
            $erreurs[] = 'Une erreur est survenue lors de la mise à jour.';
        }
    }
}

require __DIR__ . '/../_header.php';
?>

<h2 class="mb-4"><i class="bi bi-pencil"></i> Modifier une catégorie</h2>

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
                   value="<?php echo e($valeurs['nom']); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        <a href="liste.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>