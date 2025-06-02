<?php
session_start();
$host = 'localhost';
$db   = 'gamix'; 
$user = 'root';          
$pass = '';               
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $commentaire = trim($_POST['commentaire'] ?? '');
    $note = isset($_POST['note']) ? (int)$_POST['note'] : null;

    $errors = [];

    // Validation
    if (empty($nom)) {
        $errors[] = "Le champ 'Nom' est requis.";
    }

    if (empty($prenom)) {
        $errors[] = "Le champ 'Prénom' est requis.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Une adresse email valide est requise.";
    }

    if (empty($commentaire)) {
        $errors[] = "Le commentaire ne peut pas être vide.";
    }

    if ($note === null || $note < 1 || $note > 5) {
        $errors[] = "Veuillez sélectionner une note entre 1 et 5.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (nom, prenom, email, commentaire, note, statut) VALUES (?, ?, ?, ?, ?, 'en_attente')");
            $stmt->execute([$nom, $prenom, $email, $commentaire, $note]);

            echo "<script>
                alert('Merci pour votre avis ! Il sera examiné avant publication.');
                window.location.href = 'index.html#feedback';
            </script>";
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo "<script>
                alert('Erreur lors de l\'enregistrement de votre feedback.');
                window.location.href = 'index.html#feedback';
            </script>";
            exit;
        }
    } else {
        // Affiche les erreurs avec alerte JS
        $message = implode('\\n', $errors);
        echo "<script>
            alert('Erreur:\\n{$message}');
            window.location.href = 'index.html#feedback';
        </script>";
        exit;
    }
} else {
    header("Location: index.html");
    exit;
}
?>
