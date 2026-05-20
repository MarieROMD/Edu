<?php if (!isset($_SESSION['user_id'])) { header("Location: /Edu/public/login"); exit; } ?>
<?php
$events       = $events       ?? [];
$userEventIds = $userEventIds ?? [];
$myEvents     = $myEvents     ?? [];
$myFeedbacks  = $myFeedbacks  ?? [];
$activeTab    = $_GET['tab']  ?? 'home';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon espace — EduPlatform</title>
  <link rel="stylesheet" href="/Edu/public/css/dashboard-client.css">
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">Edu<span>Platform</span></div>

  <div class="nav-section-label">Menu</div>
  <nav>
    <a href="?tab=home"
       class="nav-item <?= $activeTab === 'home' ? 'active' : '' ?>">
      🏠 Accueil
    </a>
    <a href="?tab=events"
       class="nav-item <?= $activeTab === 'events' ? 'active' : '' ?>">
      📅 Événements
      <span class="nav-badge"><?= count($events) ?></span>
    </a>
    <a href="?tab=myevents"
       class="nav-item <?= $activeTab === 'myevents' ? 'active' : '' ?>">
      🎟 Mes inscriptions
      <span class="nav-badge"><?= count($myEvents) ?></span>
    </a>
    <a href="?tab=feedback"
       class="nav-item <?= $activeTab === 'feedback' ? 'active' : '' ?>">
      💬 Feedback
      <?php if (count($myFeedbacks) > 0): ?>
        <span class="nav-badge"><?= count($myFeedbacks) ?></span>
      <?php endif; ?>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-pill">
      <div class="user-avatar">
        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
      </div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></div>
        <div class="user-role">Membre</div>
      </div>
      <a href="/Edu/public/logout" class="user-logout" title="Déconnexion">⎋</a>
    </div>
  </div>
</aside>

