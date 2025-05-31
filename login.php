<?php
session_start();
require_once 'config/database.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        // Préparer la requête pour vérifier l'utilisateur
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($user && password_verify($password, $user['password'])) {
            // Créer la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            // Rediriger vers la page d'accueil
            header("Location: espaceUser.php");
            exit();
        } else {
            // Rediriger vers la page de connexion avec un message d'erreur
            header("Location: login.html?error=invalid");
            exit();
        }
    } catch(PDOException $e) {
        // En cas d'erreur, rediriger avec un message d'erreur
        header("Location: login.html?error=system");
        exit();
    }
} else {
    // Si quelqu'un essaie d'accéder directement à ce fichier
    header("Location: login.html");
    exit();
}
?> 