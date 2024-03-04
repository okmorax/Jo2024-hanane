<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

require_once("../../../database/database.php");

// Vérifiez si l'ID du lieu est fourni dans l'URL
if (!isset($_GET['id_lieu'])) {
    $_SESSION['error'] = "ID du lieu manquant.";
    header("Location: manage-places.php");
    exit();
}

$id_lieu = filter_input(INPUT_GET, 'id_lieu', FILTER_VALIDATE_INT);

// Vérifiez si l'ID du lieu est un entier valide
if (!$id_lieu && $id_lieu !== 0) {
    $_SESSION['error'] = "ID du lieu invalide.";
    header("Location: manage-places.php");
    exit();
}

try {
    // Requête pour supprimer le lieu
    $query = "DELETE FROM LIEU WHERE id_lieu = :id_lieu";
    $statement = $connexion->prepare($query);
    $statement->bindParam(':id_lieu', $id_lieu, PDO::PARAM_INT);

    // Exécutez la requête SQL
    if ($statement->execute()) {
        $_SESSION['success'] = "Le lieu a été supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression du lieu.";
    }

    // Redirigez vers la page précédente après la suppression
    header('Location: manage-places.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header('Location: manage-places.php');
    exit();
}


// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
