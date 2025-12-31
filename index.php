<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-3 main-title mb-3">Live Sports Arena</h1>
        <p class="lead text-secondary">Select a sport to view real-time scores and match statistics.</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Kabaddi Card -->
        <div class="col-md-6 col-lg-3">
            <a href="kabaddi.php" class="text-decoration-none">
                <div class="glass-card game-card kabaddi">
                    <i class="fas fa-users game-icon text-white"></i>
                    <h3 class="h4 fw-bold">Kabaddi</h3>
                    <p class="small text-secondary mb-0">Raid Points | Tackles</p>
                </div>
            </a>
        </div>

        <!-- Pickleball Card -->
        <div class="col-md-6 col-lg-3">
            <a href="pickleball.php" class="text-decoration-none">
                <div class="glass-card game-card pickleball">
                    <i class="fas fa-table-tennis game-icon text-white"></i>
                    <h3 class="h4 fw-bold">Pickleball</h3>
                    <p class="small text-secondary mb-0">Fast-Paced Action</p>
                </div>
            </a>
        </div>

        <!-- Tennis Card -->
        <div class="col-md-6 col-lg-3">
            <a href="tennis.php" class="text-decoration-none">
                <div class="glass-card game-card tennis">
                    <i class="fas fa-baseball-ball game-icon text-white"></i>
                    <h3 class="h4 fw-bold">Tennis</h3>
                    <p class="small text-secondary mb-0">Grand Slam Stakes</p>
                </div>
            </a>
        </div>

        <!-- Volleyball Card -->
        <div class="col-md-6 col-lg-3">
            <a href="volleyball.php" class="text-decoration-none">
                <div class="glass-card game-card volleyball">
                    <i class="fas fa-volleyball-ball game-icon text-white"></i>
                    <h3 class="h4 fw-bold">Volleyball</h3>
                    <p class="small text-secondary mb-0">Spike & Block</p>
                </div>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
