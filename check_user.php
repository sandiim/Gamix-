<?php
$host = 'localhost';
$db = 'gamix';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['error' => "Erreur de connexion à la base de données."]);
    exit;
}

$email = $_GET['email'] ?? '';
$username = $_GET['username'] ?? '';

if (!$email || !$username) {
    echo json_encode(['error' => 'Email et nom d’utilisateur requis']);
    exit;
}

// Vérifier si email ou username existe
$stmt = $pdo->prepare("SELECT email, username FROM users WHERE email = ? OR username = ?");
$stmt->execute([$email, $username]);
$existing = $stmt->fetchAll();

$response = ['emailExists' => false, 'usernameExists' => false];

foreach ($existing as $user) {
    if ($user['email'] === $email) {
        $response['emailExists'] = true;
    }
    if ($user['username'] === $username) {
        $response['usernameExists'] = true;
    }
}

echo json_encode($response);
