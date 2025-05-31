<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Espace Utilisateur - Gamix</title>
  <link rel="stylesheet" href="./css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
</head>
<body>

  <!-- Navbar -->
  <header class="main-header">
    <nav class="nav-container">
      <div class="logo">
        <h1>Gamix</h1>
      </div>
      <ul class="nav-links">
        <li><a href="#hero">Accueil</a></li>
        <li><a href="logout.php">Déconnexion</a></li>
      </ul>
    </nav>
  </header>

  <!-- Section de bienvenue -->
  <main class="hero" id="hero" style="margin-top: 120px;">
    <div class="hero-content">
      <h2>Bienvenue <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>
      <p>Ceci est votre espace personnel sécurisé sur Gamix Arcade.<br>Amusez-vous avec nos jeux exclusifs !</p>
      <a href="#games" class="cta-button">Découvrir les jeux</a>
    </div>
  </main>

  <!-- Section des jeux -->
  <section id="games" class="games-section">
    <h2>Nos Jeux</h2>
    <div class="games-grid">
      <div class="game-card">
        <div class="game-image">
          <img src="./assets/tictactoe.png" loading="lazy">
        </div>
        <div class="game-content">
          <h3>Tic Tac Toe</h3>
          <p>Le célèbre jeu de morpion. Alignez trois symboles pour gagner !</p>
          <a href="games/tictactoe.html" class="play-btn">Jouer</a>
        </div>
      </div>

      <div class="game-card">
        <div class="game-image">
          <img src="./assets/Card.jpeg" alt="Memory Game" loading="lazy">
        </div>
        <div class="game-content">
          <h3>Memory Game</h3>
          <p>Testez votre mémoire en trouvant les paires correspondantes.</p>
          <a href="./projet web 2025/Build A Memory Card.html" class="play-btn">Jouer</a>
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
          <img src="./assets/basket.png" alt="Basketball Game" loading="lazy">
        </div>
        <div class="game-content">
          <h3>Basketball</h3>
          <p>Lancer le ballon pour marquer des buts</p>
          <a href="./games/jeu bask.html" class="play-btn">Jouer</a>
        </div>
      </div>

      <div class="game-card">
        <div class="game-image">
          <img src="./assets/Mosquito.png" alt="Mosquitos Game" loading="lazy">
        </div>
        <div class="game-content">
          <h3>Mosquitos Hunter</h3>
          <p>Chasse les mosquitos pour gagner des points</p>
          <a href="./games/mosquito.html" class="play-btn">Jouer</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="main-footer">
    <p>&copy; 2025 Gamix - Tous droits réservés</p>
  </footer>

</body>
</html>
