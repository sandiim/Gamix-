<?php
session_start();
require 'config/database.php';

// Vérification des droits admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit();
}

// Connexion PDO
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=gamix;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les données d'un utilisateur pour l'édition
$edit_user = null;
if (isset($_GET['edit'])) {
    $userId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($userId) {
        $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $edit_user = $stmt->fetch();
    }
}

// Traitement du formulaire d'ajout d'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    // Validation
    $errors = [];
    if (!$email) $errors[] = "Email invalide";
    if (empty($username)) $errors[] = "Nom d'utilisateur requis";
    if (empty($password) && !isset($_POST['user_id'])) $errors[] = "Mot de passe requis";
    if (!empty($password) && strlen($password) < 8) $errors[] = "Le mot de passe doit faire au moins 8 caractères";
    if (!in_array($role, ['user', 'admin'])) $errors[] = "Rôle invalide";

    if (empty($errors)) {
        // Vérification doublon (sauf pour l'utilisateur en cours d'édition)
        $userId = $_POST['user_id'] ?? null;
        $params = [$email, $username];
        $sql = "SELECT COUNT(*) FROM users WHERE (email = ? OR username = ?)";
        
        if ($userId) {
            $sql .= " AND id != ?";
            $params[] = $userId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email ou nom d'utilisateur déjà utilisé";
        } else {
            // Insertion ou mise à jour
            if ($userId) {
                // Mise à jour
                if (!empty($password)) {
                    $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ?, password = ?, role = ? WHERE id = ?");
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $success = $stmt->execute([$email, $username, $hashedPassword, $role, $userId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ?, role = ? WHERE id = ?");
                    $success = $stmt->execute([$email, $username, $role, $userId]);
                }
                
                if ($success) {
                    $_SESSION['success_message'] = "Utilisateur mis à jour avec succès";
                    header('Location: manager.php');
                    exit;
                } else {
                    $errors[] = "Erreur lors de la mise à jour de l'utilisateur";
                }
            } else {
                // Insertion
                $stmt = $pdo->prepare("INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)");
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                if ($stmt->execute([$email, $username, $hashedPassword, $role])) {
                    $_SESSION['success_message'] = "Utilisateur ajouté avec succès";
                    header('Location: manager.php');
                    exit;
                } else {
                    $errors[] = "Erreur lors de l'ajout de l'utilisateur";
                }
            }
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['add_user_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header('Location: manager.php' . (isset($_POST['user_id']) ? '?edit=' . $_POST['user_id'] : ''));
        exit;
    }
}

// Traitement de la suppression d'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT);
    
    if ($userId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$userId])) {
                $_SESSION['success_message'] = "Utilisateur supprimé avec succès";
            } else {
                $_SESSION['error_message'] = "Erreur lors de la suppression";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "ID utilisateur invalide";
    }
    
    header('Location: manager.php');
    exit;
}

