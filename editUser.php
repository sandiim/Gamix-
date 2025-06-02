<?php
session_start();
require_once 'config/database.php';

// Configuration de la base de données
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

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}



// Récupérer les infos de l'utilisateur
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Récupérer les données actuelles de l'utilisateur avant traitement
try {
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die('Utilisateur non trouvé');
    }
} catch (PDOException $e) {
    die('Erreur de base de données: ' . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation des données
    if (empty($username) || empty($email)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (!empty($password) && strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères';
    } else {
        try {
            // Commencer la transaction
            $pdo->beginTransaction();
            
            // Vérifier si l'email ou le username existe déjà pour un autre utilisateur
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
            $check_stmt->execute([$email, $username, $user_id]);
            $existing_user = $check_stmt->fetch();
            
            if ($existing_user) {
                $error = 'Email ou nom d\'utilisateur déjà utilisé par un autre compte';
                $pdo->rollBack();
            } else {
                // Préparation de la requête de mise à jour
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $hashed_password, $user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $user_id]);
                }
                
                $pdo->commit();
                $success = 'Profil mis à jour avec succès!';
                
                // Mettre à jour la session
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
                // Recharger les données de l'utilisateur après mise à jour
                $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Erreur lors de la mise à jour: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gamix - Éditer le profil</title>
    <link rel="stylesheet" href="./css/forms.css" />
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            font-family: 'Orbitron', sans-serif;
        }

        .form-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            max-width: 600px;
            margin: 2rem auto;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .form-box h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .field {
            margin-bottom: 1.5rem;
        }

        .field label {
            display: block;
            margin-bottom: 0.5rem;
            color: #fff;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .field input[type="text"],
        .field input[type="email"],
        .field input[type="password"] {
            width: 100%;
            padding: 12px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .field input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            border-color: #4a90e2;
            box-shadow: 0 0 10px rgba(74, 144, 226, 0.3);
        }

        .field input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .btn-primary {
            background: linear-gradient(45deg, #4a90e2, #63b3ed);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #357abd, #4a90e2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
        }

        #password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            padding: 0.5rem;
            border-radius: 5px;
            text-align: center;
        }

        #password-strength.weak {
            background: rgba(255, 0, 0, 0.1);
            color: #ff4444;
        }

        #password-strength.medium {
            background: rgba(255, 165, 0, 0.1);
            color: #ffa500;
        }

        #password-strength.strong {
            background: rgba(0, 255, 0, 0.1);
            color: #00ff00;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin: 1rem 0;
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: #4a90e2;
        }

        .checkbox-container label {
            color: #fff;
            font-size: 0.9rem;
        }

        .main-header {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .main-footer {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            color: #fff;
            text-align: center;
            padding: 1rem;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        @media (max-width: 768px) {
            .form-box {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
   <header class="main-header">
    <nav class="nav-container">
      <div class="logo">
        <h1>Gamix</h1>
      </div>
      <ul class="nav-links">
        
           <li><a href="espaceUser.php">Accueil</a></li>
           <li><a href="historique.php">Historique de vos parties</a></li>
           <li><a href="editUser.php"class="active">Éditer profil</a></li>
         <li><a href="logout.php">Déconnexion</a></li>
       
      </ul>
    </nav>
  </header>

    <main>
        <div class="form-box">
            <!--alert-->
                <?php if ($error || $success): ?>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($error): ?>
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: '<?php echo addslashes($error); ?>',
                        confirmButtonColor: '#d33'
                    });
                <?php elseif ($success): ?>
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: '<?php echo addslashes($success); ?>',
                        confirmButtonColor: '#28a745',
                        timer: 2000,//apres 2s
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = 'espaceUser.php';
                    });
                <?php endif; ?>
            });
            </script>
        <?php endif; ?>

            <h2>Éditer votre profil</h2>
            <form method="post" action="editUser.php" id="edit-form">
                <div class="field">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required placeholder="Entrez votre nom d'utilisateur">
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder="Entrez votre email">
                </div>
                
                <div class="field">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" minlength="8" placeholder="Laissez vide pour ne pas changer">
                    <div id="password-strength"></div>
                </div>
                
                <div class="field">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" placeholder="Confirmez votre mot de passe">
                </div>
            
                <div class="checkbox-container">
                    <input type="checkbox" id="show-password" onclick="togglePasswordVisibility()">
                    <label for="show-password">Afficher le mot de passe</label>
                </div>
                
                <button type="submit" class="btn-primary">Mettre à jour le profil</button>
            </form>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="main-footer">
        <p>&copy; 2025 Gamix - Tous droits réservés</p>
    </footer>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById("password");
            const confirmInput = document.getElementById("confirm_password");
            const type = passwordInput.type === "password" ? "text" : "password";
            passwordInput.type = type;
            confirmInput.type = type;
        }

        function checkPasswordStrength() {
            const password = document.getElementById("password").value;
            const strengthText = document.getElementById("password-strength");

            if (password.length === 0) {
                strengthText.textContent = "";
                strengthText.className = "";
                return;
            }

            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            switch (strength) {
                case 0:
                case 1:
                    strengthText.textContent = "Force : faible";
                    strengthText.className = "weak";
                    break;
                case 2:
                case 3:
                    strengthText.textContent = "Force : moyenne";
                    strengthText.className = "medium";
                    break;
                case 4:
                    strengthText.textContent = "Force : forte";
                    strengthText.className = "strong";
                    break;
            }
        }

        function validateForm(event) {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const email = document.getElementById("email").value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(email)) {
                event.preventDefault();
                alert("Veuillez entrer une adresse email valide.");
                return false;
            }

            if (password !== confirmPassword) {
                event.preventDefault();
                alert("Les mots de passe ne correspondent pas.");
                return false;
            }

            if (password.length > 0 && password.length < 8) {
                event.preventDefault();
                alert("Le mot de passe doit contenir au moins 8 caractères.");
                return false;
            }

            return true;
        }

        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("edit-form");
            form.addEventListener("submit", validateForm);
            document.getElementById("password").addEventListener("input", checkPasswordStrength);
        });
    </script>
</body>
</html>