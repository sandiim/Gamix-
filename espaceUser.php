<?php
session_start();
require 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
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
// Récupérer les informations de l'utilisateur depuis la base de données
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Mettre à jour le rôle dans la session
if ($user) {
    $_SESSION['role'] = $user['role'] ?? 'user'; // Utilise 'user' comme valeur par défaut si role est NULL
} else {
    $_SESSION['role'] = 'user'; // Valeur par défaut si l'utilisateur n'est pas trouvé
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Espace Utilisateur - Gamix</title>
  <link rel="stylesheet" href="./css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

  <!-- Navbar -->
  <header class="main-header">
    <nav class="nav-container">
      <div class="logo">
        <h1>Gamix</h1>
      </div>
      <ul class="nav-links">
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="#hero" class='active'>Accueil</a></li>
         <li><a href="logout.php">Déconnexion</a></li>
        <?php else: ?>
           <li><a href="#hero" class='active'>Accueil</a></li>
           <li><a href="games.php">Jeux</a></li>
           <li><a href="./games/historique.php">Historique de vos parties</a></li>
           <li><a href="editUser.php">Éditer profil</a></li>
         <li><a href="logout.php">Déconnexion</a></li>
       
        <?php endif; ?>
      </ul>
    </nav>
  </header>

  <!-- Section de bienvenue -->
  <main class="hero" id="hero" style="margin-top: 120px;">
    <div class="hero-content">
      <h2>Bienvenue <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>
      
 <?php if ($_SESSION['role'] === 'admin'): ?>
      <p> Vous avez accès au <strong>tableau de bord administrateur</strong>.<br>
                Gérez les utilisateurs et consultez les statistiques.</p>
    <a href="dasboard.php" class="cta-button">Accéder au Dashboard</a>
<?php else: ?>
  <p>Ceci est votre espace personnel sécurisé sur Gamix Arcade.<br>Amusez-vous avec nos jeux exclusifs !</p>
    <a href="games.php" class="cta-button">Découvrir les jeux</a>


    </div>
  </main>

   <!-- Footer -->
  <footer class="main-footer">
    <p>&copy; 2025 Gamix - Tous droits réservés</p>
  </footer>
<?php endif; ?>
 

</body>
</html>
