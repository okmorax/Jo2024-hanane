<?php
// Démarrage de la session et inclusion du fichier de base de données
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'athlète est fourni dans l'URL
if (!isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "ID de l'athlète manquant.";
    header("Location: manage-athletes.php");
    exit();
}

// Filtrer et valider l'ID de l'athlète
$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'athlète est un entier valide
if (!$id_athlete && $id_athlete !== 0) {
    $_SESSION['error'] = "ID de l'athlète invalide.";
    header("Location: manage-athletes.php");
    exit();
}

try {
    // Récupérez les informations de l'athlète pour affichage dans le formulaire
    $queryAthlete = "SELECT nom_athlete, prenom_athlete, id_pays, id_genre FROM ATHLETE WHERE id_athlete = :id_athlete";
    $statementAthlete = $connexion->prepare($queryAthlete);
    $statementAthlete->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
    $statementAthlete->execute();

    // Vérifiez si l'athlète existe
    if ($statementAthlete->rowCount() > 0) {
        $athlete = $statementAthlete->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Athlète non trouvé.";
        header("Location: manage-athletes.php");
        exit();
    }

    // Récupérez la liste des pays pour la liste déroulante
    $queryCountries = "SELECT id_pays, nom_pays FROM PAYS";
    $countries = $connexion->query($queryCountries)->fetchAll(PDO::FETCH_ASSOC);

    // Récupérez la liste des genres depuis la base de données
    $queryGenres = "SELECT id_genre, nom_genre FROM GENRE";
    $genres = $connexion->query($queryGenres)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // En cas d'erreur de base de données, affichez un message d'erreur
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-athletes.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_STRING);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_STRING);
    $id_pays = filter_input(INPUT_POST, 'id_pays', FILTER_VALIDATE_INT);
    $id_genre = filter_input(INPUT_POST, 'id_genre', FILTER_VALIDATE_INT);

    // Vérifiez si des champs obligatoires sont vides
    if (empty($nomAthlete) || empty($prenomAthlete) || !$id_pays || !$id_genre) {
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
        header("Location: modify-athletes.php?id_athlete=$id_athlete");
        exit();
    }

    try {
        // Requête pour mettre à jour l'athlète
        $queryUpdateAthlete = "UPDATE ATHLETE
                               SET nom_athlete = :nomAthlete,
                                   prenom_athlete = :prenomAthlete,
                                   id_pays = :id_pays,
                                   id_genre = :id_genre
                               WHERE id_athlete = :id_athlete";

        $statementUpdateAthlete = $connexion->prepare($queryUpdateAthlete);
        $statementUpdateAthlete->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statementUpdateAthlete->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statementUpdateAthlete->bindParam(":id_pays", $id_pays, PDO::PARAM_INT);
        $statementUpdateAthlete->bindParam(":id_genre", $id_genre, PDO::PARAM_INT);
        $statementUpdateAthlete->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statementUpdateAthlete->execute()) {
            $_SESSION['success'] = "L'athlète a été modifié avec succès.";
            header("Location: manage-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'athlète.";
            header("Location: modify-athletes.php?id_athlete=$id_athlete");
            exit();
        }
    } catch (PDOException $e) {
        // En cas d'erreur de base de données lors de la mise à jour, affichez un message d'erreur
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-athletes.php?id_athlete=$id_athlete");
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
    <title>Modifier un Athlète - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <!-- En-tête de la page -->
    <header>
        <nav>
            <!-- Menu vers les différentes sections de l'administration -->
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
    <!-- Contenu principal de la page -->
    <main>
        <!-- Titre de la page -->
        <h1>Modifier un Athlète</h1>
        <?php
        // Afficher les messages d'erreur s'il y en a
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <!-- Formulaire de modification de l'athlète -->
        <form action="modify-athletes.php?id_athlete=<?php echo $id_athlete; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet athlète?')">
            <!-- Champs pour le nom de l'athlète -->
            <label for="nomAthlete">Nom de l'Athlète :</label>
            <input type="text" name="nomAthlete" id="nomAthlete"
                value="<?php echo htmlspecialchars($athlete['nom_athlete']); ?>" required>

            <!-- Champs pour le prénom de l'athlète -->
            <label for="prenomAthlete">Prénom de l'Athlète :</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete"
                value="<?php echo htmlspecialchars($athlete['prenom_athlete']); ?>" required>

            <!-- Liste déroulante pour le pays de l'athlète -->
            <label for="id_pays">Pays de l'Athlète :</label>
            <select name="id_pays" id="id_pays" required>
                <?php foreach ($countries as $country) : ?>
                    <option value="<?php echo $country['id_pays']; ?>"
                        <?php echo ($country['id_pays'] == $athlete['id_pays']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($country['nom_pays']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Liste déroulante pour le genre de l'athlète -->
            <label for="genre">Genre de l'Athlète :</label>
            <select name="id_genre" id="genre" required>
                <?php foreach ($genres as $genre) : ?>
                    <!-- Utilisation de id_genre dans le name et l'option -->
                    <option value="<?php echo $genre['id_genre']; ?>"
                        <?php echo ($genre['id_genre'] == $athlete['id_genre']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($genre['nom_genre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Bouton de soumission du formulaire -->
            <input type="submit" value="Modifier l'Athlète">
        </form>
        <!-- Lien de retour vers la gestion des athlètes -->
        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des athlètes</a>
        </p>
    </main>
    <!-- Pied de page de la page -->
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
