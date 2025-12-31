-- Database creation
CREATE DATABASE IF NOT EXISTS game_scores;
USE game_scores;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Matches table
-- stores the basic info about the match
CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_type ENUM('kabaddi', 'pickleball', 'tennis', 'volleyball') NOT NULL,
    team1_name VARCHAR(100) NOT NULL,
    team2_name VARCHAR(100) NOT NULL,
    team1_color VARCHAR(20) DEFAULT '#ff0000',
    team2_color VARCHAR(20) DEFAULT '#0000ff',
    status ENUM('scheduled', 'live', 'completed') DEFAULT 'scheduled',
    start_time DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Live Scores table
-- Uses JSON to store specific game state (sets, points, servers, etc.)
CREATE TABLE IF NOT EXISTS live_scores (
    match_id INT PRIMARY KEY,
    score_json JSON,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
);

-- Insert default admin (username: admin, password: password123)
-- Note: In a real app, use password_hash(). For this demo we will compare direct hash in PHP or use password_verify.
-- Let's assume we use password_hash in the PHP script.
-- INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); 
-- The above hash is 'password'.