<main class="main">

  <div class="topbar">
    <div>
      <h1 class="page-title">Bonjour, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h1>
      <p class="page-subtitle">
        <?php if ($activeTab === 'home'): ?>
          Voici un aperçu de vos événements à venir.
        <?php elseif ($activeTab === 'events'): ?>
          Parcourez les événements disponibles.
        <?php elseif ($activeTab === 'myevents'): ?>
          Vos inscriptions actuelles.
        <?php else: ?>
          Partagez votre expérience.
        <?php endif; ?>
      </p>
    </div>
    <div class="badge-date">📅 <?= date('d M Y') ?></div>
  </div>

  <?php if ($activeTab === 'home'): ?>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-chip">À venir</div>
        <div class="stat-icon">📅</div>
        <div class="stat-label">Événements disponibles</div>
        <div class="stat-value"><?= count($events) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🎟</div>
        <div class="stat-label">Mes inscriptions</div>
        <div class="stat-value"><?= count($myEvents) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⚡</div>
        <div class="stat-label">Cette semaine</div>
        <div class="stat-value">
          <?php
            $proche = array_filter($events, fn($e) =>
              strtotime($e['date_event']) <= strtotime('+7 days')
            );
            echo count($proche);
          ?>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💬</div>
        <div class="stat-label">Mes feedbacks</div>
        <div class="stat-value"><?= count($myFeedbacks) ?></div>
      </div>
    </div>

    <div class="section-header">
      <h2 class="card-title">Prochains événements</h2>
      <a href="?tab=events" class="btn-outline">Voir tout →</a>
    </div>

    <div class="table-card">
      <?php if (empty($events)): ?>
        <div class="empty-state">
          <span class="empty-icon">🗓</span>
          <p>Aucun événement disponible pour le moment.</p>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>Titre</th><th>Date</th><th>Places</th><th>Statut</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($events, 0, 5) as $event):
              $isJoined = in_array($event['id'], $userEventIds);
              $isFull   = $event['places_restantes'] <= 0;
            ?>
            <tr>
              <td>
                <div class="event-title-cell">
                  <div class="event-dot"></div>
                  <?= htmlspecialchars($event['titre']) ?>
                </div>
              </td>
              <td><span class="date-badge">📆 <?= date('d M Y', strtotime($event['date_event'])) ?></span></td>
              <td>
                <span class="places-value"><?= intval($event['places_restantes']) ?></span>
                <span class="places-label"> places</span>
              </td>
              <td>
                <?php if ($isJoined): ?>
                  <span style="color:#0F6E56;font-size:.8rem;font-weight:500">✓ Inscrit</span>
                <?php elseif ($isFull): ?>
                  <span style="color:#A32D2D;font-size:.8rem">Complet</span>
                <?php else: ?>
                  <span style="color:#9ca3af;font-size:.8rem">Disponible</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($isJoined): ?>
                  <button class="btn-sm btn-danger" data-id="<?= $event['id'] ?>" onclick="leave(this)">Se désinscrire</button>
                <?php elseif (!$isFull): ?>
                  <button class="btn-sm" data-id="<?= $event['id'] ?>" onclick="join(this)">Participer →</button>
                <?php else: ?>
                  <button class="btn-sm" disabled style="opacity:.4">Complet</button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  <?php elseif ($activeTab === 'events'): ?>

    <div class="table-card">
      <?php if (empty($events)): ?>
        <div class="empty-state">
          <span class="empty-icon">🗓</span>
          <p>Aucun événement disponible.</p>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>Titre</th><th>Date</th><th>Capacité</th><th>Places</th><th>Statut</th><th></th></tr>
          </thead>
          <tbody id="eventsTableBody">
            <?php foreach ($events as $event):
              $isJoined = in_array($event['id'], $userEventIds);
              $isFull   = $event['places_restantes'] <= 0;
              $pct      = $event['capacite'] > 0
                          ? round(($event['total_participants'] / $event['capacite']) * 100) : 0;
            ?>
            <tr id="erow<?= $event['id'] ?>">
              <td>
                <div class="event-title-cell">
                  <div class="event-dot"></div>
                  <strong><?= htmlspecialchars($event['titre']) ?></strong>
                </div>
              </td>
              <td><span class="date-badge">📆 <?= date('d M Y', strtotime($event['date_event'])) ?></span></td>
              <td style="font-size:.85rem">
                <?= $event['total_participants'] ?>/<?= $event['capacite'] ?>
                <div style="margin-top:4px;height:4px;background:#f0eeea;border-radius:4px">
                  <div style="width:<?= $pct ?>%;height:4px;background:#534AB7;border-radius:4px"></div>
                </div>
              </td>
              <td>
                <span class="places-value"><?= intval($event['places_restantes']) ?></span>
                <span class="places-label"> places</span>
              </td>
              <td id="status<?= $event['id'] ?>">
                <?php if ($isJoined): ?>
                  <span style="background:#E1F5EE;color:#0F6E56;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:500">✓ Inscrit</span>
                <?php elseif ($isFull): ?>
                  <span style="background:#FCEBEB;color:#A32D2D;padding:3px 10px;border-radius:20px;font-size:.75rem">Complet</span>
                <?php else: ?>
                  <span style="background:#f0eeea;color:#6b7280;padding:3px 10px;border-radius:20px;font-size:.75rem">Disponible</span>
                <?php endif; ?>
              </td>
              <td id="action<?= $event['id'] ?>">
                <?php if ($isJoined): ?>
                  <button class="btn-sm btn-danger" data-id="<?= $event['id'] ?>" onclick="leave(this)">Se désinscrire</button>
                <?php elseif (!$isFull): ?>
                  <button class="btn-sm" data-id="<?= $event['id'] ?>" onclick="join(this)">Participer →</button>
                <?php else: ?>
                  <button class="btn-sm" disabled style="opacity:.4">Complet</button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  <?php elseif ($activeTab === 'myevents'): ?>

    <?php if (empty($myEvents)): ?>
      <div class="empty-state" style="margin-top:40px;text-align:center">
        <span class="empty-icon" style="font-size:3rem">🎟</span>
        <p style="color:#9ca3af;margin-top:12px">Vous n'êtes inscrit à aucun événement.</p>
        <a href="?tab=events" class="btn-outline" style="margin-top:16px;display:inline-block">
          Parcourir les événements →
        </a>
      </div>
    <?php else: ?>
      <div class="stats-grid" style="margin-bottom:24px">
        <div class="stat-card">
          <div class="stat-icon">🎟</div>
          <div class="stat-label">Total inscriptions</div>
          <div class="stat-value"><?= count($myEvents) ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-label">Prochain événement</div>
          <div class="stat-value" style="font-size:1rem">
            <?= date('d M', strtotime($myEvents[0]['date_event'])) ?>
          </div>
        </div>
      </div>
      <div class="table-card">
        <table>
          <thead>
            <tr><th>Titre</th><th>Date</th><th>Places restantes</th><th></th></tr>
          </thead>
          <tbody id="myEventsBody">
            <?php foreach ($myEvents as $event): ?>
            <tr id="myrow<?= $event['id'] ?>">
              <td>
                <div class="event-title-cell">
                  <div class="event-dot" style="background:#0F6E56"></div>
                  <strong><?= htmlspecialchars($event['titre']) ?></strong>
                </div>
              </td>
              <td><span class="date-badge">📆 <?= date('d M Y', strtotime($event['date_event'])) ?></span></td>
              <td>
                <span class="places-value"><?= intval($event['places_restantes']) ?></span>
                <span class="places-label"> places</span>
              </td>
              <td>
                <button class="btn-sm btn-danger"
                        data-id="<?= $event['id'] ?>"
                        onclick="leaveFromMyEvents(this)">
                  Se désinscrire
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  <?php elseif ($activeTab === 'feedback'): ?>

  <div id="fbAlert"
       style="display:none;padding:12px 16px;border-radius:10px;
              margin-bottom:16px;font-size:.9rem">
  </div>

  <div id="editModal"
       style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;
              background:rgba(0,0,0,0.5);z-index:200;justify-content:center;align-items:center">
    <div style="background:#fff;border-radius:16px;padding:24px;width:420px;max-width:90vw">
      <h3 style="margin:0 0 16px;font-size:1rem;font-weight:600">✏️ Modifier le feedback</h3>

      <input type="hidden" id="edit_id">

      <div style="margin-bottom:14px">
        <label style="display:block;font-size:.8rem;color:#9ca3af;margin-bottom:8px">Note</label>
        <div style="display:flex;gap:8px;flex-direction:row-reverse;justify-content:flex-end"
             id="editStarsWrap">
          <?php for ($i = 5; $i >= 1; $i--): ?>
          <input type="radio" name="edit_note" id="es<?= $i ?>" value="<?= $i ?>"
                 style="display:none">
          <label for="es<?= $i ?>"
                 style="font-size:1.8rem;cursor:pointer;color:#e5e3de;transition:.15s"
                 onmouseover="hoverEditStars(<?= $i ?>)"
                 onmouseout="resetEditStars()"
                 onclick="selectEditStar(<?= $i ?>)">★</label>
          <?php endfor; ?>
        </div>
      </div>

      <div style="margin-bottom:20px">
        <label style="display:block;font-size:.8rem;color:#9ca3af;margin-bottom:6px">Message</label>
        <textarea id="edit_message"
                  style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e5e3de;
                         font-size:.9rem;box-sizing:border-box;min-height:90px;
                         resize:vertical;font-family:inherit">
        </textarea>
      </div>

      <div style="display:flex;gap:10px">
        <button onclick="submitEdit()"
                style="flex:1;padding:10px;background:#534AB7;color:#fff;border:none;
                       border-radius:8px;font-weight:600;cursor:pointer;font-size:.9rem">
          Enregistrer
        </button>
        <button onclick="closeEditModal()"
                style="flex:1;padding:10px;background:#f5f4f0;color:#374151;border:none;
                       border-radius:8px;cursor:pointer;font-size:.9rem">
          Annuler
        </button>
      </div>
    </div>
  </div>

  <div class="table-card" style="max-width:600px;padding:24px">
    <h2 style="font-size:1rem;font-weight:600;margin:0 0 20px">💬 Laisser un feedback</h2>

    <div style="margin-bottom:14px">
      <label style="display:block;font-size:.8rem;color:#9ca3af;font-weight:500;margin-bottom:6px">
        Événement concerné (optionnel)
      </label>
      <select id="fb_event"
              style="width:100%;padding:9px 12px;border-radius:8px;
                     border:1px solid #e5e3de;background:#fafaf8;
                     font-size:.9rem;box-sizing:border-box">
        <option value="">— Feedback général —</option>
        <?php foreach ($myEvents as $e): ?>
          <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['titre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="margin-bottom:14px">
      <label style="display:block;font-size:.8rem;color:#9ca3af;font-weight:500;margin-bottom:8px">Note</label>
      <div style="display:flex;gap:8px;flex-direction:row-reverse;justify-content:flex-end">
        <?php for ($i = 5; $i >= 1; $i--): ?>
        <input type="radio" name="note" id="s<?= $i ?>" value="<?= $i ?>" style="display:none">
        <label for="s<?= $i ?>"
               style="font-size:1.8rem;cursor:pointer;color:#e5e3de;transition:.15s"
               onmouseover="hoverStars(<?= $i ?>)"
               onmouseout="resetStars()"
               onclick="selectStar(<?= $i ?>)">★</label>
        <?php endfor; ?>
      </div>
    </div>

    <div style="margin-bottom:20px">
      <label style="display:block;font-size:.8rem;color:#9ca3af;font-weight:500;margin-bottom:6px">
        Votre message
      </label>
      <textarea id="fb_message"
                placeholder="Partagez votre expérience…"
                style="width:100%;padding:10px 12px;border-radius:8px;
                       border:1px solid #e5e3de;background:#fafaf8;
                       font-size:.9rem;box-sizing:border-box;
                       min-height:100px;resize:vertical;font-family:inherit">
      </textarea>
    </div>

    <button onclick="sendFeedback()"
            style="padding:10px 24px;background:#534AB7;color:#fff;border:none;
                   border-radius:10px;font-weight:600;cursor:pointer;font-size:.9rem">
      Envoyer →
    </button>
  </div>

  <?php if (!empty($myFeedbacks)): ?>
  <div style="margin-top:32px;max-width:700px" id="myFbList">
    <h2 style="font-size:1rem;font-weight:600;margin:0 0 16px">
      Mes feedbacks envoyés
      <span style="font-weight:400;color:#9ca3af;font-size:.85rem">(<?= count($myFeedbacks) ?>)</span>
    </h2>

    <?php foreach ($myFeedbacks as $fb): ?>
    <div id="myfb<?= $fb['id'] ?>"
         style="background:#fff;border:1px solid #e5e3de;border-radius:12px;
                padding:16px 20px;margin-bottom:10px">

      <div style="display:flex;justify-content:space-between;align-items:start;gap:12px">

        <div style="flex:1">
          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:8px">
            <?php if ($fb['event_titre']): ?>
              <span style="background:#EEEDFE;color:#3C3489;padding:2px 10px;
                           border-radius:20px;font-size:.75rem">
                🎯 <?= htmlspecialchars($fb['event_titre']) ?>
              </span>
            <?php endif; ?>
            <?php if ($fb['note']): ?>
              <span id="myfb-stars<?= $fb['id'] ?>" style="color:#EF9F27;font-size:.85rem">
                <?= str_repeat('★', $fb['note']) . str_repeat('☆', 5 - $fb['note']) ?>
              </span>
            <?php else: ?>
              <span id="myfb-stars<?= $fb['id'] ?>" style="color:#9ca3af;font-size:.8rem">Sans note</span>
            <?php endif; ?>
            <span style="font-size:.75rem;color:#9ca3af">
              📅 <?= date('d M Y à H:i', strtotime($fb['created_at'])) ?>
            </span>
          </div>
          <div id="myfb-msg<?= $fb['id'] ?>"
               style="font-size:.875rem;line-height:1.5;color:#374151">
            <?= nl2br(htmlspecialchars($fb['message'])) ?>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0">
          <button onclick="openEditModal(<?= $fb['id'] ?>, <?= (int)$fb['note'] ?>, `<?= addslashes($fb['message']) ?>`)"
                  style="background:#f0eeea;border:none;padding:5px 12px;
                         border-radius:6px;cursor:pointer;font-size:.8rem">
            ✏️ Modifier
          </button>
          <button onclick="deleteMine(<?= $fb['id'] ?>)"
                  style="background:rgba(239,68,68,0.12);color:#dc2626;border:none;
                         padding:5px 12px;border-radius:6px;cursor:pointer;font-size:.8rem">
            🗑️ Supprimer
          </button>
        </div>

      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

<?php endif; ?>

</main>

<div id="toast"
     style="position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:10px;
            font-size:.875rem;font-weight:500;opacity:0;transition:.3s;pointer-events:none;
            z-index:999;font-family:inherit">
</div>

<script>
const BASE = '/Edu/public';
let toastTimer;
let selectedNote = 0;

function showToast(msg, type = 'success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.style.opacity    = '1';
  el.style.background = type === 'success' ? '#E1F5EE' : '#FCEBEB';
  el.style.color      = type === 'success' ? '#0F6E56' : '#A32D2D';
  el.style.border     = type === 'success' ? '1px solid #9FE1CB' : '1px solid #F7C1C1';
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.style.opacity = '0', 3000);
}

