<?php if (!isset($_SESSION['user_id'])) { header("Location: /Edu/public/login"); exit; } ?>
<?php
$msgs = [
    'username'  => 'Nom trop court (min. 2 caractères).',
    'email'     => 'Email invalide.',
    'emailused' => 'Email déjà utilisé par un autre compte.',
    'pwd'       => 'Mot de passe trop court (min. 8 caractères).',
    'confirm'   => 'Les mots de passe ne correspondent pas.',
];
$user = $user ?? [
  'username' => $_SESSION['username'] ?? '',
  'email'    => $_SESSION['email'] ?? '',
  'role'     => $_SESSION['role'] ?? 'Membre',
];
$success = $success ?? false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon profil</title>
  <link rel="stylesheet" href="/Edu/public/css/dashboard-client.css">
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">Edu<span>Platform</span></div>
  <div class="nav-section-label">Menu</div>
  <nav>
    <a href="/Edu/public/dashboard/client" class="nav-item">🏠 Accueil</a>
    <a href="/Edu/public/dashboard/client?tab=events" class="nav-item">📅 Événements</a>
    <a href="/Edu/public/dashboard/client?tab=myevents" class="nav-item">🎟 Mes inscriptions</a>
    <a href="/Edu/public/dashboard/client?tab=feedback" class="nav-item">💬 Feedback</a>
    <a href="/Edu/public/profile" class="nav-item active">👤 Mon profil</a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-pill">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
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
      <h1 class="page-title">Mon profil</h1>
      <p class="page-subtitle">Modifier vos informations personnelles</p>
    </div>
  </div>

  <?php if (isset($error) && isset($msgs[$error])): ?>
    <div style="background:#FCEBEB;color:#A32D2D;border:1px solid #F7C1C1;
                padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:.9rem">
      ⚠ <?= $msgs[$error] ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div style="background:#E1F5EE;color:#0F6E56;border:1px solid #9FE1CB;
                padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:.9rem">
      ✓ Profil mis à jour avec succès.
    </div>
  <?php endif; ?>

  <div class="table-card" style="max-width:520px;padding:28px">

    <div style="display:flex;align-items:center;gap:16px;margin-bottom:28px">
      <div style="width:60px;height:60px;border-radius:50%;background:#EEEDFE;
                  color:#3C3489;font-size:1.5rem;font-weight:500;
                  display:flex;align-items:center;justify-content:center">
        <?= strtoupper(substr($user['username'] ?? $_SESSION['username'], 0, 1)) ?>
      </div>
      <div>
        <div style="font-weight:600;font-size:1rem"><?= htmlspecialchars($user['username']) ?></div>
        <div style="font-size:.85rem;color:#9ca3af"><?= htmlspecialchars($user['email']) ?></div>
        <div style="font-size:.75rem;margin-top:2px">
          <span style="background:#E1F5EE;color:#0F6E56;padding:2px 8px;border-radius:12px">
            <?= $user['role'] ?>
          </span>
        </div>
      </div>
    </div>

    <form method="POST" action="/Edu/public/profile">

      <label style="display:block;font-size:.8rem;color:#9ca3af;margin-bottom:4px">Nom d'utilisateur</label>
      <input name="username" type="text"
             value="<?= htmlspecialchars($user['username']) ?>"
             style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #e5e3de;
                    box-sizing:border-box;margin-bottom:16px;font-size:.9rem">

      <label style="display:block;font-size:.8rem;color:#9ca3af;margin-bottom:4px">Adresse email</label>
      <input name="email" type="email"
             value="<?= htmlspecialchars($user['email']) ?>"
             style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #e5e3de;
                    box-sizing:border-box;margin-bottom:16px;font-size:.9rem">

      <hr style="border:none;border-top:1px solid #f0eeea;margin:4px 0 16px">

      <label style="display:block;font-size:.8rem;color:#9ca3af;margin-bottom:4px">
        Nouveau mot de passe <span style="color:#c4c2bb">(laisser vide = inchangé)</span>
      </label>
      <input name="password" type="password" placeholder="Min. 8 caractères"
             style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #e5e3de;
                    box-sizing:border-box;margin-bottom:16px;font-size:.9rem">

      <label style="display:block;font-size:.8rem;color:#9ca3af;margin-bottom:4px">
        Confirmer le mot de passe
      </label>
      <input name="confirm" type="password" placeholder="Répéter le mot de passe"
             style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #e5e3de;
                    box-sizing:border-box;margin-bottom:24px;font-size:.9rem">

      <button type="submit"
              style="width:100%;padding:10px;background:#534AB7;color:#fff;border:none;
                     border-radius:8px;font-weight:600;cursor:pointer;font-size:.9rem">
        Enregistrer les modifications
      </button>
    </form>

  </div>
</main>
</body>
</html>