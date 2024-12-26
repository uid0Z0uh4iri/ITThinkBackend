<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 1) {
    header("Location: login.php");
    exit();
}

// Handle Subcategory Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subcategory'])) {
        $nom = trim($_POST['nom']);
        $description = trim($_POST['description']);
        $id_categorie = (int)$_POST['id_categorie'];

        try {
            $stmt = $conn->prepare("INSERT INTO souscategories (nom_sous_categorie, id_categorie) VALUES (?, ?)");
            $stmt->execute([$nom, $id_categorie]);
            $_SESSION['message'] = "Sous-catégorie ajoutée avec succès.";
            $_SESSION['message_type'] = "success";
        } catch(PDOException $e) {
            $_SESSION['message'] = "Erreur lors de l'ajout: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }

    if (isset($_POST['edit_subcategory'])) {
        $id = (int)$_POST['subcategory_id'];
        $nom = trim($_POST['nom']);
        $id_categorie = (int)$_POST['id_categorie'];

        try {
            $stmt = $conn->prepare("UPDATE souscategories SET nom_sous_categorie = ?, id_categorie = ? WHERE id_sous_categorie = ?");
            $stmt->execute([$nom, $id_categorie, $id]);
            $_SESSION['message'] = "Sous-catégorie modifiée avec succès.";
            $_SESSION['message_type'] = "success";
        } catch(PDOException $e) {
            $_SESSION['message'] = "Erreur lors de la modification: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }

    if (isset($_POST['delete_subcategory'])) {
        $id = (int)$_POST['subcategory_id'];
        try {
            $stmt = $conn->prepare("DELETE FROM souscategories WHERE id_sous_categorie = ?");
            $stmt->execute([$id]);
            $_SESSION['message'] = "Sous-catégorie supprimée avec succès.";
            $_SESSION['message_type'] = "success";
        } catch(PDOException $e) {
            $_SESSION['message'] = "Erreur lors de la suppression: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }

    header("Location: subcategory_management.php");
    exit();
}

// Fetch all categories for dropdowns
try {
    $categories = $conn->query("SELECT id_categorie, nom_categorie FROM categories ORDER BY nom_categorie")->fetchAll();
} catch(PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Fetch all subcategories with their parent category names
try {
    $stmt = $conn->query("
        SELECT s.*, c.nom_categorie as category_name 
        FROM souscategories s 
        JOIN categories c ON s.id_categorie = c.id_categorie 
        ORDER BY c.nom_categorie, s.nom_sous_categorie
    ");
    $subcategories = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Sous-catégories - ITThink Admin</title>
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
            <a href="category_management.php" class="sidebar-link">
                <i class='bx bx-category me-2'></i> Gestion Catégories
            </a>
            <a href="subcategory_management.php" class="sidebar-link active">
                <i class='bx bx-list-ul me-2'></i> Gestion Sous-catégories
            </a>
            <a href="feedback_management.php" class="sidebar-link">
                <i class='bx bx-message-square-dots me-2'></i> Gestion Témoignages
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
                <h1>Gestion des Sous-catégories</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubcategoryModal">
                    <i class='bx bx-plus'></i> Ajouter une Sous-catégorie
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
                                    <th>Catégorie</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($subcategories as $subcategory): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subcategory['id_sous_categorie']); ?></td>
                                    <td><?php echo htmlspecialchars($subcategory['nom_sous_categorie']); ?></td>
                                    <td><?php echo htmlspecialchars($subcategory['category_name']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?php echo $subcategory['id_sous_categorie']; ?>">
                                            <i class='bx bx-edit'></i>
                                        </button>
                                        <form action="" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette sous-catégorie ?');">
                                            <input type="hidden" name="subcategory_id" value="<?php echo $subcategory['id_sous_categorie']; ?>">
                                            <button type="submit" name="delete_subcategory" class="btn btn-sm btn-outline-danger">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </form>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $subcategory['id_sous_categorie']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier la sous-catégorie</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="" method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="subcategory_id" value="<?php echo $subcategory['id_sous_categorie']; ?>">
                                                            <div class="mb-3">
                                                                <label for="nom" class="form-label">Nom</label>
                                                                <input type="text" class="form-control" name="nom" 
                                                                       value="<?php echo htmlspecialchars($subcategory['nom_sous_categorie']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="id_categorie" class="form-label">Catégorie</label>
                                                                <select class="form-select" name="id_categorie" required>
                                                                    <?php foreach($categories as $category): ?>
                                                                        <option value="<?php echo $category['id_categorie']; ?>" 
                                                                                <?php echo $subcategory['id_categorie'] == $category['id_categorie'] ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($category['nom_categorie']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" name="edit_subcategory" class="btn btn-primary">Sauvegarder</button>
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

    <!-- Add Subcategory Modal -->
    <div class="modal fade" id="addSubcategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une sous-catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="id_categorie" class="form-label">Catégorie</label>
                            <select class="form-select" name="id_categorie" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id_categorie']; ?>">
                                        <?php echo htmlspecialchars($category['nom_categorie']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="add_subcategory" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
