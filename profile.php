<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
try {
    $stmt = $conn->prepare("SELECT nom_utilisateur, email FROM Utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
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
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">ITThink</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php">Déconnexion</a>
                <button type="submit">Switch to Freelancer</button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Mon Profile</h1>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Informations personnelles</h5>
                <p class="card-text">
                    <strong>Nom d'utilisateur:</strong> <?php echo htmlspecialchars($user['nom_utilisateur']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
