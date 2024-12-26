<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Get user information
    $stmt = $conn->prepare("SELECT nom_utilisateur FROM Utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Get all projects with their offers
    $stmt_offers = $conn->prepare("
        SELECT 
            o.id_offre,
            o.montant,
            o.delai,
            p.titre_projet,
            p.description as projet_description,
            f.nom_freelance,
            c.nom_categorie,
            sc.nom_sous_categorie
        FROM offres o
        JOIN Projets p ON o.id_projet = p.id_projet
        JOIN freelances f ON o.id_freelance = f.id_freelance
        JOIN Categories c ON p.id_categorie = c.id_categorie
        JOIN souscategories sc ON p.id_sous_categorie = sc.id_sous_categorie
        WHERE p.id_utilisateur = ?
        ORDER BY o.id_offre DESC
    ");
    $stmt_offers->execute([$_SESSION['user_id']]);
    $offers = $stmt_offers->fetchAll();

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Offres - ITThink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar {
            background-color: #2c3e50;
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            color: #fff !important;
        }

        .nav-item {
            margin: 0 5px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 0.5rem 1rem !important;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            color: #fff !important;
            background-color: #3498db;
        }

        .nav-link i {
            margin-right: 5px;
        }

        .offer-card {
            transition: transform 0.2s;
        }

        .offer-card:hover {
            transform: translateY(-5px);
        }

        .status-pending {
            color: #f39c12;
        }

        .status-accepted {
            color: #27ae60;
        }

        .status-rejected {
            color: #e74c3c;
        }

        @media (max-width: 991px) {
            .nav-item {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="home.php">
                <strong>ITThink</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-plus-circle"></i> Add Project
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="mes_offres.php">
                            <i class="fas fa-list"></i> View Offers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="switch_freelancer.php">
                            <i class="fas fa-sync"></i> Switch to Freelancer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Mes Offres Reçues</h1>

        <?php if (empty($offers)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Vous n'avez pas encore reçu d'offres.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($offers as $offer): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card offer-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($offer['titre_projet']); ?></h5>
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($offer['nom_categorie']); ?> / 
                                    <?php echo htmlspecialchars($offer['nom_sous_categorie']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Freelancer:</strong> 
                                    <?php echo htmlspecialchars($offer['nom_freelance']); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Prix proposé:</strong> 
                                    <span class="text-success"><?php echo number_format($offer['montant'], 2); ?> €</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Délai proposé:</strong> 
                                    <?php echo $offer['delai']; ?> jours
                                </div>
                                <div class="mb-3">
                                    <strong>Offre #:</strong> 
                                    <?php echo $offer['id_offre']; ?>
                                </div>
                            </div>
                            <?php if ($offer['statut'] === 'pending'): ?>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="accept_offer.php?id=<?php echo $offer['id_offre']; ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Accepter
                                        </a>
                                        <a href="reject_offer.php?id=<?php echo $offer['id_offre']; ?>" 
                                           class="btn btn-danger btn-sm">
                                            <i class="fas fa-times"></i> Refuser
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 