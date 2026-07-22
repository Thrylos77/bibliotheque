<?php
// _header.php - inclus en haut de chaque page protégée
$flashMessages = recupererFlash();
$pageActuelle = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titrePage) ? e($titrePage) . ' - ' : ''; ?>Bibliothèque Universitaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-bibliotheque mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
            <i class="bi bi-book-half"></i> Bibliothèque
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $pageActuelle === 'index' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $pageActuelle === 'liste' && str_contains($_SERVER['SCRIPT_NAME'], 'livres') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>views/livres/liste.php">Livres</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $pageActuelle === 'liste' && str_contains($_SERVER['SCRIPT_NAME'], 'etudiants') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>views/etudiants/liste.php">Étudiants</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $pageActuelle === 'liste' && str_contains($_SERVER['SCRIPT_NAME'], 'emprunts') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>views/emprunts/liste.php">Emprunts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo str_contains($_SERVER['SCRIPT_NAME'], 'categories') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>views/categories/liste.php">Catégories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo str_contains($_SERVER['SCRIPT_NAME'], 'logs') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>views/logs/liste.php">Journal</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item d-flex align-items-center me-3" style="color: rgba(246,239,224,0.85);">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo e($_SESSION['utilisateur_nom'] ?? ''); ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>logout.php">
                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <?php foreach ($flashMessages as $flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show alert-flash" role="alert">
            <?php echo e($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
</div>

<div class="container mb-5">