<style>
.fb-stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
.fb-stat {
  background: var(--card, #fff);
  border: 1px solid var(--border, #e5e3de);
  border-radius: 14px;
  padding: 20px;
  text-align: center;
}
.fb-stat .big   { font-size: 2rem; font-weight: 700; }
.fb-stat .label { font-size: .8rem; color: #9ca3af; margin-top: 4px; }

.fb-filters {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.fb-filters input,
.fb-filters select {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid var(--border, #e5e3de);
  background: var(--surface, #fafaf8);
  color: var(--text, #1a1a1a);
  font-size: .85rem;
}

.fb-list { display: flex; flex-direction: column; gap: 10px; }

.fb-card {
  background: var(--card, #fff);
  border: 1px solid var(--border, #e5e3de);
  border-radius: 12px;
  padding: 16px 20px;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 12px;
  align-items: start;
}
.fb-header {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
  margin-bottom: 8px;
}
.fb-user   { font-weight: 600; font-size: .9rem; }
.fb-email  { font-size: .78rem; color: #9ca3af; }
.fb-event  { font-size: .75rem; background: #EEEDFE; color: #3C3489;
             padding: 2px 10px; border-radius: 20px; }
.fb-stars  { color: #EF9F27; font-size: .85rem; }
.fb-date   { font-size: .75rem; color: #9ca3af; }
.fb-msg    { font-size: .875rem; line-height: 1.5; color: #374151; }

.btn-del-fb {
  background: rgba(239,68,68,0.12);
  color: #dc2626;
  border: none;
  padding: 5px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: .8rem;
  white-space: nowrap;
}
.btn-del-fb:hover { background: rgba(239,68,68,0.22); }

.fb-empty {
  text-align: center;
  padding: 48px;
  color: #9ca3af;
}

@media(max-width: 700px) {
  .fb-stats-grid { grid-template-columns: 1fr 1fr; }
  .fb-card       { grid-template-columns: 1fr; }
}
</style>

<?php
if (!isset($feedbackStats) || !is_array($feedbackStats)) {
  $feedbackStats = [
    'total'   => 0,
    'moyenne' => 0,
    'cinq'    => 0,
  ];
}
?>

<div class="fb-stats-grid">
  <div class="fb-stat">
    <div class="big"><?= $feedbackStats['total'] ?></div>
    <div class="label">Total feedbacks</div>
  </div>
  <div class="fb-stat">
    <div class="big" style="color:#EF9F27"><?= $feedbackStats['moyenne'] ?> ★</div>
    <div class="label">Note moyenne</div>
  </div>
  <div class="fb-stat">
    <div class="big" style="color:#0F6E56"><?= $feedbackStats['cinq'] ?></div>
    <div class="label">Notes 5 étoiles</div>
  </div>
</div>

<div class="fb-filters">
  <input type="text"
         id="fbSearch"
         placeholder="🔍 Rechercher par membre ou message…"
         oninput="filterFeedbacks()"
         style="flex:1;min-width:200px">
  <select id="fbNote" onchange="filterFeedbacks()">
    <option value="">Toutes les notes</option>
    <option value="5">★★★★★ (5)</option>
    <option value="4">★★★★☆ (4)</option>
    <option value="3">★★★☆☆ (3)</option>
    <option value="2">★★☆☆☆ (2)</option>
    <option value="1">★☆☆☆☆ (1)</option>
    <option value="0">Sans note</option>
  </select>
</div>

<div class="fb-list" id="fbList">

  <?php if (empty($feedbacks)): ?>
    <div class="fb-empty">Aucun feedback pour le moment.</div>

  <?php else: ?>
    <?php foreach ($feedbacks as $fb): ?>
    <div class="fb-card"
         id="fb<?= $fb['id'] ?>"
         data-msg="<?= strtolower(htmlspecialchars($fb['message'] . ' ' . $fb['username'])) ?>"
         data-note="<?= $fb['note'] ?? 0 ?>">

      <div>
        <div class="fb-header">
          <span class="fb-user">👤 <?= htmlspecialchars($fb['username']) ?></span>
          <span class="fb-email"><?= htmlspecialchars($fb['email']) ?></span>
          <?php if ($fb['event_titre']): ?>
            <span class="fb-event">🎯 <?= htmlspecialchars($fb['event_titre']) ?></span>
          <?php endif; ?>
          <?php if ($fb['note']): ?>
            <span class="fb-stars">
              <?= str_repeat('★', $fb['note']) . str_repeat('☆', 5 - $fb['note']) ?>
            </span>
          <?php endif; ?>
          <span class="fb-date">
            📅 <?= date('d M Y à H:i', strtotime($fb['created_at'])) ?>
          </span>
        </div>
        <div class="fb-msg"><?= nl2br(htmlspecialchars($fb['message'])) ?></div>
      </div>

      <button class="btn-del-fb" onclick="deleteFeedback(<?= $fb['id'] ?>)">
        🗑️ Supprimer
      </button>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>

<script>
const BASE = '/Edu/public';

function filterFeedbacks() {
  const search = document.getElementById('fbSearch').value.toLowerCase();
  const note   = document.getElementById('fbNote').value;

  document.querySelectorAll('#fbList .fb-card').forEach(card => {
    const matchMsg  = card.dataset.msg.includes(search);
    const cardNote  = card.dataset.note;
    const matchNote = note === ''
      ? true
      : note === '0' ? cardNote === '0' : cardNote === note;

    card.style.display = matchMsg && matchNote ? '' : 'none';
  });
}

async function deleteFeedback(id) {
  if (!confirm('Supprimer ce feedback ?')) return;

  const fd = new FormData();
  fd.append('id', id);

  const res  = await fetch(BASE + '/feedback/delete', { method: 'POST', body: fd });
  const data = await res.json();

  if (data.status === 'success') {
    document.getElementById('fb' + id)?.remove();

    const big = document.querySelector('.fb-stat .big');
    if (big) big.textContent = Math.max(0, parseInt(big.textContent) - 1);
  } else {
    alert(data.message);
  }
}
</script>