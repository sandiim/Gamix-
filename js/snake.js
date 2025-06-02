// Configuration du jeu
const canvas = document.getElementById('gameCanvas'); // Récupérer l'élément canvas pour dessiner le jeu
const ctx = canvas.getContext('2d'); // Obtenir le contexte 2D pour dessiner sur le canvas
const startButton = document.getElementById('start-btn'); // Bouton pour démarrer le jeu
const scoreElement = document.getElementById('score'); // Élément pour afficher le score actuel
const bestScoreElement = document.getElementById('bestScore'); // Élément pour afficher le meilleur score
const DureeElement = document.getElementById('Duree');
// Taille d'une cellule du serpent
const gridSize = 20; // Taille d'une cellule (20 pixels)
const tileCount = 20; // Nombre de cellules par ligne et colonne

// Variables du jeu
let snake = []; // Tableau représentant le serpent
let food = {}; // Objet représentant la nourriture
let velocityX = 0; // Vitesse du serpent sur l'axe X
let velocityY = 0; // Vitesse du serpent sur l'axe Y
let score = 0; // Score actuel du joueur
let gameRunning = false; // Booléen indiquant si le jeu est en cours
let speed = 7; // Vitesse du serpent (déplacements par seconde)
let fps = 120;  // Fréquence d'animation (frames par seconde)
let frameCount = 0; // Compteur de frames pour gérer la vitesse du jeu
let lastDirection = { x: 0, y: 0 }; // Direction précédente du mouvement


// Sons (ajoutez des fichiers audio si nécessaire)
const eatSound = new Audio('eat.mp3'); // Son joué lorsque le serpent mange la nourriture
const gameOverSound = new Audio('gameover.mp3'); // Son joué à la fin du jeu

// Récupérer le meilleur score depuis localStorage
let bestScore = localStorage.getItem('bestScore') || 0; // Si aucun meilleur score n'est trouvé, initialiser à 0

// Mettre à jour et afficher le meilleur score
function updateBestScore() {
    if (score > bestScore) { // Si le score actuel est supérieur au meilleur score
        bestScore = score; // Mettre à jour le meilleur score
        localStorage.setItem('bestScore', bestScore); // Sauvegarder le meilleur score dans localStorage
    }
    bestScoreElement.textContent = bestScore; // Afficher le meilleur score
}

// Initialisation du jeu
function initGame() {
    console.log('Le jeu a commencé !'); // Log pour signaler le début du jeu
    snake = [{ x: 10, y: 10 }]; // Réinitialiser le serpent avec une position de départ
    placeFood(); // Placer la nourriture aléatoirement
    score = 0; // Réinitialiser le score
    scoreElement.textContent = score; // Mettre à jour l'affichage du score

    velocityX = 0; // Réinitialiser la vitesse du serpent sur l'axe X
    velocityY = 0; // Réinitialiser la vitesse du serpent sur l'axe Y

    frameCount = 0; // Réinitialiser le compteur de frames
    gameRunning = true; // Commencer le jeu
    gameLoop(); // Démarrer la boucle du jeu
}

// Placement aléatoire de la nourriture
function placeFood() {
    food = {
        x: Math.floor(Math.random() * tileCount), // Position aléatoire sur l'axe X
        y: Math.floor(Math.random() * tileCount), // Position aléatoire sur l'axe Y
        animationFrame: 0, // Frame pour l'animation de la nourriture
    };
}

// Dessin de la grille
function drawGrid() {
    ctx.strokeStyle = 'rgba(100, 100, 100, 0.1)'; // Couleur des lignes de la grille
    for (let i = 0; i < tileCount; i++) { // Boucle pour dessiner chaque ligne de la grille
        ctx.beginPath();
        ctx.moveTo(i * gridSize, 0); // Début de la ligne verticale
        ctx.lineTo(i * gridSize, canvas.height); // Fin de la ligne verticale
        ctx.stroke(); // Appliquer la ligne

        ctx.beginPath();
        ctx.moveTo(0, i * gridSize); // Début de la ligne horizontale
        ctx.lineTo(canvas.width, i * gridSize); // Fin de la ligne horizontale
        ctx.stroke(); // Appliquer la ligne
    }
}

