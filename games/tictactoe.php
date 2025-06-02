<?php
session_start(); 

$usernameForJS = "Joueur"; 
$isUserLoggedIn = false;

if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    if (!empty(trim($_SESSION['username']))) {
        $usernameForJS = htmlspecialchars(trim($_SESSION['username']), ENT_QUOTES, 'UTF-8');
        $isUserLoggedIn = true;
    } else {
        $usernameForJS = "Joueur"; 
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tic Tac Toe - <?php echo $usernameForJS; ?> vs Brathom</title>
    <!-- Lien vers votre style.css global s'il existe et est utilisé -->
    <link rel="stylesheet" href="../css/style.css"> 
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        
        :root {
            --board-bg: #2c3e50; 
            --cell-bg: #ecf0f1;  
            --cell-hover-bg: #bdc3c7; 
            --text-dark: #2c3e50;
            --text-light: #ffffff;
            --player-x-color: #e74c3c; /* Rouge pour X */
            --player-o-color: #3498db; /* Bleu pour O */
            --accent-color: #1abc9c; /* Turquoise pour les messages de victoire/boutons */
            --button-bg: var(--board-bg);
            --button-hover-bg: #34495e;
            --font-primary: 'Orbitron', sans-serif;
            --font-secondary: 'Roboto', sans-serif;
        }

        body {
            font-family: var(--font-secondary);
            background-color: #f0f2f5; /* Fond général plus clair */
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            min-height: 100vh;
            padding-top: 80px; /* Espace pour le header fixe */
        }

        /* Header (si vous utilisez celui de style.css, ces styles pourraient être redondants ou à ajuster) */
        .main-header {
            position: fixed; /* Header fixe */
            top: 0;
            left: 0;
            width: 100%;
            background-color: var(--board-bg);
            color: var(--text-light);
            padding: 10px 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        .logo h1 {
            font-family: var(--font-primary);
            font-size: 1.8rem;
            margin: 0;
            color: var(--accent-color);
        }
        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        .nav-links li a {
            color: var(--text-light);
            text-decoration: none;
            padding: 10px 15px;
            font-family: var(--font-secondary);
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .nav-links li a:hover, .nav-links li a.active {
            color: var(--accent-color);
        }

        .back-button-top {
            position: fixed; /* Bouton fixe aussi pour rester visible */
            top: 100px; 
            left: 20px;
            padding: 10px 15px;
            background-color: var(--button-bg);
            color: var(--text-light);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: var(--font-secondary);
            font-size: 0.9rem;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 999;
        }
        .back-button-top:hover { 
            background-color: var(--button-hover-bg);
            transform: translateY(-2px);
        }

        .dashboard-container { 
            padding: 20px;
            max-width: 600px; /* Container plus centré */
            margin: 40px auto 20px auto; /* Marge pour espacer du header et du footer */
            text-align: center;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .dashboard-title { 
            font-size: 2.8rem; 
            margin-bottom: 25px; 
            color: var(--board-bg); 
            font-family: var(--font-primary);
            font-weight: 700;
        }
        .dashboard-title .fa-gamepad {
            margin-right: 10px;
            color: var(--accent-color);
        }

        .game-container { 
            text-align: center; 
            margin-top: 1.5rem; 
        }

        .status { 
            margin: 25px 0; 
            font-size: 1.4rem; /* Légèrement plus petit pour s'adapter */
            font-weight: 500; 
            color: var(--text-dark); 
            min-height: 60px; /* Plus d'espace pour les messages */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: var(--font-secondary);
        }
        .status small.save-message { /* Style pour les messages de sauvegarde/erreur */
            font-size: 0.9rem;
            margin-top: 8px;
            font-weight: normal;
        }

        .board {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Utilise fr pour la flexibilité */
            gap: 8px; /* Espace plus large entre les cellules */
            background-color: var(--board-bg);
            padding: 10px;
            border-radius: 10px;
            margin: 0 auto;
            width: 300px; /* Taille fixe du plateau */
            height: 300px;
            box-shadow: 0 4px 8px rgba(44, 62, 80, 0.2);
        }
        .cell {
            background-color: var(--cell-bg);
            border: none; 
            border-radius: 8px; /* Bords plus arrondis */
            font-size: 3.5rem; /* Taille de police pour X et O */
            font-weight: bold;
            font-family: var(--font-primary); /* Police plus "jeu" pour X et O */
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            outline: none; /* Enlève le contour au focus */
        }
        .cell:hover { 
            background-color: var(--cell-hover-bg);
            transform: scale(1.03);
        }
        .cell:active {
            transform: scale(0.97);
        }

        .cell.x { color: var(--player-x-color); }
        .cell.o { color: var(--player-o-color); }

        #restart-btn {
            margin-top: 30px;
            padding: 12px 25px;
            font-size: 1.1rem;
            font-family: var(--font-secondary);
            font-weight: 500;
            background-color: var(--accent-color);
            color: var(--text-light);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        #restart-btn:hover { 
            background-color: #16a085; /* Un turquoise plus foncé */
            transform: translateY(-2px);
        }

        .win-message { 
            font-size: 1.8rem; 
            color: var(--accent-color); 
            font-weight: bold;
            font-family: var(--font-primary);
        }

        @media (max-width: 640px) {
            .dashboard-container {
                margin-top: 20px;
                width: 95%;
            }
            .dashboard-title { font-size: 2.2rem; }
            .board { width: 270px; height: 270px; gap: 6px; }
            .cell { font-size: 3rem; }
            .status { font-size: 1.2rem; }
            #restart-btn { font-size: 1rem; padding: 10px 20px; }
            .back-button-top { top: 80px; /* Ajustement pour header plus petit */ }
        }
        @media (max-width: 480px) {
            .main-header { padding: 5px 0; } /* Réduire padding header */
            .logo h1 { font-size: 1.5rem; }
            .nav-links li a { padding: 8px 10px; font-size: 0.9rem; }
            body { padding-top: 60px; /* Moins d'espace pour header réduit */ }
            .back-button-top { top: 70px; padding: 8px 12px; font-size: 0.8rem; }
            .dashboard-title { font-size: 1.8rem; }
            .board { width: 240px; height: 240px; }
            .cell { font-size: 2.5rem; }
        }
    </style>

    <script>
      const LOGGED_IN_USERNAME = "<?php echo $usernameForJS; ?>";
      const IS_USER_LOGGED_IN = <?php echo $isUserLoggedIn ? 'true' : 'false'; ?>;
    </script>
</head>
<body>
    <header class="main-header">
        <nav class="nav-container">
            <div class="logo"><h1>Gamix</h1></div>
            <ul class="nav-links">
                <li><a href="../espaceUser.php">Accueil</a></li>
                <li><a href="historique.php" >Historique</a></li> 
                <li><a href="../editUser.php">Éditer profil</a></li>
                <li><a href="../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    
   

    <main class="dashboard-container">
        <h1 class="dashboard-title">
            <i class="fas fa-gamepad"></i> Tic Tac Toe
        </h1>
      
        <div class="game-container">
            <div class="status" id="status">Chargement...</div>
            <div class="board" id="board">
                <?php for ($i = 0; $i < 9; $i++): ?>
                    <button class="cell" data-cell></button>
                <?php endfor; ?>
            </div>
            <button id="restart-btn">Recommencer</button>
             
            <button id="restart-btn" onclick="window.location.href='../espaceUser.php'">
                <i class="fas fa-home"></i> Accueil
            </button>
        </div>
        </div>
    </main>
    
  
    <script src="../js/tictactoe.js"></script> 
</body>
</html>