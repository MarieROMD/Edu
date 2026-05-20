<style>
.part-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
.part-stat {
  background: var(--card, #fff);
  border: 1px solid var(--border, #e5e3de);
  border-radius: 14px;
  padding: 20px;
  text-align: center;
}
.part-stat .big   { font-size: 2rem; font-weight: 700; color: var(--text, #1a1a1a); }
.part-stat .label { font-size: .8rem; color: #9ca3af; margin-top: 4px; }

.event-stat-card {
  background: var(--card, #fff);
  border: 1px solid var(--border, #e5e3de);
  border-radius: 14px;
  padding: 20px;
  margin-bottom: 12px;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 12px;
  align-items: center;
}
.event-stat-card .event-name {
  font-weight: 600;
  font-size: .95rem;
  margin-bottom: 4px;
}
.event-stat-card .event-meta {
  font-size: .8rem;
  color: #9ca3af;
  margin-bottom: 10px;
}
.progress-wrap {
  height: 8px;
  background: var(--border, #f0eeea);
  border-radius: 8px;
  overflow: hidden;
}
.progress-bar {
  height: 8px;
  border-radius: 8px;
  transition: width .4s;
}
.badge-taux {
  font-size: .75rem;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 20px;
  white-space: nowrap;
  text-align: center;
}
.chart-part-card {
  background: var(--card, #fff);
  border: 1px solid var(--border, #e5e3de);
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 24px;
}
@media(max-width: 700px) {
  .part-grid { grid-template-columns: 1fr 1fr; }
  .event-stat-card { grid-template-columns: 1fr; }
}
</style>

<?php
$eventStats = $eventStats ?? [];

$totalParticipants = array_sum(array_column($eventStats, 'total_participants'));
$eventPlein        = count(array_filter($eventStats, fn($e) => $e['places_restantes'] <= 0));
$tauxMoyen         = count($eventStats) > 0
    ? round(array_sum(array_column($eventStats, 'taux_remplissage')) / count($eventStats))
    : 0;

$chartLabels = array_map(fn($e) => mb_strimwidth($e['titre'], 0, 20, '…'), $eventStats);
$chartData   = array_column($eventStats, 'total_participants');
?>

<div class="part-grid">
  <div class="part-stat">
    <div class="big"><?= $totalParticipants ?></div>
    <div class="label">Total participations</div>
  </div>
  <div class="part-stat">
    <div class="big"><?= $tauxMoyen ?>%</div>
    <div class="label">Taux de remplissage moyen</div>
  </div>
  <div class="part-stat">
    <div class="big"><?= $eventPlein ?>/<?= count($eventStats) ?></div>
    <div class="label">Événements complets</div>
  </div>
</div>

<div class="chart-part-card">
  <div class="card-title" style="margin-bottom:16px">
    Participations par événement
  </div>
  <div style="position:relative;height:260px">
    <canvas id="chartPart"></canvas>
  </div>
</div>

<div style="margin-bottom:8px;font-weight:600;font-size:.9rem">
  Détail par événement
</div>

<?php foreach ($eventStats as $e):
  $taux  = min((int)$e['taux_remplissage'], 100);
  $full  = $e['places_restantes'] <= 0;

  if ($taux >= 80) {
    $barColor  = '#E24B4A';
    $badgeBg   = '#FCEBEB';
    $badgeColor= '#A32D2D';
    $label     = $taux . '% · Presque plein';
  } elseif ($taux >= 50) {
    $barColor  = '#EF9F27';
    $badgeBg   = '#FEF3C7';
    $badgeColor= '#92400E';
    $label     = $taux . '% · En cours';
  } else {
    $barColor  = '#1D9E75';
    $badgeBg   = '#E1F5EE';
    $badgeColor= '#0F6E56';
    $label     = $taux . '% · Disponible';
  }

  if ($full) {
    $barColor  = '#6b7280';
    $badgeBg   = '#f0eeea';
    $badgeColor= '#374151';
    $label     = 'Complet';
  }
?>
<div class="event-stat-card">
  <div>
    <div class="event-name"><?= htmlspecialchars($e['titre']) ?></div>
    <div class="event-meta">
      📅 <?= date('d M Y', strtotime($e['date_event'])) ?>
      &nbsp;·&nbsp;
      👥 <?= $e['total_participants'] ?> / <?= $e['capacite'] ?> participants
      &nbsp;·&nbsp;
      <?= $e['places_restantes'] ?> place<?= $e['places_restantes'] > 1 ? 's' : '' ?> restante<?= $e['places_restantes'] > 1 ? 's' : '' ?>
    </div>
    <div class="progress-wrap">
      <div class="progress-bar"
           style="width:<?= $taux ?>%;background:<?= $barColor ?>">
      </div>
    </div>
  </div>
  <div>
    <span class="badge-taux"
          style="background:<?= $badgeBg ?>;color:<?= $badgeColor ?>">
      <?= $label ?>
    </span>
  </div>
</div>
<?php endforeach; ?>

<?php if (empty($eventStats)): ?>
  <div style="text-align:center;padding:48px;color:#9ca3af">
    Aucun événement trouvé.
  </div>
<?php endif; ?>

<script>
new Chart(document.getElementById('chartPart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [{
      label: 'Participants',
      data:  <?= json_encode($chartData) ?>,
      backgroundColor: 'rgba(83,74,183,0.7)',
      borderColor:     '#534AB7',
      borderWidth: 1,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: '#64748b', maxRotation: 30 } },
      y: { ticks: { color: '#64748b' }, beginAtZero: true }
    }
  }
});
</script>