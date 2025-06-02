<?php
session_start(); // Une seule fois au début

// Rediriger si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Ou login.php, selon votre structure
    exit;
}

// require 'config/database.php'; // Décommentez si vous avez un fichier de config pour $pdo
// Si vous n'utilisez pas de fichier de config centralisé pour $pdo:
$host = 'localhost';  
$db   = 'gamix';
$user = 'root';       
$pass = '';           
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$histories = []; // Initialiser au cas où il y aurait une erreur

try {
    $pdo = new PDO($dsn, $user, $pass, $options); // Établir la connexion ici
    
    $stmt = $pdo->prepare("SELECT game_name, played_at, duration, score 
                          FROM game_history 
                          WHERE user_id = ? 
                          ORDER BY played_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $histories = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("ERREUR DB (historique.php): " . $e->getMessage());
    
}

// Fonction pour formater la durée, à garder
function formatDuration($seconds) {
    if (!is_numeric($seconds) || $seconds < 0) {
        return "N/A";
    }
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    return sprintf("%02d min %02d sec", $minutes, $remainingSeconds);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Historique - Gamix</title>
  <link rel="stylesheet" href="../css/style.css" /> <!-- Assurez-vous que ce chemin est correct -->
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
   <header class="main-header">
    <nav class="nav-container">
      <div class="logo">
        <h1>Gamix</h1>
      </div>
      <ul class="nav-links">
           <li><a href="../espaceUser.php">Accueil</a></li>
           <li><a href="historique.php" class="active">Historique de vos parties</a></li>
           <li><a href="../editUser.php">Éditer profil</a></li>
           <li><a href="../logout.php">Déconnexion</a></li>
      </ul>
    </nav>
  </header>
  
  <div class="my-8"></div>
<section class="max-w-5xl mx-auto px-4 py-12" id="historique">
  <h2 class="text-4xl font-bold mb-8 text-center text-[#0ff1ce] font-orbitron">🕹️ Historique de vos parties</h2>
  
  <div class="overflow-x-auto rounded-xl shadow-lg ring-1 ring-gray-200">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-indigo-600 text-white uppercase text-xs tracking-wider">
        <tr>
          <th scope="col" class="px-6 py-4">Nom du Jeu</th>
          <th scope="col" class="px-6 py-4">Date</th>
          <th scope="col" class="px-6 py-4">Durée</th>
          <th scope="col" class="px-6 py-4">Score</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 bg-white">
        <?php if (!empty($histories)): ?>
          <?php foreach ($histories as $history): ?>
            <tr class="hover:bg-indigo-50 transition-colors">
              <td class="px-6 py-4 font-semibold text-gray-800">
                <?= htmlspecialchars($history['game_name'] ?? 'N/A') ?>
              </td>
              <td class="px-6 py-4 text-gray-600">
                <?php 
                  try {
                      // Vérifier si played_at n'est pas null et est une date valide
                      if (!empty($history['played_at'])) {
                          $date = new DateTime($history['played_at']);
                          echo $date->format('d/m/Y H:i');
                      } else {
                          echo 'N/A';
                      }
                  } catch (Exception $e) {
                      echo 'Date invalide'; // Gérer les dates malformées
                  }
                ?>
              </td>
              <td class="px-6 py-4 text-gray-600">
               <?= formatDuration($history['duration'] ?? 0) ?>
              </td>
              <td class="px-6 py-4 text-blue-600 font-semibold">
                <?= htmlspecialchars($history['score'] ?? 'N/A') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
              Aucun historique pour le moment. Jouez à un jeu pour en créer !
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
</body>
</html>