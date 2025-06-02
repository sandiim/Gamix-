
const board = document.getElementById('board');
const cells = document.querySelectorAll('[data-cell]');
const statusDisplay = document.getElementById('status');
const restartButton = document.getElementById('restart-btn');

const HUMAN_MARKER = 'X';
const AI_MARKER = 'O';


const HUMAN_DISPLAY_NAME = (typeof LOGGED_IN_USERNAME !== 'undefined' && LOGGED_IN_USERNAME && LOGGED_IN_USERNAME.trim() !== "" && LOGGED_IN_USERNAME !== "Joueur") ? LOGGED_IN_USERNAME : "Vous";


let currentPlayerMarker; // Stocke le MARQUEUR du joueur actuel (X ou O)
let gameActive;
let gameState;
let startTime;

const winningCombinations = [
    [0, 1, 2], [3, 4, 5], [6, 7, 8],
    [0, 3, 6], [1, 4, 7], [2, 5, 8],
    [0, 4, 8], [2, 4, 6]
];

async function saveGameResult(gameName, score, duration) {
    if (typeof IS_USER_LOGGED_IN === 'undefined' || !IS_USER_LOGGED_IN) {
        console.log("Utilisateur non connecté. Le score ne sera pas sauvegardé.");
        if (statusDisplay) {
            const existingSaveMsg = statusDisplay.querySelector('small.save-message');
            if (existingSaveMsg) existingSaveMsg.remove();
            
            const notLoggedInMsg = document.createElement('small');
            notLoggedInMsg.style.display = 'block';
            notLoggedInMsg.classList.add('save-message');
            notLoggedInMsg.textContent = 'Connectez-vous pour sauvegarder vos scores.';
            notLoggedInMsg.style.color = 'orange';
            statusDisplay.appendChild(notLoggedInMsg);
        }
        return;
    }

    const formData = new FormData();
    formData.append('game_name', gameName);
    formData.append('score', score);
    formData.append('duration', duration);
    const saveUrl = 'save.php';

    try {
        console.log(`Tentative de sauvegarde vers : ${new URL(saveUrl, window.location.href).href}`);
        const response = await fetch(saveUrl, { method: 'POST', body: formData });
        const result = await response.json();
        const saveMsgElement = document.createElement('small');
        saveMsgElement.style.display = 'block';
        saveMsgElement.classList.add('save-message');

     
        if (statusDisplay) {
            const existingSaveMsg = statusDisplay.querySelector('small.save-message');
            if (existingSaveMsg) existingSaveMsg.remove();
            statusDisplay.appendChild(saveMsgElement);
        }
    } catch (error) {
        console.error('Erreur lors de la sauvegarde (réseau/parsing):', error);
        const networkErrorElement = document.createElement('small');
        networkErrorElement.style.display = 'block';
        networkErrorElement.style.color = 'red';
        networkErrorElement.classList.add('save-message');
        networkErrorElement.textContent = 'Erreur réseau lors de la tentative de sauvegarde.';
        if (statusDisplay) {
            const existingSaveMsg = statusDisplay.querySelector('small.save-message');
            if (existingSaveMsg) existingSaveMsg.remove();
            statusDisplay.appendChild(networkErrorElement);
        }
    }
}

function placeMark(cellIndex, marker) {
    gameState[cellIndex] = marker;
    cells[cellIndex].textContent = marker;
    cells[cellIndex].classList.add(marker.toLowerCase()); // Ajoute la classe 'x' ou 'o'
    cells[cellIndex].classList.remove(marker === HUMAN_MARKER ? AI_MARKER.toLowerCase() : HUMAN_MARKER.toLowerCase()); // Retire l'autre classe
}

