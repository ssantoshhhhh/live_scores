<?php
include 'db_connect.php';

try {
    // Check if column exists to avoid error
    $colCheck = $conn->query("SHOW COLUMNS FROM matches LIKE 'winner_team'");
    if($colCheck->rowCount() == 0) {
        $sql = "ALTER TABLE matches ADD COLUMN winner_team VARCHAR(100) DEFAULT NULL, ADD COLUMN win_description VARCHAR(255) DEFAULT NULL";
        $conn->exec($sql);
        echo "Columns added successfully.";
    } else {
        echo "Columns already exist.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
