<?php
/**
 * helpers.php
 * Petites fonctions utilitaires reutilisees dans toute l'application.
 */

function ajouterFlash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function recupererFlash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

function e(?string $valeur): string
{
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Genere un token CSRF et le stocke en session.
 * Retourne le token sous forme de chaine.
 */
function csrf_generer_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifie un token CSRF soumis par formulaire.
 * Detruit le token apres verification (usage unique).
 */
function csrf_verifier_token(?string $tokenSoumis): bool
{
    if (empty($_SESSION['csrf_token']) || empty($tokenSoumis)) {
        return false;
    }
    $valide = hash_equals($_SESSION['csrf_token'], $tokenSoumis);
    // Regenerer le token pour le prochain formulaire
    unset($_SESSION['csrf_token']);
    return $valide;
}

/**
 * Affiche un champ hidden CSRF dans un formulaire.
 */
function csrf_champ_html(): string
{
    $token = csrf_generer_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function urlPagination(int $page, string $recherche = '', array $extraParams = []): string
{
    $params = ['page' => $page];
    if ($recherche !== '') {
        $params['recherche'] = $recherche;
    }
    // Fusionner les parametres supplementaires (ex: disponibles, retard)
    foreach ($extraParams as $key => $value) {
        if ($value !== null && $value !== false && $value !== '') {
            $params[$key] = $value;
        }
    }
    return '?' . http_build_query($params);
}

/**
 * Cree le dossier d'upload des couvertures s'il n'existe pas.
 */
function upload_creer_dossier_couvertures(): void
{
    $dossier = __DIR__ . '/../uploads/couvertures/';
    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }
}

/**
 * Verifie si l'utilisateur est admin.
 */
function est_admin(): bool
{
    return isset($_SESSION['utilisateur_role']) && $_SESSION['utilisateur_role'] === 'admin';
}

/**
 * Verifie si l'utilisateur est gestionnaire ou admin.
 */
function est_gestionnaire(): bool
{
    return isset($_SESSION['utilisateur_role']) && 
           ($_SESSION['utilisateur_role'] === 'admin' || $_SESSION['utilisateur_role'] === 'gestionnaire');
}

/**
 * Verifie que l'utilisateur a les droits requis (admin uniquement pour actions sensibles).
 */
function verifier_droits_admin(): void
{
    if (!est_admin()) {
        ajouterFlash('danger', 'Accès refusé. Droits administrateur requis.');
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}
