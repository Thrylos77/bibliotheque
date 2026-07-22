<?php
if (!isset($emprunts)) {
    return;
}
?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Livre</th>
                    <th>Étudiant</th>
                    <th>Date d'emprunt</th>
                    <th>Retour prévu</th>
                    <th>Retour effectif</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($emprunts)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun emprunt trouvé.</td></tr>
                <?php endif; ?>
                <?php foreach ($emprunts as $emprunt): ?>
                    <?php
                        $enRetard = $emprunt['statut'] === 'En cours' && $emprunt['date_retour_prevue'] < date('Y-m-d');
                    ?>
                    <tr>
                        <td><?php echo e($emprunt['livre_titre']); ?></td>
                        <td><?php echo e($emprunt['etudiant_nom'] . ' ' . $emprunt['etudiant_prenom']); ?></td>
                        <td><?php echo e($emprunt['date_emprunt']); ?></td>
                        <td><?php echo e($emprunt['date_retour_prevue']); ?></td>
                        <td><?php echo e($emprunt['date_retour'] ?? '-'); ?></td>
                        <td>
                            <?php if ($emprunt['statut'] === 'Retourne'): ?>
                                <span class="badge badge-retourne">Retourne</span>
                            <?php elseif ($enRetard): ?>
                                <span class="badge badge-retard">En retard</span>
                            <?php else: ?>
                                <span class="badge badge-encours">En cours</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($emprunt['statut'] === 'En cours'): ?>
                                <form method="POST" style="display:inline" class="form-retourner">
                                    <?php echo csrf_champ_html(); ?>
                                    <input type="hidden" name="action" value="retourner">
                                    <input type="hidden" name="id" value="<?php echo (int) $emprunt['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Marquer comme retourné">
                                        <i class="bi bi-check2-circle"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if ($emprunt['statut'] === 'En cours'): ?>
                                <a href="modifier.php?id=<?php echo (int) $emprunt['id']; ?>"
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            <?php endif; ?>
                            <a href="supprimer.php?id=<?php echo (int) $emprunt['id']; ?>"
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

<?php if (!$filtreRetard && $donneesListe['nombrePages'] > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $donneesListe['page'] <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo urlPagination($donneesListe['page'] - 1, $recherche, ['filtre' => $filtre, 'retard' => $filtreRetard ? 1 : null]); ?>">Précédent</a>
            </li>
            <?php for ($i = 1; $i <= $donneesListe['nombrePages']; $i++): ?>
                <li class="page-item <?php echo $i === $donneesListe['page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo urlPagination($i, $recherche, ['filtre' => $filtre, 'retard' => $filtreRetard ? 1 : null]); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $donneesListe['page'] >= $donneesListe['nombrePages'] ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo urlPagination($donneesListe['page'] + 1, $recherche, ['filtre' => $filtre, 'retard' => $filtreRetard ? 1 : null]); ?>">Suivant</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
