<?php
$activeTab     = $activeTab     ?? 'dashboard';
$stats         = $stats         ?? [];
$recentUsers   = $recentUsers   ?? [];
$recentEvents  = $recentEvents  ?? [];
$chart         = $chart         ?? ['labels' => [], 'data' => []];
$users         = $users         ?? [];
$eventStats    = $eventStats    ?? [];
$feedbacks     = $feedbacks     ?? [];
$feedbackStats = $feedbackStats ?? ['total' => 0, 'moyenne' => '—', 'cinq' => 0];
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
    Users <span class="nav-badge"><?= $stats['users'] ?? 0 ?></span>
  </a>
  <a href="/Edu/public/admin/events"
     class="nav-item <?= $activeTab === 'events' ? 'active' : '' ?>">
    Events <span class="nav-badge"><?= $stats['events'] ?? 0 ?></span>
  </a>
  <a href="/Edu/public/admin/categories"
     class="nav-item <?= $activeTab === 'categories' ? 'active' : '' ?>">
    Catégories <span class="nav-badge"><?= $stats['categories'] ?? 0 ?></span>
  </a>
  <a href="/Edu/public/admin/participations"
     class="nav-item <?= $activeTab === 'participations' ? 'active' : '' ?>">
    Participations <span class="nav-badge"><?= $stats['total_participations'] ?? 0 ?></span>
  </a>
  <a href="/Edu/public/admin/feedbacks"
     class="nav-item <?= $activeTab === 'feedbacks' ? 'active' : '' ?>">
    Feedbacks <span class="nav-badge"><?= $feedbackStats['total'] ?? 0 ?></span>
  </a>

  <div class="sidebar-footer">
    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?> · <?= date('d M Y') ?>
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
      <div class="stat-value"><?= $stats['users'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Events</div>
      <div class="stat-value"><?= $stats['events'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Nouveaux (7j)</div>
      <div class="stat-value"><?= $stats['new_users_7d'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Events aujourd'hui</div>
      <div class="stat-value"><?= $stats['events_today'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Participations</div>
      <div class="stat-value"><?= $stats['total_participations'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Feedbacks</div>
      <div class="stat-value"><?= $feedbackStats['total'] ?? 0 ?></div>
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
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= $u['role'] ?></td>
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
          <td><?= htmlspecialchars($e['titre']) ?></td>
          <td><?= date('d M Y', strtotime($e['date_event'])) ?></td>
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
        <?php foreach (array_slice($feedbacks, 0, 5) as $fb): ?>
        <tr>
          <td><?= htmlspecialchars($fb['username']) ?></td>
          <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= htmlspecialchars($fb['message']) ?>
          </td>
          <td>
            <?php if ($fb['note']): ?>
              <span style="color:#EF9F27"><?= str_repeat('★', $fb['note']) ?></span>
            <?php else: ?>
              <span style="color:#9ca3af">—</span>
            <?php endif; ?>
          </td>
          <td style="color:#9ca3af;font-size:.8rem">
            <?= date('d M Y', strtotime($fb['created_at'])) ?>
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
      labels: <?= json_encode($chart['labels']) ?>,
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
</body>
</html>