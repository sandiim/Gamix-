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

// Traitement des actions sur les feedbacks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['feedback_id'])) {
        $feedback_id = filter_var($_POST['feedback_id'], FILTER_VALIDATE_INT);
        $action = $_POST['action'];

        if ($feedback_id) {
            try {
                switch ($action) {
                    case 'approve':
                        $stmt = $pdo->prepare("UPDATE feedback SET statut = 'approuve' WHERE id = ?");
                        $stmt->execute([$feedback_id]);
                        $_SESSION['success_message'] = "Feedback approuvé avec succès";
                        break;
                    case 'delete':
                        $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
                        $stmt->execute([$feedback_id]);
                        $_SESSION['success_message'] = "Feedback supprimé avec succès";
                        break;
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Erreur lors du traitement du feedback";
            }
        }
    }
    header('Location: admin_feedback.php');
    exit();
}

// Récupération des feedbacks
try {
    $feedbacks = $pdo->query("
        SELECT * FROM feedback 
        ORDER BY 
            CASE statut 
                WHEN 'en_attente' THEN 1 
                WHEN 'approuve' THEN 2 
                ELSE 3 
            END,
            date_soumission DESC
    ")->fetchAll();
} catch (PDOException $e) {
    die("Erreur de requête : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Avis - Gamix</title>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .feedback-container {
            max-width: 1200px;
            margin: 80px auto 2rem;
            padding: 0 1rem;
        }

        .feedback-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .feedback-card:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-2px);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .feedback-info {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .feedback-actions {
            display: flex;
            gap: 0.5rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: rgba(234, 179, 8, 0.2);
            color: #eab308;
        }

        .status-approved {
            background-color: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-approve {
            background-color: #22c55e;
            color: white;
        }

        .btn-approve:hover {
            background-color: #16a34a;
        }

        .btn-delete {
            background-color: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background-color: #dc2626;
        }

        .rating {
            color: #eab308;
            font-size: 1.25rem;
        }

        .feedback-content {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .feedback-meta {
            display: flex;
            gap: 1rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
            margin-top: 0.5rem;
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
                <li><a href="dasboard.php">Dashboard Admin</a></li>
                <li><a href="admin_feedback.php" class="active">
                    <i class="fas fa-comments"></i> Gestion des avis
                </a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="feedback-container">
        <h1 class="dashboard-title">
            <i class="fas fa-comments"></i> Gestion des Avis
        </h1>

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

        <div class="feedback-list">
            <?php foreach ($feedbacks as $feedback): ?>
                <div class="feedback-card">
                    <div class="feedback-header">
                        <div class="feedback-info">
                            <div class="status-badge <?= $feedback['statut'] === 'en_attente' ? 'status-pending' : 'status-approved' ?>">
                                <?= $feedback['statut'] === 'en_attente' ? 'En attente' : 'Approuvé' ?>
                            </div>
                            <div class="rating">
                                <?php for ($i = 0; $i < $feedback['note']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php if ($feedback['statut'] === 'en_attente'): ?>
                            <div class="feedback-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="feedback_id" value="<?= $feedback['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-approve">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="feedback_id" value="<?= $feedback['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="feedback-content">
                        <p class="font-semibold"><?= htmlspecialchars($feedback['prenom']) ?> <?= htmlspecialchars($feedback['nom']) ?></p>
                        <p class="text-gray-300 mt-2"><?= nl2br(htmlspecialchars($feedback['commentaire'])) ?></p>
                        <div class="feedback-meta">
                            <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($feedback['email']) ?></span>
                            <span><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($feedback['date_soumission'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        // Animation des cartes au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.feedback-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html> 