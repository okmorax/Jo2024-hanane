<?php
// Démarrer la session et inclure le fichier de connexion à la base de données
session_start();
require_once("../../../database/database.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifier si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurer la sécurité des données du formulaire
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_STRING);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_STRING);
    $nomPays = filter_input(INPUT_POST, 'nomPays', FILTER_SANITIZE_STRING);
    $nomGenre = filter_input(INPUT_POST, 'nomGenre', FILTER_SANITIZE_STRING);

    // Vérifier si les champs sont vides
    if (empty($nomAthlete) || empty($prenomAthlete) || empty($nomPays) || empty($nomGenre)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: add-athletes.php");
        exit();
    }

    try {
        // Récupérer l'ID du pays à partir du nom du pays
        $queryPaysId = "SELECT id_pays FROM PAYS WHERE nom_pays = :nomPays";
        $statementPaysId = $connexion->prepare($queryPaysId);
        $statementPaysId->bindParam(":nomPays", $nomPays, PDO::PARAM_STR);
        $statementPaysId->execute();

        if ($rowPaysId = $statementPaysId->fetch(PDO::FETCH_ASSOC)) {
            $idPays = $rowPaysId['id_pays'];
        } else {
            $_SESSION['error'] = "Pays non trouvé.";
            header("Location: add-athletes.php");
            exit();
        }

        // Récupérer l'ID du genre à partir du nom du genre
        $queryGenreId = "SELECT id_genre FROM GENRE WHERE nom_genre = :nomGenre";
        $statementGenreId = $connexion->prepare($queryGenreId);
        $statementGenreId->bindParam(":nomGenre", $nomGenre, PDO::PARAM_STR);
        $statementGenreId->execute();

        if ($rowGenreId = $statementGenreId->fetch(PDO::FETCH_ASSOC)) {
            $idGenre = $rowGenreId['id_genre'];
        } else {
            $_SESSION['error'] = "Genre non trouvé.";
            header("Location: add-athletes.php");
            exit();
        }

        // Requête pour ajouter un athlète
        $query = "INSERT INTO ATHLETE (nom_athlete, prenom_athlete, id_pays, id_genre) VALUES (:nomAthlete, :prenomAthlete, :idPays, :idGenre)";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":idPays", $idPays, PDO::PARAM_INT);
        $statement->bindParam(":idGenre", $idGenre, PDO::PARAM_INT);

        // Exécuter la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'athlète a été ajouté avec succès.";
            header("Location: manage-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'athlète.";
            header("Location: add-athletes.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-athletes.php");
        exit();
    }
}

// Afficher les erreurs PHP
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
    <title>Ajouter un Athlète - Jeux Olympiques 2024</title>
    <style>
        /* Ajouter du style CSS si nécessaire */
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
        <h1>Ajouter un Athlète</h1>
        <?php
        // Afficher les messages d'erreur s'il y en a
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-athletes.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet athlète ?')">
            <label for="nomAthlete">Nom de l'Athlète :</label>
            <input type="text" name="nomAthlete" id="nomAthlete" required>

            <label for="prenomAthlete">Prénom de l'Athlète :</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete" required>

            <label for="nomPays">Pays :</label>
            <select name="nomPays" id="nomPays" required>
                <?php
                // Requête SQL pour récupérer les noms des pays depuis la base de données
                $queryPays = "SELECT nom_pays FROM PAYS";
                $statementPays = $connexion->prepare($queryPays);
                $statementPays->execute();

                // Afficher les options de la liste déroulante
                while ($rowPays = $statementPays->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . htmlspecialchars($rowPays['nom_pays']) . "'>" . htmlspecialchars($rowPays['nom_pays']) . "</option>";
                }
                ?>
            </select>

            <label for="nomGenre">Genre :</label>
            <select name="nomGenre" id="nomGenre" required>
                <?php
                // Requête SQL pour récupérer les noms des genres depuis la base de données
                $queryGenre = "SELECT nom_genre FROM GENRE";
                $statementGenre = $connexion->prepare($queryGenre);
                $statementGenre->execute();

                // Afficher les options de la liste déroulante
                while ($rowGenre = $statementGenre->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . htmlspecialchars($rowGenre['nom_genre']) . "'>" . htmlspecialchars($rowGenre['nom_genre']) . "</option>";
                }
                ?>
            </select>

            <input type="submit" value="Ajouter l'Athlète">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des Athlètes</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
