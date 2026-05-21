<?php
// ─── Fix 1 : session_start() obligatoire avant tout accès à $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activeTab     = $activeTab     ?? 'dashboard';
$stats         = $stats         ?? [];
$recentUsers   = $recentUsers   ?? [];
$recentEvents  = $recentEvents  ?? [];
$chart         = $chart         ?? ['labels' => [], 'data' => []];
$users         = $users         ?? [];
$eventStats    = $eventStats    ?? [];

$feedbacks     = isset($feedbacks) && is_array($feedbacks) ? $feedbacks : [];
$feedbackStats = $feedbackStats ?? ['total' => 0, 'moyenne' => '—', 'cinq' => 0];

function formatDateFr(string $dateStr): string {
    $mois = [
        1  => 'jan', 2  => 'fév', 3  => 'mar', 4  => 'avr',
        5  => 'mai', 6  => 'jui', 7  => 'jul', 8  => 'aoû',
        9  => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'déc',
    ];
    $ts = strtotime($dateStr);
    if ($ts === false) return htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8');
    return date('d', $ts) . ' ' . $mois[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin — EduEvents</title>
  <link rel="stylesheet" href="/Edu/public/css/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<nav class="sidebar">
  <div class="sidebar-logo">⚡ Edu<span>Events</span></div>

  <div class="nav-section-label">Vue générale</div>
  <a href="/Edu/public/dashboard/admin"
     class="nav-item <?= $activeTab === 'dashboard' ? 'active' : '' ?>">
    Dashboard
  </a>

  <div class="nav-section-label">Gestion</div>
  <a href="/Edu/public/admin/users"
     class="nav-item <?= $activeTab === 'users' ? 'active' : '' ?>">
    Users <span class="nav-badge"><?= (int)($stats['users'] ?? 0) ?></span>
  </a>
  <a href="/Edu/public/admin/events"
     class="nav-item <?= $activeTab === 'events' ? 'active' : '' ?>">
    Events <span class="nav-badge"><?= (int)($stats['events'] ?? 0) ?></span>
  </a>
  <a href="/Edu/public/admin/categories"
     class="nav-item <?= $activeTab === 'categories' ? 'active' : '' ?>">
    Catégories <span class="nav-badge"><?= (int)($stats['categories'] ?? 0) ?></span>
  </a>
  <a href="/Edu/public/admin/participations"
     class="nav-item <?= $activeTab === 'participations' ? 'active' : '' ?>">
    Participations <span class="nav-badge"><?= (int)($stats['total_participations'] ?? 0) ?></span>
  </a>
  <a href="/Edu/public/admin/feedbacks"
     class="nav-item <?= $activeTab === 'feedbacks' ? 'active' : '' ?>">
    Feedbacks <span class="nav-badge" id="feedback-badge"><?= (int)($feedbackStats['total'] ?? 0) ?></span>
  </a>

  <div class="sidebar-footer">
    <?php
    echo htmlspecialchars($_SESSION['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
    ?>
    · <?= date('d/m/Y') ?>
    <a href="/Edu/public/logout" class="btn-logout">⏻ Déconnexion</a>
  </div>
</nav>

<main class="main">

<?php if ($activeTab === 'dashboard'): ?>

  <div class="page-header">
    <div class="page-title">Dashboard</div>
    <div class="page-subtitle">Analyse en temps réel</div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Total Users</div>
      <div class="stat-value"><?= (int)($stats['users'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Events</div>
      <div class="stat-value"><?= (int)($stats['events'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Nouveaux (7j)</div>
      <div class="stat-value"><?= (int)($stats['new_users_7d'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Events aujourd'hui</div>
      <div class="stat-value"><?= (int)($stats['events_today'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Participations</div>
      <div class="stat-value"><?= (int)($stats['total_participations'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Feedbacks</div>
      <div class="stat-value"><?= (int)($feedbackStats['total'] ?? 0) ?></div>
    </div>
  </div>

  <div class="chart-card">
    <div class="card-title">Croissance des utilisateurs</div>
    <div class="chart-wrap">
      <canvas id="chartUsers"></canvas>
    </div>
  </div>

  <div class="table-card">
    <div class="table-card-header">
      <div class="card-title">Derniers utilisateurs</div>
    </div>
    <table>
      <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th></tr></thead>
      <tbody>
        <?php foreach ($recentUsers as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($u['email']    ?? '', ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($u['role']     ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="table-card">
    <div class="table-card-header">
      <div class="card-title">Prochains événements</div>
    </div>
    <table>
      <tbody>
        <?php foreach ($recentEvents as $e): ?>
        <tr>
          <td><?= htmlspecialchars($e['titre'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
          <?php
          $dateEvent = isset($e['date_event']) && $e['date_event'] !== ''
              ? formatDateFr($e['date_event'])
              : '—';
          ?>
          <td><?= $dateEvent ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (!empty($feedbacks)): ?>
  <div class="table-card">
    <div class="table-card-header">
      <div class="card-title">Derniers feedbacks</div>
      <a href="/Edu/public/admin/feedbacks" style="font-size:.8rem;color:#534AB7">Voir tout →</a>
    </div>
    <table>
      <thead><tr><th>Membre</th><th>Message</th><th>Note</th><th>Date</th></tr></thead>
      <tbody>
        <?php
        foreach (array_slice($feedbacks, 0, 5) as $fb):
            $note = isset($fb['note']) && $fb['note'] > 0 ? (int)$fb['note'] : 0;
        ?>
        <tr>
          <td><?= htmlspecialchars($fb['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
          <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= htmlspecialchars($fb['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>
          </td>
          <td>
            <?php if ($note > 0): ?>
              <span style="color:#EF9F27"><?= str_repeat('★', min($note, 5)) ?></span>
            <?php else: ?>
              <span style="color:#9ca3af">—</span>
            <?php endif; ?>
          </td>
          <td style="color:#9ca3af;font-size:.8rem">
            <?= isset($fb['created_at']) ? formatDateFr($fb['created_at']) : '—' ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <script>
  new Chart(document.getElementById('chartUsers'), {
    type: 'line',
    data: {
      labels: <?= json_encode($chart['labels'], JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        data: <?= json_encode($chart['data']) ?>,
        borderColor: '#534AB7',
        backgroundColor: 'rgba(83,74,183,0.08)',
        fill: true, tension: 0.4
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { color: '#64748b' } },
        y: { ticks: { color: '#64748b' } }
      }
    }
  });
  </script>

<?php elseif ($activeTab === 'users'): ?>

  <div class="page-header">
    <div class="page-title">Gestion des utilisateurs</div>
  </div>
  <?php require __DIR__ . '/users_crud.php'; ?>

<?php elseif ($activeTab === 'events'): ?>

  <div class="page-header">
    <div class="page-title">Gestion des événements</div>
  </div>
  <?php require __DIR__ . '/events_crud.php'; ?>

<?php elseif ($activeTab === 'categories'): ?>

  <div class="page-header">
    <div class="page-title">Gestion des catégories</div>
  </div>
  <?php require __DIR__ . '/category_crud.php'; ?>

<?php elseif ($activeTab === 'participations'): ?>

  <div class="page-header">
    <div class="page-title">Statistiques des participations</div>
    <div class="page-subtitle">Taux de remplissage par événement</div>
  </div>
  <?php require __DIR__ . '/participations_stats.php'; ?>

<?php elseif ($activeTab === 'feedbacks'): ?>

  <div class="page-header">
    <div class="page-title">Feedbacks</div>
    <div class="page-subtitle">Avis laissés par les membres</div>
  </div>
  <?php require __DIR__ . '/feedbacks_admin.php'; ?>

<?php endif; ?>

</main>

<style>
@keyframes pulse {
  0%,100% { transform: scale(1); }
  50%      { transform: scale(1.3); }
}
.nav-badge.has-new {
  background: #E24B4A;
  color: #fff;
  animation: pulse .6s ease 3;
}
</style>

<script>
(function () {
  const BASE  = '/Edu/public';
  let lastId  = 0;

  function poll() {
    fetch(BASE + '/notifs/stream?last=' + encodeURIComponent(lastId))
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.text();
      })
      .then(function (text) {
        // Extraire le JSON après "data: "
        var match = text.match(/data:\s*(\{.*\})/);
        if (!match) return;
        var data = JSON.parse(match[1]);

        var badgeEl = document.getElementById('feedback-badge');

        if (data.feedbacks > 0 && badgeEl) {
          badgeEl.textContent = '+' + data.feedbacks;
        
          badgeEl.classList.remove('has-new');
          void badgeEl.offsetWidth; 
          badgeEl.classList.add('has-new');
        }

        if (typeof data.maxId === 'number' && data.maxId > lastId) {
          lastId = data.maxId;
        }
      })
      .catch(function () {  })
      .finally(function () { setTimeout(poll, 30000); });
  }

  setTimeout(poll, 5000);
})();
</script>

</body>
</html>