<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

require_once("../../../database/database.php");

// Vérifiez si l'ID du pays est fourni dans l'URL
if (!isset($_GET['id_pays'])) {
    $_SESSION['error'] = "ID du pays manquant.";
    header("Location: manage-countries.php");
    exit();
}

$id_pays = filter_input(INPUT_GET, 'id_pays', FILTER_VALIDATE_INT);

// Vérifiez si l'ID du pays est un entier valide
if (!$id_pays && $id_pays !== 0) {
    $_SESSION['error'] = "ID du pays invalide.";
    header("Location: manage-countries.php");
    exit();
}

try {
    // Requête pour supprimer le lieu
    $query = "DELETE FROM PAYS WHERE id_pays = :id_pays";
    $statement = $connexion->prepare($query);
    $statement->bindParam(':id_pays', $id_pays, PDO::PARAM_INT);

    // Exécutez la requête SQL
    if ($statement->execute()) {
        $_SESSION['success'] = "Le pays a été supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression du lieu.";
    }

    // Redirigez vers la page précédente après la suppression
    header('Location: manage-countries.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header('Location: manage-countries.php');
    exit();
}


// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
