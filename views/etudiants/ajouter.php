<?php
/**
 * views/etudiants/ajouter.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/etudiants.php';

auth_verifier_connexion();

$titrePage = 'Ajouter un etudiant';
$erreurs   = [];
$valeurs   = ['nom' => '', 'prenom' => '', 'email' => '', 'telephone' => '', 'filiere' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultat = etudiant_valider_donnees($_POST);
    $erreurs  = $resultat['erreurs'];
    $valeurs  = $resultat['donnees'];

    if (empty($erreurs)) {
        if (db_etudiant_inserer($valeurs)) {
            ajouterFlash('success', 'L\'etudiant a ete ajoute avec succès.');
            header('Location: liste.php');
            exit;
        }
        $erreurs[] = 'Une erreur est survenue lors de l\'enregistrement.';
    }
}

require __DIR__ . '/../_header.php';
?>

<h2 class="mb-4"><i class="bi bi-plus-circle"></i> Ajouter un etudiant</h2>

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

    <form method="POST" novalidate>
        <?php echo csrf_champ_html(); ?>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nom *</label>
                <input type="text" name="nom" class="form-control" required
                       value="<?php echo e($valeurs['nom']); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Prenom *</label>
                <input type="text" name="prenom" class="form-control" required
                       value="<?php echo e($valeurs['prenom']); ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?php echo e($valeurs['email']); ?>">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Telephone</label>
                <input type="text" name="telephone" class="form-control"
                       value="<?php echo e($valeurs['telephone']); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Filière</label>
                <select name="filiere" class="form-select">
                    <option value="">-- Sélectionner une filière --</option>
                    <?php
                    $filieres = [
                        'Génie Logiciel',
                        'Réseaux et Sécurité',
                        'Systèmes et Réseaux',
                        'Cybersécurité',
                        'Base de Données',
                        'Génie Informatique',
                        'Réseaux Télécoms',
                    ];
                    foreach ($filieres as $filiere):
                    ?>
                        <option value="<?php echo e($filiere); ?>"
                            <?php echo $valeurs['filiere'] === $filiere ? 'selected' : ''; ?>>
                            <?php echo e($filiere); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="liste.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>