// Récupération des données statistiques
try {
    // Statistiques générales
    $stats = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM users WHERE role = 'admin') as total_admins,
            (SELECT COUNT(DISTINCT game_name) FROM game_history) as total_games,
            (SELECT COUNT(*) FROM game_history) as total_plays,
            (SELECT AVG(score) FROM game_history) as avg_score
    ")->fetch();

    // Liste des utilisateurs
    $users = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();

    // Derniers utilisateurs (pour le widget)
    $recent_users = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

    // Dernières parties
    $recent_games = $pdo->query("
        SELECT gh.*, u.username 
        FROM game_history gh
        JOIN users u ON gh.user_id = u.id
        ORDER BY played_at DESC 
        LIMIT 5
    ")->fetchAll();

    // Meilleurs scores
    $top_scores = $pdo->query("
        SELECT gh.*, u.username 
        FROM game_history gh
        JOIN users u ON gh.user_id = u.id
        ORDER BY score DESC 
        LIMIT 5
    ")->fetchAll();

    // Récupérer le nombre de feedbacks en attente
    $stmt = $pdo->query("SELECT COUNT(*) FROM feedback WHERE statut = 'en_attente'");
    $pending_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    die("Erreur de requête : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Gamix</title>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        
        
        .dashboard-container {
            max-width: 1400px;
            margin: 80px auto 2rem;
            padding: 0 1rem;
        }
        
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card h3 {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        .data-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin: 3rem 0;
        }
        
        @media (max-width: 768px) {
            .data-section {
                grid-template-columns: 1fr;
            }
        }
        
        .data-card {
            background: rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .data-card h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        th {
            color: var(--primary);
            font-weight: 500;
        }
        
        tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .dashboard-title {
            text-align: center;
            font-size: 2.5rem;
            margin: 1.5rem 0;
            background: linear-gradient(90deg, #0ff1ce, #ff4081);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 0 10px rgba(15, 241, 206, 0.3);
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
            padding: 0 1rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: #dc2626;
            color: white;
            border: none;
        }
        
        .btn-danger:hover {
            background-color: #b91c1c;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background-color: #d97706;
            color: white;
            border: none;
        }
        
        .btn-warning:hover {
            background-color: #b45309;
            transform: translateY(-2px);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: var(--bg-dark);
            padding: 2rem;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: var(--text-primary);
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.2);
            border-left: 4px solid #10b981;
            color: #10b981;
        }
        
        .alert-danger {
            background-color: rgba(220, 38, 38, 0.2);
            border-left: 4px solid #dc2626;
            color: #dc2626;
        }
        
        .tab-container {
            margin: 2rem 0;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .tab.active {
            border-bottom-color: var(--primary);
            color: var(--primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-admin {
            background-color: rgba(79, 70, 229, 0.2);
            color: var(--primary);
        }
        
        .badge-user {
            background-color: rgba(156, 163, 175, 0.2);
            color: var(--text-secondary);
        }
        
        .password-note {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        .feedback-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ef4444;
            color: white;
            border-radius: 9999px;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 1.5rem;
            text-align: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
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
                <li><a href="dasboard.php" class="active">Dashboard Admin</a></li>
                <li><a href="admin_feedback.php" class="feedback-link">
                    <i class="fas fa-comments"></i> Gestion des avis
                    <?php
                    if ($pending_count > 0): ?>
                        <span class="notification-badge"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="dashboard-container">
        <h1 class="dashboard-title">
            <i class="fas fa-tachometer-alt"></i> Dashboard Administrateur
        </h1>
        
        <!-- Messages d'alerte -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['add_user_errors'])): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($_SESSION['add_user_errors'] as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php unset($_SESSION['add_user_errors']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Bouton d'action -->
        <div class="flex justify-end mb-4">
            <button id="openModal" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un utilisateur
            </button>
        </div>
        
        <!-- Statistiques -->
        <div class="stat-grid">
            <div class="stat-card">
                <h3>Utilisateurs</h3>
                <div class="value"><?= htmlspecialchars($stats['total_users']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Administrateurs</h3>
                <div class="value"><?= htmlspecialchars($stats['total_admins']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Jeux différents</h3>
                <div class="value"><?= htmlspecialchars($stats['total_games']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Parties jouées</h3>
                <div class="value"><?= htmlspecialchars($stats['total_plays']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Score moyen</h3>
                <div class="value"><?= round($stats['avg_score'], 2) ?></div>
            </div>
            <div class="stat-card">
                <h3>Avis en attente</h3>
                <div class="value"><?= $pending_count ?></div>
            </div>
        </div>
        
        <!-- Onglets -->
        <div class="tab-container">
            <div class="tabs">
                <div class="tab active" data-tab="users">Utilisateurs</div>
                <div class="tab" data-tab="recent-games">Dernières parties</div>
                <div class="tab" data-tab="top-scores">Meilleurs scores</div>
            </div>
            
            <!-- Contenu des onglets -->
            <div class="tab-content active" id="users-tab">
                <div class="data-card">
                    <h2><i class="fas fa-users"></i> Liste des utilisateurs</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                                        <?= $user['role'] === 'admin' ? 'Admin' : 'User' ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                <td class="flex gap-2">
                                    <a href="?edit=<?= $user['id'] ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger" 
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="tab-content" id="recent-games-tab">
                <div class="data-card">
                    <h2><i class="fas fa-gamepad"></i> Dernières parties</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Joueur</th>
                                <th>Jeu</th>
                                <th>Score</th>
                                <th>Durée</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_games as $game): ?>
                            <tr>
                                <td><?= htmlspecialchars($game['username']) ?></td>
                                <td><?= htmlspecialchars($game['game_name']) ?></td>
                                <td><?= htmlspecialchars($game['score']) ?></td>
                                <td><?= gmdate("i:s", $game['duration']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($game['played_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="tab-content" id="top-scores-tab">
                <div class="data-card">
                    <h2><i class="fas fa-trophy"></i> Meilleurs scores</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Joueur</th>
                                <th>Jeu</th>
                                <th>Score</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_scores as $i => $score): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($score['username']) ?></td>
                                <td><?= htmlspecialchars($score['game_name']) ?></td>
                                <td><?= htmlspecialchars($score['score']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($score['played_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal d'ajout/modification d'utilisateur -->
    <div id="addUserModal" class="modal" style="<?= isset($edit_user) ? 'display: flex;' : '' ?>">
        <div class="modal-content">
            <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--primary);">
                <i class="fas fa-user-plus"></i> <?= isset($edit_user) ? 'Modifier utilisateur' : 'Ajouter un utilisateur' ?>
            </h2>
            
            <form method="POST" action="manager.php">
                <?php if (isset($edit_user)): ?>
                    <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?= isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : (isset($edit_user) ? htmlspecialchars($edit_user['email']) : '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" required
                           value="<?= isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : (isset($edit_user) ? htmlspecialchars($edit_user['username']) : '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           <?= !isset($edit_user) ? 'required' : '' ?> minlength="8">
                    <?php if (isset($edit_user)): ?>
                        <p class="password-note">Laisser vide pour ne pas modifier le mot de passe</p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
    <label for="role">Rôle</label>
    <select id="role" name="role" class="form-control" required>
        <?php
        $selectedRole = '';
        if (isset($_SESSION['form_data']['role'])) {
            $selectedRole = $_SESSION['form_data']['role'];
        } elseif (isset($edit_user)) {
            $selectedRole = $edit_user['role'];
        }
        ?>
        <option value="user" <?php echo ($selectedRole === 'user') ? 'selected' : ''; ?>>Utilisateur</option>
        <option value="admin" <?php echo ($selectedRole === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
    </select>
</div>
                
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" id="closeModal" class="btn" style="background: rgba(255,255,255,0.1);">
                        Annuler
                    </button>
                    <button type="submit" name="add_user" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= isset($edit_user) ? 'Mettre à jour' : 'Enregistrer' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php 
    // Nettoyer les données de formulaire en session après utilisation
    if (isset($_SESSION['form_data'])) {
        unset($_SESSION['form_data']);
    }
    ?>

    <script>
        // Scripts pour animations/interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animation des cartes statistiques
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, i) => {
                card.style.transitionDelay = `${i * 0.1}s`;
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 300 + (i * 100));
            });
            
            // Gestion des onglets
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Désactiver tous les onglets
                    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                    
                    // Activer l'onglet cliqué
                    tab.classList.add('active');
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });
            
            // Gestion du modal
            const modal = document.getElementById('addUserModal');
            const openBtn = document.getElementById('openModal');
            const closeBtn = document.getElementById('closeModal');
            
            if (openBtn) {
                openBtn.addEventListener('click', () => {
                    modal.style.display = 'flex';
                });
            }
            
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    modal.style.display = 'none';
                    // Retirer le paramètre edit de l'URL
                    if (window.location.search.includes('edit')) {
                        window.history.pushState({}, document.title, window.location.pathname);
                    }
                });
            }
            
            window.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    // Retirer le paramètre edit de l'URL
                    if (window.location.search.includes('edit')) {
                        window.history.pushState({}, document.title, window.location.pathname);
                    }
                }
            });
            
            // Ouvrir automatiquement le modal si on est en mode édition
            if (window.location.search.includes('edit')) {
                modal.style.display = 'flex';
            }
        });
    </script>
</body>
</html>