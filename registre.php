<?php
// Paramètres de connexion
$host = 'localhost';  
$db   = 'gamix';
$user = 'root';       
$pass = '';           
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // exceptions sur erreurs
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!$email) {
        die("Email invalide.");
    }
    if (empty($username) || empty($password)) {
        die("Tous les champs sont obligatoires.");
    }

    // Vérifier si email ou username existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        die("L'email ou le nom d'utilisateur est déjà utilisé.");
    }

    // Hasher le mot de passe
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insertion dans la base
    $stmt = $pdo->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
    $result = $stmt->execute([$email, $username, $passwordHash]);

    if ($result) {
        
        header('Location: login.html?register=success');
        exit;
    } else {
        echo "Erreur lors de l'inscription.";
    }
}
?>
