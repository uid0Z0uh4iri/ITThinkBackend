<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Get user information
    $stmt = $conn->prepare("SELECT nom_utilisateur FROM Utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Get user's latest projects with category and subcategory names
    $stmt_projects = $conn->prepare("
        SELECT p.*, c.nom_categorie as categorie_nom, sc.nom_sous_categorie as sous_categorie_nom,
               (SELECT COUNT(*) FROM Offres WHERE id_projet = p.id_projet) as nombre_offres
        FROM Projets p
        LEFT JOIN Categories c ON p.id_categorie = c.id_categorie
        LEFT JOIN souscategories sc ON p.id_sous_categorie = sc.id_sous_categorie
        WHERE p.id_utilisateur = ?
        ORDER BY p.date_creation DESC
        LIMIT 5
    ");
    $stmt_projects->execute([$_SESSION['user_id']]);
    $projects = $stmt_projects->fetchAll();

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - ITThink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="home.php">ITThink</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Ajouter un projet</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mes_offres.php">Voir les offres</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Mon Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Bienvenue, <?php echo htmlspecialchars($user['nom_utilisateur']); ?></h1>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Vos derniers projets</h5>
                <?php if (empty($projects)): ?>
                    <p class="text-muted">Vous n'avez pas encore créé de projets.</p>
                    <a href="profile.php" class="btn btn-primary">Créer votre premier projet</a>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Catégorie</th>
                                    <th>Sous-catégorie</th>
                                    <th>Date de création</th>
                                    <th>Offres reçues</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($project['titre_projet']); ?></td>
                                        <td><?php echo htmlspecialchars($project['categorie_nom']); ?></td>
                                        <td><?php echo htmlspecialchars($project['sous_categorie_nom']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($project['date_creation'])); ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo $project['nombre_offres']; ?> offre(s)
                                            </span>
                                        </td>
                                        <td>
                                            <a href="voir_offres.php?projet_id=<?php echo $project['id_projet']; ?>" 
                                               class="btn btn-sm btn-info">Voir les offres</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 