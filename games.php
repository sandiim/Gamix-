<?php
session_start();
require 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeux - Gamix</title>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <!-- Navbar -->
    <header class="main-header">
        <nav class="nav-container">
            <div class="logo">
                <h1>Gamix</h1>
            </div>
            <ul class="nav-links">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="espaceUser.php">Accueil</a></li>
                <li><a href="games.php" class='active'>Jeux</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                <li><a href="espaceUser.php">Accueil</a></li>
                <li><a href="games.php" class='active'>Jeux</a></li>
                <li><a href="./games/historique.php">Historique de vos parties</a></li>
                <li><a href="editUser.php">Éditer profil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Section des jeux -->
    <main class="games-section" style="margin-top: 120px; padding: 2rem;">
        <h2 class="text-3xl font-bold text-center mb-8">Nos Jeux Disponibles</h2>
        <section id="games" class="games-section">
      <div class="games-grid">
        <div class="game-card">
          <div class="game-image">
            <img src="./assets/tictactoe.png" loading="lazy">
          </div>
          <div class="game-content">
            <h3>Tic Tac Toe</h3>
            <p>Le célèbre jeu de morpion. Alignez trois symboles pour gagner !</p>
            <a href="games/tictactoe.php" class="play-btn">Jouer</a>
          </div>
        </div>
        <div class="game-card">
          <div class="game-image">
            <img src="./assets/Card.jpeg" alt="Memory Game" loading="lazy">
          </div>
          <div class="game-content">
            <h3>Memory Game</h3>
            <p>Testez votre mémoire en trouvant les paires correspondantes.</p>
            <a href="./games/memory.html" class="play-btn">Jouer</a>
          </div>
        </div>
        <div class="game-card">
          <div class="game-image">
            <img src="./assets/snake.jpeg" alt="Snake Game" loading="lazy">
          </div>
          <div class="game-content">
            <h3>Snake</h3>
            <p>Guidez le serpent pour manger les pommes et grandir !</p>
            <a href="./games/snake.html" class="play-btn">Jouer</a>
          </div>
        </div>
        <div class="game-card">
          <div class="game-image">
            <img src="./assets/Jeu-pierre-feuille-ciseaux.webp" alt="Basketball Game" loading="lazy">
          </div>
          <div class="game-content">
            <h3>Pierre - Feuille - Ciseaux</h3>
            <p>Choisir Pierre ou Feuille ou Ciseaux</p>
            <a href="./games/feuille.html" class="play-btn">Jouer</a>
          </div>
        </div>
        <div class="game-card">
          <div class="game-image">
            <img src="./assets/Mosquito.png" alt="Mosquitos Game" loading="lazy">
          </div>
          <div class="game-content">
            <h3>Mosquitos Hunter</h3>
            <p>Chasse les mosquitos pour gagner des points</p>
            <a href="./games/mosquito.php" class="play-btn">Jouer</a>
          </div>
        </div>
      </div>
    </section>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <p>&copy; 2025 Gamix - Tous droits réservés</p>
    </footer>
</body>
</html> 