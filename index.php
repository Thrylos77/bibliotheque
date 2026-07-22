<?php
/**
 * index.php
 * Page d'accueil protegee : tableau de bord avec statistiques generales.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/controllers/auth.php';
require_once __DIR__ . '/controllers/statistiques.php';

auth_verifier_connexion();

$titrePage = 'Accueil';

$stats = statistiques_obtenir_dashboard();

require __DIR__ . '/views/_header.php';
?>

<h2 class="mb-4">Tableau de bord</h2>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card card-accent p-3 text-center">
            <i class="bi bi-book" style="font-size: 2rem; color: #1B2A4A;"></i>
            <h3 class="mt-2 stat-number"><?php echo $stats['nombre_livres']; ?></h3>
            <p class="text-muted mb-0">Livres au catalogue</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-accent accent-sage p-3 text-center">
            <i class="bi bi-people" style="font-size: 2rem; color: #5B7B6B;"></i>
            <h3 class="mt-2 stat-number"><?php echo $stats['nombre_etudiants']; ?></h3>
            <p class="text-muted mb-0">Étudiants inscrits</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-accent p-3 text-center">
            <i class="bi bi-journal-arrow-up" style="font-size: 2rem; color: #C9A227;"></i>
            <h3 class="mt-2 stat-number"><?php echo $stats['emprunts_en_cours']; ?></h3>
            <p class="text-muted mb-0">Emprunts en cours</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-accent accent-burgundy p-3 text-center">
            <i class="bi bi-exclamation-triangle" style="font-size: 2rem; color: #7A2E2E;"></i>
            <h3 class="mt-2 stat-number"><?php echo $stats['emprunts_en_retard']; ?></h3>
            <p class="text-muted mb-0">Emprunts en retard</p>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <a href="views/livres/liste.php" class="btn btn-outline-primary w-100 py-3">
            <i class="bi bi-book"></i> Gerer les livres
        </a>
    </div>
    <div class="col-md-4">
        <a href="views/etudiants/liste.php" class="btn btn-outline-success w-100 py-3">
            <i class="bi bi-people"></i> Gerer les etudiants
        </a>
    </div>
    <div class="col-md-4">
        <a href="views/emprunts/liste.php" class="btn btn-outline-warning w-100 py-3">
            <i class="bi bi-journal-arrow-up"></i> Gerer les emprunts
        </a>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-star"></i> Livres recommandés de la semaine</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($stats['livres_recommandes'] as $livre): ?>
                        <div class="list-group-item d-flex align-items-center gap-3">
                            <?php if (!empty($livre['couverture']) && file_exists(__DIR__ . '/' . $livre['couverture'])): ?>
                                <img src="<?php echo BASE_URL . $livre['couverture']; ?>"
                                     alt="Couverture"
                                     style="width: 40px; height: 56px; object-fit: cover; border-radius: 4px; flex-shrink: 0;">
                            <?php else: ?>
                                <div style="width: 40px; height: 56px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="bi bi-book" style="color: #adb5bd; font-size: 1rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <strong><?php echo e($livre['titre']); ?></strong>
                                <small class="text-muted d-block"><?php echo e($livre['auteur']); ?></small>
                            </div>
                            <span class="badge bg-secondary"><?php echo e($livre['categorie'] ?? 'Sans catégorie'); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 5 des livres les plus empruntés</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($stats['top_livres'] as $index => $livre): ?>
                        <div class="list-group-item d-flex align-items-center gap-3">
                            <?php if (!empty($livre['couverture']) && file_exists(__DIR__ . '/' . $livre['couverture'])): ?>
                                <img src="<?php echo BASE_URL . $livre['couverture']; ?>"
                                     alt="Couverture"
                                     style="width: 40px; height: 56px; object-fit: cover; border-radius: 4px; flex-shrink: 0;">
                            <?php else: ?>
                                <div style="width: 40px; height: 56px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="bi bi-book" style="color: #adb5bd; font-size: 1rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <strong>#<?php echo $index + 1; ?> <?php echo e($livre['titre']); ?></strong>
                                <small class="text-muted d-block"><?php echo e($livre['auteur']); ?></small>
                            </div>
                            <span class="badge bg-primary"><?php echo (int) $livre['nb_emprunts']; ?> emprunts</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<p class="text-muted mt-4">
    Total d'emprunts enregistres depuis la creation : <strong><?php echo $stats['emprunts_total']; ?></strong>
</p>

<?php require __DIR__ . '/views/_footer.php'; ?>