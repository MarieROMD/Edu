<?php
$events = $events ?? [];
$userEvents = $userEvents ?? [];
$totalEvents = $totalEvents ?? count($events);
$totalParticipants = $totalParticipants ?? array_sum(array_column($events, 'total_participants'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EduEvents Pro</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/Edu/public/css/style1.css">
  
</head>
<body>

<header>
  <span class="logo">Edu<span>Events</span></span>
  <nav>
    <a href="#events">Événements</a>
    <a href="#about">À propos</a>
    <a href="#contact">Contact</a>
<a href="/Edu/public/login" class="nav-btn">Connexion</a>
  </nav>
</header>

<section class="hero">
  <div class="hero-text">
    <h1>Découvrez des événements qui vous inspirent</h1>
    <p>Participez à des ateliers, conférences et formations pensées pour votre développement.</p>
    <div class="search-wrap">
      <input type="text" id="search" placeholder="Rechercher un événement…">
      <button>Chercher</button>
    </div>
  </div>
  <div class="hero-visual">
    <div class="pill"><span class="dot dot-sage"></span>Ateliers créatifs</div>
    <div class="pill"><span class="dot dot-lav"></span>Conférences</div>
    <div class="pill"><span class="dot dot-blush"></span>Formations</div>
    <div class="pill"><span class="dot dot-sky"></span>Networking</div>
  </div>
</section>

<section id="events" class="section">
  <div class="section-head">
    <h2>Tous les événements</h2>
    <span><?= $totalEvents ?> événements disponibles</span>
  </div>

  <div class="grid" id="eventsContainer">
  <?php foreach ($events as $e):
    $isFull   = $e['places_restantes'] <= 0;
    $isJoined = in_array($e['id'], $userEvents);
    $pct      = $e['capacite'] > 0 ? ($e['total_participants'] / $e['capacite']) * 100 : 0;
  ?>
  <div class="card" data-title="<?= strtolower(htmlspecialchars($e['titre'])) ?>">
    <div class="card-img">

<img src="<?= !empty($e['image_url']) ? '/Edu/public/' . htmlspecialchars($e['image_url']) : 'https://placehold.co/400x200/EDE8F5/8FAF9A?text=' . urlencode($e['titre']) ?>"           alt="<?= htmlspecialchars($e['titre']) ?>">
      <?php if ($isFull): ?>
        <span class="badge badge-full">Complet</span>
      <?php elseif ($isJoined): ?>
        <span class="badge badge-joined">✓ Inscrit</span>
      <?php else: ?>
        <span class="badge badge-open">Disponible</span>
      <?php endif; ?>
    </div>

    <div class="card-body">
      <h3><?= htmlspecialchars($e['titre']) ?></h3>
      <div class="meta">
        <span>👥 <?= $e['total_participants'] ?>/<?= $e['capacite'] ?> places</span>
        <span>📅 <?= date('d M Y', strtotime($e['date_event'])) ?></span>
      </div>
      <div class="bar-wrap"><div class="bar" style="width:<?= round($pct) ?>%"></div></div>

      <?php if ($isFull): ?>
        <button class="card-btn btn-disabled" disabled>Complet</button>
      <?php elseif ($isJoined): ?>
        <button class="card-btn btn-joined">✓ Déjà inscrit</button>
      <?php elseif (isset($_SESSION['user_id'])): ?>
        <button class="card-btn btn-join" data-id="<?= $e['id'] ?>">Participer →</button>
      <?php else: ?>
        <a href="/Edu/public/login" class="card-btn btn-login" style="display:block;text-align:center;">Se connecter</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
</section>

<section id="about" class="about">
  <h2>Notre mission</h2>
  <p>Une plateforme moderne et inclusive pour découvrir, organiser et participer à des événements éducatifs de qualité.</p>
  <div class="stats">
    <div class="stat"><strong><?= $totalEvents ?>+</strong><span>Événements</span></div>
    <div class="stat"><strong><?= $totalParticipants ?>+</strong><span>Participants</span></div>
    <div class="stat"><strong>100%</strong><span>Gratuit</span></div>
  </div>
</section>

<section id="contact" class="contact">
  <h2>Nous contacter</h2>
  <form class="contact-form" method="POST" action="/contact">
    <input  type="text"  name="name"    placeholder="Votre nom"     required>
    <input  type="email" name="email"   placeholder="Votre email"   required>
    <textarea name="message" rows="4"   placeholder="Votre message" required></textarea>
    <button type="submit">Envoyer le message</button>
  </form>
</section>

<footer>© <?= date('Y') ?> EduEvents Pro — Tous droits réservés</footer>

<script>
document.getElementById('search').addEventListener('input', function () {
  const val = this.value.toLowerCase();
  document.querySelectorAll('.card').forEach(c => {
    c.style.display = c.dataset.title.includes(val) ? '' : 'none';
  });
});

document.querySelectorAll('.card-btn.btn-join').forEach(btn => {
  btn.addEventListener('click', async () => {
    const res  = await fetch('/events/join', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + btn.dataset.id
    });
    const data = await res.json();
    if (data.success) {
      btn.textContent = '✓ Déjà inscrit';
      btn.className = 'card-btn btn-joined';
    } else {
      btn.textContent = data.message;
    }
  });
});
</script>
</body>
</html>