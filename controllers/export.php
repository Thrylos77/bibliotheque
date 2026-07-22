<?php
/**
 * controllers/export.php
 * Export PDF et Excel des donnees.
 */

require_once __DIR__ . '/../models/livres.php';
require_once __DIR__ . '/../models/etudiants.php';
require_once __DIR__ . '/../models/emprunts.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

/**
 * Export des livres en CSV
 */
function export_livres_csv(): void
{
    $livres = db_livre_lister(1, 1000); // Tous les livres
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=livres_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM pour Excel
    
    // En-têtes
    fputcsv($output, ['ID', 'Titre', 'Auteur', 'ISBN', 'Année', 'Quantité', 'Catégorie'], ';');
    
    // Données
    foreach ($livres as $livre) {
        fputcsv($output, [
            $livre['id'],
            $livre['titre'],
            $livre['auteur'],
            $livre['isbn'],
            $livre['annee'],
            $livre['quantite'],
            $livre['categorie_nom'] ?? '',
        ], ';');
    }
    
    fclose($output);
    exit;
}

/**
 * Export des étudiants en CSV
 */
function export_etudiants_csv(): void
{
    $etudiants = db_etudiant_lister(1, 1000);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=etudiants_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");
    
    fputcsv($output, ['ID', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Filière'], ';');
    
    foreach ($etudiants as $etudiant) {
        fputcsv($output, [
            $etudiant['id'],
            $etudiant['nom'],
            $etudiant['prenom'],
            $etudiant['email'],
            $etudiant['telephone'],
            $etudiant['filiere'],
        ], ';');
    }
    
    fclose($output);
    exit;
}

/**
 * Export des emprunts en CSV
 */
function export_emprunts_csv(): void
{
    $emprunts = db_emprunt_lister(1, 1000);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=emprunts_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");
    
    fputcsv($output, ['ID', 'Livre', 'Étudiant', 'Date emprunt', 'Retour prévu', 'Retour effectif', 'Statut'], ';');
    
    foreach ($emprunts as $emprunt) {
        fputcsv($output, [
            $emprunt['id'],
            $emprunt['livre_titre'],
            $emprunt['etudiant_nom'] . ' ' . $emprunt['etudiant_prenom'],
            $emprunt['date_emprunt'],
            $emprunt['date_retour_prevue'],
            $emprunt['date_retour'] ?? '',
            $emprunt['statut'],
        ], ';');
    }
    
    fclose($output);
    exit;
}

/**
 * Export des emprunts en retard en CSV
 */
function export_retards_csv(): void
{
    $retards = db_statistiques_emprunts_retard();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=retards_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");
    
    fputcsv($output, ['ID', 'Livre', 'Étudiant', 'Date emprunt', 'Retour prévu', 'Jours de retard'], ';');
    
    foreach ($retards as $retard) {
        fputcsv($output, [
            $retard['id'],
            $retard['titre'],
            $retard['etudiant_nom'] . ' ' . $retard['etudiant_prenom'],
            $retard['date_emprunt'],
            $retard['date_retour_prevue'],
            $retard['jours_retard'],
        ], ';');
    }
    
    fclose($output);
    exit;
}