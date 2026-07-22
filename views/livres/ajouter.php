<?php
/**
 * views/livres/ajouter.php
 * Formulaire d'ajout d'un livre + traitement de la soumission.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/livres.php';
require_once __DIR__ . '/../../models/categories.php';

auth_verifier_connexion();

$titrePage = 'Ajouter un livre';
$erreurs   = [];
$valeurs   = ['titre' => '', 'auteur' => '', 'isbn' => '', 'annee' => '', 'quantite' => '', 'id_categorie' => '', 'couverture' => ''];

$categories = db_categorie_lister_toutes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultat = livre_valider_donnees($_POST);
    $erreurs  = $resultat['erreurs'];
    $valeurs  = $resultat['donnees'];

    // Gestion de l'upload de couverture
    $couverture = '';
    if (isset($_FILES['couverture']) && $_FILES['couverture']['error'] === UPLOAD_ERR_OK) {
        $fichier = $_FILES['couverture'];
        $ext = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $extensionsAutorisees)) {
            upload_creer_dossier_couvertures();

            $nomFichier = uniqid('couverture_') . '.' . $ext;
            $destination = __DIR__ . '/../../uploads/couvertures/' . $nomFichier;
            
            if (move_uploaded_file($fichier['tmp_name'], $destination)) {
                $couverture = 'uploads/couvertures/' . $nomFichier;
            } else {
                $erreurs[] = 'Erreur lors de l\'upload de l\'image.';
            }
        } else {
            $erreurs[] = 'Format d\'image non autorisé. Utilisez JPG, PNG, GIF ou WebP.';
        }
    }

    if (empty($erreurs)) {
        $donnees = $resultat['donnees'];
        $donnees['couverture'] = $couverture;
        
        $id = db_livre_inserer($donnees);
        if ($id) {
            db_log_inserer('ajout', 'livres', (int) $id, 'Ajout du livre : ' . $donnees['titre']);
            ajouterFlash('success', 'Le livre a ete ajoute avec succès.');
            header('Location: liste.php');
            exit;
        }
        $erreurs[] = 'Une erreur est survenue lors de l\'enregistrement.';
    }
}

require __DIR__ . '/../_header.php';
?>

<h2 class="mb-4"><i class="bi bi-plus-circle"></i> Ajouter un livre</h2>

<div class="card p-4 mx-auto" style="max-width: 700px;">
    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?php echo e($erreur); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate enctype="multipart/form-data">
        <?php echo csrf_champ_html(); ?>
        <div class="mb-3">
            <label class="form-label">Titre *</label>
            <input type="text" name="titre" class="form-control" required
                   value="<?php echo e($valeurs['titre']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Auteur *</label>
            <input type="text" name="auteur" class="form-control" required
                   value="<?php echo e($valeurs['auteur']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">ISBN</label>
            <input type="text" name="isbn" class="form-control"
                   value="<?php echo e($valeurs['isbn']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Catégorie</label>
            <select name="id_categorie" class="form-select">
                <option value="">-- Sans catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int) $cat['id']; ?>"
                        <?php echo (string) $valeurs['id_categorie'] === (string) $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo e($cat['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Annee *</label>
                <input type="number" name="annee" class="form-control" required
                       value="<?php echo e((string) $valeurs['annee']); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Quantite disponible *</label>
                <input type="number" name="quantite" min="0" class="form-control" required
                       value="<?php echo e((string) $valeurs['quantite']); ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Couverture (image)</label>
            <input type="file" name="couverture" class="form-control" accept="image/*">
            <small class="text-muted">Formats autorisés : JPG, PNG, GIF, WebP</small>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="liste.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>
