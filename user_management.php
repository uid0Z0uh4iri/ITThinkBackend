<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 1) {
    header("Location: login.php");
    exit();
}

// Handle Delete User
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    if ($user_id != $_SESSION['user_id']) {
        try {
            $stmt = $conn->prepare("DELETE FROM Utilisateurs WHERE id_utilisateur = ?");
            $stmt->execute([$user_id]);
            $_SESSION['message'] = "Utilisateur supprimé avec succès.";
            $_SESSION['message_type'] = "success";
        } catch(PDOException $e) {
            $_SESSION['message'] = "Erreur lors de la suppression: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }
    header("Location: user_management.php");
    exit();
}

// Handle Edit User
if (isset($_POST['edit_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = (int)$_POST['role'];

    try {
        $stmt = $conn->prepare("UPDATE Utilisateurs SET nom_utilisateur = ?, email = ?, role = ? WHERE id_utilisateur = ?");
        $stmt->execute([$username, $email, $role, $user_id]);
        $_SESSION['message'] = "Utilisateur modifié avec succès.";
        $_SESSION['message_type'] = "success";
    } catch(PDOException $e) {
        $_SESSION['message'] = "Erreur lors de la modification: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header("Location: user_management.php");
    exit();
}

// Fetch all users
try {
    $stmt = $conn->query("SELECT id_utilisateur, nom_utilisateur, email, role FROM Utilisateurs ORDER BY id_utilisateur DESC");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - ITThink Admin</title>
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
            <a href="user_management.php" class="sidebar-link active">
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

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Gestion des Utilisateurs</h1>
                <a href="add_admin.php" class="btn btn-primary">
                    <i class='bx bx-plus'></i> Ajouter un Admin
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom d'utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id_utilisateur']); ?></td>
                                    <td><?php echo htmlspecialchars($user['nom_utilisateur']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 1 ? 'bg-danger' : 'bg-primary'; ?>">
                                            <?php echo $user['role'] === 1 ? 'Admin' : 'Utilisateur'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?php echo $user['id_utilisateur']; ?>">
                                            <i class='bx bx-edit'></i>
                                        </button>
                                        <?php if($user['id_utilisateur'] != $_SESSION['user_id']): ?>
                                            <form action="" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id_utilisateur']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $user['id_utilisateur']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier l'utilisateur</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="" method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id_utilisateur']; ?>">
                                                            <div class="mb-3">
                                                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                                                <input type="text" class="form-control" name="username" 
                                                                       value="<?php echo htmlspecialchars($user['nom_utilisateur']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="email" class="form-label">Email</label>
                                                                <input type="email" class="form-control" name="email" 
                                                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="role" class="form-label">Rôle</label>
                                                                <select class="form-select" name="role" required>
                                                                    <option value="0" <?php echo $user['role'] === 0 ? 'selected' : ''; ?>>Utilisateur</option>
                                                                    <option value="1" <?php echo $user['role'] === 1 ? 'selected' : ''; ?>>Admin</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" name="edit_user" class="btn btn-primary">Sauvegarder</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