// Dessin de la nourriture avec effet lumineux
function drawFood() {
    const pulse = Math.sin(food.animationFrame / 30) * 0.5 + 0.5; // Effet de "pulse" pour l'animation de la nourriture
    ctx.shadowBlur = 20 * pulse; // Appliquer un flou d'ombre à la nourriture
    ctx.shadowColor = '#f1c40f'; // Couleur de l'ombre
    ctx.fillStyle = `rgba(243, 156, 18, ${0.7 + 0.3 * pulse})`; // Couleur de la nourriture avec animation
    ctx.beginPath();
    ctx.arc(food.x * gridSize + gridSize / 2, food.y * gridSize + gridSize / 2, gridSize / 2, 0, Math.PI * 2); // Dessiner la nourriture sous forme de cercle
    ctx.fill(); // Remplir la forme
    ctx.shadowBlur = 0; // Réinitialiser l'ombre

    food.animationFrame++; // Avancer l'animation de la nourriture
}

// Dessin du serpent avec un dégradé dynamique
function drawSnake() {
    snake.forEach((segment, index) => { // Boucle pour dessiner chaque segment du serpent
        const gradient = ctx.createLinearGradient(
            segment.x * gridSize, 
            segment.y * gridSize, 
            (segment.x + 1) * gridSize, 
            (segment.y + 1) * gridSize
        ); // Créer un dégradé dynamique pour chaque segment
        if (index === 0) { // Si c'est la tête du serpent
            gradient.addColorStop(0, '#1abc9c'); // Couleur de la tête
            gradient.addColorStop(1, '#16a085'); // Dégradé de la tête
        } else { // Pour les autres segments
            gradient.addColorStop(0, '#2ecc71'); // Couleur des segments
            gradient.addColorStop(1, '#27ae60'); // Dégradé des segments
        }
        ctx.fillStyle = gradient; // Appliquer le dégradé comme couleur de remplissage
        ctx.beginPath();
        ctx.arc(segment.x * gridSize + gridSize / 2, segment.y * gridSize + gridSize / 2, gridSize / 2, 0, Math.PI * 2); // Dessiner chaque segment sous forme de cercle
        ctx.fill(); // Remplir la forme
    });
}

// Mise à jour de la position du serpent
function update() {
    if (!gameRunning) return; // Si le jeu est terminé, ne rien faire

    let newHead = {
        x: snake[0].x + velocityX, // Calculer la nouvelle position de la tête sur l'axe X
        y: snake[0].y + velocityY // Calculer la nouvelle position de la tête sur l'axe Y
    };

    if (velocityX !== 0 || velocityY !== 0) { // Si le serpent est en mouvement
        if (
            newHead.x < 0 || newHead.x >= tileCount || // Vérifier si le serpent sort des limites du canvas
            newHead.y < 0 || newHead.y >= tileCount || // Vérifier si le serpent sort des limites du canvas
            snake.some(segment => segment.x === newHead.x && segment.y === newHead.y) // Vérifier si la tête touche un autre segment
        ) {
            gameOver(); // Fin du jeu
            return;
        }
    }

    snake.unshift(newHead); // Ajouter la nouvelle tête du serpent au début du tableau

    if (newHead.x === food.x && newHead.y === food.y) { // Si le serpent mange la nourriture
        score += 10; // Augmenter le score
        scoreElement.textContent = score; // Mettre à jour le score à l'écran
        eatSound.play(); // Jouer le son de la nourriture mangée
        placeFood(); // Placer une nouvelle nourriture
    } else {
        snake.pop(); // Retirer le dernier segment du serpent (mouvement)
    }
    
    // Mettre à jour la dernière direction valide
    lastDirection = { x: velocityX, y: velocityY };
}

// Dessin du canvas
function draw() {
    ctx.fillStyle = '#171717'; // Couleur de fond du canvas
    ctx.fillRect(0, 0, canvas.width, canvas.height); // Remplir le canvas avec la couleur de fond

    drawGrid(); // Dessiner la grille
    drawSnake(); // Dessiner le serpent
    drawFood(); // Dessiner la nourriture
}

// Boucle principale du jeu
function gameLoop() {
    if (!gameRunning) return; // Si le jeu est terminé, ne pas continuer la boucle

    frameCount++; // Incrémenter le compteur de frames

    if (frameCount >= fps / speed) { // Si la fréquence d'images atteint la vitesse du jeu
        update(); // Mettre à jour la position du serpent
        frameCount = 0; // Réinitialiser le compteur de frames
    }

    draw(); // Dessiner l'état actuel du jeu
    requestAnimationFrame(gameLoop); // Demander à la fonction de se rappeler à la prochaine frame
}