function checkGameEnd(marker) { // marker est 'X' ou 'O'
    const endTime = new Date();
    const durationInSeconds = Math.round((endTime - startTime) / 1000);
    let gameResultTitle = `Tic Tac Toe`;

    if (checkWin(marker)) {
        gameActive = false;
        if (statusDisplay) {
            statusDisplay.textContent = (marker === HUMAN_MARKER) ? `${HUMAN_DISPLAY_NAME} gagne !` : "AI gagne !";
            statusDisplay.classList.add('win-message');
        }
        const score = (marker === HUMAN_MARKER) ? 100 : 10;
        saveGameResult(gameResultTitle, score, durationInSeconds);
        return true;
    }

    if (checkDraw()) {
        gameActive = false;
        if (statusDisplay) statusDisplay.textContent = "Match nul !";
        saveGameResult(gameResultTitle, 50, durationInSeconds);
        return true;
    }
    return false;
}

function AIMove() {
    if (!gameActive || currentPlayerMarker !== AI_MARKER) return;

    const emptyCells = gameState.map((val, idx) => val === '' ? idx : null).filter(val => val !== null);
    if (emptyCells.length > 0) {
        const randomIndex = emptyCells[Math.floor(Math.random() * emptyCells.length)];
        placeMark(randomIndex, AI_MARKER);

        if (checkGameEnd(AI_MARKER)) return;

        currentPlayerMarker = HUMAN_MARKER;
        if (statusDisplay) statusDisplay.textContent = `Tour de ${HUMAN_DISPLAY_NAME} (${HUMAN_MARKER})`;
    }
}

function handleCellClick(e) {
    if (currentPlayerMarker !== HUMAN_MARKER || !gameActive) return;

    const cell = e.target;
    const cellIndex = [...cells].indexOf(cell);

    if (gameState[cellIndex] !== '') return;

    placeMark(cellIndex, HUMAN_MARKER);

    if (checkGameEnd(HUMAN_MARKER)) return;

    currentPlayerMarker = AI_MARKER;
    if (statusDisplay) statusDisplay.textContent = `Tour de AI (${AI_MARKER})...`;
    
    setTimeout(AIMove, 800);
}

function checkWin(marker) {
    return winningCombinations.some(combination => {
        return combination.every(index => gameState[index] === marker);
    });
}

function checkDraw() {
    return gameState.every(cell => cell !== '');
}

function restartGame() {
    startTime = new Date(); 
    currentPlayerMarker = HUMAN_MARKER; // Le joueur humain commence toujours
    gameActive = true;
    gameState = ['', '', '', '', '', '', '', '', ''];
    
    if (statusDisplay) {
        statusDisplay.textContent = `Tour de ${HUMAN_DISPLAY_NAME} (${HUMAN_MARKER})`; 
        statusDisplay.classList.remove('win-message');
        const existingSaveMsg = statusDisplay.querySelector('small.save-message');
        if (existingSaveMsg) existingSaveMsg.remove();
    }
    
    cells.forEach(cell => {
        cell.textContent = '';
        cell.classList.remove('x', 'o'); // Enlever les classes 'x' et 'o'
    });
}

function goHome() {
    window.location.href = '../espaceUser.php'; 
}

// --- Initialisation ---
// Vérifier si les variables globales du PHP sont définies
if (typeof LOGGED_IN_USERNAME === 'undefined' || typeof IS_USER_LOGGED_IN === 'undefined') {
    console.error("Variables PHP (LOGGED_IN_USERNAME, IS_USER_LOGGED_IN) non définies.");
    if(statusDisplay) statusDisplay.textContent = "Erreur de configuration du jeu.";
    // On pourrait désactiver le jeu ou afficher un message plus proéminent
} else {
    if (cells.length) {
        cells.forEach(cell => {
            cell.addEventListener('click', handleCellClick);
        });
    } else {
        console.error("Aucune cellule de jeu trouvée.");
    }

    if (restartButton) {
        restartButton.addEventListener('click', restartGame);
    } else {
        console.error("Bouton de redémarrage non trouvé.");
    }

    // Appel initial pour configurer le jeu
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restartGame);
    } else {
        restartGame();
    }
}