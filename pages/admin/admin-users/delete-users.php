<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'utilisateur est fourni dans l'URL
if (!isset($_GET['id_utilisateur'])) {
    $_SESSION['error'] = "ID de l'utilisateur manquant.";
    header("Location: manage-users.php");
    exit();
} else {
    $id_utilisateur = filter_input(INPUT_GET, 'id_utilisateur', FILTER_VALIDATE_INT);
    // Vérifiez si l'ID de l'utilisateur est un entier valide
    if (!$id_utilisateur && $id_utilisateur !== 0) {
        $_SESSION['error'] = "ID de l'utilisateur invalide.";
        header("Location: manage-users.php");
        exit();
    } else {
        try {
            // Récupérez l'ID de l'utilisateur à supprimer depuis la requête GET
            $id_utilisateur = $_GET['id_utilisateur'];

            // Requête pour supprimer l'utilisateur
            $query = "DELETE FROM UTILISATEUR WHERE id_utilisateur = :id_utilisateur";
            $statement = $connexion->prepare($query);
            $statement->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            
            // Exécutez la requête SQL
            if ($statement->execute()) {
                $_SESSION['success'] = "L'utilisateur a été supprimé avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur.";
            }

            // Redirigez vers la page précédente après la suppression
            header('Location: manage-users.php');
            exit();

        } catch (PDOException $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }
}
// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