function handleKeyPress(event) {
    if (!gameRunning) return; // Si le jeu est terminé, ne rien faire

    switch (event.key) {
        case 'ArrowUp':
            if (lastDirection.y !== 1) { // Empêcher de revenir dans la direction opposée
                velocityX = 0;
                velocityY = -1;
            }
            break;
        case 'ArrowDown':
            if (lastDirection.y !== -1) {
                velocityX = 0;
                velocityY = 1;
            }
            break;
        case 'ArrowLeft':
            if (lastDirection.x !== 1) {
                velocityX = -1;
                velocityY = 0;
            }
            break;
        case 'ArrowRight':
            if (lastDirection.x !== -1) {
                velocityX = 1;
                velocityY = 0;
            }
            break;
    }
}


const game_name = "Snake"; 
let startTime; 

function gameOver() {
    console.log("[gameOver] Début. Score:", currentScore);
    if(animationFrameId) {
        cancelAnimationFrame(animationFrameId);
        console.log("[gameOver] Animation frame annulée.");
    }
    gameRunning = false;
    
    if (typeof gameOverSound !== 'undefined' && gameOverSound.play) {
        gameOverSound.play().catch(e => console.warn("[gameOver] Erreur lecture son 'gameOver':", e));
    }
    
    updateBestScoreDisplay();
    
    if (typeof startButton !== 'undefined') {
        startButton.textContent = 'Rejouer';
        startButton.disabled = false;
        console.log("[gameOver] Bouton 'Rejouer' activé.");
    }

    const endTime = new Date();
    const duration = startTime ? Math.floor((endTime - startTime) / 1000) : 0;
    console.log("[gameOver] Durée calculée:", duration, "secondes.");

    const formData = new URLSearchParams();
    formData.append('game_name', game_name);
    formData.append('score', currentScore);
    formData.append('duration', duration);

    console.log('[gameOver] Données envoyées au serveur:', Object.fromEntries(formData));

    // MODIFIEZ 'save.php' CI-DESSOUS SI VOTRE SCRIPT PHP A UN AUTRE NOM OU CHEMIN
    const saveScriptPath = 'save.php'; 
    console.log(`[gameOver] Tentative de fetch vers: ${saveScriptPath}`);

    fetch(saveScriptPath, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('[gameOver] Réponse brute du serveur reçue:', response);
        if (!response.ok) {
            // Si la réponse HTTP n'est pas OK (ex: 404, 500)
            console.error(`[gameOver] Erreur HTTP! Statut: ${response.status} ${response.statusText}`);
            // Essayer de lire le corps de la réponse comme texte pour plus de détails
            return response.text().then(text => {
                console.error('[gameOver] Corps de la réponse (erreur HTTP):', text);
                // Créer un objet d'erreur personnalisé pour le bloc catch
                const error = new Error(`Erreur HTTP ${response.status}: ${response.statusText}.`);
                error.responseBody = text; // Ajouter le corps de la réponse à l'objet d'erreur
                error.status = response.status;
                throw error; 
            });
        }
        // Si la réponse est OK, essayer de la parser comme JSON
        console.log('[gameOver] Réponse OK, tentative de parser en JSON...');
        return response.json();
    })
    .then(data => {
        console.log('[gameOver] Réponse JSON du serveur:', data);
        let message = `Game Over!\nScore: ${currentScore}\nMeilleur score: ${bestScore}`; // bestScore est le meilleur score local affiché
        if (data && data.success) {
            message += `\nScore sauvegardé en ligne.`;
            console.log('[gameOver] Score sauvegardé avec succès selon le serveur.');
        } else {
            const serverErrorMsg = data && data.error ? data.error : 'Réponse inattendue ou échec non spécifié du serveur.';
            message += `\nATTENTION: Le score n'a pas pu être sauvegardé en ligne.\nErreur serveur: ${serverErrorMsg}`;
            console.error("[gameOver] Erreur d'enregistrement (réponse serveur):", serverErrorMsg, "Données reçues:", data);
        }
        alert(message);
    })
    .catch(error => {
        console.error('[gameOver] ERREUR dans fetch.catch():', error);
        let detailedErrorMessage = 'Erreur de communication avec le serveur pour sauvegarder le score.';
        
        if (error.status) { // Erreur personnalisée avec statut HTTP
            detailedErrorMessage += ` (Statut ${error.status})`;
        }
        if (error.responseBody) { // Si nous avons pu lire le corps de la réponse d'erreur
            detailedErrorMessage += `\nDétails serveur: ${error.responseBody.substring(0, 300)}`; // Afficher une partie
        } else if (error.message) {
             detailedErrorMessage += `\nMessage: ${error.message}`;
        }
        
        alert(`Game Over!\nScore: ${currentScore}\nMeilleur score: ${bestScore}\n${detailedErrorMessage}\nDuree: ${Duree}\n`);
    });
}

