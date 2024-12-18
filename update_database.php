<?php
require_once 'config/database.php';

try {
    // Check if role column exists
    $columnExists = false;
    $columns = $conn->query("SHOW COLUMNS FROM Utilisateurs");
    while($column = $columns->fetch(PDO::FETCH_ASSOC)) {
        if($column['Field'] === 'role') {
            $columnExists = true;
            break;
        }
    }

    // Add role column if it doesn't exist
    if (!$columnExists) {
        $conn->exec("ALTER TABLE Utilisateurs ADD COLUMN role INT DEFAULT 0");
        echo "Role column added successfully!<br>";
    } else {
        echo "Role column already exists.<br>";
    }

    // Update existing users based on leurs autres_informations
    $stmt = $conn->query("SELECT id_utilisateur, autres_informations FROM Utilisateurs");
    $users = $stmt->fetchAll();

    foreach ($users as $user) {
        $autres_informations = json_decode($user['autres_informations'], true);
        $role = isset($autres_informations['role']) && $autres_informations['role'] === 'admin' ? 1 : 0;
        
        $update = $conn->prepare("UPDATE Utilisateurs SET role = ? WHERE id_utilisateur = ?");
        $update->execute([$role, $user['id_utilisateur']]);
    }
    echo "Existing users updated successfully!<br>";

    // Show current users and their roles
    echo "<br>Current Users:<br>";
    $stmt = $conn->query("SELECT id_utilisateur, nom_utilisateur, email, role, autres_informations FROM Utilisateurs");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id_utilisateur'] . 
             ", Name: " . $row['nom_utilisateur'] . 
             ", Email: " . $row['email'] . 
             ", Role: " . $row['role'] . 
             ", Autres: " . $row['autres_informations'] . "<br>";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
