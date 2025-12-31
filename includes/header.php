<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiveScore Pro | Premium Tournament Dashboard</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Outfit for modern feel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-bolt text-warning me-2"></i>LiveScore<span class="text-primary">Pro</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">Admin Login</a></li>
                    <li class="nav-item ms-2">
                        <button onclick="toggleDarkMode()" class="btn btn-outline-light btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const icon = document.getElementById('theme-icon');
            if(document.body.classList.contains('dark-mode')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                localStorage.setItem('theme', 'light');
            }
        }
        
        // Init
        if(localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
            document.getElementById('theme-icon').classList.remove('fa-moon');
            document.getElementById('theme-icon').classList.add('fa-sun');
        }
    </script>
        </div>
    </nav>
