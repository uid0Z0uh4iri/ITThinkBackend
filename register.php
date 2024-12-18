<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_utilisateur = trim($_POST['nom_utilisateur']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    // Validate input
    if (empty($nom_utilisateur) || empty($email) || empty($mot_de_passe)) {
        $error = "Tous les champs sont requis";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id_utilisateur FROM Utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Cet email est déjà utilisé";
        } else {
            // Hash password
            $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            
            try {
                // Store role in both columns for compatibility
                $autres_informations = json_encode(['role' => 'user']);
                $stmt = $conn->prepare("INSERT INTO Utilisateurs (nom_utilisateur, email, mot_de_passe, role, autres_informations) VALUES (?, ?, ?, 0, ?)");
                $stmt->execute([$nom_utilisateur, $email, $hashed_password, $autres_informations]);
                
                $_SESSION['success'] = "Compte créé avec succès! Vous pouvez maintenant vous connecter.";
                header("Location: login.php");
                exit();
            } catch(PDOException $e) {
                $error = "Erreur lors de l'inscription: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - ITThink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Inscription</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="nom_utilisateur" class="form-label">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="nom_utilisateur" name="nom_utilisateur" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">S'inscrire</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Déjà inscrit? <a href="login.php">Connectez-vous ici</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
