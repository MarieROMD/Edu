<?php
require __DIR__ . '/../../../config.php';

if(isset($_POST['register'])){
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $check->execute([$email]);

    if($check->rowCount() > 0){
        $error = "Email déjà utilisé";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users(username,email,password) VALUES(?,?,?)");
        $stmt->execute([$username, $email, $password]);
        header("Location: /Edu/public/login");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription</title>
  <link rel="stylesheet" href="/Edu/public/css/auth.css">
</head>
<body>

<div class="auth-box">

  <div class="auth-box-header">
    <div class="icon">🎓</div>
    <h2>Créer un compte</h2>
    <p>Rejoignez EduEvents dès aujourd'hui</p>
  </div>

  <div class="auth-body">

    <?php if(isset($error)): ?>
      <div class="auth-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">

      <div class="field-group">
        <label>Nom d'utilisateur</label>
        <div class="input-wrap">
          <span class="input-icon">👤</span>
          <input name="username" type="text" placeholder="ex : marie_dupont" required>
        </div>
      </div>

      <div class="field-group">
        <label>Adresse email</label>
        <div class="input-wrap">
          <span class="input-icon">✉</span>
          <input name="email" type="email" placeholder="vous@exemple.com" required>
        </div>
      </div>

      <div class="field-group">
        <label>Mot de passe</label>
        <div class="input-wrap">
          <span class="input-icon">🔒</span>
          <input name="password" type="password" placeholder="Min. 8 caractères"
                 id="pwd" oninput="checkStrength(this.value)" required>
        </div>
        <div class="pwd-strength">
          <div class="pwd-bar" id="b1"></div>
          <div class="pwd-bar" id="b2"></div>
          <div class="pwd-bar" id="b3"></div>
          <div class="pwd-bar" id="b4"></div>
        </div>
        <div class="pwd-hint" id="pwd-hint">Lettres, chiffres et symboles recommandés</div>
      </div>

      <button name="register" class="btn-submit">S'inscrire</button>

    </form>

    <div class="link">
      <a href="/Edu/public/login">Déjà un compte ? Se connecter</a>
    </div>

  </div>
</div>  

<script>
function checkStrength(v) {
  var score = 0;
  if (v.length >= 8)           score++;
  if (/[A-Z]/.test(v))         score++;
  if (/[0-9]/.test(v))         score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;

  var colors = ['', '#E24B4A', '#EF9F27', '#378ADD', '#1D9E75'];
  var labels = ['', 'Trop faible', 'Faible', 'Moyen', 'Fort'];

  for (var i = 1; i <= 4; i++) {
    document.getElementById('b' + i).style.background =
      i <= score ? colors[score] : '#e5e7eb';
  }
  document.getElementById('pwd-hint').textContent =
    v.length ? labels[score] : 'Lettres, chiffres et symboles recommandés';
  document.getElementById('pwd-hint').style.color =
    v.length ? colors[score] : '#9ca3af';
}
</script>

</body>
</html>