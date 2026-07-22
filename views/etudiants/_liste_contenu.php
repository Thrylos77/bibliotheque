<?php
if (!isset($etudiants)) {
    return;
}
?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Filière</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($etudiants)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun étudiant trouvé.</td></tr>
                <?php endif; ?>
                <?php foreach ($etudiants as $etudiant): ?>
                    <tr>
                        <td><?php echo e($etudiant['nom']); ?></td>
                        <td><?php echo e($etudiant['prenom']); ?></td>
                        <td><?php echo e($etudiant['email']); ?></td>
                        <td><?php echo e($etudiant['telephone']); ?></td>
                        <td><?php echo e($etudiant['filiere']); ?></td>
                        <td class="text-end">
                            <a href="modifier.php?id=<?php echo (int) $etudiant['id']; ?>"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="supprimer.php?id=<?php echo (int) $etudiant['id']; ?>"
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

<?php if ($donneesListe['nombrePages'] > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $donneesListe['page'] <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo urlPagination($donneesListe['page'] - 1, $recherche, ['filtre' => $filtre]); ?>">Précédent</a>
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
