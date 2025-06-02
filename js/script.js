const gameCards = document.querySelectorAll(".card"); // Renommé pour clarté
const timerDisplay = document.getElementById("timer-display"); // Élément pour afficher le minuteur

let cardOne, cardTwo;
let matchedPairs = 0; // Renommé matcheGame pour clarté
let disableDeck = false; // Pour empêcher les clics pendant les animations ou la vérification

let timerInterval;
let secondsElapsed = 0;

// --- Fonctions du chronomètre ---
function updateTimerDisplay() {
    if (timerDisplay) {
        const minutes = Math.floor(secondsElapsed / 60);
        const seconds = secondsElapsed % 60;
        timerDisplay.textContent = `${String(minutes).padStart(2, '0')} min ${String(seconds).padStart(2, '0')} sec`;
    }
}

function startTimer() {
    stopTimer(); // Arrête tout chronomètre existant
    secondsElapsed = 0;
    updateTimerDisplay(); // Initialise l'affichage à 00:00
    timerInterval = setInterval(() => {
        secondsElapsed++;
        updateTimerDisplay();
    }, 1000);
}

function stopTimer() {
    clearInterval(timerInterval);
}

// --- Fonction pour sauvegarder le résultat du jeu ---
async function saveGameResult(durationInSeconds) {
    const gameName = "Memory Game"; // Nom du jeu
    const score = 100; // Score fixe pour une victoire (vous pouvez le rendre dynamique)

    // Préparation des données à envoyer
    // FormData est pratique pour simuler un envoi de formulaire
    const formData = new URLSearchParams();
    formData.append('game_name', gameName);
    formData.append('score', String(score)); // Le script PHP attend des chaînes pour $_POST
    formData.append('duration', String(durationInSeconds));

    try {
        // Le chemin vers save.php doit être correct par rapport à l'emplacement du fichier HTML du jeu.
        // Si memory.html et save.php sont dans le même dossier, 'save.php' est correct.
        const response = await fetch('../games/save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded', // Type de contenu pour URLSearchParams
            },
            body: formData.toString() // Convertit les données en chaîne de requête
        });

        const result = await response.json(); // save.php renvoie du JSON

        if (result.success) {
            console.log('Partie sauvegardée avec succès:', result.message);
            // Optionnel: Afficher un message à l'utilisateur ou rediriger
            // alert('Partie sauvegardée ! Vous pouvez consulter votre historique.');
        } else {
            console.error('Échec de la sauvegarde de la partie:', result.error);
            // alert('Erreur lors de la sauvegarde : ' + result.error);
        }
    } catch (error) {
        console.error('Erreur lors de l\'envoi des données de la partie:', error);
        // alert('Erreur de communication avec le serveur.');
    }
}


// --- Logique du jeu ---
function flipCard(e) {
    if (disableDeck) return; // Si le plateau est désactivé, ne rien faire

    // S'assurer que clickedCard est bien l'élément LI avec la classe .card
    const clickedCard = e.target.closest('.card');
    if (!clickedCard) return; // Si le clic n'était pas sur une carte ou son contenu pertinent

    // Si la carte cliquée n'est pas la première carte et n'est pas déjà retournée
    if (clickedCard !== cardOne && !clickedCard.classList.contains("flip")) {
        clickedCard.classList.add("flip");

        if (!cardOne) { // Si c'est la première carte cliquée
            cardOne = clickedCard;
            return;
        }
        // Si c'est la deuxième carte cliquée
        cardTwo = clickedCard;
        disableDeck = true; // Désactiver le plateau pendant la vérification

        let cardOneImg = cardOne.querySelector(".back img").src;
        let cardTwoImg = cardTwo.querySelector(".back img").src;
        matchCards(cardOneImg, cardTwoImg); // Renommé matchGame pour clarté
    }
}

function matchCards(img1, img2) { // Renommé de matchGame
    if (img1 === img2) { // Si les images correspondent
        matchedPairs++;
        if (matchedPairs === 8) { // Toutes les paires trouvées, fin de la partie
            stopTimer(); // Arrêter le chronomètre
            console.log(`Partie terminée en ${secondsElapsed} secondes.`);

            // Sauvegarder le résultat AVANT de mélanger pour une nouvelle partie
            saveGameResult(secondsElapsed).then(() => {
                // Mettre un délai pour que le joueur voie la dernière paire et le message de sauvegarde (via console)
                setTimeout(() => {
                    shuffleCards(); // Mélanger pour une nouvelle partie (ceci redémarrera aussi le timer)
                }, 1500); // Délai de 1.5s
            });
            return; // Sortir de la fonction ici pour éviter la logique "non-match"
        }
        // Les cartes correspondent, mais la partie n'est pas finie
        cardOne.removeEventListener("click", flipCard);
        cardTwo.removeEventListener("click", flipCard);
        cardOne = cardTwo = ""; // Réinitialiser pour le prochain tour
        disableDeck = false; // Réactiver le plateau
        return;
    }

    // Si les images ne correspondent pas
    setTimeout(() => {
        cardOne.classList.add("shake");
        cardTwo.classList.add("shake");
    }, 400);

    setTimeout(() => {
        cardOne.classList.remove("shake", "flip");
        cardTwo.classList.remove("shake", "flip");
        cardOne = cardTwo = ""; // Réinitialiser pour le prochain tour
        disableDeck = false; // Réactiver le plateau
    }, 1200);
}

function shuffleCards() { // Renommé de suffleCard
    matchedPairs = 0;
    cardOne = cardTwo = "";
    disableDeck = false;

    startTimer(); // Démarrer/Réinitialiser le chronomètre pour la nouvelle partie

    // Créer un tableau d'images (8 paires)
    let arr = [1, 2, 3, 4, 5, 6, 7, 8, 1, 2, 3, 4, 5, 6, 7, 8];
    arr.sort(() => Math.random() > 0.5 ? 1 : -1); // Mélanger le tableau

    gameCards.forEach((card, i) => {
        card.classList.remove("flip"); // S'assurer que toutes les cartes sont face cachée
        
        let imgTag = card.querySelector(".back img");
        // Le chemin des images doit être correct par rapport à l'emplacement du fichier HTML.
        // Si votre HTML est dans `games/memory/index.html` et vos images dans `assets/images/`,
        // et que `script.js` est dans `js/`, alors `../assets/images/` est correct.
        imgTag.src = `../assets/images/img${arr[i]}.jpeg`;
        
        // Supprimer l'ancien écouteur d'événement pour éviter les duplications si shuffleCards est appelé plusieurs fois
        card.removeEventListener("click", flipCard);
        card.addEventListener("click", flipCard); // Ajouter l'écouteur d'événement
    });
}

// Initialisation du jeu
if (timerDisplay) { // Initialiser l'affichage du timer au chargement
    updateTimerDisplay();
}
shuffleCards(); // Mélanger les cartes et démarrer la première partie (ce qui inclut le démarrage du timer)

// La boucle forEach initiale pour ajouter les écouteurs d'événements n'est plus nécessaire ici,
// car shuffleCards() s'en occupe déjà.
// gameCards.forEach(card => {
//    card.addEventListener("click", flipCard);
// });