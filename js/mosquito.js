// Variables du jeu
let score = 0;
let gameInterval;
let timeLeft = 60;
let isGameRunning = false;
let mosquitoCount = 0;

// Éléments du DOM
const gameArea = document.getElementById('game-area');
const scoreDisplay = document.getElementById('score');
const timeDisplay = document.getElementById('time');
const mosquitoCountDisplay = document.getElementById('mosquito-count');
const startButton = document.getElementById('startGame');
const crosshair = document.getElementById('crosshair');

// Suivre le curseur
document.addEventListener('mousemove', (e) => {
    crosshair.style.left = e.clientX + 'px';
    crosshair.style.top = e.clientY + 'px';
});

// Démarrer le jeu
function startGame() {
    if (isGameRunning) return;
    
    // Réinitialiser le jeu
    score = 0;
    timeLeft = 60;
    mosquitoCount = 0;
    scoreDisplay.textContent = 'Score: 0';
    timeDisplay.textContent = 'Temps: 60s';
    mosquitoCountDisplay.textContent = 'Moustiques: 0';
    gameArea.innerHTML = '';
    
    // Démarrer le jeu
    isGameRunning = true;
    startButton.disabled = true;
    
    // Créer des moustiques plus fréquemment
    gameInterval = setInterval(() => {
        // Créer 2 à 4 moustiques à la fois
        const count = Math.floor(Math.random() * 3) + 2;
        for (let i = 0; i < count; i++) {
            createMosquito();
        }
    }, 1500);
    
    // Compte à rebours
    const timer = setInterval(() => {
        timeLeft--;
        timeDisplay.textContent = `Temps: ${timeLeft}s`;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            clearInterval(gameInterval);
            endGame();
        }
    }, 1000);
}

// Créer un moustique
function createMosquito() {
    const mosquito = document.createElement('div');
    mosquito.className = 'mosquito';
    
    // Position aléatoire
    const gameRect = gameArea.getBoundingClientRect();
    const x = Math.random() * (gameRect.width - 40);
    const y = Math.random() * (gameRect.height - 40);
    
    mosquito.style.left = x + 'px';
    mosquito.style.top = y + 'px';
    
    // Incrémenter le compteur de moustiques
    mosquitoCount++;
    mosquitoCountDisplay.textContent = `Moustiques: ${mosquitoCount}`;
    
    // Clic sur le moustique
    mosquito.onclick = () => {
        if (!mosquito.classList.contains('dead')) {
            score += 10;
            scoreDisplay.textContent = `Score: ${score}`;
            mosquito.classList.add('dead');
            
            // Attendre que l'animation de chute soit terminée
            setTimeout(() => {
                mosquito.classList.add('landed'); // Le moustique reste au sol
                
                // Attendre que la tête de mort reste au sol
                setTimeout(() => {
                    mosquito.classList.add('fade'); // Commence à se dissiper
                    setTimeout(() => {
                        if (mosquito && mosquito.parentNode) {
                            mosquito.remove();
                            mosquitoCount--;
                            mosquitoCountDisplay.textContent = `Moustiques: ${mosquitoCount}`;
                        }
                    }, 500);
                }, 2000); // La tête de mort reste au sol pendant 2 secondes
            }, 500); // Durée de l'animation de chute
        }
    };
    
    gameArea.appendChild(mosquito);
    
    // Faire disparaître le moustique s'il n'est pas tué
    setTimeout(() => {
        if (mosquito && !mosquito.classList.contains('dead')) {
            mosquito.remove();
            mosquitoCount--;
            mosquitoCountDisplay.textContent = `Moustiques: ${mosquitoCount}`;
        }
    }, 2000);
}

// Fin du jeu
function endGame() {
    isGameRunning = false;
    startButton.disabled = false;
    alert(`Temps écoulé ! Votre score est ${score}`);
}

// Événement du bouton de démarrage
startButton.addEventListener('click', startGame);
