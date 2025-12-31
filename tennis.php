<?php include 'includes/header.php'; ?>

<div class="container py-5 scoreboard-container theme-tennis" id="app">
    <div class="text-center mb-4">
        <span class="badge bg-secondary mb-2">LIVE TENNIS MATCH</span>
        <h2 id="match-title" class="fw-bold text-white">Loading Match...</h2>
    </div>

    <div class="row align-items-center justify-content-center">
        <!-- Team 1 -->
        <div class="col-md-5 mb-4">
            <div class="glass-card p-5 text-center position-relative">
                <i class="fas fa-baseball-ball text-warning position-absolute top-0 start-50 translate-middle d-none" id="serve-t1"></i>
                <h3 id="team1-name" class="team-name mb-3">Player 1</h3>
                <div id="team1-score" class="score-big">0</div>
                <div class="text-secondary mt-1">Sets: <span id="sets-t1" class="text-white fw-bold">0</span></div>
            </div>
        </div>

        <!-- VS -->
        <div class="col-md-2 text-center mb-4">
            <div class="vs-badge mb-3">VS</div>
        </div>

        <!-- Team 2 -->
        <div class="col-md-5 mb-4">
            <div class="glass-card p-5 text-center position-relative">
                <i class="fas fa-baseball-ball text-warning position-absolute top-0 start-50 translate-middle d-none" id="serve-t2"></i>
                <h3 id="team2-name" class="team-name mb-3">Player 2</h3>
                <div id="team2-score" class="score-big">0</div>
                <div class="text-secondary mt-1">Sets: <span id="sets-t2" class="text-white fw-bold">0</span></div>
            </div>
        </div>
    </div>

    <!-- Stats / Footer -->
    <div class="row mt-4">
        <div class="col-12 text-center text-secondary">
             <button class="btn btn-outline-light mb-3" data-bs-toggle="modal" data-bs-target="#historyModal">
                <i class="fas fa-history me-2"></i> Show Match History
            </button>
            <p id="match-status-text">Waiting for updates...</p>
        </div>
    </div>
    
    <!-- MATCHES LIST (Upcoming / Previous) -->
    <?php
    include 'db_connect.php';
    // Upcoming
    $upcoming = $conn->query("SELECT * FROM matches WHERE game_type='tennis' AND status='scheduled' ORDER BY start_time ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Pagination Logic for Previous
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;
    $total_stmt = $conn->query("SELECT COUNT(*) FROM matches WHERE game_type='tennis' AND status='completed'");
    $total_matches = $total_stmt->fetchColumn();
    $total_pages = ceil($total_matches / $limit);
    
    $prev_sql = "SELECT * FROM matches WHERE game_type='tennis' AND status='completed' ORDER BY start_time DESC LIMIT :limit OFFSET :offset";
    $prev_stmt = $conn->prepare($prev_sql);
    $prev_stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $prev_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $prev_stmt->execute();
    $previous = $prev_stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <p class="mb-1 text-success fw-bold"><i class="fas fa-trophy me-1"></i> <?php echo htmlspecialchars($match['win_description']); ?></p>
                <?php else: ?>
                    <p class="mb-1 text-secondary">Completed</p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        
        <?php if($total_pages > 1): ?>
        <nav aria-label="Match history pages">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>"><a class="page-link bg-dark border-secondary text-white" href="?page=<?php echo $page-1; ?>">Previous</a></li>
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>"><a class="page-link <?php echo ($page == $i) ? 'bg-primary border-primary' : 'bg-dark border-secondary text-white'; ?>" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"><a class="page-link bg-dark border-secondary text-white" href="?page=<?php echo $page+1; ?>">Next</a></li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <?php endif; ?>
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
        <ul class="list-group list-group-flush" id="history-list"></ul>
      </div>
    </div>
  </div>
</div>

<script>
    function updateScore() {
        fetch('get_score.php?game=tennis')
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    const scores = data.scores || {};
                    
                    document.getElementById('match-title').innerText = data.match_info.team1 + " vs " + data.match_info.team2;
                    document.getElementById('team1-name').innerText = data.match_info.team1;
                    document.getElementById('team2-name').innerText = data.match_info.team2;
                    
                    // Tennis Score
                    document.getElementById('team1-score').innerText = scores.current_game_t1 || '0';
                    document.getElementById('team2-score').innerText = scores.current_game_t2 || '0';
                    
                    document.getElementById('sets-t1').innerText = scores.sets_won_t1 || 0;
                    document.getElementById('sets-t2').innerText = scores.sets_won_t2 || 0;
                    
                    // Serve
                    if(scores.server === 'team1') {
                        document.getElementById('serve-t1').classList.remove('d-none');
                        document.getElementById('serve-t2').classList.add('d-none');
                    } else if(scores.server === 'team2') {
                        document.getElementById('serve-t1').classList.add('d-none');
                        document.getElementById('serve-t2').classList.remove('d-none');
                    }
                    
                    document.getElementById('match-status-text').innerText = "Status: " + data.match_info.status;
                    renderHistory(scores.history || []);
                } else {
                    document.getElementById('match-title').innerText = "No Live Match";
                }
            });
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
            li.innerHTML = `
                <div>
                    <span class="text-secondary small me-2">${item.time}</span>
                    <span class="fw-bold text-info">${item.team}</span>
                    <span class="mx-2">-</span>
                    <span>${item.action}</span>
                </div>
            `;
            list.appendChild(li);
        });
    }

    setInterval(updateScore, 2000);
    updateScore();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
