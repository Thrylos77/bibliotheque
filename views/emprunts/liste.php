<?php
/**
 * views/emprunts/liste.php
 * Affiche la liste des emprunts, gère le retour d'un livre, la suppression,
 * et le filtre "emprunts en retard" (Exercice 3).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../controllers/auth.php';
require_once __DIR__ . '/../../controllers/emprunts.php';

auth_verifier_connexion();

$titrePage = 'Emprunts';

// Traitement en POST avec CSRF (Cross-Site Request Forgery) : retourner ou supprimer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    if (!csrf_verifier_token($_POST['csrf_token'] ?? '')) {
        ajouterFlash('danger', 'Token de sécurité invalide. Veuillez réessayer.');
    } else {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($_POST['action'] === 'retourner') {
            if ($id && emprunt_retourner_livre($id)) {
                ajouterFlash('success', 'Le livre a ete marque comme retourne et remis en stock.');
            } else {
                ajouterFlash('danger', 'Impossible de traiter ce retour.');
            }
        } elseif ($_POST['action'] === 'supprimer') {
            if ($id && emprunt_supprimer($id)) {
                ajouterFlash('success', 'L\'emprunt a ete supprime avec succès.');
            } else {
                ajouterFlash('danger', 'Impossible de supprimer cet emprunt.');
            }
        }
    }
    header('Location: liste.php');
    exit;
}

$filtreRetard = isset($_GET['retard']);
$page         = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
$recherche    = trim(filter_var($_GET['recherche'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));
$filtreBrut   = $_GET['filtre'] ?? 'tout';
$filtre       = in_array($filtreBrut, ['tout', 'livre', 'etudiant', 'date', 'statut'], true) ? $filtreBrut : 'tout';

if ($filtreRetard) {
    if ($recherche !== '' || $filtre !== 'tout') {
        $donneesListe = emprunt_obtenir_liste($page, $recherche, $filtre);
        $emprunts = array_filter($donneesListe['emprunts'], function ($e) {
            return $e['statut'] === 'En cours' && $e['date_retour_prevue'] < date('Y-m-d');
        });
        $donneesListe['total'] = count($emprunts);
        $donneesListe['nombrePages'] = 1;
    } else {
        $emprunts = db_emprunt_lister_en_retard();
        $donneesListe = ['page' => 1, 'nombrePages' => 1, 'total' => count($emprunts), 'recherche' => $recherche, 'filtre' => $filtre];
    }
} else {
    $donneesListe = emprunt_obtenir_liste($page, $recherche, $filtre);
    $emprunts = $donneesListe['emprunts'];
}

$htmxRequest = !empty($_SERVER['HTTP_HX_REQUEST']);

if ($htmxRequest) {
    require __DIR__ . '/_liste_contenu.php';
    exit;
}

require __DIR__ . '/../_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-journal-arrow-up"></i> Gestion des emprunts</h2>
    <a href="ajouter.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nouvel emprunt
    </a>
</div>

<div class="card p-3 mb-3">
    <form id="form-filtres-emprunts" method="GET" class="row g-2"
          hx-get="liste.php"
          hx-target="#emprunts-results"
          hx-swap="innerHTML"
          hx-trigger="keyup changed delay:300ms, change"
          hx-include="#form-filtres-emprunts"
          hx-push-url="true">
        <?php if ($filtreRetard): ?>
            <input type="hidden" name="retard" value="1">
        <?php endif; ?>
        <div class="col-md-4">
            <select name="filtre" class="form-select">
                <option value="tout" <?php echo $filtre === 'tout' ? 'selected' : ''; ?>>-- Tous les champs --</option>
                <option value="livre" <?php echo $filtre === 'livre' ? 'selected' : ''; ?>>Livre</option>
                <option value="etudiant" <?php echo $filtre === 'etudiant' ? 'selected' : ''; ?>>Étudiant</option>
                <option value="date" <?php echo $filtre === 'date' ? 'selected' : ''; ?>>Dates</option>
                <option value="statut" <?php echo $filtre === 'statut' ? 'selected' : ''; ?>>Statut</option>
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" name="recherche" class="form-control"
                   placeholder="<?php echo $filtre === 'livre' ? 'Rechercher par livre...' : ($filtre === 'etudiant' ? 'Rechercher par étudiant...' : ($filtre === 'date' ? 'Rechercher par dates...' : ($filtre === 'statut' ? 'Rechercher par statut...' : 'Rechercher dans tous les champs...'))); ?>"
                   value="<?php echo e($recherche); ?>">
        </div>
        <div class="col-md-3">
            <?php if ($filtreRetard): ?>
                <a href="liste.php" class="btn btn-secondary w-100"
                   hx-get="liste.php"
                   hx-target="#emprunts-results"
                   hx-swap="innerHTML"
                   hx-push-url="true">Voir tous les emprunts</a>
            <?php else: ?>
                <a href="liste.php?retard=1<?php echo $recherche !== '' ? '&recherche=' . urlencode($recherche) : ''; ?><?php echo $filtre !== 'tout' ? '&filtre=' . urlencode($filtre) : ''; ?>"
                   class="btn btn-outline-danger w-100"
                   hx-get="liste.php?retard=1<?php echo $recherche !== '' ? '&recherche=' . urlencode($recherche) : ''; ?><?php echo $filtre !== 'tout' ? '&filtre=' . urlencode($filtre) : ''; ?>"
                   hx-target="#emprunts-results"
                   hx-swap="innerHTML"
                   hx-push-url="true">
                    <i class="bi bi-exclamation-triangle"></i> Emprunts en retard
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div id="emprunts-results">
    <?php require __DIR__ . '/_liste_contenu.php'; ?>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>
