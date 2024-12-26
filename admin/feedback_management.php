<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 1) {
    header("Location: login.php");
    exit();
}

// Handle Feedback Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feedback'])) {
    $id = (int)$_POST['feedback_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM temoignages WHERE id_temoignage = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Témoignage supprimé avec succès.";
        $_SESSION['message_type'] = "success";
    } catch(PDOException $e) {
        $_SESSION['message'] = "Erreur lors de la suppression: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header("Location: feedback_management.php");
    exit();
}

// Fetch all feedbacks with user information
try {
    $stmt = $conn->query("
        SELECT t.*, u.nom_utilisateur, u.email 
        FROM temoignages t 
        JOIN utilisateurs u ON t.id_utilisateur = u.id_utilisateur 
        ORDER BY t.id_temoignage DESC
    ");
    $feedbacks = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Témoignages - ITThink Admin</title>
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
        .feedback-text {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
            <a href="feedback_management.php" class="sidebar-link active">
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
                <h1>Gestion des Témoignages</h1>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Commentaire</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($feedbacks as $feedback): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($feedback['id_temoignage']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['nom_utilisateur']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['email']); ?></td>
                                    <td class="feedback-text" title="<?php echo htmlspecialchars($feedback['commentaire']); ?>">
                                        <?php echo htmlspecialchars($feedback['commentaire']); ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewModal<?php echo $feedback['id_temoignage']; ?>">
                                            <i class='bx bx-show'></i>
                                        </button>
                                        <form action="" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce témoignage ?');">
                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['id_temoignage']; ?>">
                                            <button type="submit" name="delete_feedback" class="btn btn-sm btn-outline-danger">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </form>

                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal<?php echo $feedback['id_temoignage']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Détails du témoignage</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <h6>Utilisateur</h6>
                                                            <p><?php echo htmlspecialchars($feedback['nom_utilisateur']); ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <h6>Email</h6>
                                                            <p><?php echo htmlspecialchars($feedback['email']); ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <h6>Commentaire</h6>
                                                            <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($feedback['commentaire']); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                        <form action="" method="POST" class="d-inline">
                                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['id_temoignage']; ?>">
                                                            <button type="submit" name="delete_feedback" class="btn btn-danger">Supprimer</button>
                                                        </form>
                                                    </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
