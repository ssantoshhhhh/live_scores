<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
$game = $_GET['game'] ?? 'kabaddi';

// Access Control
$allowed = $_SESSION['allowed_game'] ?? 'all';
if($allowed !== 'all' && $allowed !== $game) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Control Room - <?php echo ucfirst($game); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .control-btn { height: 80px; font-size: 1.2rem; font-weight: bold; }
        .score-input { font-size: 2rem; text-align: center; background: #0f172a; color: white; border: 1px solid #334155; }
        .active-raider-btn { box-shadow: 0 0 15px #facc15; border-color: #facc15 !important; color: #facc15 !important; }
        
        /* Force Admin Navbar to Official Blue (overriding global .bg-dark -> white change) */
        .navbar-custom-admin { background-color: #002147 !important; color: white !important; }
        
        /* Admin Dark Mode Overrides */
        body.dark-mode { background-color: #0f172a; color: #f8fafc; }
        body.dark-mode .glass-card { background: #1e293b; border-color: #334155; }
        body.dark-mode .form-control, body.dark-mode .form-select { background-color: #334155 !important; border-color: #475569 !important; color: #f8fafc !important; }
        body.dark-mode .text-secondary { color: #94a3b8 !important; }
        body.dark-mode .bg-dark { background-color: #1e293b !important; }
        
        /* Admin Mobile Responsive */
        @media (max-width: 768px) {
            .control-btn { height: 60px; font-size: 1rem; }
            .display-3 { font-size: 2.5rem; }
            .h4 { font-size: 1.2rem; }
            .container { padding-left: 10px; padding-right: 10px; }
            .glass-card { padding: 1rem !important; }
            /* Stack buttons */
            .d-flex.gap-2 { flex-wrap: wrap; }
            .d-flex.gap-2 > button { width: 100%; margin-bottom: 0.5rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom-admin">
        <div class="container-fluid">
            <span class="navbar-brand">Admin: <?php echo ucfirst($game); ?></span>
            <div class="d-flex align-items-center gap-3">
                <button onclick="toggleDarkMode()" class="btn btn-outline-light btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                <a href="dashboard.php" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- SETUP SECTION -->
        <div id="setup-panel" class="glass-card p-4 mx-auto" style="max-width: 600px;">
            <h3 class="text-white mb-3">Start New Match</h3>
            <div class="mb-3">
                <label class="text-secondary">Team 1 Name</label>
                <input type="text" id="setup-t1" class="form-control" placeholder="e.g. Titans" value="Team A">
            </div>
            <div class="mb-3">
                <label class="text-secondary">Team 2 Name</label>
                <input type="text" id="setup-t2" class="form-control" placeholder="e.g. Warriors" value="Team B">
            </div>
            <button onclick="startMatch()" class="btn btn-primary w-100 py-3 fw-bold">START MATCH</button>
        </div>

        <!-- LIVE CONTROL SECTION -->
        <div id="live-panel" class="d-none">
            
            <!-- Quick Edit Names -->
            <div class="d-flex justify-content-center mb-3">
                 <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#editNamesPanel">
                    <i class="fas fa-edit me-1"></i> Edit Team Names
                 </button>
            </div>
            <div class="collapse mb-3" id="editNamesPanel">
                <div class="glass-card p-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="text-secondary small">Team 1 Name</label>
                            <input type="text" id="edit-t1" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <label class="text-secondary small">Team 2 Name</label>
                            <input type="text" id="edit-t2" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <button onclick="updateNames()" class="btn btn-primary btn-sm w-100">Update</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <div class="glass-card p-3 text-center border-primary">
                        <h4 id="lbl-t1" class="text-primary">Team 1</h4>
                        <div class="display-3 fw-bold text-white" id="val-t1">0</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="glass-card p-3 text-center border-danger">
                        <h4 id="lbl-t2" class="text-danger">Team 2</h4>
                        <div class="display-3 fw-bold text-white" id="val-t2">0</div>
                    </div>
                </div>
            </div>

            <!-- Game Specific Controls -->
            <div class="glass-card p-4 mb-4">
                <?php if($game == 'kabaddi'): ?>
                    <!-- KABADDI DETAILED CONTROLS -->
                    <div class="row g-3">
                        <!-- TIMEOUT -->
                        <div class="col-12 text-center mb-2">
                             <button onclick="toggleTimeout()" id="btn-timeout" class="btn btn-warning fw-bold px-5">START TIMEOUT</button>
                        </div>

                        <!-- TEAM 1 CONTROLS -->
                        <div class="col-md-6 border-end border-secondary">
                            <h5 class="text-center text-primary mb-3 text-uppercase fw-bold">Team 1 Scoring</h5>
                            
                            <div class="mb-4">
                                <label class="text-secondary small fw-bold text-uppercase mb-2">Raid Points (Add Score)</label>
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <button onclick="addEvent('t1', 'Raid Point', 1)" class="btn btn-outline-primary w-100 py-3 fw-bold fs-5">+1</button>
                                    </div>
                                    <div class="col-4">
                                        <button onclick="addEvent('t1', 'Raid Points', 2)" class="btn btn-outline-primary w-100 py-3 fw-bold fs-5">+2</button>
                                    </div>
                                    <div class="col-4">
                                        <button onclick="addEvent('t1', 'Super Raid', 3)" class="btn btn-primary w-100 py-3 fw-bold lh-1" style="font-size:0.9rem;">SUPER<br>RAID +3</button>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-dark text-secondary border-secondary">Manual Add</span>
                                    <input type="number" id="manual-pts-t1" class="form-control bg-dark text-white border-secondary" placeholder="Pts">
                                    <button onclick="addManualPoints('t1')" class="btn btn-outline-secondary">Add</button>
                                </div>
                            </div>

                            <div class="mb-4 border-top border-secondary pt-3">
                                <label class="text-secondary small fw-bold text-uppercase mb-2">Scoreboard Animations (No Points)</label>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <button onclick="triggerAnimation('t1', 'SUPER RAID')" class="btn btn-outline-warning w-100 py-2 fw-bold small text-uppercase" style="height: 100%;">Anim:<br>Super Raid</button>
                                    </div>
                                    <div class="col-4">
                                        <button onclick="triggerAnimation('t1', 'SUPER TACKLE')" class="btn btn-outline-info w-100 py-2 fw-bold small text-uppercase" style="height: 100%;">Anim:<br>Super Tkl</button>
                                    </div>
                                    <div class="col-4">
                                        <button onclick="triggerAnimation('t1', 'ALL OUT')" class="btn btn-outline-danger w-100 py-2 fw-bold small text-uppercase" style="height: 100%;">Anim:<br>All Out</button>
                                    </div>
                                </div>
                            </div>

                            <button onclick="setRaider('team1')" id="btn-raid-t1" class="btn btn-warning btn-raider w-100 py-3 mb-3 text-uppercase fw-bold fs-5 shadow-sm">
                                <i class="fas fa-running me-2"></i> Set Raiding
                            </button>

                            <div class="p-3 border border-secondary rounded bg-dark">
                                <label class="text-white small d-block mb-2 text-uppercase fw-bold text-center">Players on Court</label>
                                <div class="d-flex justify-content-between align-items-center px-4">
                                    <button onclick="updatePlayers('t1', -1)" class="btn btn-secondary btn-lg fw-bold" style="width: 50px;">-</button>
                                    <span id="pcount-t1" class="display-6 fw-bold text-white mb-0">7</span>
                                    <button onclick="updatePlayers('t1', 1)" class="btn btn-secondary btn-lg fw-bold" style="width: 50px;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- TEAM 2 CONTROLS -->
                        <div class="col-md-6">
                            <h5 class="text-center text-danger mb-3 text-uppercase fw-bold">Team 2 Scoring</h5>
                            
                            <div class="mb-4">
                                <label class="text-secondary small fw-bold text-uppercase mb-2">Raid Points (Add Score)</label>
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <button onclick="addEvent('t2', 'Raid Point', 1)" class="btn btn-outline-danger w-100 py-3 fw-bold fs-5">+1</button>
                                    </div>
                                    <div class="col-4">
                                        <button onclick="addEvent('t2', 'Raid Points', 2)" class="btn btn-outline-danger w-100 py-3 fw-bold fs-5">+2</button>
                                    </div>
                                    <div class="col-4">
                                        <button onclick="addEvent('t2', 'Super Raid', 3)" class="btn btn-danger w-100 py-3 fw-bold lh-1" style="font-size:0.9rem;">SUPER<br>RAID +3</button>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-dark text-secondary border-secondary">Manual Add</span>
                                    <input type="number" id="manual-pts-t2" class="form-control bg-dark text-white border-secondary" placeholder="Pts">
                                    <button onclick="addManualPoints('t2')" class="btn btn-outline-secondary">Add</button>
                                </div>
                            </div>

                            <div class="mb-4 border-top border-secondary pt-3">
                                <label class="text-secondary small fw-bold text-uppercase mb-2">Scoreboard Animations (No Points)</label>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <button onclick="triggerAnimation('t2', 'SUPER RAID')" class="btn btn-outline-warning w-100 py-2 fw-bold small text-uppercase" style="height: 100%;">Anim:<br>Super Raid</button>
                                    </div>
                                    <div class="col-4">
                                        <button onclick="triggerAnimation('t2', 'SUPER TACKLE')" class="btn btn-outline-info w-100 py-2 fw-bold small text-uppercase" style="height: 100%;">Anim:<br>Super Tkl</button>
                                    </div>
                                    <div class="col-4">
                                        <button onclick="triggerAnimation('t2', 'ALL OUT')" class="btn btn-outline-danger w-100 py-2 fw-bold small text-uppercase" style="height: 100%;">Anim:<br>All Out</button>
                                    </div>
                                </div>
                            </div>

                            <button onclick="setRaider('team2')" id="btn-raid-t2" class="btn btn-warning btn-raider w-100 py-3 mb-3 text-uppercase fw-bold fs-5 shadow-sm">
                                <i class="fas fa-running me-2"></i> Set Raiding
                            </button>

                            <div class="p-3 border border-secondary rounded bg-dark">
                                <label class="text-white small d-block mb-2 text-uppercase fw-bold text-center">Players on Court</label>
                                <div class="d-flex justify-content-between align-items-center px-4">
                                    <button onclick="updatePlayers('t2', -1)" class="btn btn-secondary btn-lg fw-bold" style="width: 50px;">-</button>
                                    <span id="pcount-t2" class="display-6 fw-bold text-white mb-0">7</span>
                                    <button onclick="updatePlayers('t2', 1)" class="btn btn-secondary btn-lg fw-bold" style="width: 50px;">+</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif($game == 'tennis'): ?>
                    <!-- TENNIS CONTROLS -->
                    <div class="text-center text-secondary mb-3">Manual Score Edit: 
                         <button onclick="tennisPoint('t1')" class="btn btn-success me-2">P1 Point</button>
                         <button onclick="tennisPoint('t2')" class="btn btn-danger">P2 Point</button>
                    </div>
                    <div class="row">
                         <div class="col-12"><hr class="bg-secondary"></div>
                         <div class="col-6 text-center">
                             <div class="h4">Games: <span id="games-t1">0</span></div>
                             <button onclick="addGame('t1')" class="btn btn-sm btn-light">+ Game</button>
                             <div class="mt-2 text-secondary">Sets: <span id="sets-t1">0</span></div>
                             <button onclick="addSet('t1')" class="btn btn-sm btn-outline-light">+ Set</button>
                         </div>
                         <div class="col-6 text-center">
                             <div class="h4">Games: <span id="games-t2">0</span></div>
                             <button onclick="addGame('t2')" class="btn btn-sm btn-light">+ Game</button>
                             <div class="mt-2 text-secondary">Sets: <span id="sets-t2">0</span></div>
                             <button onclick="addSet('t2')" class="btn btn-sm btn-outline-light">+ Set</button>
                         </div>
                    </div>

                <?php elseif($game == 'tennis'): ?>
                    <!-- TENNIS DETAILED CONTROLS -->
                    <div class="row g-3">
                        <div class="col-6 border-end border-secondary">
                             <h5 class="text-primary text-center">P1 Scoring</h5>
                             <div class="d-grid gap-2">
                                 <button onclick="tennisScore('t1', 'Ace')" class="btn btn-primary">ACE (+1)</button>
                                 <button onclick="tennisScore('t1', 'Winner')" class="btn btn-outline-primary">WINNER</button>
                                 <button onclick="tennisScore('t1', 'Opponent Error')" class="btn btn-outline-secondary">OPP ERROR</button>
                             </div>
                             <hr>
                             <div class="d-flex justify-content-between align-items-center bg-dark p-2 rounded">
                                 <div class="text-secondary small">SETS</div>
                                 <div class="h4 text-white mb-0" id="sets-t1">0</div>
                                 <button onclick="manualSet('t1')" class="btn btn-sm btn-outline-light">+ Set</button>
                             </div>
                        </div>
                        <div class="col-6">
                             <h5 class="text-danger text-center">P2 Scoring</h5>
                             <div class="d-grid gap-2">
                                 <button onclick="tennisScore('t2', 'Ace')" class="btn btn-danger">ACE (+1)</button>
                                 <button onclick="tennisScore('t2', 'Winner')" class="btn btn-outline-danger">WINNER</button>
                                 <button onclick="tennisScore('t2', 'Opponent Error')" class="btn btn-outline-secondary">OPP ERROR</button>
                             </div>
                             <hr>
                             <div class="d-flex justify-content-between align-items-center bg-dark p-2 rounded">
                                 <div class="text-secondary small">SETS</div>
                                 <div class="h4 text-white mb-0" id="sets-t2">0</div>
                                 <button onclick="manualSet('t2')" class="btn btn-sm btn-outline-light">+ Set</button>
                             </div>
                        </div>
                        <div class="col-12 mt-3 text-center">
                            <button onclick="resetGameScore()" class="btn btn-sm btn-outline-warning">Reset Current Game Points</button>
                        </div>
                    </div>

                <?php elseif($game == 'pickleball' || $game == 'volleyball'): ?>
                    <!-- VOLLEYBALL / PICKLEBALL DETAILED -->
                    <div class="row g-3">
                        <div class="col-6 border-end border-secondary">
                            <h5 class="text-primary text-center">Team 1</h5>
                            <button onclick="addEvent('t1', 'Service Ace', 1)" class="btn btn-primary w-100 mb-2">SERVICE ACE</button>
                            <button onclick="addEvent('t1', 'Winner (Smash/Drop)', 1)" class="btn btn-outline-primary w-100 mb-2">WINNER / KILL</button>
                            <button onclick="addEvent('t1', 'Opponent Fault', 1)" class="btn btn-outline-secondary w-100 mb-2">OPP ERROR</button>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Sets Won: <strong id="sets-t1" class="text-white">0</strong></span>
                                <button onclick="manualSet('t1')" class="btn btn-sm btn-success">+ Set</button>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-danger text-center">Team 2</h5>
                            <button onclick="addEvent('t2', 'Service Ace', 1)" class="btn btn-danger w-100 mb-2">SERVICE ACE</button>
                            <button onclick="addEvent('t2', 'Winner (Smash/Drop)', 1)" class="btn btn-outline-danger w-100 mb-2">WINNER / KILL</button>
                            <button onclick="addEvent('t2', 'Opponent Fault', 1)" class="btn btn-outline-secondary w-100 mb-2">OPP ERROR</button>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Sets Won: <strong id="sets-t2" class="text-white">0</strong></span>
                                <button onclick="manualSet('t2')" class="btn btn-sm btn-success">+ Set</button>
                            </div>
                        </div>
                        
                        <!-- Volleyball Rotation / Server -->
                        <div class="col-12 mt-3 p-3 bg-dark rounded border border-secondary text-center">
                            <span class="text-secondary text-uppercase ls-1 small d-block mb-2">Current Serve</span>
                            <div class="btn-group w-50" role="group">
                                <input type="radio" class="btn-check" name="server" id="srv-t1" autocomplete="off" onchange="toggleServer('team1')" checked>
                                <label class="btn btn-outline-primary" for="srv-t1">Team 1</label>

                                <input type="radio" class="btn-check" name="server" id="srv-t2" autocomplete="off" onchange="toggleServer('team2')">
                                <label class="btn btn-outline-danger" for="srv-t2">Team 2</label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <button onclick="endMatch()" class="btn btn-danger w-100">END MATCH</button>
        </div>
    </div>

<script>
    const GAME_TYPE = "<?php echo $game; ?>";
    let matchId = null;
    
    // State
    let state = {
        t1_points: 0, t2_points: 0,
        t1_sets: [], t2_sets: [], // For tennis/volley array of scores
        sets_won_t1: 0, sets_won_t2: 0,
        current_game_t1: 0, current_game_t2: 0, // Tennis points (0,15,30,40)
        current_raider: '',
        server: 'team1'
    };

    function startMatch(pT1=null, pT2=null, pId=null) {
        const t1 = pT1 || document.getElementById('setup-t1').value;
        const t2 = pT2 || document.getElementById('setup-t2').value;
        
        // Reset state
        state = {
            t1_points: 0, t2_points: 0,
            t1_players: 7, t2_players: 7,
            current_points_t1: 0, current_points_t2: 0,
            t1_sets: [0,0,0,0,0], t2_sets: [0,0,0,0,0],
            sets_won_t1: 0, sets_won_t2: 0,
            current_game_t1: '0', current_game_t2: '0',
            current_raider: 'team1',
            server: 'team1',
            history: [],
            is_timeout: false
        };

        fetch('update_score.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'start_match',
                game: GAME_TYPE,
                team1: t1,
                team2: t2,
                id: pId, // Pass specific ID if exists
                initial_score: state
            })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                matchId = data.match_id;
                document.getElementById('setup-panel').classList.add('d-none');
                document.getElementById('live-panel').classList.remove('d-none');
                document.getElementById('lbl-t1').innerText = t1;
                document.getElementById('lbl-t2').innerText = t2;
                render();
            } else {
                alert('Error starting match: ' + (data.message || 'Unknown'));
            }
        });
    }

    // Auto-Restore or Start
    window.addEventListener('load', () => {
        const urlParams = new URLSearchParams(window.location.search);
        
        // CASE 1: Explicit Start via URL (from Dashboard)
        if(urlParams.has('start') && urlParams.has('id')) {
            const t1 = urlParams.get('t1');
            const t2 = urlParams.get('t2');
            const id = urlParams.get('id');
            if(t1 && t2 && id) {
                document.getElementById('setup-t1').value = t1;
                document.getElementById('setup-t2').value = t2;
                startMatch(t1, t2, id);
            }
        } 
        // CASE 2: Check for existing live match (Persistence)
        else {
            restoreMatchState();
        }
    });

    function restoreMatchState() {
        // Fetch current public state to see if there's a live match
        fetch(`../get_score.php?game=${GAME_TYPE}`)
        .then(r => r.json())
        .then(data => {
            if(data.success && data.match_info) {
                // Found a live match!
                console.log("Restoring match:", data);
                
                matchId = data.match_info.id;
                state = data.scores;
                
                // Ensure default structure if empty (new match might have empty scores)
                if(!state.t1_points) state.t1_points = 0;
                
                // Update UI State
                document.getElementById('setup-panel').classList.add('d-none');
                document.getElementById('live-panel').classList.remove('d-none');
                
                // Update Names
                const t1Name = data.match_info.team1;
                const t2Name = data.match_info.team2;
                document.getElementById('lbl-t1').innerText = t1Name;
                document.getElementById('lbl-t2').innerText = t2Name;
                
                // Pre-fill edit inputs
                document.getElementById('edit-t1').value = t1Name;
                document.getElementById('edit-t2').value = t2Name;

                // Render Scoreboard
                render();
                
                // Highlight Raiders / Server if applies
                if(state.current_raider) setRaider(state.current_raider);
            }
        });
    }

    // Kabaddi Specific Functions
    function toggleTimeout() {
        state.is_timeout = !state.is_timeout;
        sync();
    }

    function triggerAllOut(team) {
        if(team === 't1') {
            state.t1_points += 2;
        } else {
            state.t2_points += 2;
        }
        addEvent(team, "ALL OUT INFLICTED", 2);
    }

    function setRaider(team) {
        state.current_raider = team;
        
        // UI Update
        const btnT1 = document.getElementById('btn-raid-t1');
        const btnT2 = document.getElementById('btn-raid-t2');
        
        if(btnT1) btnT1.classList.remove('active-raider-btn');
        if(btnT2) btnT2.classList.remove('active-raider-btn');
        
        if(team === 'team1' && btnT1) {
             btnT1.classList.add('active-raider-btn');
        } else if (team === 'team2' && btnT2) {
             btnT2.classList.add('active-raider-btn');
        }
        sync();
    }

    function updatePlayers(team, change) {
        if(team === 't1') {
            let current = state.t1_players || 7;
            let newVal = current + change;
            if(newVal < 0) newVal = 0;
            if(newVal > 7) newVal = 7;
            state.t1_players = newVal;
        } else {
            let current = state.t2_players || 7;
            let newVal = current + change;
            if(newVal < 0) newVal = 0;
            if(newVal > 7) newVal = 7;
            state.t2_players = newVal;
        }
        sync();
    }

    function toggleServer(team) {
        state.server = team;
        sync();
    }

    function triggerAnimation(team, type) {
        state.last_animation = {
            id: Date.now(),
            team: team, // 't1' or 't2'
            type: type  // 'Super Raid' or 'Super Tackle'
        };
        sync();
    }

    function addManualPoints(team) {
        const inputId = team === 't1' ? 'manual-pts-t1' : 'manual-pts-t2';
        const input = document.getElementById(inputId);
        const points = parseInt(input.value);
        
        if(!points || isNaN(points)) {
            alert('Please enter a valid number');
            return;
        }
        
        addEvent(team, 'Manual Adjustment', points);
        input.value = '';
    }

    // Tennis Logic
    const tennisPoints = ['0', '15', '30', '40', 'AD'];
    function tennisScore(team, type) {
        // 1. Advance Point
        let key = team === 't1' ? 'current_game_t1' : 'current_game_t2';
        let oppKey = team === 't1' ? 'current_game_t2' : 'current_game_t1';
        
        let current = state[key];
        let opp = state[oppKey];
        
        let idx = tennisPoints.indexOf(current);
        
        // Simple Game Logic (0->15->30->40)
        // Deuce logic can be complex, for now strictly manual cycling or simple win
        if(current === '40' && opp !== '40' && opp !== 'AD') {
             // Game Won usually
             state[key] = 'GAME';
             // Don't auto increment set, let user do it via "+ Set" or we can do a "Game" counter? 
             // This demo focuses on "Game Points". User can manually reset.
        } else if (idx < tennisPoints.length - 1) {
             state[key] = tennisPoints[idx+1];
        }
        
        // 2. Log History
        addEvent(team, type + " (" + state[key] + ")", 0); // 0 pts because tennis points aren't numeric sums usually
    }
    
    function resetGameScore() {
        state.current_game_t1 = '0';
        state.current_game_t2 = '0';
        sync();
    }
    
    function manualSet(team) {
        if(team === 't1') state.sets_won_t1++;
        else state.sets_won_t2++;
        addEvent(team, "Set Won", 0);
    }
    
    // Updated AddEvent to handle Volley/Pickleball Points
    function addEvent(team, type, points) {
        if(!state.history) state.history = [];
        
        // Update Score based on Game Type
        if(GAME_TYPE === 'kabaddi') {
            if(team === 't1') state.t1_points += points;
            else state.t2_points += points;
        } else if (GAME_TYPE === 'pickleball' || GAME_TYPE === 'volleyball') {
             if(team === 't1') state.current_points_t1 = (state.current_points_t1 || 0) + points;
             else state.current_points_t2 = (state.current_points_t2 || 0) + points;
        }

        // Add to History
        const now = new Date().toLocaleTimeString();
        const teamName = team === 't1' ? document.getElementById('lbl-t1').innerText : document.getElementById('lbl-t2').innerText;
        
        state.history.unshift({
            time: now,
            team: teamName,
            action: type,
            points: points
        });
        
        sync();
    }


    function sync() {
        render();
        fetch('update_score.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'update_score',
                match_id: matchId,
                scores: state
            })
        });
    }

    function render() {
        if(GAME_TYPE === 'kabaddi') {
             document.getElementById('val-t1').innerText = state.t1_points;
             document.getElementById('val-t2').innerText = state.t2_points;
             // Update player counts if elements exist (only for kabaddi)
             if(document.getElementById('pcount-t1')) {
                 document.getElementById('pcount-t1').innerText = state.t1_players || 7;
                 document.getElementById('pcount-t2').innerText = state.t2_players || 7;
             }
             
             // Update Timeout Button
             const btnTimeout = document.getElementById('btn-timeout');
             if(btnTimeout) {
                 if(state.is_timeout) {
                     btnTimeout.innerText = "END TIMEOUT";
                     btnTimeout.className = "btn btn-danger fw-bold px-5";
                 } else {
                     btnTimeout.innerText = "START TIMEOUT";
                     btnTimeout.className = "btn btn-warning fw-bold px-5";
                 }
             }
        } else if (GAME_TYPE === 'tennis') {
             document.getElementById('val-t1').innerText = state.current_game_t1;
             document.getElementById('val-t2').innerText = state.current_game_t2;
             document.getElementById('sets-t1').innerText = state.sets_won_t1;
             document.getElementById('sets-t2').innerText = state.sets_won_t2;
        } else {
             document.getElementById('val-t1').innerText = state.current_points_t1 || 0;
             document.getElementById('val-t2').innerText = state.current_points_t2 || 0;
        }
    }
    
    function endMatch() {
        // Calculate Winner
        let winner = null;
        let desc = "Match Drawn";
        
        let s1 = state.t1_points || 0;
        let s2 = state.t2_points || 0;
        
        // Handle tennis/sets
        if(GAME_TYPE === 'tennis' || GAME_TYPE === 'volleyball' || GAME_TYPE === 'pickleball') {
             s1 = state.sets_won_t1 || 0;
             s2 = state.sets_won_t2 || 0;
        }

        const t1 = document.getElementById('lbl-t1').innerText;
        const t2 = document.getElementById('lbl-t2').innerText;

        if (s1 > s2) {
            winner = t1;
            desc = `${t1} won by ${s1 - s2} point(s)/set(s)`;
        } else if (s2 > s1) {
            winner = t2;
            desc = `${t2} won by ${s2 - s1} point(s)/set(s)`;
        }

        if(confirm(`End Match?\n\nResult: ${desc}`)) {
            fetch('update_score.php', {
                method: 'POST',
                body: JSON.stringify({ 
                    action: 'end_match', 
                    match_id: matchId,
                    winner: winner,
                    description: desc
                })
            }).then(() => location.reload());
        }
    }
    // Name Updater
    function updateNames() {
        const t1 = document.getElementById('edit-t1').value;
        const t2 = document.getElementById('edit-t2').value;
        
        if(!t1 || !t2) {
            alert('Please enter both team names');
            return;
        }
        
        // Optimistic UI update
        document.getElementById('lbl-t1').innerText = t1;
        document.getElementById('lbl-t2').innerText = t2;
        
        // Close collapse
        const bsCollapse = new bootstrap.Collapse(document.getElementById('editNamesPanel'), { toggle: true });
        
        fetch('update_score.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'update_names',
                match_id: matchId,
                team1: t1,
                team2: t2
            })
        });
    }

    // Capture initial names on start
    const originalStart = startMatch;
    startMatch = function(pT1, pT2, pId) {
        // Pre-fill edit inputs
        const t1 = pT1 || document.getElementById('setup-t1').value;
        const t2 = pT2 || document.getElementById('setup-t2').value;
        document.getElementById('edit-t1').value = t1;
        document.getElementById('edit-t2').value = t2;
        
        originalStart(pT1, pT2, pId);
    }
    
    // Admin Dark Mode
    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        const icon = document.getElementById('theme-icon');
        if(document.body.classList.contains('dark-mode')) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            localStorage.setItem('admin_theme', 'dark');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
            localStorage.setItem('admin_theme', 'light');
        }
    }
    
    // Init Admin Theme
    if(localStorage.getItem('admin_theme') === 'dark') {
        document.body.classList.add('dark-mode');
        document.getElementById('theme-icon').classList.remove('fa-moon');
        document.getElementById('theme-icon').classList.add('fa-sun');
    }
</script>
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
