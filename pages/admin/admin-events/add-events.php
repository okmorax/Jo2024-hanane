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
// Assurez-vous d'obtenir des données sécurisées et filtrées
$nomEpreuve = filter_input(INPUT_POST, 'nomEpreuve', FILTER_SANITIZE_STRING);
$dateEpreuve = filter_input(INPUT_POST, 'dateEpreuve', FILTER_SANITIZE_STRING);
$heureEpreuve = filter_input(INPUT_POST, 'heureEpreuve', FILTER_SANITIZE_STRING);
$nomLieu = filter_input(INPUT_POST, 'nomLieu', FILTER_SANITIZE_STRING);
$villeLieu = filter_input(INPUT_POST, 'villeLieu', FILTER_SANITIZE_STRING);
$idSport = filter_input(INPUT_POST, 'idSport', FILTER_VALIDATE_INT); // Assurez-vous que 'idSport' est le nom correct du champ dans votre formulaire

// Vérifiez si les champs sont vides
if (empty($nomEpreuve) || empty($dateEpreuve) || empty($heureEpreuve) || empty($nomLieu) || empty($villeLieu) || $idSport === false) {
    $_SESSION['error'] = "Veuillez remplir tous les champs et sélectionner un sport valide.";
    header("Location: add-events.php");
    exit();
}

try {
    // Requête pour obtenir l'ID du lieu à partir du nom du lieu et de la ville
    $queryLieuId = "SELECT id_lieu FROM LIEU WHERE nom_lieu = :nomLieu AND ville_lieu = :villeLieu";
    $statementLieuId = $connexion->prepare($queryLieuId);
    $statementLieuId->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
    $statementLieuId->bindParam(":villeLieu", $villeLieu, PDO::PARAM_STR);
    $statementLieuId->execute();

    if ($rowLieuId = $statementLieuId->fetch(PDO::FETCH_ASSOC)) {
        $idLieu = $rowLieuId['id_lieu'];
    } else {
        $_SESSION['error'] = "Lieu non trouvé.";
        header("Location: add-events.php");
        exit();
    }

    // Requête pour ajouter une épreuve
    $query = "INSERT INTO EPREUVE (nom_epreuve, date_epreuve, heure_epreuve, id_lieu, id_sport) VALUES (:nomEpreuve, :dateEpreuve, :heureEpreuve, :idLieu, :idSport)";
    $statement = $connexion->prepare($query);
    $statement->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
    $statement->bindParam(":dateEpreuve", $dateEpreuve, PDO::PARAM_STR);
    $statement->bindParam(":heureEpreuve", $heureEpreuve, PDO::PARAM_STR);
    $statement->bindParam(":idLieu", $idLieu, PDO::PARAM_INT);

    // Assurez-vous que 'idSport' est la bonne variable et qu'elle n'est pas nulle
    if ($idSport !== null) {
        $statement->bindParam(":idSport", $idSport, PDO::PARAM_INT);
    } else {
        $_SESSION['error'] = "Veuillez sélectionner un sport valide.";
        header("Location: add-events.php");
        exit();
    }

    // Exécutez la requête
    if ($statement->execute()) {
        $_SESSION['success'] = "L'épreuve a été ajoutée avec succès.";
        header("Location: manage-events.php");
        exit();
    } else {
        $_SESSION['error'] = "Erreur lors de l'ajout de l'épreuve.";
        header("Location: add-events.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: add-events.php");
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
    <title>Ajouter une Épreuve - Jeux Olympiques 2024</title>
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
        <h1>Ajouter une Épreuve</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-events.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cette épreuve ?')">
            <label for="nomEpreuve">Nom de l'Épreuve :</label>
            <input type="text" name="nomEpreuve" id="nomEpreuve" required>

            <label for="dateEpreuve">Date de l'Épreuve :</label>
            <input type="date" name="dateEpreuve" id="dateEpreuve" required>

            <label for="heureEpreuve">Heure de l'Épreuve :</label>
            <input type="time" name="heureEpreuve" id="heureEpreuve" required>

            <label for="nomLieu">Nom du Lieu :</label>
            <select name="nomLieu" id="nomLieu" required>
                <?php
                // Requête SQL pour récupérer les noms des lieux depuis la base de données
                $queryLieu = "SELECT nom_lieu FROM LIEU";
                $statementLieu = $connexion->prepare($queryLieu);
                $statementLieu->execute();

                // Afficher les options de la liste déroulante
                while ($rowLieu = $statementLieu->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . htmlspecialchars($rowLieu['nom_lieu']) . "'>" . htmlspecialchars($rowLieu['nom_lieu']) . "</option>";
                }
                ?>
            </select>

            <label for="villeLieu">Ville du Lieu :</label>
            <select name="villeLieu" id="villeLieu" required>
                <?php
                // Requête SQL pour récupérer les noms des villes depuis la base de données
                $queryVille = "SELECT DISTINCT ville_lieu FROM LIEU";
                $statementVille = $connexion->prepare($queryVille);
                $statementVille->execute();

                // Afficher les options de la liste déroulante
                while ($rowVille = $statementVille->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . htmlspecialchars($rowVille['ville_lieu']) . "'>" . htmlspecialchars($rowVille['ville_lieu']) . "</option>";
                }
                ?>
            </select>
            <label for="idSport">Sport :</label>
    <select name="idSport" id="idSport" required>
        <?php
            // Récupérez les sports depuis la base de données
            $querySports = "SELECT id_sport, nom_sport FROM SPORT";
            $statementSports = $connexion->prepare($querySports);
            $statementSports->execute();

            // Affichez les options du menu déroulant avec les sports disponibles
            while ($rowSport = $statementSports->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value=\"" . $rowSport['id_sport'] . "\">" . htmlspecialchars($rowSport['nom_sport']) . "</option>";
            }
        ?>
    </select>



            <input type="submit" value="Ajouter l'Épreuve">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion des Épreuves</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