if (typeof startButton !== 'undefined') {
    startButton.addEventListener('click', () => {
        if (!gameRunning) {
            startTime = new Date(); // Initialiser startTime ici
            initGame(); 
        }
    });
}




function goHome() {
    window.location.href = '../index.html';
}


// Attend que le DOM soit complètement chargé
document.addEventListener('DOMContentLoaded', () => {

    const canvas = document.getElementById('gameCanvas');
    const startButton = document.getElementById('start-btn');
    const scoreElement = document.getElementById('score');
    const bestScoreElement = document.getElementById('bestScore');

    // Vérification cruciale que les éléments HTML existent
    if (!canvas || !startButton || !scoreElement || !bestScoreElement) {
        console.error("FATAL: Un ou plusieurs éléments HTML (canvas, start-btn, score, bestScore) sont introuvables. Vérifiez les IDs dans votre HTML.");
        alert("Erreur critique : Impossible de charger les composants du jeu. Veuillez vérifier la console pour plus de détails.");
        return; // Arrêter l'exécution si les éléments manquent
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error("FATAL: Impossible d'obtenir le contexte 2D du canvas.");
        alert("Erreur critique : Impossible d'initialiser le graphique du jeu.");
        return;
    }

    // Configuration du jeu
    const gridSize = 20; // Taille d'une cellule
    // tileCount est déterminé par la taille du canvas divisée par gridSize
    // Assurez-vous que canvas.width et canvas.height sont des multiples de gridSize
    const tileCountX = canvas.width / gridSize;
    const tileCountY = canvas.height / gridSize;

    // Variables du jeu
    let snake;
    let food;
    let velocityX;
    let velocityY;
    let currentScore; // Renommé pour éviter confusion avec la variable globale 'score' si elle existe
    let gameRunning;
    let speed = 7; // Vitesse du serpent (déplacements par seconde)
    let fps = 60;  // Fréquence de rafraîchissement souhaitée
    let frameCount;
    let lastDirection;
    const game_name = "Snake"; // Nom du jeu pour la sauvegarde
    let startTime;
    let animationFrameId; // Pour pouvoir annuler la boucle de jeu

    // Sons (gestion basique des erreurs si les fichiers sont absents)
    let eatSound, gameOverSound;
    try {
        eatSound = new Audio('eat.mp3'); // Chemin relatif au fichier HTML
        gameOverSound = new Audio('gameover.mp3'); // Chemin relatif au fichier HTML
    } catch (e) {
        console.warn("Impossible de charger les fichiers audio. Le jeu fonctionnera sans son.", e);
        const dummySound = { play: () => Promise.resolve(), pause: () => {}, currentTime: 0 }; // Mock pour éviter les erreurs
        eatSound = eatSound || dummySound;
        gameOverSound = gameOverSound || dummySound;
    }

    // Meilleur score depuis localStorage (clé spécifique au jeu)
    let bestScore = localStorage.getItem('snake_bestScore') || 0;
    bestScoreElement.textContent = bestScore;

    function updateBestScoreDisplay() {
        if (currentScore > bestScore) {
            bestScore = currentScore;
            localStorage.setItem('snake_bestScore', bestScore);
        }
        bestScoreElement.textContent = bestScore;
    }

    function initGame() {
        console.log('Initialisation du jeu Snake...');
        snake = [{ x: Math.floor(tileCountX / 2), y: Math.floor(tileCountY / 2) }];
        placeFood();
        currentScore = 0;
        scoreElement.textContent = currentScore;
        velocityX = 0; // Pas de mouvement initial
        velocityY = 0;
        lastDirection = { x: 0, y: 0 }; // Important pour la logique anti-retournement
        frameCount = 0;
        gameRunning = true;
        startTime = new Date();
        
        startButton.textContent = 'Jeu en cours...'; // Ou le désactiver
        startButton.disabled = true;

        if (animationFrameId) { // Nettoyer une précédente boucle si elle existe
            cancelAnimationFrame(animationFrameId);
        }
        console.log('Jeu démarré !');
        gameLoop();
    }

    function placeFood() {
        let foodPlacedOnSnake;
        do {
            foodPlacedOnSnake = false;
            food = {
                x: Math.floor(Math.random() * tileCountX),
                y: Math.floor(Math.random() * tileCountY),
                animationFrame: 0,
            };
            // Vérifier si la nourriture est sur le serpent
            for (let segment of snake) {
                if (segment.x === food.x && segment.y === food.y) {
                    foodPlacedOnSnake = true;
                    break;
                }
            }
        } while (foodPlacedOnSnake);
    }

    function drawGrid() {
        ctx.strokeStyle = 'rgba(100, 100, 100, 0.1)';
        for (let i = 0; i <= tileCountX; i++) {
            ctx.beginPath();
            ctx.moveTo(i * gridSize, 0);
            ctx.lineTo(i * gridSize, canvas.height);
            ctx.stroke();
        }
        for (let i = 0; i <= tileCountY; i++) {
            ctx.beginPath();
            ctx.moveTo(0, i * gridSize);
            ctx.lineTo(canvas.width, i * gridSize);
            ctx.stroke();
        }
    }

    function drawFood() {
        const pulse = Math.sin(food.animationFrame / 20) * 0.4 + 0.6; // 0.6 à 1.0
        ctx.shadowBlur = 10 * pulse;
        ctx.shadowColor = '#f1c40f';
        ctx.fillStyle = `rgba(243, 156, 18, ${0.8 + 0.2 * pulse})`; // Plus opaque
        ctx.beginPath();
        // Nourriture un peu plus petite que la cellule pour mieux la voir
        ctx.arc(food.x * gridSize + gridSize / 2, food.y * gridSize + gridSize / 2, gridSize / 2.8, 0, Math.PI * 2);
        ctx.fill();
        ctx.shadowBlur = 0; // Réinitialiser pour les autres dessins
        food.animationFrame++;
    }

    function drawSnake() {
        snake.forEach((segment, index) => {
            const gradient = ctx.createLinearGradient(
                segment.x * gridSize, segment.y * gridSize,
                (segment.x + 1) * gridSize, (segment.y + 1) * gridSize
            );
            if (index === 0) { // Tête
                gradient.addColorStop(0, '#1abc9c'); gradient.addColorStop(1, '#16a085');
                ctx.fillStyle = gradient;
                // Dessin de la tête (peut être différent)
                ctx.fillRect(segment.x * gridSize, segment.y * gridSize, gridSize, gridSize);
            } else { // Corps
                gradient.addColorStop(0, '#2ecc71'); gradient.addColorStop(1, '#27ae60');
                ctx.fillStyle = gradient;
                ctx.fillRect(segment.x * gridSize, segment.y * gridSize, gridSize -1 , gridSize -1 ); // -1 pour voir les segments
            }
        });
    }
    
    function update() {
        if (!gameRunning) return;

        // Si aucune direction n'a été choisie, le serpent ne bouge pas
        if (velocityX === 0 && velocityY === 0) {
            return; 
        }

        let headX = snake[0].x + velocityX;
        let headY = snake[0].y + velocityY;

        // Collision avec les murs
        if (headX < 0 || headX >= tileCountX || headY < 0 || headY >= tileCountY) {
            gameOver();
            return;
        }

        // Collision avec soi-même
        for (let i = 1; i < snake.length; i++) { // Commencer à 1 pour ne pas comparer la tête avec elle-même avant qu'elle ne bouge
            if (snake[i].x === headX && snake[i].y === headY) {
                gameOver();
                return;
            }
        }
        
        const newHead = { x: headX, y: headY };
        snake.unshift(newHead); // Ajouter la nouvelle tête

        // Manger la nourriture
        if (headX === food.x && headY === food.y) {
            currentScore += 10;
            scoreElement.textContent = currentScore;
            eatSound.play().catch(e => console.warn("Erreur lecture son 'eat':", e));
            placeFood(); // Placer une nouvelle nourriture
        } else {
            snake.pop(); // Retirer la queue si on n'a pas mangé
        }
        
        // Mettre à jour la dernière direction APRÈS avoir traité le mouvement
        lastDirection = { x: velocityX, y: velocityY };
    }

    function draw() {
        // Fond
        ctx.fillStyle = '#171717';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        drawGrid();
        drawFood(); // Dessiner la nourriture avant le serpent pour qu'elle soit en dessous si le serpent passe dessus
        drawSnake();
    }

    function gameLoop() {
        if (!gameRunning) {
            if(animationFrameId) cancelAnimationFrame(animationFrameId);
            return;
        }

        frameCount++;
        // Ajuster la vitesse du jeu
        if (frameCount >= Math.max(1, Math.floor(fps / speed))) {
            update();
            frameCount = 0;
        }

        draw();
        animationFrameId = requestAnimationFrame(gameLoop);
    }

    function handleKeyPress(event) {
        // Si le jeu n'est pas en cours et qu'on appuie sur une flèche,
        // on peut considérer cela comme un démarrage implicite, ou ignorer.
        // Pour l'instant, on n'agit que si gameRunning est vrai pour le mouvement.
        if (!gameRunning && (velocityX === 0 && velocityY === 0)) { // Permet le premier mouvement
             //  Ne rien faire ici, le premier mouvement est géré par les conditions ci-dessous
        } else if (!gameRunning) {
            return;
        }


        const key = event.key;
        let newVX = velocityX;
        let newVY = velocityY;

        switch (key) {
            case 'ArrowUp':
                if (lastDirection.y !== 1 || snake.length === 1) { newVX = 0; newVY = -1; }
                break;
            case 'ArrowDown':
                if (lastDirection.y !== -1 || snake.length === 1) { newVX = 0; newVY = 1; }
                break;
            case 'ArrowLeft':
                if (lastDirection.x !== 1 || snake.length === 1) { newVX = -1; newVY = 0; }
                break;
            case 'ArrowRight':
                if (lastDirection.x !== -1 || snake.length === 1) { newVX = 1; newVY = 0; }
                break;
            default:
                return; // Ignorer les autres touches
        }
        
        // Appliquer la nouvelle vélocité si elle est différente
        // et que le jeu n'a pas encore commencé à bouger, ou si la direction est valide
        if ( (velocityX === 0 && velocityY === 0) || (newVX !== -lastDirection.x || newVY !== -lastDirection.y) ) {
            velocityX = newVX;
            velocityY = newVY;
        }
    }

    function gameOver() {
        console.log("Game Over. Score:", currentScore);
        if(animationFrameId) cancelAnimationFrame(animationFrameId);
        gameRunning = false;
        gameOverSound.play().catch(e => console.warn("Erreur lecture son 'gameOver':", e));
        updateBestScoreDisplay();
        
        startButton.textContent = 'Rejouer';
        startButton.disabled = false; // Réactiver le bouton

        const endTime = new Date();
        const duration = startTime ? Math.floor((endTime - startTime) / 1000) : 0;

        // Envoyer le score au serveur (le chemin 'save.php' doit être correct)
        fetch('save.php', { // Assurez-vous que ce chemin est correct par rapport à votre HTML
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
            body: `game_name=${encodeURIComponent(game_name)}&score=${currentScore}&duration=${duration}`
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error(`HTTP error! status: ${response.status}, body: ${text}`) });
            }
            return response.json();
        })
        .then(data => {
            console.log('Réponse du serveur (sauvegarde):', data);
            let message = `Game Over!\nScore: ${currentScore}\nMeilleur score: ${bestScore}`;
            if (!data.success) {
                message += `\nATTENTION: Le score n'a pas pu être sauvegardé en ligne.\nErreur: ${data.error || 'Inconnue'}`;
                console.error("Erreur d'enregistrement du score (réponse serveur):", data.error);
            } else {
                message += `\nScore sauvegardé en ligne.`;
            }
            alert(message);
        })
        .catch(error => {
            console.error('Erreur fetch ou JSON lors de la sauvegarde:', error);
            alert(`Game Over!\nScore: ${currentScore}\nMeilleur score: ${bestScore}\nErreur de communication avec le serveur pour sauvegarder le score.`);
        });
    }

    // Écouteurs d'événements
    startButton.addEventListener('click', () => {
        if (!gameRunning) {
            initGame();
        }
    });
    document.addEventListener('keydown', handleKeyPress);

    // Fonction goHome (doit être globale si appelée par onclick HTML)
    window.goHome = function() {
        // Adapter le chemin si nécessaire. Si snake_game.html est dans un dossier 'games'
        // et index.html est à la racine.
        window.location.href = '../index.html'; 
    };

    // État initial du canvas (avant de cliquer sur Start)
    function drawInitialState() {
        ctx.fillStyle = '#171717';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        drawGrid();
        // Optionnel: dessiner un serpent statique ou un message
        ctx.fillStyle = "rgba(0, 241, 206, 0.7)";
        ctx.font = "24px Orbitron";
        ctx.textAlign = "center";
        ctx.fillText("Appuyez sur Démarrer", canvas.width / 2, canvas.height / 2);
    }
    drawInitialState();

}); // Fin de DOMContentLoaded