<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

require_once("../../../database/database.php");

// Vérifiez si l'ID du résultat est fourni dans l'URL
if (isset($_GET['resultat'])) {
    // Récupérez et filtrez l'ID du résultat
    $resultat = filter_input(INPUT_GET, 'resultat', FILTER_SANITIZE_STRING);

    // Vérifiez si l'ID du résultat est un entier valide
    if ($resultat !== false || $resultat === "0") {
        try {
            // Commencez une transaction
            $connexion->beginTransaction();

            // Requête pour récupérer l'ID de l'athlète associé au résultat
            $queryGetAthlete = "SELECT id_athlete FROM PARTICIPER WHERE resultat = :resultat";
            $statementGetAthlete = $connexion->prepare($queryGetAthlete);
            $statementGetAthlete->bindParam(':resultat', $resultat, PDO::PARAM_STR);
            $statementGetAthlete->execute();
            $rowAthlete = $statementGetAthlete->fetch(PDO::FETCH_ASSOC);

            if ($rowAthlete) {
                // Suppression de l'enregistrement dans PARTICIPER
                $queryDeleteResultat = "DELETE FROM PARTICIPER WHERE resultat = :resultat";
                $statementDeleteResultat = $connexion->prepare($queryDeleteResultat);
                $statementDeleteResultat->bindParam(':resultat', $resultat, PDO::PARAM_STR);
                $statementDeleteResultat->execute();

                // Commit de la transaction
                $connexion->commit();
                $_SESSION['success'] = "Le résultat et les informations associées ont été supprimés avec succès.";
            } else {
                $_SESSION['error'] = "Aucun athlète associé à ce résultat.";
            }
        } catch (PDOException $e) {
            // En cas d'erreur, annulez la transaction
            $connexion->rollBack();
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "ID du résultat invalide.";
    }
}

// Redirigez l'utilisateur vers la page de gestion des résultats
header('Location: manage-results.php');
exit();
?>
