<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 1) {
    header("Location: login.php");
    exit();
}

// Fetch statistics
try {
    // User statistics
    $stmt = $conn->query("SELECT COUNT(*) as total_users FROM utilisateurs");
    $userStats = $stmt->fetch();
    $stmt = $conn->query("SELECT COUNT(*) as total_admins FROM utilisateurs WHERE role = 1");
    $adminStats = $stmt->fetch();

    // Category statistics
    $stmt = $conn->query("SELECT COUNT(*) as total_categories FROM categories");
    $categoryStats = $stmt->fetch();
    $stmt = $conn->query("SELECT COUNT(*) as total_subcategories FROM souscategories");
    $subcategoryStats = $stmt->fetch();

    // Feedback statistics
    $stmt = $conn->query("SELECT COUNT(*) as total_feedbacks FROM temoignages");
    $feedbackStats = $stmt->fetch();

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - ITThink</title>
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
        .stat-card {
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
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
            <a href="admin_dashboard.php" class="sidebar-link active">
                <i class='bx bx-home-alt me-2'></i> Dashboard
            </a>
            <a href="user_management.php" class="sidebar-link">
                <i class='bx bx-user me-2'></i> Gestion Utilisateurs
            </a>
            <a href="category_management.php" class="sidebar-link">
                <i class='bx bx-category me-2'></i> Gestion Catégories
            </a>
            <a href="subcategory_management.php" class="sidebar-link">
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
                <a class="nav-link" href="../logout.php">
                    <i class='bx bx-log-out'></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4">Tableau de bord</h1>

            <div class="row g-4">
                <!-- Users Stats -->
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Utilisateurs</h6>
                                    <h2 class="mb-0"><?php echo $userStats['total_users']; ?></h2>
                                </div>
                                <div class="stat-icon">
                                    <i class='bx bx-user'></i>
                                </div>
                            </div>
                            <small>Dont <?php echo $adminStats['total_admins']; ?> administrateurs</small>
                        </div>
                        <div class="card-footer bg-primary border-0">
                            <a href="user_management.php" class="text-white text-decoration-none">
                                Gérer les utilisateurs <i class='bx bx-right-arrow-alt'></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Categories Stats -->
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Catégories</h6>
                                    <h2 class="mb-0"><?php echo $categoryStats['total_categories']; ?></h2>
                                </div>
                                <div class="stat-icon">
                                    <i class='bx bx-category'></i>
                                </div>
                            </div>
                            <small>Et <?php echo $subcategoryStats['total_subcategories']; ?> sous-catégories</small>
                        </div>
                        <div class="card-footer bg-success border-0">
                            <a href="category_management.php" class="text-white text-decoration-none">
                                Gérer les catégories <i class='bx bx-right-arrow-alt'></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Feedbacks Stats -->
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Témoignages</h6>
                                    <h2 class="mb-0"><?php echo $feedbackStats['total_feedbacks']; ?></h2>
                                </div>
                                <div class="stat-icon">
                                    <i class='bx bx-message-square-dots'></i>
                                </div>
                            </div>
                            <small>Avis des utilisateurs</small>
                        </div>
                        <div class="card-footer bg-info border-0">
                            <a href="feedback_management.php" class="text-white text-decoration-none">
                                Gérer les témoignages <i class='bx bx-right-arrow-alt'></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-5">
                <div class="col-12">
                    <h2 class="mb-4">Actions rapides</h2>
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-3">
                            <a href="add_admin.php" class="card text-decoration-none">
                                <div class="card-body text-center">
                                    <i class='bx bx-user-plus fs-1 text-primary'></i>
                                    <h5 class="mt-3 text-dark">Ajouter un utilisateur</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="category_management.php" class="card text-decoration-none">
                                <div class="card-body text-center">
                                    <i class='bx bx-plus-circle fs-1 text-success'></i>
                                    <h5 class="mt-3 text-dark">Nouvelle catégorie</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="subcategory_management.php" class="card text-decoration-none">
                                <div class="card-body text-center">
                                    <i class='bx bx-list-plus fs-1 text-warning'></i>
                                    <h5 class="mt-3 text-dark">Nouvelle sous-catégorie</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="feedback_management.php" class="card text-decoration-none">
                                <div class="card-body text-center">
                                    <i class='bx bx-message-square-detail fs-1 text-info'></i>
                                    <h5 class="mt-3 text-dark">Voir les témoignages</h5>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
