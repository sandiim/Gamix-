<?php
require 'config/database.php';

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

    // Récupération des feedbacks approuvés
    $stmt = $pdo->query("
        SELECT * FROM feedback 
        WHERE statut = '' 
        ORDER BY date_soumission DESC 
        LIMIT 5
    ");
    $feedbacks = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avis des Joueurs - Gamix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .approved-feedback-section {
            padding: 2rem;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
        }

        .approved-feedback-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .approved-feedback-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .feedback-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 1rem;
        }

        .feedback-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .feedback-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .feedback-author {
            font-weight: 600;
            font-size: 1.1rem;
            color: #4a90e2;
        }

        .rating {
            color: #eab308;
            font-size: 1.25rem;
        }

        .feedback-content {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
            margin: 1rem 0;
        }

        .feedback-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .feedback-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .feedback-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animation pour les cartes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .feedback-card {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
        }

        .feedback-card:nth-child(1) { animation-delay: 0.1s; }
        .feedback-card:nth-child(2) { animation-delay: 0.2s; }
        .feedback-card:nth-child(3) { animation-delay: 0.3s; }
        .feedback-card:nth-child(4) { animation-delay: 0.4s; }
        .feedback-card:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <section class="approved-feedback-section">
        <div class="approved-feedback-container">
            <h2 class="approved-feedback-title">
                <i class="fas fa-star"></i> Avis de nos Joueurs
            </h2>
            
            <div class="feedback-grid">
                <?php foreach ($feedbacks as $feedback): ?>
                    <div class="feedback-card">
                        <div class="feedback-header">
                            <div class="feedback-author">
                                <?= htmlspecialchars($feedback['prenom']) ?> <?= htmlspecialchars($feedback['nom']) ?>
                            </div>
                            <div class="rating">
                                <?php for ($i = 0; $i < $feedback['note']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="feedback-content">
                            <?= nl2br(htmlspecialchars($feedback['commentaire'])) ?>
                        </div>
                        <div class="feedback-meta">
                            <div class="feedback-date">
                                <i class="fas fa-clock"></i>
                                <?= date('d/m/Y', strtotime($feedback['date_soumission'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</body>
</html> 