async function postTo(url, id) {
  const fd = new FormData();
  fd.append('id', id);
  const res = await fetch(BASE + url, { method: 'POST', body: fd });
  return res.json();
}

function hoverStars(n) {
  document.querySelectorAll('label[for^="s"]').forEach(l => {
    const v = parseInt(l.getAttribute('for').replace('s',''));
    l.style.color = v <= n ? '#EF9F27' : '#e5e3de';
  });
}
function resetStars() {
  document.querySelectorAll('label[for^="s"]').forEach(l => {
    const v = parseInt(l.getAttribute('for').replace('s',''));
    l.style.color = v <= selectedNote ? '#EF9F27' : '#e5e3de';
  });
}
function selectStar(n) {
  selectedNote = n;
  const radio = document.getElementById('s' + n);
  if (radio) radio.checked = true;
}

async function sendFeedback() {
  const message = document.getElementById('fb_message').value.trim();
  const eventId = document.getElementById('fb_event').value;
  const alertEl = document.getElementById('fbAlert');

  if (message.length < 5) {
    alertEl.style.display = 'block';
    alertEl.style.background = '#FCEBEB';
    alertEl.style.color      = '#A32D2D';
    alertEl.style.border     = '1px solid #F7C1C1';
    alertEl.textContent = 'Message trop court (min. 5 caractères)';
    return;
  }

  const fd = new FormData();
  fd.append('message',  message);
  fd.append('event_id', eventId);
  fd.append('note',     selectedNote || '');

  const res  = await fetch(BASE + '/feedback/send', { method: 'POST', body: fd });
  const data = await res.json();

  alertEl.style.display = 'block';
  if (data.status === 'success') {
    alertEl.style.background = '#E1F5EE';
    alertEl.style.color      = '#0F6E56';
    alertEl.style.border     = '1px solid #9FE1CB';
    alertEl.textContent      = data.message;
    document.getElementById('fb_message').value = '';
    document.getElementById('fb_event').value   = '';
    selectedNote = 0;
    resetStars();
    showToast('Feedback envoyé !');
  } else {
    alertEl.style.background = '#FCEBEB';
    alertEl.style.color      = '#A32D2D';
    alertEl.style.border     = '1px solid #F7C1C1';
    alertEl.textContent      = data.message;
  }
  setTimeout(() => alertEl.style.display = 'none', 4000);
}

