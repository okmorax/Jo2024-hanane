<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

require_once("../../../database/database.php");

// Vérifiez si l'ID de l'épreuve est fourni dans l'URL
if (!isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'épreuve manquant.";
    header("Location: manage-events.php");
    exit();
}

$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'épreuve est un entier valide
if (!$id_epreuve && $id_epreuve !== 0) {
    $_SESSION['error'] = "ID de l'épreuve invalide.";
    header("Location: manage-events.php");
    exit();
}

try {
    // Commencez une transaction
    $connexion->beginTransaction();

    // Requête pour récupérer l'id_lieu associé à l'épreuve
    $queryGetLieu = "SELECT id_lieu FROM EPREUVE WHERE id_epreuve = :id_epreuve";
    $statementGetLieu = $connexion->prepare($queryGetLieu);
    $statementGetLieu->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
    $statementGetLieu->execute();
    $row = $statementGetLieu->fetch(PDO::FETCH_ASSOC);
    $id_lieu = $row['id_lieu'];

    // Requête pour supprimer l'épreuve
    $queryDeleteEpreuve = "DELETE FROM EPREUVE WHERE id_epreuve = :id_epreuve";
    $statementDeleteEpreuve = $connexion->prepare($queryDeleteEpreuve);
    $statementDeleteEpreuve->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
    $statementDeleteEpreuve->execute();

    // Requête pour supprimer le lieu associé si aucun autre événement ne l'utilise
    $queryCountEventsWithSameLieu = "SELECT COUNT(*) AS event_count FROM EPREUVE WHERE id_lieu = :id_lieu";
    $statementCountEventsWithSameLieu = $connexion->prepare($queryCountEventsWithSameLieu);
    $statementCountEventsWithSameLieu->bindParam(':id_lieu', $id_lieu, PDO::PARAM_INT);
    $statementCountEventsWithSameLieu->execute();
    $row = $statementCountEventsWithSameLieu->fetch(PDO::FETCH_ASSOC);
    $eventCount = $row['event_count'];

    if ($eventCount == 0) {
        // Aucun autre événement n'utilise ce lieu, alors supprimez-le
        $queryDeleteLieu = "DELETE FROM LIEU WHERE id_lieu = :id_lieu";
        $statementDeleteLieu = $connexion->prepare($queryDeleteLieu);
        $statementDeleteLieu->bindParam(':id_lieu', $id_lieu, PDO::PARAM_INT);
        $statementDeleteLieu->execute();
    }

    // Commit de la transaction
    $connexion->commit();

    $_SESSION['success'] = "L'épreuve et les informations associées ont été supprimées avec succès.";
    header('Location: manage-events.php');
    exit();

} catch (PDOException $e) {
    // En cas d'erreur, annulez la transaction
    $connexion->rollBack();

    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header('Location: manage-events.php');
    exit();
} catch (Exception $e) {
    // En cas d'erreur, annulez la transaction
    $connexion->rollBack();

    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header('Location: manage-events.php');
    exit();
}

// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
