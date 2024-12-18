<?php
require_once 'config/database.php';

// Admin credentials
$admin_username = "admin";
$admin_email = "admin@itthink.com";
$admin_password = "admin123"; // You should change this password

try {
    // Check if admin already exists
    $stmt = $conn->prepare("SELECT id_utilisateur FROM Utilisateurs WHERE email = ?");
    $stmt->execute([$admin_email]);
    
    if ($stmt->rowCount() == 0) {
        // Create admin account
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO Utilisateurs (nom_utilisateur, email, mot_de_passe, autres_informations) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_username, $admin_email, $hashed_password, json_encode(['role' => 'admin'])]);
        
        echo "Admin account created successfully!<br>";
        echo "Email: " . $admin_email . "<br>";
        echo "Password: " . $admin_password . "<br>";
        echo "Please delete this file after using it for security reasons.";
    } else {
        echo "Admin account already exists!";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
