<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 1) {
    header("Location: login.php");
    exit();
}

// Fetch statistics
try {
    // Total Users
    $stmt = $conn->query("SELECT COUNT(*) as total FROM Utilisateurs");
    $totalUsers = $stmt->fetch()['total'];

    // Total Projects
    $stmt = $conn->query("SELECT COUNT(*) as total FROM Projets");
    $totalProjects = $stmt->fetch()['total'];

    // Total Freelances
    $stmt = $conn->query("SELECT COUNT(*) as total FROM Freelances");
    $totalFreelances = $stmt->fetch()['total'];

    // Total Categories
    $stmt = $conn->query("SELECT COUNT(*) as total FROM Categories");
    $totalCategories = $stmt->fetch()['total'];

    // Total Offers
    $stmt = $conn->query("SELECT COUNT(*) as total FROM Offres");
    $totalOffers = $stmt->fetch()['total'];

    // Total Testimonials
    $stmt = $conn->query("SELECT COUNT(*) as total FROM Temoignages");
    $totalTestimonials = $stmt->fetch()['total'];

    // Recent Projects
    $stmt = $conn->query("SELECT p.titre_projet, u.nom_utilisateur, p.date_creation 
                         FROM Projets p 
                         JOIN Utilisateurs u ON p.id_utilisateur = u.id_utilisateur 
                         ORDER BY p.date_creation DESC LIMIT 5");
    $recentProjects = $stmt->fetchAll();

    // Recent Freelancers
    $stmt = $conn->query("SELECT f.nom_freelance, u.email 
                         FROM Freelances f 
                         JOIN Utilisateurs u ON f.id_utilisateur = u.id_utilisateur 
                         ORDER BY f.id_freelance DESC LIMIT 5");
    $recentFreelancers = $stmt->fetchAll();

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
        :root {
            --sidebar-width: 250px;
        }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background-color: #2c3e50;
            padding-top: 20px;
            color: white;
        }
        .main-content {
            margin-left: var(--sidebar-width);
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
        .stat-card {
            border-radius: 15px;
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
        }
        .navbar {
            margin-left: var(--sidebar-width);
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
            <h1 class="mb-4">Tableau de Bord</h1>
            
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Utilisateurs</h6>
                                    <h2 class="mb-0"><?php echo $totalUsers; ?></h2>
                                </div>
                                <i class='bx bx-user stat-icon'></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Projets</h6>
                                    <h2 class="mb-0"><?php echo $totalProjects; ?></h2>
                                </div>
                                <i class='bx bx-folder stat-icon'></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Freelances</h6>
                                    <h2 class="mb-0"><?php echo $totalFreelances; ?></h2>
                                </div>
                                <i class='bx bx-briefcase stat-icon'></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Catégories</h6>
                                    <h2 class="mb-0"><?php echo $totalCategories; ?></h2>
                                </div>
                                <i class='bx bx-category stat-icon'></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card stat-card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Offres</h6>
                                    <h2 class="mb-0"><?php echo $totalOffers; ?></h2>
                                </div>
                                <i class='bx bx-money stat-icon'></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card stat-card bg-secondary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Témoignages</h6>
                                    <h2 class="mb-0"><?php echo $totalTestimonials; ?></h2>
                                </div>
                                <i class='bx bx-message-square-dots stat-icon'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <!-- Recent Projects -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Projets Récents</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Titre</th>
                                            <th>Créateur</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recentProjects as $project): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($project['titre_projet']); ?></td>
                                            <td><?php echo htmlspecialchars($project['nom_utilisateur']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($project['date_creation'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Freelancers -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Freelances Récents</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recentFreelancers as $freelancer): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($freelancer['nom_freelance']); ?></td>
                                            <td><?php echo htmlspecialchars($freelancer['email']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
