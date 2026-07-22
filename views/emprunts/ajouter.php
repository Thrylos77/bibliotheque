<?php
/**
 * views/emprunts/ajouter.php
 * Formulaire d'ajout d'un emprunt : verifie la disponibilite et decremente le stock.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/emprunts.php';
require_once __DIR__ . '/../../models/livres.php';
require_once __DIR__ . '/../../models/etudiants.php';

auth_verifier_connexion();

$titrePage = 'Nouvel emprunt';
$erreurs   = [];
$valeurs   = [
    'id_livre'           => '',
    'id_etudiant'        => '',
    'date_emprunt'       => date('Y-m-d'),
    'date_retour_prevue' => date('Y-m-d', strtotime('+14 days')),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultat = emprunt_valider_donnees($_POST);
    $erreurs  = $resultat['erreurs'];
    $valeurs  = $resultat['donnees'];

    if (empty($erreurs)) {
        if (emprunt_ajouter($valeurs)) {
            ajouterFlash('success', 'L\'emprunt a ete enregistre et le stock mis à jour.');
            header('Location: liste.php');
            exit;
        }
        $erreurs[] = 'Une erreur est survenue lors de l\'enregistrement (stock peut-être epuise entre-temps).';
    }
}

$livresDisponibles = db_livre_lister_disponibles();
$etudiants         = db_etudiant_lister_tous();

require __DIR__ . '/../_header.php';
?>

<h2 class="mb-4"><i class="bi bi-plus-circle"></i> Nouvel emprunt</h2>

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
        <div class="mb-3">
            <label class="form-label">Livre *</label>
            <select name="id_livre" class="form-select" required>
                <option value="">-- Selectionner un livre disponible --</option>
                <?php foreach ($livresDisponibles as $livre): ?>
                    <option value="<?php echo (int) $livre['id']; ?>"
                        <?php echo (string) $valeurs['id_livre'] === (string) $livre['id'] ? 'selected' : ''; ?>>
                        <?php echo e($livre['titre'] . ' — ' . $livre['auteur'] . ' (' . $livre['quantite'] . ' dispo.)'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($livresDisponibles)): ?>
                <div class="form-text text-danger">Aucun livre disponible actuellement.</div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">etudiant *</label>
            <select name="id_etudiant" class="form-select" required>
                <option value="">-- Selectionner un etudiant --</option>
                <?php foreach ($etudiants as $etudiant): ?>
                    <option value="<?php echo (int) $etudiant['id']; ?>"
                        <?php echo (string) $valeurs['id_etudiant'] === (string) $etudiant['id'] ? 'selected' : ''; ?>>
                        <?php echo e($etudiant['nom'] . ' ' . $etudiant['prenom'] . ' (' . $etudiant['filiere'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Date d'emprunt *</label>
                <input type="date" name="date_emprunt" class="form-control" required
                       value="<?php echo e($valeurs['date_emprunt']); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Date de retour prevue *</label>
                <input type="date" name="date_retour_prevue" class="form-control" required
                       value="<?php echo e($valeurs['date_retour_prevue']); ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer l'emprunt</button>
        <a href="liste.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>
