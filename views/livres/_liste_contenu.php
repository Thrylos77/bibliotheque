<?php
if (!isset($livres)) {
    return;
}
?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th></th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>ISBN</th>
                    <th>Catégorie</th>
                    <th>Année</th>
                    <th>Quantité</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($livres)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucun livre trouvé.</td></tr>
                <?php endif; ?>
                <?php foreach ($livres as $livre): ?>
                    <tr>
                        <td>
                            <?php if (!empty($livre['couverture']) && file_exists(__DIR__ . '/../../' . $livre['couverture'])): ?>
                                <img src="<?php echo BASE_URL . $livre['couverture']; ?>"
                                     alt="Couverture"
                                     style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <div style="width: 50px; height: 70px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-book" style="color: #adb5bd; font-size: 1.2rem;"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($livre['titre']); ?></td>
                        <td><?php echo e($livre['auteur']); ?></td>
                        <td><?php echo e($livre['isbn']); ?></td>
                        <td>
                            <?php if (!empty($livre['categorie_nom'])): ?>
                                <span class="badge bg-secondary"><?php echo e($livre['categorie_nom']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e((string) $livre['annee']); ?></td>
                        <td>
                            <?php if ((int) $livre['quantite'] === 0): ?>
                                <span class="badge bg-danger">Indisponible</span>
                            <?php else: ?>
                                <span class="badge bg-success"><?php echo (int) $livre['quantite']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="modifier.php?id=<?php echo (int) $livre['id']; ?>"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="supprimer.php?id=<?php echo (int) $livre['id']; ?>"
                               class="btn btn-sm btn-outline-danger btn-supprimer"
                               title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!$disponibleUniquement && $donneesListe['nombrePages'] > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $donneesListe['page'] <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo urlPagination($donneesListe['page'] - 1, $recherche, ['filtre' => $filtre]); ?>">Precedent</a>
            </li>
            <?php for ($i = 1; $i <= $donneesListe['nombrePages']; $i++): ?>
                <li class="page-item <?php echo $i === $donneesListe['page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo urlPagination($i, $recherche, ['filtre' => $filtre]); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $donneesListe['page'] >= $donneesListe['nombrePages'] ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo urlPagination($donneesListe['page'] + 1, $recherche, ['filtre' => $filtre]); ?>">Suivant</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
