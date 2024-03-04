<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $idAthlete = filter_input(INPUT_POST, 'idAthlete', FILTER_VALIDATE_INT);
    $idEpreuve = filter_input(INPUT_POST, 'idEpreuve', FILTER_VALIDATE_INT);
    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);

    // Vérifiez si les champs sont vides
    if ($idAthlete === false || $idEpreuve === false || empty($resultat)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: add-results.php");
        exit();
    }

    try {
        // Requête SQL pour vérifier si le résultat existe déjà pour cet athlète et cette épreuve
        $queryCheckResult = "SELECT COUNT(*) as count FROM PARTICIPER WHERE id_athlete = :idAthlete AND id_epreuve = :idEpreuve";
        $statementCheckResult = $connexion->prepare($queryCheckResult);
        $statementCheckResult->bindParam(":idAthlete", $idAthlete, PDO::PARAM_INT);
        $statementCheckResult->bindParam(":idEpreuve", $idEpreuve, PDO::PARAM_INT);
        $statementCheckResult->execute();

        $count = $statementCheckResult->fetch(PDO::FETCH_ASSOC)['count'];

        // Si le résultat existe déjà, affichez une erreur
        if ($count > 0) {
            $_SESSION['error'] = "Ce résultat existe déjà pour cet athlète et cette épreuve.";
            header("Location: add-results.php");
            exit();
        }

        // Requête pour ajouter un résultat seulement s'il n'existe pas déjà
        $query = "INSERT INTO PARTICIPER (id_athlete, id_epreuve, resultat) 
                  VALUES (:idAthlete, :idEpreuve, :resultat)";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":idAthlete", $idAthlete, PDO::PARAM_INT);
        $statement->bindParam(":idEpreuve", $idEpreuve, PDO::PARAM_INT);
        $statement->bindParam(":resultat", $resultat, PDO::PARAM_STR);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le résultat a été ajouté avec succès.";
            header("Location: manage-results.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du résultat.";
            header("Location: add-results.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-results.php");
        exit();
    }
}

// Afficher les erreurs en PHP
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Ajouter un Résultat - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-genres/manage-genres.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter un Résultat</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-results.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce résultat ?')">
            <label for="idAthlete">Athlète :</label>
            <select name="idAthlete" id="idAthlete" required>
                <?php
                // Requête SQL pour récupérer les athlètes depuis la base de données
                $queryAthletes = "SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE";
                $statementAthletes = $connexion->prepare($queryAthletes);
                $statementAthletes->execute();

                // Afficher les options de la liste déroulante
                while ($rowAthlete = $statementAthletes->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . $rowAthlete['id_athlete'] . "'>" . htmlspecialchars($rowAthlete['nom_athlete'] . ' ' . $rowAthlete['prenom_athlete']) . "</option>";
                }
                ?>
            </select>

            <label for="idEpreuve">Épreuve :</label>
            <select name="idEpreuve" id="idEpreuve" required>
                <?php
                // Requête SQL pour récupérer les épreuves depuis la base de données
                $queryEpreuves = "SELECT id_epreuve, nom_epreuve FROM EPREUVE";
                $statementEpreuves = $connexion->prepare($queryEpreuves);
                $statementEpreuves->execute();

                // Afficher les options de la liste déroulante
                while ($rowEpreuve = $statementEpreuves->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . $rowEpreuve['id_epreuve'] . "'>" . htmlspecialchars($rowEpreuve['nom_epreuve']) . "</option>";
                }
                ?>
            </select>

            <label for="resultat">Résultat :</label>
            <input type="text" name="resultat" id="resultat" required>

            <input type="submit" value="Ajouter le Résultat">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-results.php">Retour à la gestion des Résultats</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
