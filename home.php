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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar {
            background-color: #2c3e50;
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            color: #fff !important;
        }

        .nav-item {
            margin: 0 5px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 0.5rem 1rem !important;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            color: #fff !important;
            background-color: #3498db;
        }

        .nav-link i {
            margin-right: 5px;
        }

        @media (max-width: 991px) {
            .nav-item {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="home.php">
                <strong>ITThink</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                            <i class="fas fa-plus-circle"></i> Add Project
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mes_offres.php' ? 'active' : ''; ?>" href="mes_offres.php">
                            <i class="fas fa-list"></i> View Offers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="switch_freelancer.php">
                            <i class="fas fa-sync"></i> Switch to Freelancer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
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