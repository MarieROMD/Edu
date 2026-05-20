<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion</title>
  <link rel="stylesheet" href="/Edu/public/css/auth.css">
</head>
<body>

<div class="auth-box">

  <div class="auth-box-header">
    <div class="icon">🔐</div>
    <h2>Connexion</h2>
    <p>Accédez à votre compte</p>
  </div>

  <div class="auth-body">

    <?php if(isset($error)): ?>
      <div class="auth-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

   <form method="POST" action="/Edu/public/index.php/login">
      <div class="field-group">
        <label>Email</label>
        <div class="input-wrap">
          <span class="input-icon">✉</span>
          <input name="email" type="email" placeholder="vous@exemple.com" required>
        </div>
      </div>

      <div class="field-group">
        <label>Mot de passe</label>
        <div class="input-wrap">
          <span class="input-icon">🔒</span>
          <input name="password" type="password" placeholder="••••••••" required>
        </div>
      </div>

      <button type="submit" class="btn-submit">Se connecter</button>

    </form>

    <div class="link">
      <a href="/Edu/public/register">Créer un compte</a>
    </div>

  </div>
</div>

</body>
</html>