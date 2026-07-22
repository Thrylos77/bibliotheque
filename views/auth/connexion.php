<?php
// views/auth/connexion.php
// $erreur est fournie par login.php si la connexion a échoué
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Bibliothèque Universitaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-icon"><i class="bi bi-book-half"></i></div>
            <h1>Bibliothèque Universitaire</h1>
            <p>Espace d'administration</p>
        </div>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger py-2"><?php echo e($erreur); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>login.php" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Adresse email</label>
                <input type="email" class="form-control" id="email" name="email" required
                       value="<?php echo e($_POST['email'] ?? ''); ?>" placeholder="admin@bibliotheque.local">
            </div>
            <div class="mb-4">
                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required
                       placeholder="admin123">
            </div>
            <button type="submit" class="btn btn-login w-100">Se connecter</button>
        </form>
    </div>
</div>

</body>
</html>