async function join(btn) {
  const id = btn.dataset.id;
  btn.disabled = true; btn.textContent = '...';
  const data = await postTo('/events/join', id);
  if (data.success) {
    showToast('Inscription réussie !');
    btn.textContent = 'Se désinscrire';
    btn.className   = 'btn-sm btn-danger';
    btn.onclick     = function() { leave(this); };
    btn.disabled    = false;
    const s = document.getElementById('status' + id);
    if (s) s.innerHTML = '<span style="background:#E1F5EE;color:#0F6E56;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:500">✓ Inscrit</span>';
  } else {
    showToast(data.message, 'error');
    btn.textContent = 'Participer →'; btn.disabled = false;
  }
}

async function leave(btn) {
  if (!confirm('Se désinscrire de cet événement ?')) return;
  const id = btn.dataset.id;
  btn.disabled = true; btn.textContent = '...';
  const data = await postTo('/events/leave', id);
  if (data.success) {
    showToast('Désinscription effectuée');
    btn.textContent = 'Participer →';
    btn.className   = 'btn-sm';
    btn.onclick     = function() { join(this); };
    btn.disabled    = false;
    const s = document.getElementById('status' + id);
    if (s) s.innerHTML = '<span style="background:#f0eeea;color:#6b7280;padding:3px 10px;border-radius:20px;font-size:.75rem">Disponible</span>';
  } else {
    showToast(data.message, 'error');
    btn.textContent = 'Se désinscrire'; btn.disabled = false;
  }
}

