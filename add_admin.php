<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 1) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = (int)$_POST['role'];

    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id_utilisateur FROM Utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = "Un utilisateur avec cet email existe déjà.";
            $_SESSION['message_type'] = "danger";
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Utilisateurs (nom_utilisateur, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $role]);
            
            $_SESSION['message'] = "Utilisateur créé avec succès!";
            $_SESSION['message_type'] = "success";
            header("Location: user_management.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['message'] = "Erreur: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Utilisateur - ITThink Admin</title>
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

            <div class="card">
                <div class="card-header">
                    <h3>Ajouter un Utilisateur</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="0">Utilisateur</option>
                                <option value="1">Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
                        <a href="user_management.php" class="btn btn-secondary">Retour</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
