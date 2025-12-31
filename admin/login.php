<?php
session_start();
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../db_connect.php';
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT id, password, allowed_game FROM admins WHERE username = :u");
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password, $user['password'])) {
             $_SESSION['admin_logged_in'] = true;
             $_SESSION['admin_id'] = $user['id'];
             $_SESSION['allowed_game'] = $user['allowed_game'];
             $_SESSION['admin_username'] = $username;
             
             header("Location: dashboard.php");
             exit;
        } else {
            $error = "Invalid username or password";
        }
    } catch(PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="glass-card p-5" style="width: 100%; max-width: 400px;">
        <h3 class="text-white text-center mb-4">Admin Login</h3>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="text-secondary">Username</label>
                <input type="text" name="username" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <div class="mb-3">
                <label class="text-secondary">Password</label>
                <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">Login</button>
        </form>
        <div class="mt-3 text-center">
            <a href="../index.php" class="text-secondary text-decoration-none small">Back to Home</a>
        </div>
    </div>
</body>
</html>