async function leaveFromMyEvents(btn) {
  if (!confirm('Se désinscrire de cet événement ?')) return;
  const id = btn.dataset.id;
  btn.disabled = true; btn.textContent = '...';
  const data = await postTo('/events/leave', id);
  if (data.success) {
    showToast('Désinscription effectuée');
    document.getElementById('myrow' + id)?.remove();
    const a = document.getElementById('action' + id);
    if (a) a.innerHTML = `<button class="btn-sm" data-id="${id}" onclick="join(this)">Participer →</button>`;
    const s = document.getElementById('status' + id);
    if (s) s.innerHTML = '<span style="background:#f0eeea;color:#6b7280;padding:3px 10px;border-radius:20px;font-size:.75rem">Disponible</span>';
  } else {
    showToast(data.message, 'error');
    btn.textContent = 'Se désinscrire'; btn.disabled = false;
  }
}


let editNote = 0;

function hoverEditStars(n) {
  document.querySelectorAll('label[for^="es"]').forEach(l => {
    const v = parseInt(l.getAttribute('for').replace('es',''));
    l.style.color = v <= n ? '#EF9F27' : '#e5e3de';
  });
}
function resetEditStars() {
  document.querySelectorAll('label[for^="es"]').forEach(l => {
    const v = parseInt(l.getAttribute('for').replace('es',''));
    l.style.color = v <= editNote ? '#EF9F27' : '#e5e3de';
  });
}
function selectEditStar(n) {
  editNote = n;
  const r = document.getElementById('es' + n);
  if (r) r.checked = true;
}

