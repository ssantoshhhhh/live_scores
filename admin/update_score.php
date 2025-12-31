<?php
session_start();
require '../db_connect.php';

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$action = $data['action'] ?? '';
$game_type = $data['game'] ?? '';

try {
    if ($action === 'start_match') {
        $existing_id = $data['id'] ?? null;
        $team1 = $data['team1'];
        $team2 = $data['team2'];

        // Disable other live matches of this game type
        $stmt = $conn->prepare("UPDATE matches SET status = 'completed' WHERE game_type = :g AND status = 'live'");
        $stmt->execute(['g' => $game_type]);

        if($existing_id) {
            // Start Existing Scheduled Match
            $stmt = $conn->prepare("UPDATE matches SET status = 'live' WHERE id = :id");
            $stmt->execute(['id' => $existing_id]);
            $match_id = $existing_id;
        } else {
            // Create New Match
            $stmt = $conn->prepare("INSERT INTO matches (game_type, team1_name, team2_name, status) VALUES (:g, :t1, :t2, 'live')");
            $stmt->execute(['g' => $game_type, 't1' => $team1, 't2' => $team2]);
            $match_id = $conn->lastInsertId();
        }

        // Init scores (delete old score if exists for safety, though unlikely for new match)
        $conn->prepare("DELETE FROM live_scores WHERE match_id = :id")->execute(['id' => $match_id]);
        
        $initial_score = json_encode($data['initial_score']);
        $stmt = $conn->prepare("INSERT INTO live_scores (match_id, score_json) VALUES (:id, :s)");
        $stmt->execute(['id' => $match_id, 's' => $initial_score]);

        echo json_encode(['success' => true, 'match_id' => $match_id]);

    } elseif ($action === 'update_score') {
        // Update existing match
        $match_id = $data['match_id'];
        $score_json = json_encode($data['scores']);
        
        $stmt = $conn->prepare("UPDATE live_scores SET score_json = :s WHERE match_id = :id");
        $stmt->execute(['s' => $score_json, 'id' => $match_id]);
        
        echo json_encode(['success' => true]);
        
    } elseif ($action === 'end_match') {
        $match_id = $data['match_id'];
        $winner = $data['winner'] ?? null;
        $desc = $data['description'] ?? null;
        
        // 1. Fetch the final live score data to archive it
        $stmtScore = $conn->prepare("SELECT score_json FROM live_scores WHERE match_id = :mid");
        $stmtScore->execute(['mid' => $match_id]);
        $liveData = $stmtScore->fetch(PDO::FETCH_ASSOC);
        $finalJson = $liveData ? $liveData['score_json'] : null;

        // 2. Update matches table with winner, description AND final JSON history
        $stmt = $conn->prepare("UPDATE matches SET status = 'completed', winner_team = :w, win_description = :d, final_score_json = :json WHERE id = :id");
        $stmt->execute(['id' => $match_id, 'w' => $winner, 'd' => $desc, 'json' => $finalJson, 'w' => $winner]);
        
        echo json_encode(['success' => true]);

    } elseif ($action === 'update_names') {
        $match_id = $data['match_id'];
        $t1 = $data['team1'];
        $t2 = $data['team2'];
        
        $stmt = $conn->prepare("UPDATE matches SET team1_name = :t1, team2_name = :t2 WHERE id = :id");
        $stmt->execute(['t1' => $t1, 't2' => $t2, 'id' => $match_id]);
        
        echo json_encode(['success' => true]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
