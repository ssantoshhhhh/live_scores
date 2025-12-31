<?php
header('Content-Type: application/json');
require 'db_connect.php';

$game_type = $_GET['game'] ?? '';

if (!$game_type) {
    echo json_encode(['error' => 'No game specified']);
    exit;
}

try {
    // Fetch the active match for this game type
    $stmt = $conn->prepare("
        SELECT m.id, m.team1_name, m.team2_name, m.team1_color, m.team2_color, m.status, ls.score_json 
        FROM matches m 
        LEFT JOIN live_scores ls ON m.id = ls.match_id 
        WHERE m.game_type = :game AND m.status != 'completed' 
        ORDER BY CASE WHEN m.status = 'live' THEN 1 ELSE 2 END ASC, m.id DESC LIMIT 1
    ");
    
    $stmt->execute(['game' => $game_type]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($match) {
        // If score_json is null (new match), return default structure
        $scores = $match['score_json'] ? json_decode($match['score_json'], true) : new stdClass();
        
        echo json_encode([
            'success' => true,
            'match_info' => [
                'id' => $match['id'],
                'team1' => $match['team1_name'],
                'team2' => $match['team2_name'],
                'status' => $match['status']
            ],
            'scores' => $scores
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No live match found']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
