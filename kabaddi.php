<?php include 'includes/header.php'; ?>

<div class="container py-5 scoreboard-container theme-kabaddi" id="app">
    <div class="text-center mb-4">
        <span class="badge bg-secondary mb-2">LIVE KABADDI MATCH</span>
        <h2 id="match-title" class="fw-bold text-white">Loading Match...</h2>
    </div>

    <div class="row align-items-center justify-content-center">
        <!-- Team 1 -->
        <div class="col-md-5 mb-4">
            <div class="glass-card p-5 text-center position-relative">
                <div id="team1-indicator" class="position-absolute top-0 start-50 translate-middle badge bg-warning text-dark d-none">RAID</div>
                <h3 id="team1-name" class="team-name mb-3">Team A</h3>
                <div id="team1-score" class="score-big">0</div>
                <div class="mt-3 d-flex justify-content-center gap-1" id="t1-players-container">
                    <!-- Player Icons Rendered Here -->
                </div>
            </div>
        </div>

        <!-- VS / Timer -->
        <div class="col-md-2 text-center mb-4">
            <div class="vs-badge mb-3">VS</div>
        </div>

        <!-- Team 2 -->
        <div class="col-md-5 mb-4">
            <div class="glass-card p-5 text-center position-relative">
                <div id="team2-indicator" class="position-absolute top-0 start-50 translate-middle badge bg-warning text-dark d-none">RAID</div>
                <h3 id="team2-name" class="team-name mb-3">Team B</h3>
                <div id="team2-score" class="score-big">0</div>
                <div class="mt-3 d-flex justify-content-center gap-1" id="t2-players-container">
                    <!-- Player Icons Rendered Here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Match Stats / Footer -->
    <div class="row mt-4">
        <div class="col-12 text-center text-secondary">
            <button class="btn btn-outline-light mb-3" data-bs-toggle="modal" data-bs-target="#historyModal">
                <i class="fas fa-history me-2"></i> Show Match History
            </button>
            <p id="match-status-text">Waiting for updates...</p>
        </div>
    </div>
    
    <!-- Match Lists -->
    <?php
    include 'db_connect.php';
    // Fetch Upcoming
    $upcoming = $conn->query("SELECT * FROM matches WHERE game_type='kabaddi' AND status='scheduled' ORDER BY start_time ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    // Fetch Previous
    $previous = $conn->query("SELECT * FROM matches WHERE game_type='kabaddi' AND status='completed' ORDER BY start_time DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- UPCOMING -->
    <?php if(count($upcoming) > 0): ?>
    <div class="glass-card p-4 mt-5 mb-4">
        <h4 class="text-white mb-3 border-bottom border-secondary pb-2">Upcoming Matches</h4>
        <div class="row g-3">
            <?php foreach($upcoming as $match): ?>
            <div class="col-md-6">
                <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-info"><?php echo htmlspecialchars($match['team1_name']); ?></span>
                        <span class="text-secondary mx-2">vs</span>
                        <span class="fw-bold text-info"><?php echo htmlspecialchars($match['team2_name']); ?></span>
                    </div>
                    <span class="badge bg-secondary">Scheduled</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- PREVIOUS -->
    <?php
    // Pagination Logic
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;

    // Get Total Count
    $total_stmt = $conn->query("SELECT COUNT(*) FROM matches WHERE game_type='kabaddi' AND status='completed'");
    $total_matches = $total_stmt->fetchColumn();
    $total_pages = ceil($total_matches / $limit);

    // Fetch Paginated
    $prev_sql = "SELECT * FROM matches WHERE game_type='kabaddi' AND status='completed' ORDER BY start_time DESC LIMIT :limit OFFSET :offset";
    $prev_stmt = $conn->prepare($prev_sql);
    $prev_stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $prev_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $prev_stmt->execute();
    $previous = $prev_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if(count($previous) > 0): ?>
    <div class="glass-card p-4">
        <h4 class="text-white mb-3 border-bottom border-secondary pb-2">Previous Matches</h4>
        <div class="list-group list-group-flush mb-3">
            <?php foreach($previous as $match): ?>
            <a href="match_details.php?id=<?php echo $match['id']; ?>" class="list-group-item list-group-item-action bg-transparent text-white border-secondary">
                <div class="d-flex justify-content-between">
                    <h5 class="mb-1">
                        <?php echo htmlspecialchars($match['team1_name']); ?> vs <?php echo htmlspecialchars($match['team2_name']); ?>
                    </h5>
                    <small class="text-secondary"><?php echo date('M d, H:i', strtotime($match['start_time'])); ?></small>
                </div>
                <?php if($match['winner_team']): ?>
                    <p class="mb-1 text-success fw-bold">
                        <i class="fas fa-trophy me-1"></i> <?php echo htmlspecialchars($match['win_description']); ?>
                    </p>
                <?php elseif($match['win_description']): ?>
                    <p class="mb-1 text-warning fw-bold">
                        <i class="fas fa-handshake me-1"></i> <?php echo htmlspecialchars($match['win_description']); ?>
                    </p>
                <?php else: ?>
                    <p class="mb-1 text-secondary">Completed</p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination Controls -->
        <?php if($total_pages > 1): ?>
        <nav aria-label="Match history pages">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link bg-dark border-secondary text-white" href="?page=<?php echo $page-1; ?>">Previous</a>
                </li>
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link <?php echo ($page == $i) ? 'bg-primary border-primary' : 'bg-dark border-secondary text-white'; ?>" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link bg-dark border-secondary text-white" href="?page=<?php echo $page+1; ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- TIMEOUT OVERLAY -->
<!-- TIMEOUT OVERLAY -->
<div id="timeout-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-none d-flex flex-column align-items-center justify-content-center" style="background: rgba(0,0,0,0.95); z-index: 2000;">
    <h1 class="display-1 fw-bold text-warning mb-5" style="letter-spacing: 5px;">TIMEOUT</h1>
    
    <!-- Timeout Scoreboard -->
    <div class="container">
        <div class="row align-items-center justify-content-center text-center">
            <div class="col-5 text-end">
                <h2 id="to-t1-name" class="text-white fw-bold mb-0 display-6">Team A</h2>
            </div>
            <div class="col-2">
                <span class="display-4 fw-bold text-white">:</span>
            </div>
            <div class="col-5 text-start">
                <h2 id="to-t2-name" class="text-white fw-bold mb-0 display-6">Team B</h2>
            </div>
            
            <div class="col-5 text-end">
                <div id="to-t1-score" class="display-1 fw-bold text-info" style="font-size: 6rem;">0</div>
            </div>
            <div class="col-2">
            </div>
            <div class="col-5 text-start">
                <div id="to-t2-score" class="display-1 fw-bold text-info" style="font-size: 6rem;">0</div>
            </div>
        </div>
    </div>

    <div class="spinner-grow text-warning mt-5" role="status" style="width: 3rem; height: 3rem;"></div>
</div>

<!-- HISTORY MODAL -->
<div class="modal fade" id="historyModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title">Match History</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <ul class="list-group list-group-flush" id="history-list">
             <!-- Items -->
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- ANIMATION OVERLAY -->
<div id="animation-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-none d-flex align-items-center justify-content-center overflow-hidden" 
     style="z-index: 3000; background: rgba(0,0,0,0.2);">
    
    <!-- Background Flash -->
    <div id="anim-bg-flash" class="position-absolute w-100 h-100 bg-white" style="opacity: 0; pointer-events: none;"></div>

    <!-- Close Button (Visible only for persistent animations) -->
    <button id="anim-close-btn" class="btn btn-outline-light position-absolute top-0 end-0 m-4 rounded-circle d-none" style="z-index: 3600; width: 50px; height: 50px;" onclick="closeAnimation()">
        <i class="fas fa-times"></i>
    </button>

    <div id="anim-content" class="text-center position-relative p-5" style="transform: perspective(1000px) rotateX(10deg) scale(0.5); opacity: 0; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        
        <!-- Text Container -->
        <div class="position-relative d-inline-block">
             <h1 id="anim-text" class="display-1 fw-bold text-uppercase fst-italic m-0" 
            style="font-size: 8rem; -webkit-text-stroke: 3px black; text-shadow: 10px 10px 0px rgba(0,0,0,0.5); filter: drop-shadow(0 0 30px rgba(255,255,255,0.7));">
            SUPER RAID
            </h1>
            <!-- Shine Effect -->
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
    @keyframes shine {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    @keyframes impact {
        0% { transform: scale(2); opacity: 0; }
        50% { transform: scale(0.9); opacity: 1; }
        70% { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }
    @keyframes slideOut {
        0% { transform: translateX(0); opacity: 1; }
        100% { transform: translateX(100vw) skewX(-20deg); opacity: 0; }
    }
    .anim-active {
        animation: impact 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
    .anim-exit {
        animation: slideOut 0.4s ease-in forwards !important;
    }
    
    /* Type Styles */
    .type-super-raid { color: #ffd700; } /* Gold */
    .type-super-raid #anim-bar { background-color: #ffd700; color: #ffd700; }
    
    .type-super-tackle { color: #ff4500; } /* Orange Red */
    .type-super-tackle #anim-bar { background-color: #ff4500; color: #ff4500; }
    
    .type-all-out { color: #dc3545; } /* Danger Red */
    .type-all-out #anim-bar { background-color: #dc3545; color: #dc3545; }

    .type-winner { color: #ffd700; text-shadow: 0 0 50px #ffd700; } /* Gold */
    .type-winner #anim-bar { background-color: #ffd700; box-shadow: 0 0 30px #ffd700; }
    
    /* Mobile Responsive Animation */
    @media (max-width: 768px) {
        #anim-text { font-size: 4rem !important; -webkit-text-stroke: 1px black !important; }
        #anim-team { font-size: 2.5rem !important; transform: translateY(10px); }
        #anim-content { padding: 1rem !important; width: 95%; }
        .match-card { margin-bottom: 1rem; }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
    let lastAnimId = 0;
    let isFirstPoll = true;
    let lastStatus = '';
    let winnerShown = false;

    function updateScore() {
        fetch('get_score.php?game=kabaddi')
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Update Names
                    document.getElementById('match-title').innerText = data.match_info.team1 + " vs " + data.match_info.team2;
                    document.getElementById('team1-name').innerText = data.match_info.team1;
                    document.getElementById('team2-name').innerText = data.match_info.team2;
                    
                    // Update Scores
                    const scores = data.scores || {};
                    document.getElementById('team1-score').innerText = scores.t1_points || 0;
                    document.getElementById('team2-score').innerText = scores.t2_points || 0;
                    
                    // Update Player Icons
                    renderPlayers('t1-players-container', scores.t1_players !== undefined ? scores.t1_players : 7);
                    renderPlayers('t2-players-container', scores.t2_players !== undefined ? scores.t2_players : 7);
                    
                    // Check Completion
                    if(data.match_info.status === 'completed') {
                         if(!winnerShown) {
                              let wTeam = data.match_info.winner_team;
                              const desc = data.match_info.win_description || "";

                              // FAILSAFE: Calculate winner locally from scores if not explicitly provided
                              if (!wTeam) {
                                  const s1 = parseInt(scores.t1_points || 0);
                                  const s2 = parseInt(scores.t2_points || 0);
                                  const t1Name = data.match_info.team1;
                                  const t2Name = data.match_info.team2;
                                  
                                  if (s1 > s2) wTeam = t1Name;
                                  else if (s2 > s1) wTeam = t2Name;
                                  // else it remains null (Draw)
                              }

                              // Secondary Fallback: Try description
                              if (!wTeam && desc.includes(' won by ')) {
                                  wTeam = desc.split(' won by ')[0];
                              }

                              if (wTeam) {
                                  triggerVisualAnimation(wTeam, 'WINNER', 0);
                              } else {
                                  triggerVisualAnimation('MATCH DRAWN', '', 0);
                              }
                              winnerShown = true;
                         }
                         document.getElementById('match-status-text').innerText = "Result: " + (data.match_info.win_description || "Match Completed");
                         document.getElementById('match-status-text').className = "fw-bold text-success display-6 mt-3";
                    } else {
                         if(winnerShown) {
                             // Match restarted or new match
                             document.getElementById('animation-overlay').classList.add('d-none');
                             winnerShown = false;
                         }
                         document.getElementById('match-status-text').innerText = "Status: " + data.match_info.status;
                         document.getElementById('match-status-text').className = "";
                    }
                    lastStatus = data.match_info.status;

                    // Timeout
                    const timeoutOverlay = document.getElementById('timeout-overlay');
                    if(scores.is_timeout) {
                        timeoutOverlay.classList.remove('d-none');
                        // Update Timeout Scoreboard
                        document.getElementById('to-t1-name').innerText = data.match_info.team1;
                        document.getElementById('to-t2-name').innerText = data.match_info.team2;
                        document.getElementById('to-t1-score').innerText = scores.t1_points || 0;
                        document.getElementById('to-t2-score').innerText = scores.t2_points || 0;
                    } else {
                        timeoutOverlay.classList.add('d-none');
                    }

                    // Check Animation
                    if(scores.last_animation) {
                        const remoteId = scores.last_animation.id;
                        if (isFirstPoll) {
                            // Sync but don't play
                            lastAnimId = remoteId;
                        } else if (remoteId > lastAnimId) {
                            // New event during live session
                            lastAnimId = remoteId;
                            const tName = scores.last_animation.team === 't1' ? data.match_info.team1 : data.match_info.team2;
                            triggerVisualAnimation(scores.last_animation.type, tName);
                        }
                    }
                    isFirstPoll = false;
                    
                    // Render History
                    renderHistory(scores.history || []);

                    // Raid Indicator (Hide if timeout)
                    if(!scores.is_timeout) {
                        if (scores.current_raider === 'team1') {
                            document.getElementById('team1-indicator').classList.remove('d-none');
                            document.getElementById('team2-indicator').classList.add('d-none');
                        } else if (scores.current_raider === 'team2') {
                            document.getElementById('team1-indicator').classList.add('d-none');
                            document.getElementById('team2-indicator').classList.remove('d-none');
                        } else {
                            document.getElementById('team1-indicator').classList.add('d-none');
                            document.getElementById('team2-indicator').classList.add('d-none');
                        }
                    }
                } else {
                    document.getElementById('match-title').innerText = "No Live Match";
                }
            })
            .catch(err => console.error('Error fetching score:', err));
    }

    function triggerVisualAnimation(text, teamName, duration = 4000) {
        const overlay = document.getElementById('animation-overlay');
        const content = document.getElementById('anim-content');
        const bgFlash = document.getElementById('anim-bg-flash');
        const txt = document.getElementById('anim-text');
        const tm = document.getElementById('anim-team');
        const bar = document.getElementById('anim-bar');
        
        // Reset styles
        content.classList.remove('anim-active', 'anim-exit');
        content.className = "text-center position-relative p-5"; // Base classes
        
        // Determine Type Class
        let typeClass = 'type-super-raid'; // Default
        const lowerText = text.toLowerCase();
        const lowerTeam = teamName.toLowerCase();
        
        if (lowerText.includes('tackle')) typeClass = 'type-super-tackle';
        else if (lowerText.includes('all out')) typeClass = 'type-all-out';
        else if (lowerText.includes('winner') || lowerTeam.includes('winner')) {
            typeClass = 'type-winner';
            // Trigger Confetti Celebration for Winner
            if(window.confetti) {
                const duration = 5000;
                const end = Date.now() + duration;
                
                (function frame() {
                    confetti({
                        particleCount: 5,
                        angle: 60,
                        spread: 55,
                        origin: { x: 0 },
                        colors: ['#ff0000', '#00ff00', '#0000ff', '#ffd700'],
                        zIndex: 3500
                    });
                    confetti({
                        particleCount: 5,
                        angle: 120,
                        spread: 55,
                        origin: { x: 1 },
                        colors: ['#ff0000', '#00ff00', '#0000ff', '#ffd700'],
                        zIndex: 3500
                    });
                
                    if (Date.now() < end) {
                        requestAnimationFrame(frame);
                    }
                }());
            }
        }
        
        content.classList.add(typeClass);
        
        // Set Content
        txt.innerText = text;
        tm.innerText = teamName;
        
        // Show Overlay
        overlay.classList.remove('d-none');

        // Show Close Button if persistent
        const closeBtn = document.getElementById('anim-close-btn');
        if (duration === 0) {
            closeBtn.classList.remove('d-none');
        } else {
            closeBtn.classList.add('d-none');
        }
        
        // --- ANIMATION SEQUENCE ---
        
        // 1. Flash Background
        bgFlash.style.transition = 'none';
        bgFlash.style.opacity = '0.8';
        setTimeout(() => {
            bgFlash.style.transition = 'opacity 0.5s ease-out';
            bgFlash.style.opacity = '0';
        }, 50);

        // 2. Trigger Entrance
        setTimeout(() => {
             content.style.opacity = '1';
             content.classList.add('anim-active');
             
             // 3. Expand Bar & Show Team Name
             setTimeout(() => {
                 bar.style.width = '120%';
                 tm.style.transform = 'translateY(0)';
                 tm.style.opacity = '1';
             }, 300);
        }, 100);

        // 4. Exit (Only if duration > 0)
        if (duration > 0) {
            setTimeout(() => {
                closeAnimation();
            }, duration); // Display Duration
        }
    }

    function closeAnimation() {
        const overlay = document.getElementById('animation-overlay');
        const content = document.getElementById('anim-content');
        const tm = document.getElementById('anim-team');
        const bar = document.getElementById('anim-bar');
        const closeBtn = document.getElementById('anim-close-btn');

        content.classList.remove('anim-active');
        content.classList.add('anim-exit');
        
        setTimeout(() => {
            overlay.classList.add('d-none');
            closeBtn.classList.add('d-none');
            
            // Cleanup
            content.style.opacity = '0';
            content.classList.remove('type-winner', 'type-super-raid', 'type-super-tackle', 'type-all-out');
            bar.style.width = '0';
            tm.style.opacity = '0';
            tm.style.transform = 'translateY(20px)';
            
            // Stop Confetti if running (by clearing canvas usually, but here we just let it fall)
            if(window.confetti && typeof window.confetti.reset === 'function') {
                window.confetti.reset();
            }
        }, 400);
    }

    function renderPlayers(containerId, activeCount) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        const total = 7;
        for(let i=0; i<total; i++) {
            const icon = document.createElement('i');
            icon.className = 'fas fa-user';
            
            // First 'activeCount' players are white (active)
            // Remaining are red (out)
            if(i < activeCount) {
                icon.className += ' text-white';
            } else {
                icon.className += ' text-danger opacity-50';
            }
            
            // Make them a bit larger
            icon.style.fontSize = '1.2rem';
            container.appendChild(icon);
        }
    }

    function renderHistory(history) {
        const list = document.getElementById('history-list');
        list.innerHTML = '';
        
        if(history.length === 0) {
            list.innerHTML = '<li class="list-group-item bg-dark text-secondary text-center">No events yet</li>';
            return;
        }

        history.forEach(item => {
            const li = document.createElement('li');
            li.className = 'list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center';
            
            let badgeClass = 'bg-secondary';
            if(item.points > 1) badgeClass = 'bg-warning text-dark';
            if(item.action === 'ALL OUT') badgeClass = 'bg-danger text-white';

            li.innerHTML = `
                <div>
                    <span class="text-secondary small me-2">${item.time}</span>
                    <span class="fw-bold text-info">${item.team}</span>
                    <span class="mx-2">-</span>
                    <span>${item.action}</span>
                </div>
                <span class="badge ${badgeClass} rounded-pill">+${item.points}</span>
            `;
            list.appendChild(li);
        });
    }

    // Poll every 2 seconds
    setInterval(updateScore, 2000);
    updateScore(); // Initial call
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
