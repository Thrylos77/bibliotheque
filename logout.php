<?php
/**
 * logout.php
 * Detruit la session en cours et redirige vers la page de connexion.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/auth.php';

auth_deconnecter(); // redirige et exit() en interne
