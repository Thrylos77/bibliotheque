<?php
/**
 * views/etudiants/liste.php
 * Affiche la liste des etudiants avec recherche multicritère et pagination.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/etudiants.php';

auth_verifier_connexion();

$titrePage = 'etudiants';

// Traitement de la suppression en POST avec CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    if (!csrf_verifier_token($_POST['csrf_token'] ?? '')) {
        ajouterFlash('danger', 'Token de sécurité invalide. Veuillez réessayer.');
    } elseif ($_POST['action'] === 'supprimer') {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id && db_etudiant_supprimer($id)) {
            ajouterFlash('success', 'L\'etudiant a ete supprime avec succès.');
        } else {
            ajouterFlash('danger', 'Impossible de supprimer cet etudiant : il est encore lie à un ou plusieurs emprunts.');
        }
    }
    header('Location: liste.php');
    exit;
}

$page      = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
$recherche = trim(filter_var($_GET['recherche'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));
$filtreBrut = $_GET['filtre'] ?? 'tout';
$filtre = in_array($filtreBrut, ['tout', 'nom', 'prenom', 'email', 'filiere'], true) ? $filtreBrut : 'tout';

$donneesListe = etudiant_obtenir_liste($page, $recherche, $filtre);
$etudiants    = $donneesListe['etudiants'];

$htmxRequest = !empty($_SERVER['HTTP_HX_REQUEST']);

if ($htmxRequest) {
    require __DIR__ . '/_liste_contenu.php';
    exit;
}

require __DIR__ . '/../_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-people"></i> Gestion des etudiants</h2>
    <a href="ajouter.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Ajouter un etudiant
    </a>
</div>

<div class="card p-3 mb-3">
    <form id="form-filtres-etudiants" method="GET" class="row g-2 form-recherche-auto"
          hx-get="liste.php"
          hx-target="#etudiants-results"
          hx-swap="innerHTML"
          hx-trigger="keyup changed delay:300ms, change"
          hx-include="#form-filtres-etudiants"
          hx-push-url="true">
        <div class="col-md-4">
            <select name="filtre" class="form-select">
                <option value="tout" <?php echo $filtre === 'tout' ? 'selected' : ''; ?>>-- Tous les champs --</option>
                <option value="nom" <?php echo $filtre === 'nom' ? 'selected' : ''; ?>>Nom</option>
                <option value="prenom" <?php echo $filtre === 'prenom' ? 'selected' : ''; ?>>Prénom</option>
                <option value="email" <?php echo $filtre === 'email' ? 'selected' : ''; ?>>Email</option>
                <option value="filiere" <?php echo $filtre === 'filiere' ? 'selected' : ''; ?>>Filière</option>
            </select>
        </div>
        <div class="col-md-8">
            <input type="text" name="recherche" class="form-control"
                   placeholder="<?php echo $filtre === 'nom' ? 'Rechercher par nom...' : ($filtre === 'prenom' ? 'Rechercher par prénom...' : ($filtre === 'email' ? 'Rechercher par email...' : ($filtre === 'filiere' ? 'Rechercher par filière...' : 'Rechercher dans tous les champs...'))); ?>"
                   value="<?php echo e($recherche); ?>">
        </div>
    </form>
</div>

<div id="etudiants-results">
    <?php require __DIR__ . '/_liste_contenu.php'; ?>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>
