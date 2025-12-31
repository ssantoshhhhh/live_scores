<?php
include 'db_connect.php';
$match_id = $_GET['id'] ?? null;

if(!$match_id) {
    die("Match ID not specified.");
}

$stmt = $conn->prepare("SELECT * FROM matches WHERE id = :id");
$stmt->execute(['id' => $match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$match) {
    die("Match not found.");
}

$scores = json_decode($match['final_score_json'] ?? '{}', true);
$history = $scores['history'] ?? [];
$game = ucfirst($match['game_type']);
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <a href="index.php" class="btn btn-outline-secondary mb-4"><i class="fas fa-arrow-left"></i> Back to Sports</a>

    <div class="glass-card p-5 mb-5 text-center">
        <span class="badge bg-secondary mb-3"><?php echo $game; ?> - <?php echo date('M d, Y', strtotime($match['start_time'])); ?></span>
        
        <?php if($match['status'] == 'completed'): ?>
            <h2 class="text-success mb-2">WINNER: <?php echo htmlspecialchars($match['winner_team']); ?></h2>
            <p class="text-secondary mb-4"><?php echo htmlspecialchars($match['win_description']); ?></p>
        <?php else: ?>
            <h2 class="text-warning mb-4">MATCH IN PROGRESS</h2>
        <?php endif; ?>

        <div class="row align-items-center justify-content-center">
            <div class="col-md-4">
                <h3 class="team-name text-primary"><?php echo htmlspecialchars($match['team1_name']); ?></h3>
                <div class="display-1 fw-bold"><?php echo $scores['t1_points'] ?? 0; ?></div>
            </div>
            <div class="col-md-1">
                <div class="h2 text-secondary">VS</div>
            </div>
            <div class="col-md-4">
                <h3 class="team-name text-danger"><?php echo htmlspecialchars($match['team2_name']); ?></h3>
                <div class="display-1 fw-bold"><?php echo $scores['t2_points'] ?? 0; ?></div>
            </div>
        </div>
    </div>

    <!-- Stats Calculation -->
    <?php
    $stats = [
        't1' => ['raid'=>0, 'tackle'=>0, 'allout'=>0, 'extra'=>0],
        't2' => ['raid'=>0, 'tackle'=>0, 'allout'=>0, 'extra'=>0]
    ];
    
    // Helper to identify team key based on name
    $teamStats = [];
    $teamStats[$match['team1_name']] = ['raid'=>0, 'tackle'=>0, 'allout'=>0, 'ace'=>0, 'winner'=>0, 'error'=>0, 'total'=>0, 'sets'=>0];
    $teamStats[$match['team2_name']] = ['raid'=>0, 'tackle'=>0, 'allout'=>0, 'ace'=>0, 'winner'=>0, 'error'=>0, 'total'=>0, 'sets'=>0];

    foreach($history as $event) {
        $t = $event['team'];
        $p = (int)$event['points'];
        $a = strtolower($event['action']);
        
        if(!isset($teamStats[$t])) $teamStats[$t] = ['raid'=>0, 'tackle'=>0, 'allout'=>0, 'ace'=>0, 'winner'=>0, 'error'=>0, 'total'=>0, 'sets'=>0];
        
        $teamStats[$t]['total'] += $p;

        // Kabaddi Limits
        if(strpos($a, 'raid') !== false) $teamStats[$t]['raid'] += $p;
        elseif(strpos($a, 'tackle') !== false) $teamStats[$t]['tackle'] += $p;
        elseif(strpos($a, 'all out') !== false) $teamStats[$t]['allout'] += 1;
        
        // Tennis/Volley/Pickle Limits
        elseif(strpos($a, 'ace') !== false) $teamStats[$t]['ace'] += 1;
        elseif(strpos($a, 'winner') !== false) $teamStats[$t]['winner'] += 1;
        elseif(strpos($a, 'error') !== false || strpos($a, 'fault') !== false) $teamStats[$t]['error'] += 1;
        elseif(strpos($a, 'set won') !== false) $teamStats[$t]['sets'] += 1;
    }
    
    // Determine rows to show based on Game Type
    $gameKey = strtolower($match['game_type']);
    $rows = [];
    
    if($gameKey === 'kabaddi') {
        $rows = [
            'Raid Points' => 'raid',
            'Tackle Points' => 'tackle',
            'All Outs' => 'allout'
        ];
    } elseif($gameKey === 'tennis') {
        $rows = [
            'Aces' => 'ace',
            'Winners' => 'winner',
            'Opponent Errors' => 'error',
            'Sets Won' => 'sets'
        ];
    } else { // Volley/Pickle
        $rows = [
            'Aces' => 'ace',
            'Winners/Kills' => 'winner',
            'Opponent Errors' => 'error',
            'Sets Won' => 'sets'
        ];
    }
    ?>

    <!-- Stats Table -->
    <div class="row mb-5">
        <div class="col-12">
            <h4 class="text-primary mb-3">Match Stats</h4>
            <div class="glass-card p-0 overflow-hidden">
                <table class="table table-borderless mb-0 text-center">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="w-25 text-primary"><?php echo htmlspecialchars($match['team1_name']); ?></th>
                            <th class="w-50 text-secondary text-uppercase small ls-1">Statistic</th>
                            <th class="w-25 text-danger"><?php echo htmlspecialchars($match['team2_name']); ?></th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                        <?php foreach($rows as $label => $key): ?>
                        <tr>
                            <td class="fw-bold fs-4"><?php echo $teamStats[$match['team1_name']][$key]; ?></td>
                            <td class="text-secondary"><?php echo $label; ?></td>
                            <td class="fw-bold fs-4"><?php echo $teamStats[$match['team2_name']][$key]; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-light border-top">
                            <td class="fw-bolder fs-3 text-primary"><?php echo $gameKey==='tennis' ? $scores['sets_won_t1'] ?? $teamStats[$match['team1_name']]['sets'] : $teamStats[$match['team1_name']]['total']; ?></td>
                            <td class="fw-bold text-dark"><?php echo $gameKey==='tennis' ? 'SETS WON' : 'TOTAL POINTS'; ?></td>
                            <td class="fw-bolder fs-3 text-danger"><?php echo $gameKey==='tennis' ? $scores['sets_won_t2'] ?? $teamStats[$match['team2_name']]['sets'] : $teamStats[$match['team2_name']]['total']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Timeline / History -->
    <?php if(!empty($history) && false): // Timeline hidden by request ?>
    <!-- Timeline Removed -->
    <?php endif; ?>

</div>

<!-- Dark Mode JS Init (Copied from header to ensure it applies inside body content if needed) -->
<script>
    if(localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }
</script>
</body>
</html>
