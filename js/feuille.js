document.addEventListener('DOMContentLoaded', () => {
    const choicesButtons = document.querySelectorAll('.choices button');
    const playerChoiceDisplay = document.getElementById('player-choice');
    const computerChoiceDisplay = document.getElementById('computer-choice');
    const roundResultDisplay = document.getElementById('round-result');
    const playerScoreDisplay = document.getElementById('player-score');
    const computerScoreDisplay = document.getElementById('computer-score');
    const saveGameButton = document.getElementById('save-game-pfc');
    const timerDisplayPFC = document.getElementById('timer-display-pfc');

    let playerScore = 0;
    let computerScore = 0;
    const choices = ['pierre', 'feuille', 'ciseaux'];

    let timerIntervalPFC;
    let secondsElapsedPFC = 0;

    // --- Fonctions du chronomètre ---
    function updateTimerDisplayPFC() {
        if (timerDisplayPFC) {
            const minutes = Math.floor(secondsElapsedPFC / 60);
            const seconds = secondsElapsedPFC % 60;
            timerDisplayPFC.textContent = `${String(minutes).padStart(2, '0')} min ${String(seconds).padStart(2, '0')} sec`;
        }
    }

    function startTimerPFC() {
        stopTimerPFC(); 
        secondsElapsedPFC = 0;
        updateTimerDisplayPFC();
        timerIntervalPFC = setInterval(() => {
            secondsElapsedPFC++;
            updateTimerDisplayPFC();
        }, 1000);
    }

    function stopTimerPFC() {
        clearInterval(timerIntervalPFC);
    }

    // --- Logique du jeu ---
    choicesButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (secondsElapsedPFC === 0 && !timerIntervalPFC) { // Démarre le timer au premier clic si pas déjà démarré
                startTimerPFC();
            }

            const playerChoice = e.target.dataset.choice;
            const computerChoice = choices[Math.floor(Math.random() * choices.length)];
            playRound(playerChoice, computerChoice);
        });
    });

    function playRound(player, computer) {
        playerChoiceDisplay.textContent = player.charAt(0).toUpperCase() + player.slice(1);
        computerChoiceDisplay.textContent = computer.charAt(0).toUpperCase() + computer.slice(1);

        let resultText = "";
        roundResultDisplay.className = ''; // Reset class

        if (player === computer) {
            resultText = "Égalité !";
            roundResultDisplay.classList.add('tie');
        } else if (
            (player === 'pierre' && computer === 'ciseaux') ||
            (player === 'feuille' && computer === 'pierre') ||
            (player === 'ciseaux' && computer === 'feuille')
        ) {
            resultText = "Vous avez gagné cette manche !";
            playerScore++;
            roundResultDisplay.classList.add('win');
        } else {
            resultText = "L'ordinateur gagne cette manche.";
            computerScore++;
            roundResultDisplay.classList.add('lose');
        }
        roundResultDisplay.textContent = resultText;
        playerScoreDisplay.textContent = playerScore;
        computerScoreDisplay.textContent = computerScore;
    }

    // --- Sauvegarde du jeu ---
    saveGameButton.addEventListener('click', async () => {
        if (secondsElapsedPFC === 0 && playerScore === 0 && computerScore === 0) {
            alert("Jouez au moins une manche avant de sauvegarder !");
            return;
        }
        
        stopTimerPFC(); // Arrêter le chronomètre avant de sauvegarder

        const gameName = "Pierre-Feuille-Ciseaux";
        // Le "score" ici sera le nombre de victoires du joueur
        const score = playerScore; 
        const durationInSeconds = secondsElapsedPFC;

        const formData = new URLSearchParams();
        formData.append('game_name', gameName);
        formData.append('score', String(score));
        formData.append('duration', String(durationInSeconds));

        try {
            // Ajustez le chemin vers save.php si nécessaire. 
            // Si pierre_feuille_ciseaux.html et save.php sont dans le même dossier 'games',
            // et save.php est à la racine du dossier 'games':
            const response = await fetch('../games/save.php', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });

            const result = await response.json();

            if (result.success) {
                alert('Partie sauvegardée avec succès ! Vous pouvez consulter votre historique.');
                console.log('Partie sauvegardée:', result.message);
                // Réinitialiser le jeu et le timer pour une nouvelle session
                resetGame();
            } else {
                alert('Échec de la sauvegarde de la partie : ' + (result.error || 'Erreur inconnue'));
                console.error('Échec de la sauvegarde:', result.error);
                startTimerPFC(); // Redémarrer le timer si la sauvegarde échoue et que l'utilisateur veut continuer
            }
        } catch (error) {
            alert('Erreur de communication lors de la sauvegarde.');
            console.error('Erreur lors de l\'envoi des données:', error);
            startTimerPFC(); // Redémarrer le timer
        }
    });

    function resetGame() {
        playerScore = 0;
        computerScore = 0;
        playerScoreDisplay.textContent = '0';
        computerScoreDisplay.textContent = '0';
        playerChoiceDisplay.textContent = '-';
        computerChoiceDisplay.textContent = '-';
        roundResultDisplay.textContent = '-';
        roundResultDisplay.className = '';
        
        stopTimerPFC(); // S'assurer que le timer est arrêté
        secondsElapsedPFC = 0; // Réinitialiser le compteur
        updateTimerDisplayPFC(); // Mettre à jour l'affichage du timer à 00:00
        // Le timer redémarrera au prochain clic sur un choix
    }
    
    // Initialisation de l'affichage du timer
    updateTimerDisplayPFC();
});