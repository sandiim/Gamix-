<?php
// Démarrer la session EN PREMIER
session_start();

// Déterminer si l'utilisateur est connecté
$isUserLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chasseur de Moustiques - Arcade Games</title>
    <!-- Ajustez le chemin si chasseur_moustiques.php est à la racine -->
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    /* Styles pour le jeu Chasseur de Moustiques */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        /* background-color: #f0f0f0; Si non défini dans style.css */
        text-align: center; /* Centrer le contenu globalement si besoin */
    }

    /* Styles du header (peuvent venir de style.css) */
    .main-header { /* ... vos styles de header ... */ }
    .nav-container { /* ... vos styles de nav ... */ }

    /* Conteneur principal du jeu */
    .game-page-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 15px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .game-page-container h2 {
        color: #333;
        margin-bottom: 20px;
    }

    #game-area {
        width: 90%;
        max-width: 700px;
        height: 450px; /* Un peu plus haut */
        border: 3px solid #4CAF50; /* Bordure verte */
        margin: 20px auto;
        position: relative;
        /* Mettez le chemin correct si l'image est dans ../images/ ou images/ */
        background-image: url('../images/swamp_background.jpg'); /* Exemple de fond */
        background-size: cover;
        cursor: none; 
        overflow: hidden;
        border-radius: 5px;
    }

    .mosquito {
        width: 40px;
        height: 40px;
        /* Mettez le chemin correct */
        background-image: url('../images/mosquito.png'); 
        background-size: contain;
        background-repeat: no-repeat;
        position: absolute;
        transition: transform 0.3s ease-out, opacity 0.3s ease-out;
    }
    .mosquito.dead {
        /* Mettez le chemin correct */
        background-image: url('../images/mosquito_dead.png'); 
        transform: translateY(80px) rotate(90deg) scale(0.8);
        pointer-events: none;
    }
    .mosquito.landed { /* Pour quand il "tombe" vraiment */
        transform: translateY(calc(450px - 40px)) rotate(90deg) scale(0.8); /* Atterrir en bas */
    }
    .mosquito.fade {
        opacity: 0;
    }

    #crosshair {
        position: absolute;
        width: 35px; /* Viseur un peu plus grand */
        height: 35px;
        /* Mettez le chemin correct */
        background-image: url('../images/crosshair.png'); 
        background-size: contain;
        pointer-events: none; 
        transform: translate(-50%, -50%); 
        z-index: 100;
        display: none; /* Caché par défaut, affiché par JS au démarrage */
    }

    .game-header-info { 
        display: flex; 
        justify-content: space-around; 
        margin-bottom: 15px; 
        font-size: 1.2em; 
        color: #333;
        padding: 10px;
        background-color: #e8f5e9; /* Fond léger pour les infos */
        border-radius: 5px;
    }
    .game-header-info div {
        padding: 5px 10px;
    }

    .controls { 
        margin-top: 20px; 
        margin-bottom: 10px;
    }
    .game-button, .nav-button { 
        padding: 12px 20px; 
        margin: 5px 10px; 
        font-size: 1em; 
        font-weight: bold;
        cursor: pointer; 
        border-radius: 5px;
        border: none;
        background-color: #4CAF50; /* Boutons verts */
        color: white;
        transition: background-color 0.3s;
    }
    .game-button:hover, .nav-button:hover { 
        background-color: #45a049; 
    }
    .game-button:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }
    .nav-button .fas { margin-right: 8px; }

    #save-status-message { 
        margin-top:15px; 
        font-weight: bold; 
        min-height: 22px; /* Pour l'affichage du message */
        font-size: 0.9em;
    }
        .mosquito {
        width: 40px;
        height: 40px;
        /* Mettez le chemin correct vers votre image de moustique */
        background-image: url('../images/mosquito.png'); 
        background-size: contain;
        background-repeat: no-repeat;
        position: absolute;
        transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        
        /* Filtres pour essayer de teinter l'image en bleu */
        /* Ajustez ces valeurs pour obtenir la teinte souhaitée */
        filter: invert(20%) sepia(100%) saturate(500%) hue-rotate(180deg) brightness(90%) contrast(100%);
        /* Explication des filtres :
           - hue-rotate(180deg) : décale fortement les teintes vers le bleu/cyan
           - saturate(500%) : augmente la saturation pour rendre la couleur plus vive
           - sepia(100%) : peut aider à obtenir des tons bleutés en combinaison
           - invert(20%) et brightness(90%) : ajustements pour l'apparence générale
           Vous devrez expérimenter avec ces valeurs.
        */
    }

    .mosquito.dead {
        /* Mettez le chemin correct vers votre image de moustique mort */
        background-image: url('../images/mosquito_dead.png'); 
        transform: translateY(80px) rotate(90deg) scale(0.8);
        pointer-events: none;
        filter: none; /* Enlever les filtres pour le moustique mort */
    }
</style>
</head>
<body>
    <header class="main-header">
        <nav class="nav-container">
            <div class="logo"><h1>Gamix</h1></div>
            <ul class="nav-links">
                <!-- Ajustez les chemins si chasseur_moustiques.php est à la racine -->
                <li><a href="../espaceUser.php" class='<?php echo (basename($_SERVER['PHP_SELF']) == "espaceUser.php" ? "active" : "");?>'>Accueil</a></li>
                <li><a href="../php/historique.php">Historique</a></li> 
                <li><a href="../editUser.php">Éditer profil</a></li>
                <li><a href="../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="game-page-container">
        <h2>Chasseur de Moustiques</h2>
        
        <div class="game-header-info">
            <div id="score">Score: 0</div>
            <div id="mosquito-count">Moustiques: 0</div>
            <div id="time">Temps: 60s</div>
        </div>
        
        <div id="game-area">
            <!-- Les moustiques seront ajoutés ici par JavaScript -->
        </div>
        <div id="crosshair"></div> <!-- Viseur personnalisé -->

        <div class="controls">
            <button id="startGame" class="game-button">Démarrer le Jeu</button>
            <!-- Ajustez le chemin si chasseur_moustiques.php est à la racine -->
            <button class="game-button nav-button" onclick="window.location.href='../espaceUser.php'">
                <i class="fas fa-home"></i> Accueil
            </button>
        </div>
        <div id="save-status-message"></div> <!-- Pour afficher les messages de sauvegarde -->
    </main>

    
    <!-- Ajustez le chemin si chasseur_moustiques.php est à la racine -->
    <script src="../js/mosquito.js"></script>
</body>
</html>