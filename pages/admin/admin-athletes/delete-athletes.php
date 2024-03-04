<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    // Rediriger vers la page de connexion s'il n'est pas connecté
    header('Location: ../../../index.php');
    exit();
}

// Inclure le fichier de connexion à la base de données
require_once("../../../database/database.php");

// Vérifier si l'ID de l'athlète est fourni dans l'URL
if (!isset($_GET['id_athlete'])) {
    // Rediriger avec un message d'erreur si l'ID est manquant
    $_SESSION['error'] = "ID de l'athlète manquant.";
    header("Location: manage-athletes.php");
    exit();
}

// Récupérer et filtrer l'ID de l'athlète depuis l'URL
$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifier si l'ID de l'athlète est un entier valide
if (!$id_athlete && $id_athlete !== 0) {
    // Rediriger avec un message d'erreur si l'ID n'est pas valide
    $_SESSION['error'] = "ID de l'athlète invalide.";
    header("Location: manage-athletes.php");
    exit();
}

try {
    // Commencer une transaction pour la suppression sécurisée
    $connexion->beginTransaction();

    // Requête pour supprimer l'athlète
    $queryDeleteAthlete = "DELETE FROM ATHLETE WHERE id_athlete = :id_athlete";
    $statementDeleteAthlete = $connexion->prepare($queryDeleteAthlete);
    $statementDeleteAthlete->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
    $statementDeleteAthlete->execute();

    // Valider la suppression en commitant la transaction
    $connexion->commit();

    // Rediriger avec un message de succès
    $_SESSION['success'] = "L'athlète a été supprimé avec succès.";
    header('Location: manage-athletes.php');
    exit();

} catch (PDOException $e) {
    // En cas d'erreur, annuler la transaction et rediriger avec un message d'erreur
    $connexion->rollBack();
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header('Location: manage-athletes.php');
    exit();
} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction et rediriger avec un message d'erreur
    $connexion->rollBack();
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header('Location: manage-athletes.php');
    exit();
}

// Afficher les erreurs PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