function openEditModal(id, note, message) {
  document.getElementById('edit_id').value      = id;
  document.getElementById('edit_message').value = message;
  editNote = note;

  document.querySelectorAll('label[for^="es"]').forEach(l => {
    const v = parseInt(l.getAttribute('for').replace('es',''));
    l.style.color = v <= note ? '#EF9F27' : '#e5e3de';
  });
  if (note) {
    const r = document.getElementById('es' + note);
    if (r) r.checked = true;
  }

  const modal = document.getElementById('editModal');
  modal.style.display = 'flex';
}

function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
  editNote = 0;
}

async function submitEdit() {
  const id      = document.getElementById('edit_id').value;
  const message = document.getElementById('edit_message').value.trim();

  if (message.length < 5) {
    showToast('Message trop court (min. 5 caractères)', 'error'); return;
  }

  const fd = new FormData();
  fd.append('id',      id);
  fd.append('message', message);
  fd.append('note',    editNote || '');

  const res  = await fetch(BASE + '/feedback/update-mine', { method: 'POST', body: fd });
  const data = await res.json();

  if (data.status === 'success') {
    showToast('Feedback modifié !');
    closeEditModal();

    
    const msgEl = document.getElementById('myfb-msg' + id);
    if (msgEl) msgEl.innerHTML = message.replace(/\n/g, '<br>');

    
    const starsEl = document.getElementById('myfb-stars' + id);
    if (starsEl) {
      if (editNote) {
        starsEl.style.color = '#EF9F27';
        starsEl.textContent = '★'.repeat(editNote) + '☆'.repeat(5 - editNote);
      } else {
        starsEl.style.color = '#9ca3af';
        starsEl.textContent = 'Sans note';
      }
    }
  } else {
    showToast(data.message, 'error');
  }
}

async function deleteMine(id) {
  if (!confirm('Supprimer ce feedback ?')) return;

  const fd = new FormData();
  fd.append('id', id);

  const res  = await fetch(BASE + '/feedback/delete-mine', { method: 'POST', body: fd });
  const data = await res.json();

  if (data.status === 'success') {
    showToast('Feedback supprimé');
    document.getElementById('myfb' + id)?.remove();
  } else {
    showToast(data.message, 'error');
  }
}
</script>

<style>
.btn-danger {
  background: rgba(239,68,68,0.12) !important;
  color: #dc2626 !important;
  border: 1px solid rgba(239,68,68,0.2) !important;
}
.btn-danger:hover { background: rgba(239,68,68,0.22) !important; }
</style>

</body>
</html>