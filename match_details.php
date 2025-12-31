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
        <? else: ?>
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

<!-- ANIMATION OVERLAY (Shared with Kabaddi.php) -->
<div id="animation-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-none d-flex align-items-center justify-content-center overflow-hidden" 
     style="z-index: 3000; background: rgba(0,0,0,0.8);">
    
    <div id="anim-bg-flash" class="position-absolute w-100 h-100 bg-white" style="opacity: 0; pointer-events: none;"></div>

    <button id="anim-close-btn" class="btn btn-outline-light position-absolute top-0 end-0 m-4 rounded-circle" style="z-index: 3600; width: 50px; height: 50px;" onclick="closeAnimation()">
        <i class="fas fa-times"></i>
    </button>

    <div id="anim-content" class="text-center position-relative p-5" style="transform: perspective(1000px) rotateX(10deg) scale(0.5); opacity: 0; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <div class="position-relative d-inline-block">
             <h1 id="anim-text" class="display-1 fw-bold text-uppercase fst-italic m-0" 
            style="font-size: 8rem; -webkit-text-stroke: 3px black; text-shadow: 10px 10px 0px rgba(0,0,0,0.5); filter: drop-shadow(0 0 30px rgba(255,255,255,0.7));">
            WINNER
            </h1>
            <div class="position-absolute top-0 start-0 w-100 h-100" 
                 style="background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.8) 50%, transparent 60%); background-size: 200% 100%; animation: shine 1s infinite linear; mix-blend-mode: overlay;"></div>
        </div>
        <div id="anim-bar" class="w-100 mt-2 mb-3 mx-auto" style="height: 8px; background: white; box-shadow: 0 0 20px currentColor; border-radius: 4px; width: 0 !important; transition: width 0.5s ease-out;"></div>
        <h2 id="anim-team" class="h1 fw-bold text-uppercase text-white" 
            style="letter-spacing: 5px; text-shadow: 0 2px 10px black; transform: translateY(20px); opacity: 0; transition: all 0.5s 0.2s;">
            Team Name
        </h2>
    </div>
</div>

<style>
    @keyframes shine { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    @keyframes impact { 0% { transform: scale(2); opacity: 0; } 50% { transform: scale(0.9); opacity: 1; } 70% { transform: scale(1.1); } 100% { transform: scale(1); opacity: 1; } }
    @keyframes slideOut { 0% { transform: translateX(0); opacity: 1; } 100% { transform: translateX(100vw) skewX(-20deg); opacity: 0; } }
    .anim-active { animation: impact 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    .anim-exit { animation: slideOut 0.4s ease-in forwards !important; }
    
    .type-winner { color: #ffd700; text-shadow: 0 0 50px #ffd700; }
    .type-winner #anim-bar { background-color: #ffd700; box-shadow: 0 0 30px #ffd700; }
    
    @media (max-width: 768px) {
        #anim-text { font-size: 4rem !important; -webkit-text-stroke: 1px black !important; }
        #anim-team { font-size: 2.5rem !important; transform: translateY(10px); }
        #anim-content { padding: 1rem !important; width: 95%; }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    function triggerVisualAnimation(text, teamName) {
        const overlay = document.getElementById('animation-overlay');
        const content = document.getElementById('anim-content');
        const bgFlash = document.getElementById('anim-bg-flash');
        const txt = document.getElementById('anim-text');
        const tm = document.getElementById('anim-team');
        const bar = document.getElementById('anim-bar');
        
        content.classList.remove('anim-active', 'anim-exit');
        content.className = "text-center position-relative p-5 type-winner";
        
        txt.innerText = teamName; // Name is Winner
        tm.innerText = text; // WINNER subtext
        
        overlay.classList.remove('d-none');
        
        // Flash
        bgFlash.style.transition = 'none';
        bgFlash.style.opacity = '0.8';
        setTimeout(() => { bgFlash.style.transition = 'opacity 0.5s ease-out'; bgFlash.style.opacity = '0'; }, 50);

        // Entrance
        setTimeout(() => {
             content.style.opacity = '1';
             content.classList.add('anim-active');
             setTimeout(() => {
                 bar.style.width = '120%';
                 tm.style.transform = 'translateY(0)';
                 tm.style.opacity = '1';
             }, 300);
        }, 100);

        // Confetti
        if(window.confetti) {
            const duration = 5000;
            const end = Date.now() + duration;
            (function frame() {
                confetti({ particleCount: 5, angle: 60, spread: 55, origin: { x: 0 }, colors: ['#ff0000', '#00ff00', '#0000ff', '#ffd700'], zIndex: 3500 });
                confetti({ particleCount: 5, angle: 120, spread: 55, origin: { x: 1 }, colors: ['#ff0000', '#00ff00', '#0000ff', '#ffd700'], zIndex: 3500 });
                if (Date.now() < end) requestAnimationFrame(frame);
            }());
        }
    }

    function closeAnimation() {
        const overlay = document.getElementById('animation-overlay');
        const content = document.getElementById('anim-content');
        content.classList.remove('anim-active');
        content.classList.add('anim-exit');
        setTimeout(() => {
            overlay.classList.add('d-none');
            // Reset confetti
            if(window.confetti && typeof window.confetti.reset === 'function') window.confetti.reset();
        }, 400);
    }

    // Auto Trigger if match is completed
    window.addEventListener('load', () => {
        <?php if($match['status'] == 'completed' && !empty($match['winner_team'])): ?>
            setTimeout(() => {
                triggerVisualAnimation('WINNER', "<?php echo addslashes($match['winner_team']); ?>");
            }, 500);
        <?php endif; ?>
    });
</script>
<!-- Dark Mode JS Init (Copied from header to ensure it applies inside body content if needed) -->
<script>
    if(localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }
</script>
</body>
</html>
