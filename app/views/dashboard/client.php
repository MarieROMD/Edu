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
  <script src="/Edu/public/js/darkmode.js"></script>
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
    <a href="?tab=calendrier"
       class="nav-item <?= $activeTab === 'calendrier' ? 'active' : '' ?>">
      🗓️ Calendrier
    </a>
    <a href="/Edu/public/profile" class="nav-item">
      👤 Mon profil
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-pill">
      <div class="user-avatar">
        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
      </div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="user-role">Membre</div>
      </div>
      <a href="/Edu/public/logout" class="user-logout" title="Déconnexion">⎋</a>
    </div>
  </div>
</aside>

<main class="main">

  <div class="topbar">
    <div>
      <h1 class="page-title">Bonjour, <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?> 👋</h1>
      <p class="page-subtitle">
        <?php if ($activeTab === 'home'): ?>
          Voici un aperçu de vos événements à venir.
        <?php elseif ($activeTab === 'events'): ?>
          Parcourez les événements disponibles.
        <?php elseif ($activeTab === 'myevents'): ?>
          Vos inscriptions actuelles.
        <?php elseif ($activeTab === 'feedback'): ?>
          Partagez votre expérience.
        <?php elseif ($activeTab === 'calendrier'): ?>
          Vue calendrier de vos événements par mois.
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
              isset($e['date_event']) &&
              strtotime($e['date_event']) !== false &&
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
              $isFull   = (int)($event['places_restantes'] ?? 0) <= 0;
              $eid = (int)$event['id'];
            ?>
            <tr>
              <td>
                <div class="event-title-cell">
                  <div class="event-dot"></div>
                  <?= htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8') ?>
                </div>
              </td>
              <td>
                <?php
                  $ts = isset($event['date_event']) ? strtotime($event['date_event']) : false;
                ?>
                <span class="date-badge">📆 <?= $ts !== false ? date('d M Y', $ts) : '—' ?></span>
              </td>
              <td>
                <span class="places-value"><?= (int)($event['places_restantes'] ?? 0) ?></span>
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
                  <button class="btn-sm btn-danger" data-id="<?= $eid ?>" onclick="leave(this)">
                    Se désinscrire
                  </button>
                <?php elseif (!$isFull): ?>
                  <button class="btn-sm" data-id="<?= $eid ?>" onclick="join(this)">
                    Participer →
                  </button>
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
            <tr>
              <th>Titre</th><th>Date</th><th>Capacité</th>
              <th>Places</th><th>Statut</th><th></th>
            </tr>
          </thead>
          <tbody id="eventsTableBody">
            <?php foreach ($events as $event):
              $isJoined = in_array($event['id'], $userEventIds);
              $isFull   = (int)($event['places_restantes'] ?? 0) <= 0;
              $pct      = (int)($event['capacite'] ?? 0) > 0
                          ? round(((int)$event['total_participants'] / (int)$event['capacite']) * 100)
                          : 0;
              $pct      = min(100, max(0, $pct)); 
              $eid      = (int)$event['id'];
              $ts       = isset($event['date_event']) ? strtotime($event['date_event']) : false;
            ?>
            <tr id="erow<?= $eid ?>">
              <td>
                <div class="event-title-cell">
                  <div class="event-dot"></div>
                  <strong><?= htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
              </td>
              <td>
                <span class="date-badge">
                  📆 <?= $ts !== false ? date('d M Y', $ts) : '—' ?>
                </span>
              </td>
              <td style="font-size:.85rem">
                <?= (int)$event['total_participants'] ?>/<?= (int)$event['capacite'] ?>
                <div style="margin-top:4px;height:4px;background:#f0eeea;border-radius:4px">
                  <div style="width:<?= $pct ?>%;height:4px;background:#534AB7;border-radius:4px"></div>
                </div>
              </td>
              <td>
                <span class="places-value"><?= (int)($event['places_restantes'] ?? 0) ?></span>
                <span class="places-label"> places</span>
              </td>
              <td id="status<?= $eid ?>">
                <?php if ($isJoined): ?>
                  <span style="background:#E1F5EE;color:#0F6E56;padding:3px 10px;
                               border-radius:20px;font-size:.75rem;font-weight:500">✓ Inscrit</span>
                <?php elseif ($isFull): ?>
                  <span style="background:#FCEBEB;color:#A32D2D;padding:3px 10px;
                               border-radius:20px;font-size:.75rem">Complet</span>
                <?php else: ?>
                  <span style="background:#f0eeea;color:#6b7280;padding:3px 10px;
                               border-radius:20px;font-size:.75rem">Disponible</span>
                <?php endif; ?>
              </td>
              <td id="action<?= $eid ?>">
                <?php if ($isJoined): ?>
                  <button class="btn-sm btn-danger" data-id="<?= $eid ?>" onclick="leave(this)">
                    Se désinscrire
                  </button>
                <?php elseif (!$isFull): ?>
                  <button class="btn-sm" data-id="<?= $eid ?>" onclick="join(this)">
                    Participer →
                  </button>
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
            <?php
              $firstTs = isset($myEvents[0]['date_event'])
                         ? strtotime($myEvents[0]['date_event']) : false;
              echo $firstTs !== false ? date('d M', $firstTs) : '—';
            ?>
          </div>
        </div>
      </div>

      <div style="margin-bottom:16px">
        <a href="/Edu/public/client/export/inscriptions"
           style="display:inline-block;padding:8px 18px;background:#534AB7;color:#fff;
                  border-radius:8px;font-size:.85rem;text-decoration:none;font-weight:500">
          Exporter en PDF
        </a>
      </div>

      <div class="table-card">
        <table>
          <thead>
            <tr>
              <th>Titre</th>
              <th>Date</th>
              <th>Places restantes</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="myEventsBody">
            <?php foreach ($myEvents as $event):
              $eid = (int)$event['id'];
              $ts  = isset($event['date_event']) ? strtotime($event['date_event']) : false;
            ?>
            <tr id="myrow<?= $eid ?>">
              <td>
                <div class="event-title-cell">
                  <div class="event-dot" style="background:#0F6E56"></div>
                  <strong><?= htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
              </td>
              <td>
                <span class="date-badge">
                  📆 <?= $ts !== false ? date('d M Y', $ts) : '—' ?>
                </span>
              </td>
              <td>
                <span class="places-value"><?= (int)($event['places_restantes'] ?? 0) ?></span>
                <span class="places-label"> places</span>
              </td>
              <td>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                  <button class="btn-sm btn-danger"
                          data-id="<?= $eid ?>"
                          onclick="leaveFromMyEvents(this)">
                    Se désinscrire
                  </button>
                 
                </div>
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
                background:rgba(0,0,0,0.5);z-index:200;
                justify-content:center;align-items:flex-start;padding-top:80px">
      <div style="background:#fff;border-radius:16px;padding:24px;
                  width:420px;max-width:90vw;margin:0 auto">
        <h3 style="margin:0 0 16px;font-size:1rem;font-weight:600">Modifier le feedback</h3>

        <input type="hidden" id="edit_id">

        <div style="margin-bottom:14px">
          <label style="display:block;font-size:.8rem;color:#9ca3af;margin-bottom:8px">Note</label>
          <div style="display:flex;gap:8px;flex-direction:row-reverse;justify-content:flex-end">
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
                    style="width:100%;padding:10px 12px;border-radius:8px;
                           border:1px solid #e5e3de;font-size:.9rem;
                           box-sizing:border-box;min-height:90px;
                           resize:vertical;font-family:inherit"></textarea>
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
            <option value="<?= (int)$e['id'] ?>"><?= htmlspecialchars($e['titre'], ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-bottom:14px">
        <label style="display:block;font-size:.8rem;color:#9ca3af;font-weight:500;margin-bottom:8px">
          Note
        </label>
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
                         min-height:100px;resize:vertical;font-family:inherit"></textarea>
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
        <span style="font-weight:400;color:#9ca3af;font-size:.85rem">
          (<?= count($myFeedbacks) ?>)
        </span>
      </h2>

      <?php foreach ($myFeedbacks as $fb):
        $fbId   = (int)$fb['id'];
        $fbNote = (int)($fb['note'] ?? 0);
        $ts     = isset($fb['created_at']) ? strtotime($fb['created_at']) : false;
      ?>
      <div id="myfb<?= $fbId ?>"
           style="background:#fff;border:1px solid #e5e3de;border-radius:12px;
                  padding:16px 20px;margin-bottom:10px">
        <div style="display:flex;justify-content:space-between;align-items:start;gap:12px">
          <div style="flex:1">
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:8px">
              <?php if (!empty($fb['event_titre'])): ?>
                <span style="background:#EEEDFE;color:#3C3489;padding:2px 10px;
                             border-radius:20px;font-size:.75rem">
                  🎯 <?= htmlspecialchars($fb['event_titre'], ENT_QUOTES, 'UTF-8') ?>
                </span>
              <?php endif; ?>
              <?php if ($fbNote > 0): ?>
                <span id="myfb-stars<?= $fbId ?>" style="color:#EF9F27;font-size:.85rem">
                  <?= str_repeat('★', min($fbNote, 5)) . str_repeat('☆', 5 - min($fbNote, 5)) ?>
                </span>
              <?php else: ?>
                <span id="myfb-stars<?= $fbId ?>" style="color:#9ca3af;font-size:.8rem">
                  Sans note
                </span>
              <?php endif; ?>
              <span style="font-size:.75rem;color:#9ca3af">
                📅 <?= $ts !== false ? date('d M Y à H:i', $ts) : '—' ?>
              </span>
            </div>
            <div id="myfb-msg<?= $fbId ?>"
                 style="font-size:.875rem;line-height:1.5;color:#374151">
              <?= nl2br(htmlspecialchars($fb['message'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
            </div>
          </div>
          <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0">
            <button onclick="openEditModal(this.dataset.id, parseInt(this.dataset.note), this.dataset.msg)"
                    data-id="<?= $fbId ?>"
                    data-note="<?= $fbNote ?>"
                    data-msg="<?= htmlspecialchars($fb['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    style="background:#f0eeea;border:none;padding:5px 12px;
                           border-radius:6px;cursor:pointer;font-size:.8rem">
              ✏️ Modifier
            </button>
            <button onclick="deleteMine(<?= $fbId ?>)"
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

  <?php elseif ($activeTab === 'calendrier'): ?>

    <?php
      // Préparer les events sous forme JSON pour le JS
      $allEventsJson    = json_encode($events,   JSON_UNESCAPED_UNICODE);
      $myEventIdsJson   = json_encode($userEventIds);
    ?>

    <div id="cal-nav" style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
      <button onclick="changeMonth(-1)"
              style="background:#f0eeea;border:none;border-radius:8px;padding:8px 14px;cursor:pointer;font-size:1rem;">‹</button>
      <div id="cal-title"
           style="font-size:1.1rem;font-weight:600;min-width:180px;text-align:center"></div>
      <button onclick="changeMonth(1)"
              style="background:#f0eeea;border:none;border-radius:8px;padding:8px 14px;cursor:pointer;font-size:1rem;">›</button>
      <button onclick="goToday()"
              style="margin-left:8px;background:#534AB7;color:#fff;border:none;border-radius:8px;
                     padding:8px 16px;cursor:pointer;font-size:.85rem;font-weight:500;">
        Aujourd'hui
      </button>
    </div>

    <div style="background:#fff;border:1px solid #e5e3de;border-radius:16px;overflow:hidden;">
      <div id="cal-header"
           style="display:grid;grid-template-columns:repeat(7,1fr);
                  background:#f8f7f5;border-bottom:1px solid #e5e3de;">
      </div>
      <div id="cal-grid" style="display:grid;grid-template-columns:repeat(7,1fr);"></div>
    </div>

    <!-- Légende -->
    <div style="display:flex;gap:20px;margin-top:16px;font-size:.8rem;color:#6b7280;flex-wrap:wrap;">
      <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;
                         background:#534AB7;margin-right:6px;vertical-align:middle"></span>Inscrit</span>
      <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;
                         background:#9FE1CB;margin-right:6px;vertical-align:middle"></span>Disponible</span>
      <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;
                         background:#F7C1C1;margin-right:6px;vertical-align:middle"></span>Complet</span>
    </div>

    <!-- Popup détail event -->
    <div id="cal-popup"
         style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
                background:#fff;border-radius:16px;padding:24px;width:360px;max-width:90vw;
                box-shadow:0 8px 40px rgba(0,0,0,0.15);z-index:300;">
      <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;">
        <h3 id="popup-title" style="margin:0;font-size:1rem;font-weight:600;flex:1"></h3>
        <button onclick="closePopup()"
                style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#9ca3af;
                       padding:0;margin-left:12px;line-height:1;">×</button>
      </div>
      <div id="popup-date"  style="font-size:.85rem;color:#6b7280;margin-bottom:8px;"></div>
      <div id="popup-places" style="font-size:.85rem;color:#6b7280;margin-bottom:16px;"></div>
      <div id="popup-desc"
           style="font-size:.85rem;color:#374151;line-height:1.6;margin-bottom:20px;
                  max-height:100px;overflow-y:auto;"></div>
      <div id="popup-action"></div>
    </div>
    <div id="cal-overlay"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.3);z-index:299;"
         onclick="closePopup()"></div>

    <script>
    const CAL_EVENTS = <?= $allEventsJson ?>;
    let MY_EVENT_IDS = <?= $myEventIdsJson ?>;
    const CAL_BASE   = '/Edu/public';

    const JOURS = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
    const MOIS  = ['Janvier','Février','Mars','Avril','Mai','Juin',
                   'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    let cur = new Date();
    let curYear  = cur.getFullYear();
    let curMonth = cur.getMonth();

    function buildCalendar(year, month) {
      document.getElementById('cal-title').textContent = MOIS[month] + ' ' + year;

      // Header jours
      const hdr = document.getElementById('cal-header');
      hdr.innerHTML = '';
      JOURS.forEach(j => {
        const d = document.createElement('div');
        d.textContent = j;
        d.style.cssText = 'padding:10px 0;text-align:center;font-size:.78rem;' +
                          'font-weight:500;color:#9ca3af;';
        hdr.appendChild(d);
      });

      const grid = document.getElementById('cal-grid');
      grid.innerHTML = '';

      const first = new Date(year, month, 1);
      // lundi=0 … dimanche=6
      let startDay = (first.getDay() + 6) % 7;
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const today = new Date();

      // Indexer les events par date YYYY-MM-DD
      const byDate = {};
      CAL_EVENTS.forEach(ev => {
        const d = ev.date_event ? ev.date_event.substring(0,10) : null;
        if (!d) return;
        if (!byDate[d]) byDate[d] = [];
        byDate[d].push(ev);
      });

      // Cases vides avant le 1er
      for (let i = 0; i < startDay; i++) {
        const empty = document.createElement('div');
        empty.style.cssText = 'min-height:90px;border-right:0.5px solid #f0eeea;' +
                              'border-bottom:0.5px solid #f0eeea;background:#fafaf8;';
        grid.appendChild(empty);
      }

      for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = year + '-' +
          String(month+1).padStart(2,'0') + '-' +
          String(day).padStart(2,'0');

        const isToday = (today.getFullYear()===year &&
                         today.getMonth()===month &&
                         today.getDate()===day);

        const cell = document.createElement('div');
        cell.style.cssText = 'min-height:90px;border-right:0.5px solid #f0eeea;' +
                             'border-bottom:0.5px solid #f0eeea;padding:6px;' +
                             'box-sizing:border-box;vertical-align:top;' +
                             (isToday ? 'background:#F5F3FF;' : '');

        // Numéro du jour
        const num = document.createElement('div');
        num.textContent = day;
        num.style.cssText = 'font-size:.8rem;font-weight:' + (isToday?'700':'400') + ';' +
          'color:' + (isToday?'#534AB7':'#6b7280') + ';margin-bottom:4px;';
        cell.appendChild(num);

        // Events du jour
        const evs = byDate[dateStr] || [];
        evs.slice(0,3).forEach(ev => {
          const isJoined = MY_EVENT_IDS.includes(ev.id);
          const isFull   = (parseInt(ev.places_restantes)||0) <= 0;

          let bg = '#9FE1CB'; let col = '#085041'; // disponible
          if (isJoined)    { bg = '#EEEDFE'; col = '#3C3489'; }
          else if (isFull) { bg = '#F7C1C1'; col = '#791F1F'; }

          const pill = document.createElement('div');
          pill.textContent = ev.titre.length > 18 ? ev.titre.substring(0,17)+'…' : ev.titre;
          pill.title       = ev.titre;
          pill.style.cssText = 'background:' + bg + ';color:' + col + ';' +
            'font-size:.68rem;padding:2px 6px;border-radius:4px;margin-bottom:2px;' +
            'cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;' +
            'font-weight:500;';
          pill.onclick = (e) => { e.stopPropagation(); openPopup(ev); };
          cell.appendChild(pill);
        });

        if (evs.length > 3) {
          const more = document.createElement('div');
          more.textContent = '+' + (evs.length - 3) + ' autres';
          more.style.cssText = 'font-size:.65rem;color:#9ca3af;margin-top:2px;cursor:pointer;';
          more.onclick = () => openPopup(evs[3]);
          cell.appendChild(more);
        }

        grid.appendChild(cell);
      }
    }

    function changeMonth(dir) {
      curMonth += dir;
      if (curMonth > 11) { curMonth = 0; curYear++; }
      if (curMonth < 0)  { curMonth = 11; curYear--; }
      buildCalendar(curYear, curMonth);
    }

    function goToday() {
      const t = new Date();
      curYear = t.getFullYear(); curMonth = t.getMonth();
      buildCalendar(curYear, curMonth);
    }

    function openPopup(ev) {
      const isJoined = MY_EVENT_IDS.includes(ev.id);
      const isFull   = (parseInt(ev.places_restantes)||0) <= 0;
      const ts       = ev.date_event ? new Date(ev.date_event) : null;

      document.getElementById('popup-title').textContent  = ev.titre;
      document.getElementById('popup-date').textContent   =
        ts ? '📅 ' + ts.toLocaleDateString('fr-FR',{day:'numeric',month:'long',year:'numeric'}) : '';
      document.getElementById('popup-places').textContent =
        '👥 ' + (parseInt(ev.places_restantes)||0) + ' place(s) restante(s) / ' + (parseInt(ev.capacite)||0);
      document.getElementById('popup-desc').textContent   = ev.description || '';

      const actionEl = document.getElementById('popup-action');
      if (isJoined) {
        actionEl.innerHTML =
          '<button onclick="popupLeave(' + ev.id + ')" ' +
          'style="width:100%;padding:10px;background:rgba(239,68,68,0.1);color:#dc2626;' +
          'border:1px solid rgba(239,68,68,0.2);border-radius:8px;cursor:pointer;font-size:.9rem;font-weight:500;">' +
          'Se désinscrire</button>';
      } else if (!isFull) {
        actionEl.innerHTML =
          '<button onclick="popupJoin(' + ev.id + ')" ' +
          'style="width:100%;padding:10px;background:#534AB7;color:#fff;' +
          'border:none;border-radius:8px;cursor:pointer;font-size:.9rem;font-weight:500;">' +
          'Participer →</button>';
      } else {
        actionEl.innerHTML =
          '<div style="text-align:center;color:#A32D2D;font-size:.85rem;font-weight:500;">Événement complet</div>';
      }

      document.getElementById('cal-popup').style.display   = 'block';
      document.getElementById('cal-overlay').style.display = 'block';
    }

    function closePopup() {
      document.getElementById('cal-popup').style.display   = 'none';
      document.getElementById('cal-overlay').style.display = 'none';
    }

    async function popupJoin(id) {
      const fd = new FormData(); fd.append('id', id);
      const res = await fetch(CAL_BASE + '/events/join', {method:'POST',body:fd});
      const data = await res.json();
      if (data.success) {
        MY_EVENT_IDS.push(id);
        showToast('Inscription réussie !');
        closePopup();
        buildCalendar(curYear, curMonth);
      } else { showToast(data.message, 'error'); }
    }

    async function popupLeave(id) {
      if (!confirm('Se désinscrire ?')) return;
      const fd = new FormData(); fd.append('id', id);
      const res = await fetch(CAL_BASE + '/events/leave', {method:'POST',body:fd});
      const data = await res.json();
      if (data.success) {
        const idx = MY_EVENT_IDS.indexOf(id);
        if (idx > -1) MY_EVENT_IDS.splice(idx, 1);
        showToast('Désinscription effectuée');
        closePopup();
        buildCalendar(curYear, curMonth);
      } else { showToast(data.message, 'error'); }
    }

    buildCalendar(curYear, curMonth);
    </script>

  <?php endif; ?>

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
  el.textContent      = msg;
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
  if (!res.ok) throw new Error('HTTP ' + res.status);
  return res.json();
}

function hoverStars(n) {
  document.querySelectorAll('label[for^="s"]').forEach(l => {
    const v = parseInt(l.getAttribute('for').replace('s', ''));
    l.style.color = v <= n ? '#EF9F27' : '#e5e3de';
  });
}
function resetStars() {
  document.querySelectorAll('label[for^="s"]').forEach(l => {
    const v = parseInt(l.getAttribute('for').replace('s', ''));
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
    alertEl.style.cssText = 'display:block;background:#FCEBEB;color:#A32D2D;border:1px solid #F7C1C1;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:.9rem';
    alertEl.textContent = 'Message trop court (min. 5 caractères)';
    return;
  }

  const fd = new FormData();
  fd.append('message',  message);
  fd.append('event_id', eventId);
  fd.append('note',     selectedNote || '');

  let data;
  try {
    const res = await fetch(BASE + '/feedback/send', { method: 'POST', body: fd });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    data = await res.json();
  } catch (err) {
    showToast('Erreur réseau : ' + err.message, 'error');
    return;
  }

  alertEl.style.display = 'block';
  if (data.status === 'success') {
    alertEl.style.cssText = 'display:block;background:#E1F5EE;color:#0F6E56;border:1px solid #9FE1CB;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:.9rem';
    alertEl.textContent   = data.message;
    document.getElementById('fb_message').value = '';
    document.getElementById('fb_event').value   = '';
    selectedNote = 0;
    resetStars();
    showToast('Feedback envoyé !');
  } else {
    alertEl.style.cssText = 'display:block;background:#FCEBEB;color:#A32D2D;border:1px solid #F7C1C1;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:.9rem';
    alertEl.textContent   = data.message;
  }
  setTimeout(() => alertEl.style.display = 'none', 4000);
}

async function join(btn) {
  const id = btn.dataset.id;
  btn.disabled = true; btn.textContent = '...';
  try {
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
  } catch (err) {
    showToast('Erreur réseau', 'error');
    btn.textContent = 'Participer →'; btn.disabled = false;
  }
}

async function leave(btn) {
  if (!confirm('Se désinscrire de cet événement ?')) return;
  const id = btn.dataset.id;
  btn.disabled = true; btn.textContent = '...';
  try {
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
  } catch (err) {
    showToast('Erreur réseau', 'error');
    btn.textContent = 'Se désinscrire'; btn.disabled = false;
  }
}

async function leaveFromMyEvents(btn) {
  if (!confirm('Se désinscrire de cet événement ?')) return;
  const id = btn.dataset.id;
  btn.disabled = true; btn.textContent = '...';
  try {
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
  } catch (err) {
    showToast('Erreur réseau', 'error');
    btn.textContent = 'Se désinscrire'; btn.disabled = false;
  }
}

let editNote = 0;

function hoverEditStars(n) {
  document.querySelectorAll('label[for^="es"]').forEach(l => {
    const v = parseInt(l.getAttribute('for').replace('es', ''));
    l.style.color = v <= n ? '#EF9F27' : '#e5e3de';
  });
}
function resetEditStars() {
  document.querySelectorAll('label[for^="es"]').forEach(l => {
    const v = parseInt(l.getAttribute('for').replace('es', ''));
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
  editNote = note || 0;
  document.querySelectorAll('label[for^="es"]').forEach(l => {
    const v = parseInt(l.getAttribute('for').replace('es', ''));
    l.style.color = v <= editNote ? '#EF9F27' : '#e5e3de';
  });
  if (editNote) {
    const r = document.getElementById('es' + editNote);
    if (r) r.checked = true;
  }
  document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
  editNote = 0;
}

async function submitEdit() {
  const id      = document.getElementById('edit_id').value;
  const message = document.getElementById('edit_message').value.trim();
  if (message.length < 5) { showToast('Message trop court', 'error'); return; }

  const fd = new FormData();
  fd.append('id', id); fd.append('message', message); fd.append('note', editNote || '');

  let data;
  try {
    const res = await fetch(BASE + '/feedback/update-mine', { method: 'POST', body: fd });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    data = await res.json();
  } catch (err) {
    showToast('Erreur réseau', 'error'); return;
  }

  if (data.status === 'success') {
    showToast('Feedback modifié !');
    closeEditModal();
    const msgEl   = document.getElementById('myfb-msg' + id);
    const starsEl = document.getElementById('myfb-stars' + id);
    if (msgEl)   msgEl.innerHTML   = message.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])).replace(/\n/g, '<br>');
    if (starsEl) {
      starsEl.style.color = editNote ? '#EF9F27' : '#9ca3af';
      starsEl.textContent = editNote
        ? '★'.repeat(editNote) + '☆'.repeat(5 - editNote)
        : 'Sans note';
    }
  } else {
    showToast(data.message, 'error');
  }
}

async function deleteMine(id) {
  if (!confirm('Supprimer ce feedback ?')) return;
  const fd = new FormData();
  fd.append('id', id);
  let data;
  try {
    const res = await fetch(BASE + '/feedback/delete-mine', { method: 'POST', body: fd });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    data = await res.json();
  } catch (err) {
    showToast('Erreur réseau', 'error'); return;
  }
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