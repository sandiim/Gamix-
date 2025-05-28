// Configuration du jeu
const canvas = document.getElementById('gameCanvas'); // Récupérer l'élément canvas pour dessiner le jeu
const ctx = canvas.getContext('2d'); // Obtenir le contexte 2D pour dessiner sur le canvas
const startButton = document.getElementById('start-btn'); // Bouton pour démarrer le jeu
const scoreElement = document.getElementById('score'); // Élément pour afficher le score actuel
const bestScoreElement = document.getElementById('bestScore'); // Élément pour afficher le meilleur score

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

// Gestion de fin de partie
function gameOver() {
    gameRunning = false; // Arrêter le jeu
    gameOverSound.play(); // Jouer le son de fin de jeu
    updateBestScore(); // Mettre à jour le meilleur score
    alert('Game Over! Score: ' + score + '\nMeilleur score: ' + bestScore); // Afficher une alerte
    startButton.textContent = 'Rejouer'; // Changer le texte du bouton pour rejouer
}

// Événements
document.addEventListener('keydown', handleKeyPress); // Ajouter un écouteur d'événements pour les touches du clavier
startButton.addEventListener('click', () => {
    if (!gameRunning) {
        initGame(); // Initialiser le jeu si ce n'est pas déjà en cours
    }
});

function goHome() {
    window.location.href = '../index.html';
}
