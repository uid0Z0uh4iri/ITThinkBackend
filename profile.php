<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information and categories
try {
    $stmt = $conn->prepare("SELECT nom_utilisateur, email FROM Utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Fetch all categories
    $stmt_categories = $conn->query("SELECT * FROM Categories");
    $categories = $stmt_categories->fetchAll();

    // Fetch sous-categories if a category is selected
    $sous_categories = [];
    if (isset($_POST['categorie'])) {
        $stmt_sous_cat = $conn->prepare("SELECT id_sous_categorie, nom_sous_categorie FROM souscategories WHERE id_categorie = ?");
        $stmt_sous_cat->execute([$_POST['categorie']]);
        $sous_categories = $stmt_sous_cat->fetchAll();
    }

    // Handle project creation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_project'])) {
        if (!empty($_POST['titre_projet']) && !empty($_POST['description']) && 
            !empty($_POST['categorie']) && !empty($_POST['sous_categorie'])) {
            
            $stmt_project = $conn->prepare("INSERT INTO Projets (titre_projet, description, id_categorie, id_sous_categorie, date_creation, id_utilisateur) VALUES (?, ?, ?, ?, NOW(), ?)");
            $stmt_project->execute([
                $_POST['titre_projet'],
                $_POST['description'],
                $_POST['categorie'],
                $_POST['sous_categorie'],
                $_SESSION['user_id']
            ]);
            
            // Redirection vers home.php après création réussie
            header("Location: home.php");
            exit();
        } else {
            $error_message = "Veuillez remplir tous les champs";
        }
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ITThink</title>
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
        <h1>Mon Profile</h1>
        
        <!-- Existing profile card -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Informations personnelles</h5>
                <p class="card-text">
                    <strong>Nom d'utilisateur:</strong> <?php echo htmlspecialchars($user['nom_utilisateur']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                </p>
            </div>
        </div>

        <!-- Project Creation Form -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Créer un nouveau projet</h5>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="titre_projet" class="form-label">Titre du projet</label>
                        <input type="text" class="form-control" id="titre_projet" name="titre_projet" 
                               value="<?php echo isset($_POST['titre_projet']) ? htmlspecialchars($_POST['titre_projet']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="categorie" class="form-label">Catégorie</label>
                        <select class="form-control" id="categorie" name="categorie" onchange="this.form.submit()" required>
                            <option value="">Sélectionnez une catégorie</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id_categorie']; ?>" 
                                    <?php echo (isset($_POST['categorie']) && $_POST['categorie'] == $category['id_categorie']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['nom_categorie']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($sous_categories)): ?>
                        <div class="mb-3">
                            <label for="sous_categorie" class="form-label">Sous-catégorie</label>
                            <select class="form-control" id="sous_categorie" name="sous_categorie" required>
                                <option value="">Sélectionnez une sous-catégorie</option>
                                <?php foreach ($sous_categories as $sous_cat): ?>
                                    <option value="<?php echo $sous_cat['id_sous_categorie']; ?>" 
                                        <?php echo (isset($_POST['sous_categorie']) && $_POST['sous_categorie'] == $sous_cat['id_sous_categorie']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sous_cat['nom_sous_categorie']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="create_project" class="btn btn-primary">Créer le projet</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
