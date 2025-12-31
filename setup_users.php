<?php
include 'db_connect.php';

try {
    // 1. Add allowed_game column if it doesn't exist
    // We can't easily check "IF NOT EXISTS" for a column in a single query in all MySQL versions without a procedure, 
    // so we'll just try to add it and catch the error if it exists, or check information_schema.
    
    // Easier way: Check if column exists
    $colCheck = $conn->query("SHOW COLUMNS FROM admins LIKE 'allowed_game'");
    if($colCheck->rowCount() == 0) {
        $sql = "ALTER TABLE admins ADD COLUMN allowed_game VARCHAR(50) DEFAULT 'all'";
        $conn->exec($sql);
        echo "Column 'allowed_game' added successfully.<br>";
    } else {
        echo "Column 'allowed_game' already exists.<br>";
    }

    // 2. Clear existing admins to avoid duplicates during this setup
    $conn->exec("TRUNCATE TABLE admins");
    echo "Admins table truncated.<br>";

    // 3. Insert Users
    $users = [
        ['username' => 'admin', 'password' => 'admin123', 'game' => 'all'],
        ['username' => 'kabaddi', 'password' => 'kabaddi123', 'game' => 'kabaddi'],
        ['username' => 'pickleball', 'password' => 'pickleball123', 'game' => 'pickleball'],
        ['username' => 'tennis', 'password' => 'tennis123', 'game' => 'tennis'],
        ['username' => 'volleyball', 'password' => 'volleyball123', 'game' => 'volleyball']
    ];

    $stmt = $conn->prepare("INSERT INTO admins (username, password, allowed_game) VALUES (:u, :p, :g)");

    foreach ($users as $user) {
        // hashed password
        // For simplicity in this specific user request environment and previous code context,
        // we will use password_hash.
        $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
        
        $stmt->execute([
            ':u' => $user['username'],
            ':p' => $hashed_password,
            ':g' => $user['game']
        ]);
        echo "User '{$user['username']}' created with access to '{$user['game']}'.<br>";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
