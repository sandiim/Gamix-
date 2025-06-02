<?php
// 1. Démarrer la session EN PREMIER
session_start();

// 2. Définir le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// 3. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Journaliser l'erreur côté serveur pour le débogage
    error_log("save.php: Tentative de sauvegarde de score sans user_id en session.");
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté. Veuillez vous connecter pour sauvegarder votre score.']);
    exit;
}

// 4. Vérifier si la méthode de requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("save.php: Tentative d'accès avec une méthode non-POST: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'error' => 'Méthode de requête non autorisée.']);
    exit;
}

// 5. Récupérer et valider les données POST
$user_id = $_SESSION['user_id'];
$game_name = $_POST['game_name'] ?? null;
$score_str = $_POST['score'] ?? null;
$duration_str = $_POST['duration'] ?? null;

if (empty($game_name) || $score_str === null || $duration_str === null) {
    error_log("save.php: Données POST manquantes. Reçu: " . print_r($_POST, true));
    echo json_encode(['success' => false, 'error' => 'Données de jeu manquantes ou invalides (nom, score, durée).']);
    exit;
}

// Convertir score et durée en nombres et valider
$score = filter_var($score_str, FILTER_VALIDATE_INT);
$duration = filter_var($duration_str, FILTER_VALIDATE_INT);

if ($score === false || $duration === false || $score < 0 || $duration < 0) {
    error_log("save.php: Score ou durée invalide. Score reçu: '$score_str', Durée reçue: '$duration_str'");
    echo json_encode(['success' => false, 'error' => 'Format de score ou de durée invalide.']);
    exit;
}

// 6. Configuration de la base de données
$host = 'localhost';  
$db   = 'gamix'; // Assurez-vous que c'est le bon nom de base de données
$user = 'root';       
$pass = ''; // Mettez votre mot de passe si vous en avez un
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// 7. Tenter la connexion et l'insertion
try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $stmt = $pdo->prepare("INSERT INTO game_history (user_id, game_name, score, duration, played_at) 
                           VALUES (?, ?, ?, ?, NOW())");
    
    if ($stmt->execute([$user_id, $game_name, $score, $duration])) {
        echo json_encode(['success' => true, 'message' => 'Historique de jeu sauvegardé avec succès.']);
    } else {
        // Ce cas est moins probable avec ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        error_log("save.php: PDO execute() a retourné false sans lever d'exception.");
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'exécution de la requête de sauvegarde.']);
    }

} catch (PDOException $e) {
    // Journaliser l'erreur réelle de la base de données côté serveur
    error_log("save.php ERREUR DB: " . $e->getMessage() . " | Query: INSERT INTO game_history... avec user_id=$user_id, game_name=$game_name, score=$score, duration=$duration");
    
    // Envoyer une erreur générique au client
    echo json_encode(['success' => false, 'error' => 'Erreur de base de données lors de la sauvegarde du score. Veuillez contacter l\'administrateur si le problème persiste.']);
}
?>