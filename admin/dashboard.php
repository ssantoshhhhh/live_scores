<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Force Admin Navbar to Official Blue */
        .navbar-custom-admin { background-color: #002147 !important; color: white !important; }

        /* Admin Dark Mode Overrides */
        body.dark-mode { background-color: #0f172a; color: #f8fafc; }
        body.dark-mode .glass-card { background: #1e293b; border-color: #334155; }
        body.dark-mode .form-select, body.dark-mode .form-control { background-color: #334155 !important; border-color: #475569 !important; color: white !important; }
        body.dark-mode .text-secondary { color: #94a3b8 !important; }
        body.dark-mode .list-group-item { background-color: #1e293b !important; color: white !important; border-color: #334155 !important; }
        body.dark-mode .text-white { color: #f8fafc !important; }
        
        /* Dashboard Mobile */
        @media (max-width: 768px) {
            .glass-card { padding: 1.5rem !important; margin-bottom: 1rem; }
            .navbar-brand { font-size: 1.25rem; }
            .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
            /* Stack schedule form */
            .col-md-5.mb-4, .col-md-7 { width: 100%; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom-admin mb-4">
        <div class="container">
            <span class="navbar-brand">Admin Dashboard</span>
            <div class="d-flex align-items-center gap-3">
                 <button onclick="toggleDarkMode()" class="btn btn-outline-light btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                <a href="../index.php" class="btn btn-outline-light btn-sm">View Site</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <h2 class="text-white mb-4">Select Game to Manage <small class="text-secondary fs-6">(<?php echo htmlspecialchars($_SESSION['admin_username']); ?>)</small></h2>
        <div class="row g-4 mb-5">
            <!-- Game Cards Here -->
            <?php 
            $allowed = $_SESSION['allowed_game'] ?? 'all'; 
            ?>

            <?php if($allowed === 'all' || $allowed === 'kabaddi'): ?>
            <div class="col-md-6 col-lg-3">
                <a href="control.php?game=kabaddi" class="text-decoration-none">
                    <div class="glass-card p-4 text-center text-white h-100 hover-effect">
                        <i class="fas fa-users fs-1 mb-3 text-warning"></i>
                        <h4>Kabaddi</h4>
                        <p class="text-secondary small">Manage Scores & Raiders</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if($allowed === 'all' || $allowed === 'pickleball'): ?>
            <div class="col-md-6 col-lg-3">
                <a href="control.php?game=pickleball" class="text-decoration-none">
                    <div class="glass-card p-4 text-center text-white h-100 hover-effect">
                        <i class="fas fa-table-tennis fs-1 mb-3 text-info"></i>
                        <h4>Pickleball</h4>
                        <p class="text-secondary small">Manage Sets & Points</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if($allowed === 'all' || $allowed === 'tennis'): ?>
            <div class="col-md-6 col-lg-3">
                <a href="control.php?game=tennis" class="text-decoration-none">
                    <div class="glass-card p-4 text-center text-white h-100 hover-effect">
                        <i class="fas fa-baseball-ball fs-1 mb-3 text-success"></i>
                        <h4>Tennis</h4>
                        <p class="text-secondary small">Manage Games & Sets</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if($allowed === 'all' || $allowed === 'volleyball'): ?>
            <div class="col-md-6 col-lg-3">
                <a href="control.php?game=volleyball" class="text-decoration-none">
                    <div class="glass-card p-4 text-center text-white h-100 hover-effect">
                        <i class="fas fa-volleyball-ball fs-1 mb-3 text-danger"></i>
                        <h4>Volleyball</h4>
                        <p class="text-secondary small">Manage Points & Rotations</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- SCHEDULE MANAGER -->
        <?php
        include '../db_connect.php';
        
        // Handle Create
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_match'])) {
            $gt = $_POST['game_type'];
            $t1 = $_POST['team1'];
            $t2 = $_POST['team2'];
            $time = $_POST['start_time'];
            
            $stmt = $conn->prepare("INSERT INTO matches (game_type, team1_name, team2_name, status, start_time) VALUES (:g, :t1, :t2, 'scheduled', :t)");
            $stmt->execute(['g'=>$gt, 't1'=>$t1, 't2'=>$t2, 't'=>$time]);
            echo "<div class='alert alert-success'>Match Scheduled!</div>";
        }
        
        // Fetch Scheduled
        $params = [];
        $sql = "SELECT * FROM matches WHERE status='scheduled'";
        if($allowed !== 'all') {
             $sql .= " AND game_type = :g";
             $params['g'] = $allowed;
        }
        $sql .= " ORDER BY start_time ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $scheduled = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="row">
            <!-- CREATE ENTRY -->
            <div class="col-md-5 mb-4">
                <div class="glass-card p-4">
                    <h4 class="text-white mb-3">Schedule Match</h4>
                    <form method="POST">
                        <div class="mb-2">
                             <label class="text-secondary small">Game Type</label>
                             <select name="game_type" class="form-select bg-dark text-white border-secondary">
                                 <?php if($allowed === 'all' || $allowed === 'kabaddi') echo '<option value="kabaddi">Kabaddi</option>'; ?>
                                 <?php if($allowed === 'all' || $allowed === 'pickleball') echo '<option value="pickleball">Pickleball</option>'; ?>
                                 <?php if($allowed === 'all' || $allowed === 'tennis') echo '<option value="tennis">Tennis</option>'; ?>
                                 <?php if($allowed === 'all' || $allowed === 'volleyball') echo '<option value="volleyball">Volleyball</option>'; ?>
                             </select>
                        </div>
                        <div class="mb-2">
                             <label class="text-secondary small">Team 1</label>
                             <input type="text" name="team1" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="mb-2">
                             <label class="text-secondary small">Team 2</label>
                             <input type="text" name="team2" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="mb-3">
                             <label class="text-secondary small">Start Time</label>
                             <input type="datetime-local" name="start_time" class="form-control bg-dark text-white border-secondary" required value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                        <button type="submit" name="create_match" class="btn btn-success w-100">Schedule Match</button>
                    </form>
                </div>
            </div>
            
            <!-- LIST -->
            <div class="col-md-7">
                <div class="glass-card p-4">
                    <h4 class="text-white mb-3">Scheduled Matches</h4>
                    <div class="list-group list-group-flush">
                        <?php if(count($scheduled) == 0): ?>
                             <div class="text-secondary text-center py-3">No scheduled matches</div>
                        <?php endif; ?>
                        
                        <?php foreach($scheduled as $m): ?>
                        <div class="list-group-item bg-transparent border-secondary text-white d-flex align-items-center justify-content-between">
                            <div>
                                <span class="badge bg-secondary mb-1"><?php echo ucfirst($m['game_type']); ?></span>
                                <div class="fw-bold"><?php echo htmlspecialchars($m['team1_name'] . ' vs ' . $m['team2_name']); ?></div>
                                <small class="text-secondary"><?php echo date('M d, H:i', strtotime($m['start_time'])); ?></small>
                            </div>
                            <a href="control.php?game=<?php echo $m['game_type']; ?>&id=<?php echo $m['id']; ?>&t1=<?php echo urlencode($m['team1_name']); ?>&t2=<?php echo urlencode($m['team2_name']); ?>&start=true" class="btn btn-primary btn-sm px-3 fw-bold">START <i class="fas fa-play ms-1"></i></a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
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
    <!-- Add Bootstrap Bundle for features like Modals/Collapse/Dropdowns -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
