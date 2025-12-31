<?php
include 'db_connect.php';

try {
    $conn->exec("ALTER TABLE matches ADD COLUMN final_score_json TEXT DEFAULT NULL");
    echo "Column final_score_json added successfully.";
} catch (PDOException $e) {
    echo "Error (might already exist): " . $e->getMessage();
}
?>
