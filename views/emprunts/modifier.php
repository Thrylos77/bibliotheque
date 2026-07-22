<?php
/**
 * views/emprunts/modifier.php
 * Permet de modifier les dates d'un emprunt (le livre/etudiant restent fixes
 * pour ne pas fausser la gestion du stock dejà decremente).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/emprunts.php';

auth_verifier_connexion();

$titrePage = 'Modifier un emprunt';

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

// Un emprunt retourné ne peut plus être modifié
if ($emprunt['statut'] === 'Retourne') {
    ajouterFlash('warning', 'Cet emprunt est déjà retourné et ne peut plus être modifié.');
    header('Location: liste.php');
    exit;
}

$erreurs = [];
$valeurs = [
    'date_emprunt'       => $emprunt['date_emprunt'],
    'date_retour_prevue' => $emprunt['date_retour_prevue'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateEmprunt      = trim($_POST['date_emprunt'] ?? '');
    $dateRetourPrevue = trim($_POST['date_retour_prevue'] ?? '');

    $dEmprunt = DateTime::createFromFormat('Y-m-d', $dateEmprunt);
    $dRetour  = DateTime::createFromFormat('Y-m-d', $dateRetourPrevue);

    if (!$dEmprunt || $dEmprunt->format('Y-m-d') !== $dateEmprunt) {
        $erreurs[] = 'La date d\'emprunt est invalide.';
    }
    if (!$dRetour || $dRetour->format('Y-m-d') !== $dateRetourPrevue) {
        $erreurs[] = 'La date de retour prevue est invalide.';
    }
    if (empty($erreurs) && $dateRetourPrevue < $dateEmprunt) {
        $erreurs[] = 'La date de retour prevue doit être posterieure à la date d\'emprunt.';
    }

    $valeurs = [
        'id_livre'           => $emprunt['id_livre'],
        'id_etudiant'        => $emprunt['id_etudiant'],
        'date_emprunt'       => $dateEmprunt,
        'date_retour_prevue' => $dateRetourPrevue,
    ];

    if (empty($erreurs)) {
        if (db_emprunt_modifier($id, $valeurs)) {
            ajouterFlash('success', 'L\'emprunt a ete modifie avec succès.');
            header('Location: liste.php');
            exit;
        }
        $erreurs[] = 'Une erreur est survenue lors de la mise à jour.';
    }
}

require __DIR__ . '/../_header.php';
?>

<h2 class="mb-4"><i class="bi bi-pencil"></i> Modifier un emprunt</h2>

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

    <p><strong>Livre :</strong> <?php echo e($emprunt['livre_titre']); ?></p>
    <p><strong>etudiant :</strong> <?php echo e($emprunt['etudiant_nom'] . ' ' . $emprunt['etudiant_prenom']); ?></p>
    <hr>

    <form method="POST" novalidate>
        <?php echo csrf_champ_html(); ?>
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
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        <a href="liste.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>
