// Attend que le DOM soit entièrement chargé et analysé avant d'exécuter le code du jeu.
document.addEventListener('DOMContentLoaded', () => {
    console.log("MOSQUITO.JS: DOM entièrement chargé et parsé.");

    // Éléments du DOM - Récupérés après le chargement du DOM
    const gameArea = document.getElementById('game-area');
    const scoreDisplay = document.getElementById('score');
    const timeDisplay = document.getElementById('time');
    const mosquitoCountDisplay = document.getElementById('mosquito-count');
    const startButton = document.getElementById('startGame');
    const crosshair = document.getElementById('crosshair');
    const saveStatusMessage = document.getElementById('save-status-message');

    // Vérifications initiales des éléments DOM essentiels
    if (!gameArea) console.error("MOSQUITO.JS: Élément #game-area NON TROUVÉ !");
    if (!scoreDisplay) console.error("MOSQUITO.JS: Élément #score NON TROUVÉ !");
    if (!timeDisplay) console.error("MOSQUITO.JS: Élément #time NON TROUVÉ !");
    if (!mosquitoCountDisplay) console.error("MOSQUITO.JS: Élément #mosquito-count NON TROUVÉ !");
    if (!startButton) console.error("MOSQUITO.JS: Bouton #startGame NON TROUVÉ !");
    if (!crosshair) console.error("MOSQUITO.JS: Élément #crosshair NON TROUVÉ !");
    // saveStatusMessage est optionnel pour le fonctionnement du jeu, mais utile pour les messages
    if (!saveStatusMessage) console.warn("MOSQUITO.JS: Élément #save-status-message non trouvé (pour les messages de sauvegarde).");


    // Variables du jeu
    let score = 0;
    let gameInterval; // Timer pour la création des moustiques
    let countdownTimer; // Timer pour le compte à rebours du temps
    let timeLeft = 60;
    const GAME_DURATION = 60;
    let isGameRunning = false;
    let mosquitoCount = 0;
    let gameStartTime;

    // --- Initialisation des écouteurs d'événements ---
    if (crosshair) {
        document.addEventListener('mousemove', (e) => {
            crosshair.style.left = e.clientX + 'px';
            crosshair.style.top = e.clientY + 'px';
        });
        crosshair.style.display = 'none'; // Caché par défaut
    }

    if (startButton) {
        startButton.addEventListener('click', startGame);
        console.log("MOSQUITO.JS: Écouteur d'événement ajouté au bouton Démarrer.");
    } else {
        // Si le bouton n'est pas trouvé, le jeu ne pourra pas démarrer par clic.
        // L'erreur aura déjà été loguée ci-dessus.
    }

    // --- Fonctions du jeu ---
    async function saveGameResult(gameName, gameScore, gameDuration) {
        console.log("MOSQUITO.JS: saveGameResult appelée avec:", gameName, gameScore, gameDuration);
        
        if (typeof IS_USER_LOGGED_IN === 'undefined') {
            console.error("MOSQUITO.JS: Variable IS_USER_LOGGED_IN n'est pas définie. Vérifiez le script PHP dans le HTML.");
            if (saveStatusMessage) {
                saveStatusMessage.textContent = 'Erreur de configuration (connexion).';
                saveStatusMessage.style.color = 'red';
            }
            return;
        }

        if (!IS_USER_LOGGED_IN) {
            console.log("MOSQUITO.JS: Utilisateur non connecté. Score non sauvegardé.");
            if (saveStatusMessage) {
                saveStatusMessage.textContent = 'Connectez-vous pour sauvegarder vos scores.';
                saveStatusMessage.style.color = 'orange';
            }
            return;
        }

        const formData = new FormData();
        formData.append('game_name', gameName);
        formData.append('score', gameScore);
        formData.append('duration', gameDuration);
        
        // !!! VÉRIFIEZ ATTENTIVEMENT CE CHEMIN !!!
        // Si chasseur_moustiques.php est à la racine : 'php/save.php'
        // Si chasseur_moustiques.php est dans un dossier (ex: jeux/) : '../php/save.php'
        const saveUrl = 'save.php'; 

        try {
            const absoluteUrl = new URL(saveUrl, window.location.href).href;
            console.log(`MOSQUITO.JS: Tentative de sauvegarde vers : ${absoluteUrl}`);
            const response = await fetch(saveUrl, { method: 'POST', body: formData });
            const result = await response.json();

            if (saveStatusMessage) saveStatusMessage.textContent = ''; 

            if (response.ok && result.success) {
                console.log('MOSQUITO.JS: Résultat sauvegardé avec succès:', result.message);
                // Message de succès discret ou absent comme demandé
            } else {
                let userMessage = `Échec de la sauvegarde: ${result ? result.error : `Erreur HTTP ${response.status}`}`;
                console.error('MOSQUITO.JS: Échec de la sauvegarde -', userMessage);
                if (saveStatusMessage) {
                    saveStatusMessage.textContent = userMessage;
                    saveStatusMessage.style.color = 'red';
                }
            }
        } catch (error) {
            console.error('MOSQUITO.JS: Erreur lors de la sauvegarde (réseau/parsing JSON):', error);
            if (saveStatusMessage) {
                saveStatusMessage.textContent = 'Erreur réseau ou serveur lors de la sauvegarde.';
                saveStatusMessage.style.color = 'red';
            }
        }
    }

    function startGame() {
        console.log("MOSQUITO.JS: startGame appelée.");
        if (isGameRunning) {
            console.warn("MOSQUITO.JS: Jeu déjà en cours, tentative de démarrage ignorée.");
            return;
        }
        // Vérification que les éléments DOM nécessaires pour le jeu sont bien présents
        if (!gameArea || !scoreDisplay || !timeDisplay || !mosquitoCountDisplay || !startButton || !crosshair) {
            console.error("MOSQUITO.JS: Un ou plusieurs éléments DOM essentiels sont manquants. Démarrage annulé.");
            alert("Erreur critique : Impossible de démarrer le jeu. Vérifiez la console pour les détails (F12).");
            return;
        }
        
        score = 0;
        timeLeft = GAME_DURATION;
        mosquitoCount = 0;
        gameStartTime = new Date(); 

        scoreDisplay.textContent = 'Score: 0';
        timeDisplay.textContent = `Temps: ${timeLeft}s`;
        mosquitoCountDisplay.textContent = 'Moustiques: 0';
        gameArea.innerHTML = ''; // Nettoyer l'aire de jeu
        if(saveStatusMessage) saveStatusMessage.textContent = ''; 
        
        isGameRunning = true;
        startButton.disabled = true;
        crosshair.style.display = 'block'; 
        
        console.log("MOSQUITO.JS: Jeu démarré. Lancement des timers.");
        // Timer pour la création des moustiques
        gameInterval = setInterval(() => {
            if (!isGameRunning) { // Sécurité pour arrêter la création si le jeu est terminé entre-temps
                clearInterval(gameInterval);
                return;
            }
            const count = Math.floor(Math.random() * 2) + 1; // 1 à 2 moustiques
            for (let i = 0; i < count; i++) {
                createMosquito();
            }
        }, 1200); // Fréquence d'apparition
        
        // Timer pour le compte à rebours du temps
        countdownTimer = setInterval(() => {
            if (!isGameRunning) { 
                clearInterval(countdownTimer);
                return;
            }
            timeLeft--;
            timeDisplay.textContent = `Temps: ${timeLeft}s`;
            
            if (timeLeft <= 0) {
                console.log("MOSQUITO.JS: Temps écoulé.");
                endGame(); // endGame s'occupera d'arrêter les timers et isGameRunning
            }
        }, 1000);
    }

    function createMosquito() {
        if (!isGameRunning || !gameArea) return; 

        const mosquito = document.createElement('div');
        mosquito.className = 'mosquito';
        
        const gameRect = gameArea.getBoundingClientRect();
        if (gameRect.width <= 0 || gameRect.height <= 0) {
            // console.warn("MOSQUITO.JS: gameArea a des dimensions invalides pour créer un moustique:", gameRect);
            return; 
        }

        const mosquitoSize = 40; 
        const areaWidth = gameRect.width > mosquitoSize ? gameRect.width - mosquitoSize : 1;
        const areaHeight = gameRect.height > mosquitoSize ? gameRect.height - mosquitoSize : 1;

        const x = Math.random() * areaWidth;
        const y = Math.random() * areaHeight;
        
        mosquito.style.left = x + 'px';
        mosquito.style.top = y + 'px';
        
        mosquitoCount++;
        if (mosquitoCountDisplay) mosquitoCountDisplay.textContent = `Moustiques: ${mosquitoCount}`;
        
        mosquito.onclick = () => {
            if (!isGameRunning || mosquito.classList.contains('dead')) return;
            
            score += 10;
            if (scoreDisplay) scoreDisplay.textContent = `Score: ${score}`;
            mosquito.classList.add('dead');
            
            setTimeout(() => {
                mosquito.classList.add('landed');
                setTimeout(() => {
                    mosquito.classList.add('fade');
                    setTimeout(() => {
                        if (mosquito && mosquito.parentNode) {
                            mosquito.remove();
                            mosquitoCount--;
                            if (mosquitoCountDisplay) mosquitoCountDisplay.textContent = `Moustiques: ${Math.max(0, mosquitoCount)}`;
                        }
                    }, 500); // Durée de dissipation
                }, 1000); // Temps au sol
            }, 300); // Durée de chute
        };
        
        gameArea.appendChild(mosquito);
        
        // Faire disparaître le moustique s'il n'est pas tué
        setTimeout(() => {
            if (isGameRunning && mosquito && !mosquito.classList.contains('dead') && mosquito.parentNode) {
                mosquito.remove();
                mosquitoCount--;
                if (mosquitoCountDisplay) mosquitoCountDisplay.textContent = `Moustiques: ${Math.max(0, mosquitoCount)}`;
            }
        }, 2800); // Temps avant disparition si non cliqué
    }

    function endGame() {
        console.log("MOSQUITO.JS: endGame appelée.");
        if (!isGameRunning && timeLeft > 0) {
            // Si le jeu était déjà arrêté et qu'il restait du temps (ex: arrêt manuel non implémenté), ne rien faire.
            // Ou si endGame est appelé plusieurs fois par erreur.
            console.warn("MOSQUITO.JS: endGame appelée alors que le jeu n'est pas en cours ou pas terminé par le temps.");
           // return; // On peut choisir de sortir si on ne veut pas de sauvegarde multiple
        }

        isGameRunning = false; // État principal pour arrêter toute action de jeu
        
        // Arrêter les timers
        if(gameInterval) clearInterval(gameInterval);
        if(countdownTimer) clearInterval(countdownTimer);
        
        if (startButton) startButton.disabled = false;
        if (crosshair) crosshair.style.display = 'none'; 
        
        let actualDuration = GAME_DURATION;
        if (gameStartTime) {
            const gameEndTime = new Date();
            actualDuration = Math.round((gameEndTime - gameStartTime) / 1000);
        }

        // L'alerte peut être agaçante pour le débogage, mais on la garde pour l'instant
        alert(`Temps écoulé ! Votre score final est ${score}`);
        
        saveGameResult("Chasseur de Moustiques", score, actualDuration);
    }

}); // Fin de DOMContentLoaded