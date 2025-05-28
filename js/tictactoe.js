// Sélection des éléments du DOM
const board = document.getElementById('board');
const cells = document.querySelectorAll('[data-cell]');
const status = document.getElementById('status');
const restartButton = document.getElementById('restart-btn');

// Variables du jeu
let currentPlayer = 'X';
let gameActive = true;
let gameState = ['', '', '', '', '', '', '', '', ''];

// Combinaisons gagnantes
const winningCombinations = [
    [0, 1, 2], [3, 4, 5], [6, 7, 8], // Lignes
    [0, 3, 6], [1, 4, 7], [2, 5, 8], // Colonnes
    [0, 4, 8], [2, 4, 6] // Diagonales
];

// Gestionnaire de clic sur une cellule
function handleCellClick(e) {
    const cell = e.target;
    const cellIndex = [...cells].indexOf(cell);

    if (gameState[cellIndex] !== '' || !gameActive) return;

    // Mise à jour de la cellule
    gameState[cellIndex] = currentPlayer;
    cell.textContent = currentPlayer;
    cell.classList.add(currentPlayer.toLowerCase());

    // Vérification de la victoire
    if (checkWin()) {
        gameActive = false;
        status.textContent = `Le joueur ${currentPlayer} gagne !`;
        status.classList.add('win-message');
        return;
    }

    // Vérification du match nul
    if (checkDraw()) {
        gameActive = false;
        status.textContent = "Match nul !";
        return;
    }

    // Changement de joueur
    if (currentPlayer === 'X') {
        currentPlayer = 'O';
    } else {
        currentPlayer = 'X';
    }    status.textContent = `Tour du joueur ${currentPlayer}`;
}

// Vérification de la victoire
function checkWin() {
    // Parcour de chaque combinaison gagnante
    for (let i = 0; i < winningCombinations.length; i++) {
        let combination = winningCombinations[i];

        // Vérifie si tous les indices de la combinaison ont le même symbole que le joueur actuel
        let isWinning = true;
        for (let j = 0; j < combination.length; j++) {
            let index = combination[j];
            if (gameState[index] !== currentPlayer) {
                isWinning = false;
                break;
            }
        }

        // Si une combinaison gagnante est trouvée, retourne true
        if (isWinning) {
            return true;
        }
    }

    // Si aucune combinaison gagnante n'est trouvée, retourne false
    return false;
}


// Vérification du match nul
function checkDraw() {
    return gameState.every(cell => cell !== '');
}

// Réinitialisation du jeu
function restartGame() {
    currentPlayer = 'X';
    gameActive = true;
    gameState = ['', '', '', '', '', '', '', '', ''];
    status.textContent = `Tour du joueur ${currentPlayer}`;
    status.classList.remove('win-message');
    
    cells.forEach(cell => {
        cell.textContent = '';
        cell.classList.remove('x', 'o');
    });
}

// Navigation vers la page d'accueil
function goHome() {
    window.location.href = '../index.html';
}

// Ajout des écouteurs d'événements
cells.forEach(cell => {
    cell.addEventListener('click', handleCellClick);
});

restartButton.onclick = restartGame;