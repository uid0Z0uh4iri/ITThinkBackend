<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 1) {
    header("Location: login.php");
    exit();
}

// Handle Category Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $nom = trim($_POST['nom']);

        try {
            $stmt = $conn->prepare("INSERT INTO categories (nom_categorie) VALUES (?)");
            $stmt->execute([$nom]);
            $_SESSION['message'] = "Catégorie ajoutée avec succès.";
            $_SESSION['message_type'] = "success";
        } catch(PDOException $e) {
            $_SESSION['message'] = "Erreur lors de l'ajout: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }

    if (isset($_POST['edit_category'])) {
        $id = (int)$_POST['category_id'];
        $nom = trim($_POST['nom']);

        try {
            $stmt = $conn->prepare("UPDATE categories SET nom_categorie = ? WHERE id_categorie = ?");
            $stmt->execute([$nom, $id]);
            $_SESSION['message'] = "Catégorie modifiée avec succès.";
            $_SESSION['message_type'] = "success";
        } catch(PDOException $e) {
            $_SESSION['message'] = "Erreur lors de la modification: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }

    if (isset($_POST['delete_category'])) {
        $id = (int)$_POST['category_id'];
        try {
            // First check if category has subcategories
            $stmt = $conn->prepare("SELECT COUNT(*) FROM souscategories WHERE id_categorie = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['message'] = "Impossible de supprimer: Cette catégorie contient des sous-catégories.";
                $_SESSION['message_type'] = "danger";
            } else {
                $stmt = $conn->prepare("DELETE FROM categories WHERE id_categorie = ?");
                $stmt->execute([$id]);
                $_SESSION['message'] = "Catégorie supprimée avec succès.";
                $_SESSION['message_type'] = "success";
            }
        } catch(PDOException $e) {
            $_SESSION['message'] = "Erreur lors de la suppression: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }

    header("Location: category_management.php");
    exit();
}

// Fetch all categories with subcategory count
try {
    $stmt = $conn->query("
        SELECT c.*, COUNT(s.id_sous_categorie) as subcategory_count 
        FROM categories c 
        LEFT JOIN souscategories s ON c.id_categorie = s.id_categorie 
        GROUP BY c.id_categorie 
        ORDER BY c.nom_categorie
    ");
    $categories = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories - ITThink Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background-color: #2c3e50;
            padding-top: 20px;
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar-link:hover {
            background-color: #34495e;
            color: white;
        }
        .sidebar-link.active {
            background-color: #3498db;
        }
        .navbar {
            margin-left: 250px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="px-3 mb-4">
            <h4>ITThink Admin</h4>
        </div>
        <nav>
            <a href="admin_dashboard.php" class="sidebar-link">
                <i class='bx bx-home-alt me-2'></i> Dashboard
            </a>
            <a href="user_management.php" class="sidebar-link">
                <i class='bx bx-user me-2'></i> Gestion Utilisateurs
            </a>
            <a href="category_management.php" class="sidebar-link active">
                <i class='bx bx-category me-2'></i> Gestion Catégories
            </a>
            <a href="subcategory_management.php" class="sidebar-link">
                <i class='bx bx-list-ul me-2'></i> Gestion Sous-catégories
            </a>
        </nav>
    </div>

    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php">
                    <i class='bx bx-log-out'></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Gestion des Catégories</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class='bx bx-plus'></i> Ajouter une Catégorie
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Sous-catégories</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($categories as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['id_categorie']); ?></td>
                                    <td><?php echo htmlspecialchars($category['nom_categorie']); ?></td>
                                    <td><?php echo $category['subcategory_count']; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?php echo $category['id_categorie']; ?>">
                                            <i class='bx bx-edit'></i>
                                        </button>
                                        <form action="" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id_categorie']; ?>">
                                            <button type="submit" name="delete_category" class="btn btn-sm btn-outline-danger">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </form>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $category['id_categorie']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier la catégorie</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="" method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="category_id" value="<?php echo $category['id_categorie']; ?>">
                                                            <div class="mb-3">
                                                                <label for="nom" class="form-label">Nom</label>
                                                                <input type="text" class="form-control" name="nom" 
                                                                       value="<?php echo htmlspecialchars($category['nom_categorie']); ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" name="edit_category" class="btn btn-primary">Sauvegarder</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
