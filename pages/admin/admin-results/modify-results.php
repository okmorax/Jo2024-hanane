<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Vérifiez si l'ID du résultat est fourni dans l'URL
if (!isset($_GET['resultat'])) {
    $_SESSION['error'] = "ID du résultat manquant.";
    header("Location: manage-results.php");
    exit();
}
$resultat = filter_input(INPUT_GET, 'resultat', FILTER_SANITIZE_STRING);

// Vérifiez si l'ID du résultat est vide
if (empty($resultat)) {
    $_SESSION['error'] = "ID du résultat invalide.";
    header("Location: manage-results.php");
    exit();
}

// Récupérez les informations du résultat pour affichage dans le formulaire
try {
    $queryResultat = "SELECT id_athlete, resultat, id_epreuve
                    FROM PARTICIPER
                    WHERE resultat = :resultat";
    $statementResultat = $connexion->prepare($queryResultat);
    $statementResultat->bindParam(":resultat", $resultat, PDO::PARAM_STR);
    $statementResultat->execute();

    if ($statementResultat->rowCount() > 0) {
        $resultatInfo = $statementResultat->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Résultat non trouvé.";
        header("Location: manage-results.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-results.php");
    exit();
}

// Récupérez la liste des athlètes pour la liste déroulante
try {
    $queryAthletes = "SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE";
    $statementAthletes = $connexion->query($queryAthletes);
    $athletes = $statementAthletes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-results.php");
    exit();
}

// Récupérez la liste des épreuves pour la liste déroulante
try {
    $queryEpreuves = "SELECT id_epreuve, nom_epreuve FROM EPREUVE";
    $statementEpreuves = $connexion->query($queryEpreuves);
    $epreuves = $statementEpreuves->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-results.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $id_athlete = filter_input(INPUT_POST, 'id_athlete', FILTER_VALIDATE_INT);
    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);
    $id_epreuve = filter_input(INPUT_POST, 'id_epreuve', FILTER_VALIDATE_INT);

    // Vérifiez si des champs obligatoires sont vides
    if (!$id_athlete || empty($resultat) || !$id_epreuve) {
        $_SESSION['error'] = "Veuillez sélectionner un athlète, une épreuve et entrer un résultat.";
        header("Location: modify-results.php?resultat=$resultat");
        exit();
    }

    try {
        // Requête pour mettre à jour le résultat
        $query = "UPDATE PARTICIPER
                  SET resultat = :resultat,
                      id_epreuve = :id_epreuve
                  WHERE resultat = :old_resultat AND id_athlete = :id_athlete";

        $statement = $connexion->prepare($query);
        $statement->bindParam(":resultat", $resultat, PDO::PARAM_STR);
        $statement->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);
        $statement->bindParam(":old_resultat", $_GET['resultat'], PDO::PARAM_STR);
        $statement->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le résultat a été modifié avec succès.";
            header("Location: manage-results.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du résultat.";
            header("Location: modify-results.php?resultat=$resultat");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-results.php?resultat=$resultat");
        exit();
    }
}
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
    <title>Modifier un Résultat - Jeux Olympiques 2024</title>
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
        <h1>Modifier un Résultat</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-results.php?resultat=<?php echo $resultat; ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce résultat?')">
            <input type="hidden" name="resultat" value="<?php echo $resultat; ?>">

            <label for="id_athlete">Athlète :</label>
            <select name="id_athlete" id="id_athlete" required>
                <?php foreach ($athletes as $athlete) : ?>
                    <option value="<?php echo $athlete['id_athlete']; ?>" <?php echo ($athlete['id_athlete'] == $resultatInfo['id_athlete']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($athlete['prenom_athlete'] . ' ' . $athlete['nom_athlete']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_epreuve">Épreuve :</label>
            <select name="id_epreuve" id="id_epreuve" required>
                <?php foreach ($epreuves as $epreuve) : ?>
                    <option value="<?php echo $epreuve['id_epreuve']; ?>" <?php echo ($epreuve['id_epreuve'] == $resultatInfo['id_epreuve']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($epreuve['nom_epreuve']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="resultat">Résultat :</label>
            <input type="text" name="resultat" id="resultat" value="<?php echo htmlspecialchars($resultatInfo['resultat']); ?>" required>

            <input type="submit" value="Modifier le Résultat">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-results.php">Retour à la gestion des résultats</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
