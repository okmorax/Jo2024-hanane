<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

require_once("../../../database/database.php");

// Vérifiez si l'ID du genre est fourni dans l'URL
if (!isset($_GET['id_genre'])) {
    $_SESSION['error'] = "ID du genre manquant.";
    header("Location: manage-genres.php");
    exit();
}

$id_genre = filter_input(INPUT_GET, 'id_genre', FILTER_VALIDATE_INT);

// Vérifiez si l'ID du genre est un entier valide
if (!$id_genre && $id_genre !== 0) {
    $_SESSION['error'] = "ID du genre invalide.";
    header("Location: manage-genres.php");
    exit();
}

try {
    // Requête pour supprimer le genre
    $query = "DELETE FROM GENRE WHERE id_genre = :id_genre";
    $statement = $connexion->prepare($query);
    $statement->bindParam(':id_genre', $id_genre, PDO::PARAM_INT);

    // Exécutez la requête SQL
    if ($statement->execute()) {
        $_SESSION['success'] = "Le genre a été supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression du genre.";
    }

    // Redirigez vers la page précédente après la suppression
    header('Location: manage-genres.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header('Location: manage-genres.php');
    exit();
}

// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
