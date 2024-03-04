<?php
// Démarrage de la session et inclusion du fichier de base de données
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

try {
    // Requête pour récupérer la liste des athlètes depuis la base de données
    $query = "SELECT id_athlete, nom_athlete, prenom_athlete, nom_pays, nom_genre
              FROM ATHLETE
              INNER JOIN PAYS ON ATHLETE.id_pays = PAYS.id_pays
              INNER JOIN GENRE ON ATHLETE.id_genre = GENRE.id_genre
              ORDER BY nom_athlete";

    // Exécutez la requête et récupérez les athlètes dans un tableau associatif
    $athletes = $connexion->query($query)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // En cas d'erreur de base de données, affichez un message d'erreur
    echo "Erreur : " . $e->getMessage();
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
    <title>Liste des Athlètes - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .action-buttons button {
            background-color: #1b1b1b;
            color: #d7c378;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .action-buttons button:hover {
            background-color: #d7c378;
            color: #1b1b1b;
        }
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
        <h1>Liste des Athlètes</h1>

        <!-- Boutons d'action -->
        <div class="action-buttons">
            <button onclick="openAddAthleteForm()">Ajouter un athlète</button>
            <!-- Vous pouvez ajouter d'autres boutons d'action ici... -->
        </div>

        <!-- Tableau des athlètes -->
        <?php if (!empty($athletes)) : ?>
            <table>
                <!-- En-têtes du tableau -->
                <tr>
                    <th class='color'>Nom</th>
                    <th class='color'>Prénom</th>
                    <th class='color'>Pays</th>
                    <th class='color'>Genre</th>
                    <th class='color'>Modifier</th>
                    <th class='color'>Supprimer</th>
                </tr>

                <!-- Affichage des données des athlètes dans le tableau -->
                <?php foreach ($athletes as $athlete) : ?>
                    <tr>
                        <td><?= htmlspecialchars($athlete['nom_athlete']); ?></td>
                        <td><?= htmlspecialchars($athlete['prenom_athlete']); ?></td>
                        <td><?= htmlspecialchars($athlete['nom_pays']); ?></td>
                        <td><?= htmlspecialchars($athlete['nom_genre']); ?></td>
                        <!-- Bouton pour modifier l'athlète avec son ID comme paramètre -->
                        <td><button onclick="openModifyAthleteForm(<?= $athlete['id_athlete']; ?>)">Modifier</button></td>
                        <!-- Bouton pour supprimer l'athlète avec son ID comme paramètre -->
                        <td><button onclick="deleteAthleteConfirmation(<?= $athlete['id_athlete']; ?>)">Supprimer</button></td>
                    </tr>
                <?php endforeach; ?>

            </table>
        <?php else : ?>
            <!-- Message si aucun athlète trouvé -->
            <p>Aucun athlète trouvé.</p>
        <?php endif; ?>

        <!-- Lien de retour à l'accueil de l'administration -->
        <p class="paragraph-link">
            <a class="link-home" href="../admin.php">Accueil administration</a>
        </p>
    </main>

    <!-- Pied de page -->
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

    <!-- Script JavaScript pour les actions des boutons -->
    <script>
        function openAddAthleteForm() {
            // Rediriger vers la page d'ajout d'athlète
            window.location.href = 'add-athletes.php';
        }

        function openModifyAthleteForm(id_athlete) {
            console.log("ID de l'athlète : " + id_athlete);
            // Rediriger vers la page de modification avec l'ID de l'athlète
            window.location.href = 'modify-athletes.php?id_athlete=' + id_athlete;
        }

        function deleteAthleteConfirmation(id_athlete) {
            // Afficher une fenêtre de confirmation pour supprimer un athlète
            if (confirm("Êtes-vous sûr de vouloir supprimer cet athlète?")) {
                // Rediriger vers la page de suppression avec l'ID de l'athlète
                window.location.href = 'delete-athletes.php?id_athlete=' + id_athlete;
            }
        }
    </script>
</body>

</html>
