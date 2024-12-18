<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    if (empty($email) || empty($mot_de_passe)) {
        $error = "Tous les champs sont requis";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id_utilisateur, mot_de_passe, autres_informations FROM Utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($user = $stmt->fetch()) {
                if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                    $_SESSION['user_id'] = $user['id_utilisateur'];
                    $autres_informations = json_decode($user['autres_informations'], true);
                    $role = isset($autres_informations['role']) ? $autres_informations['role'] : 'user';
                    $_SESSION['role'] = $role;

                    // Redirect based on role
                    if ($role === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: profile.php");
                    }
                    exit();
                } else {
                    $error = "Email ou mot de passe incorrect";
                }
            } else {
                $error = "Email ou mot de passe incorrect";
            }
        } catch(PDOException $e) {
            $error = "Erreur de connexion: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ITThink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Connexion</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Se connecter</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Pas encore de compte? <a href="register.php">Inscrivez-vous ici</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
