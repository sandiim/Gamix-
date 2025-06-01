<?php
require_once 'config/database.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // Requête pour récupérer l'historique des jeux
    $stmt = $conn->prepare("SELECT game_name, played_at, duration, score FROM game_history WHERE user_id = ? ORDER BY played_at DESC");
    $stmt->execute([$userId]);
    $histories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

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
        <?php if (count($histories) > 0): ?>
          <?php foreach ($histories as $history): ?>
            <tr class="hover:bg-indigo-50 transition-colors">
              <td class="px-6 py-4 font-semibold text-gray-800">
                <?= htmlspecialchars($history['game_name']) ?>
              </td>
              <td class="px-6 py-4 text-gray-600">
                <?= date('d/m/Y H:i', strtotime($history['played_at'])) ?>
              </td>
              <td class="px-6 py-4 text-gray-600">
                <?= floor($history['duration'] / 60) ?> min
              </td>
              <td class="px-6 py-4 text-blue-600 font-semibold">
                <?= htmlspecialchars($history['score']) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
              Aucun historique pour le moment.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
