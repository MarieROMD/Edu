<style>
.feedback-wrap {
  max-width: 600px;
}
.feedback-wrap h2 {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 20px;
}
.field { margin-bottom: 16px; }
.field label {
  display: block;
  font-size: .8rem;
  color: #9ca3af;
  margin-bottom: 6px;
  font-weight: 500;
}
.field select,
.field textarea {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid var(--border, #e5e3de);
  background: var(--surface, #fafaf8);
  color: var(--text, #1a1a1a);
  font-size: .9rem;
  box-sizing: border-box;
  font-family: inherit;
}
.field textarea { resize: vertical; min-height: 100px; }
.field select:focus,
.field textarea:focus { border-color: #534AB7; outline: none; }

.stars { display: flex; gap: 8px; flex-direction: row-reverse; justify-content: flex-end; }
.stars input { display: none; }
.stars label {
  font-size: 1.6rem;
  cursor: pointer;
  color: #e5e3de;
  transition: color .15s;
  margin: 0;
}
.stars input:checked ~ label,
.stars label:hover,
.stars label:hover ~ label { color: #EF9F27; }

.btn-feedback {
  padding: 10px 24px;
  background: #534AB7;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  font-size: .9rem;
  transition: .2s;
}
.btn-feedback:hover { background: #3C3489; }


.feedback-history { margin-top: 40px; }
.feedback-item {
  background: var(--card, #fff);
  border: 1px solid var(--border, #e5e3de);
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 10px;
}
.feedback-item .fb-meta {
  font-size: .78rem;
  color: #9ca3af;
  margin-bottom: 8px;
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.feedback-item .fb-message {
  font-size: .9rem;
  line-height: 1.5;
}
.fb-stars { color: #EF9F27; font-size: .9rem; }
</style>

<div class="feedback-wrap">

  <h2>💬 Laisser un feedback</h2>

  <div id="fbAlert" style="display:none;padding:12px 16px;border-radius:10px;
       margin-bottom:16px;font-size:.9rem"></div>

  <div class="field">
    <label>Événement concerné (optionnel)</label>
    <select id="fb_event">
      <option value="">— Feedback général —</option>
      <?php if (!empty($myEvents) && is_array($myEvents)): ?>
        <?php foreach ($myEvents as $e): ?>
          <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['titre']) ?></option>
        <?php endforeach; ?>
      <?php endif; ?>
    </select>
  </div>

  <div class="field">
    <label>Note</label>
    <div class="stars">
      <input type="radio" name="note" id="s5" value="5"><label for="s5">★</label>
      <input type="radio" name="note" id="s4" value="4"><label for="s4">★</label>
      <input type="radio" name="note" id="s3" value="3"><label for="s3">★</label>
      <input type="radio" name="note" id="s2" value="2"><label for="s2">★</label>
      <input type="radio" name="note" id="s1" value="1"><label for="s1">★</label>
    </div>
  </div>

  <div class="field">
    <label>Votre message</label>
    <textarea id="fb_message" placeholder="Partagez votre expérience…"></textarea>
  </div>

  <button class="btn-feedback" onclick="sendFeedback()">Envoyer →</button>

  <?php if (!empty($myFeedbacks)): ?>
  <div class="feedback-history">
    <h2 style="font-size:1rem;font-weight:600;margin:0 0 16px">Mes feedbacks envoyés</h2>
    <?php foreach ($myFeedbacks as $fb): ?>
    <div class="feedback-item">
      <div class="fb-meta">
        <span>📅 <?= date('d M Y à H:i', strtotime($fb['created_at'])) ?></span>
        <?php if ($fb['event_titre']): ?>
          <span>🎯 <?= htmlspecialchars($fb['event_titre']) ?></span>
        <?php endif; ?>
        <?php if ($fb['note']): ?>
          <span class="fb-stars"><?= str_repeat('★', $fb['note']) . str_repeat('☆', 5 - $fb['note']) ?></span>
        <?php endif; ?>
      </div>
      <div class="fb-message"><?= nl2br(htmlspecialchars($fb['message'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<script>
async function sendFeedback() {
  const message  = document.getElementById('fb_message').value.trim();
  const eventId  = document.getElementById('fb_event').value;
  const noteEl   = document.querySelector('input[name="note"]:checked');
  const note     = noteEl ? noteEl.value : '';
  const alertEl  = document.getElementById('fbAlert');

  const fd = new FormData();
  fd.append('message',  message);
  fd.append('event_id', eventId);
  fd.append('note',     note);

  const res  = await fetch('/Edu/public/feedback/send', { method: 'POST', body: fd });
  const data = await res.json();

  alertEl.style.display = 'block';
  if (data.status === 'success') {
    alertEl.style.background = '#E1F5EE';
    alertEl.style.color      = '#0F6E56';
    alertEl.style.border     = '1px solid #9FE1CB';
    alertEl.textContent      = data.message;
    document.getElementById('fb_message').value = '';
    if (noteEl) noteEl.checked = false;
    document.getElementById('fb_event').value = '';
  } else {
    alertEl.style.background = '#FCEBEB';
    alertEl.style.color      = '#A32D2D';
    alertEl.style.border     = '1px solid #F7C1C1';
    alertEl.textContent      = data.message;
  }

  setTimeout(() => alertEl.style.display = 'none', 4000);
}
</script>