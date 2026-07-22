<?php
/**
 * views/livres/liste.php
 * Affiche la liste des livres avec recherche multicritère, pagination et catégorie.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/livres.php';

auth_verifier_connexion();

$titrePage = 'Livres';

// Traitement de la suppression en POST avec CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    if (!csrf_verifier_token($_POST['csrf_token'] ?? '')) {
        ajouterFlash('danger', 'Token de sécurité invalide. Veuillez réessayer.');
    } elseif ($_POST['action'] === 'supprimer') {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id && db_livre_supprimer($id)) {
            ajouterFlash('success', 'Le livre a ete supprime avec succès.');
        } else {
            ajouterFlash('danger', 'Impossible de supprimer ce livre : il est encore lie à un ou plusieurs emprunts.');
        }
    }
    header('Location: liste.php');
    exit;
}

$page       = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
$recherche  = trim(filter_var($_GET['recherche'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));
$filtreBrut = $_GET['filtre'] ?? 'tout';
$filtre     = in_array($filtreBrut, ['tout', 'titre', 'auteur', 'date'], true) ? $filtreBrut : 'tout';
$disponibleUniquement = isset($_GET['disponibles']);

if ($disponibleUniquement) {
    if ($recherche !== '' || $filtre !== 'titre') {
        $donneesListe = livre_obtenir_liste($page, $recherche, $filtre);
        $livres = array_values(array_filter($donneesListe['livres'], function ($l) {
            return (int) $l['quantite'] > 0;
        }));
        $donneesListe['total'] = count($livres);
        $donneesListe['nombrePages'] = 1;
    } else {
        $livres = db_livre_lister_disponibles();
        $donneesListe = ['page' => 1, 'nombrePages' => 1, 'total' => count($livres), 'recherche' => $recherche, 'filtre' => $filtre];
    }
} else {
    $donneesListe = livre_obtenir_liste($page, $recherche, $filtre);
    $livres = $donneesListe['livres'];
}

$htmxRequest = !empty($_SERVER['HTTP_HX_REQUEST']);

if ($htmxRequest) {
    require __DIR__ . '/_liste_contenu.php';
    exit;
}

require __DIR__ . '/../_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-book"></i> Gestion des livres</h2>
    <a href="ajouter.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Ajouter un livre
    </a>
</div>

<div class="card p-3 mb-3">
    <form id="form-filtres-livres" method="GET" class="row g-2 align-items-center"
          hx-get="liste.php"
          hx-target="#livres-results"
          hx-swap="innerHTML"
          hx-trigger="keyup changed delay:300ms, change"
          hx-include="#form-filtres-livres"
          hx-push-url="true">
        <?php if ($disponibleUniquement): ?>
            <input type="hidden" name="disponibles" value="1">
        <?php endif; ?>
        <div class="col-md-4">
            <select name="filtre" class="form-select">
                <option value="tout" <?php echo $filtre === 'tout' ? 'selected' : ''; ?>>-- Tous les champs --</option>
                <option value="titre" <?php echo $filtre === 'titre' ? 'selected' : ''; ?>>Titre</option>
                <option value="auteur" <?php echo $filtre === 'auteur' ? 'selected' : ''; ?>>Auteur</option>
                <option value="date" <?php echo $filtre === 'date' ? 'selected' : ''; ?>>Date</option>
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" name="recherche" class="form-control"
                   placeholder="<?php echo $filtre === 'auteur' ? 'Rechercher par auteur...' : ($filtre === 'date' ? 'Rechercher par année...' : ($filtre === 'titre' ? 'Rechercher par titre...' : 'Rechercher dans tous les champs...')); ?>"
                   value="<?php echo e($recherche); ?>">
        </div>
        <div class="col-md-3">
            <?php if ($disponibleUniquement): ?>
                <a href="liste.php" class="btn btn-secondary w-100"
                   hx-get="liste.php"
                   hx-target="#livres-results"
                   hx-swap="innerHTML"
                   hx-push-url="true">Voir tous les livres</a>
            <?php else: ?>
                <a href="liste.php?disponibles=1<?php echo $recherche !== '' ? '&recherche=' . urlencode($recherche) : ''; ?><?php echo $filtre !== 'tout' ? '&filtre=' . urlencode($filtre) : ''; ?>"
                   class="btn btn-outline-success w-100"
                   hx-get="liste.php?disponibles=1<?php echo $recherche !== '' ? '&recherche=' . urlencode($recherche) : ''; ?><?php echo $filtre !== 'tout' ? '&filtre=' . urlencode($filtre) : ''; ?>"
                   hx-target="#livres-results"
                   hx-swap="innerHTML"
                   hx-push-url="true">
                    <i class="bi bi-check-circle"></i> Disponibles uniquement
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div id="livres-results">
    <?php require __DIR__ . '/_liste_contenu.php'; ?>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>
