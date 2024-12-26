<?php
require_once '../config/database.php';

try {
    // Update admin role to 1
    $stmt = $conn->prepare("UPDATE Utilisateurs SET role = 1 WHERE email = 'admin@itthink.com'");
    $stmt->execute();
    echo "Admin role updated successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
