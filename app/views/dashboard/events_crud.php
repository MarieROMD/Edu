<style>
.page {
  width: 100%;
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 20px;
}
.card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}
.card h2 {
  font-family: 'Syne', sans-serif;
  font-size: 1.1rem;
  margin-bottom: 12px;
}
.card input, .card select, .card textarea {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
  font-size: 0.9rem;
  box-sizing: border-box;
  font-family: inherit;
}
.card textarea { resize: vertical; min-height: 80px; }
.card input:focus, .card select:focus, .card textarea:focus {
  border-color: var(--accent); outline: none;
}
.btn {
  width: 100%; padding: 10px; border: none;
  border-radius: 10px; background: var(--accent);
  color: white; font-weight: 600; cursor: pointer;
  transition: 0.2s; margin-top: 8px;
}
.btn:hover { background: #2563eb; }
.crud-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.crud-table td { padding: 12px; border-bottom: 1px solid var(--border); font-size: 0.85rem; }
.crud-table tr:hover td { background: rgba(255,255,255,0.03); }
.btn-edit {
  background: rgba(59,130,246,0.15); color: #60a5fa;
  border: none; padding: 6px 10px; border-radius: 8px;
  cursor: pointer; margin-right: 6px;
}
.btn-delete {
  background: rgba(239,68,68,0.15); color: #f87171;
  border: none; padding: 6px 10px; border-radius: 8px; cursor: pointer;
}
.modal-overlay {
  position: fixed; top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  display: flex; justify-content: center; align-items: center;
  opacity: 0; pointer-events: none; transition: 0.25s; z-index: 200;
}
.modal-overlay.show { opacity: 1; pointer-events: auto; }
.modal-box {
  background: var(--card); border: 1px solid var(--border);
  padding: 24px; border-radius: 16px; width: 380px;
}
.modal-box h3 { font-family: 'Syne', sans-serif; margin: 0 0 16px; }
.modal-box input, .modal-box select, .modal-box textarea {
  width: 100%; padding: 10px; margin: 6px 0;
  border-radius: 10px; border: 1px solid var(--border);
  background: var(--surface); color: var(--text);
  font-size: 0.9rem; box-sizing: border-box; font-family: inherit;
}
.modal-box textarea { resize: vertical; min-height: 70px; }
.btn-close {
  width: 100%; padding: 9px; margin-top: 8px;
  border: 1px solid var(--border); border-radius: 10px;
  background: transparent; color: var(--text); cursor: pointer; font-size: 0.9rem;
}
@media(max-width: 900px) { .page { grid-template-columns: 1fr; } }
</style>

<div class="page">

  <div class="card">
    <h2>Nouvel événement</h2>

    <input    id="e_titre"       placeholder="Titre de l'événement">
    <textarea id="e_description" placeholder="Description"></textarea>
    <input    id="e_date"        placeholder="Date (YYYY-MM-DD)" type="date">
    <input    id="e_capacite"    placeholder="Capacité (ex: 50)" type="number" min="1">
    <input    id="e_image"       placeholder="URL de l'image (optionnel)">

    <button class="btn" onclick="EventCRUD.save()">Enregistrer</button>
  </div>

  <div class="card">
    <h2>Événements</h2>

    <table class="crud-table">
      <thead>
        <tr>
          <th style="text-align:left;padding:8px 12px;font-size:.75rem;color:var(--text);opacity:.5">Titre</th>
          <th style="text-align:left;padding:8px 12px;font-size:.75rem;color:var(--text);opacity:.5">Date</th>
          <th style="text-align:left;padding:8px 12px;font-size:.75rem;color:var(--text);opacity:.5">Capacité</th>
          <th style="text-align:left;padding:8px 12px;font-size:.75rem;color:var(--text);opacity:.5">Actions</th>
        </tr>
      </thead>
      <tbody id="eventBody">
        <?php foreach ($events ?? [] as $e): ?>
        <tr id="erow<?= (int)$e['id'] ?>">
          <td><strong><?= htmlspecialchars($e['titre'], ENT_QUOTES, 'UTF-8') ?></strong></td>
          <td style="color:#9ca3af;font-size:.8rem">
            <?php
              $ts = isset($e['date_event']) ? strtotime($e['date_event']) : false;
              echo $ts !== false ? date('d M Y', $ts) : '—';
            ?>
          </td>
          <td style="font-size:.8rem">
            <?= (int)$e['total_participants'] ?>/<?= (int)$e['capacite'] ?>
            <?php if ((int)$e['places_restantes'] <= 0): ?>
              <span style="color:#f87171;font-size:.75rem"> · Complet</span>
            <?php endif; ?>
          </td>
          <td>
            <?php
             
              $eJson = htmlspecialchars(json_encode([
                'id'          => (int)$e['id'],
                'titre'       => $e['titre'],
                'description' => $e['description'],
                'date_event'  => $e['date_event'],
                'capacite'    => (int)$e['capacite'],
                'image_url'   => $e['image_url'] ?? '',
              ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
            ?>
            <button class="btn-edit"
              onclick="EventCRUD.openModalFromJson(this.dataset.event)"
              data-event="<?= $eJson ?>">✏️</button>
            <button class="btn-delete"
              onclick="EventCRUD.del(<?= (int)$e['id'] ?>, <?= htmlspecialchars(json_encode($e['titre']), ENT_QUOTES, 'UTF-8') ?>)">
              🗑️
            </button>

            <?php  ?>
            <a href="/Edu/public/admin/export/event?id=<?= (int)$e['id'] ?>"
               style="background:#E1F5EE;color:#0F6E56;border:none;padding:5px 10px;
                      border-radius:6px;font-size:.8rem;text-decoration:none">
              PDF
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<div class="modal-overlay" id="eventModal">
  <div class="modal-box">
    <h3>Modifier événement</h3>

    <input type="hidden" id="m_eid">
    <input    id="m_titre"       placeholder="Titre">
    <textarea id="m_description" placeholder="Description"></textarea>
    <input    id="m_date"        type="date">
    <input    id="m_capacite"    type="number" min="1" placeholder="Capacité">
    <input    id="m_image"       placeholder="URL image (optionnel)">

    <button class="btn"       onclick="EventCRUD.update()">Enregistrer</button>
    <button class="btn-close" onclick="EventCRUD.closeModal()">Fermer</button>
  </div>
</div>

<script>
const EventCRUD = (() => {
  const BASE = '/Edu/public';

  async function post(url, data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
   
    const res = await fetch(BASE + url, { method: 'POST', body: fd });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
  }

  function formatDate(dateStr) {
   
    const [y, m, d] = dateStr.split('-').map(Number);
    const date = new Date(y, m - 1, d);
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  
  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }
  function openModalFromJson(jsonStr) {
    try {
      const e = JSON.parse(jsonStr);
      openModal(e.id, e.titre, e.description, e.date_event, e.capacite, e.image_url);
    } catch (err) {
      console.error('openModalFromJson parse error', err);
    }
  }

  function openModal(id, titre, description, date, capacite, image) {
    document.getElementById('m_eid').value         = id;
    document.getElementById('m_titre').value       = titre;
    document.getElementById('m_description').value = description;
    document.getElementById('m_date').value        = date;
    document.getElementById('m_capacite').value    = capacite;
    document.getElementById('m_image').value       = image ?? '';
    document.getElementById('eventModal').classList.add('show');
  }

  function closeModal() {
    document.getElementById('eventModal').classList.remove('show');
  }

  async function save() {
    const titre    = document.getElementById('e_titre').value.trim();
    const date     = document.getElementById('e_date').value;
    const capacite = document.getElementById('e_capacite').value;
    if (!titre || !date || !capacite) {
      alert('Veuillez remplir le titre, la date et la capacité.');
      return;
    }

    let data;
    try {
      data = await post('/admin/events/save', {
        titre,
        description: document.getElementById('e_description').value.trim(),
        date_event:  date,
        capacite,
        image_url:   document.getElementById('e_image').value.trim(),
      });
    } catch (err) {
      alert('Erreur réseau : ' + err.message);
      return;
    }

    if (data.status !== 'success') { alert(data.message); return; }
    alert(data.message);

    const e  = data.event;
    const tr = document.createElement('tr');
    tr.id = 'erow' + e.id;

    const eJsonAttr = escHtml(JSON.stringify({
      id: e.id, titre: e.titre, description: e.description,
      date_event: e.date_event, capacite: e.capacite, image_url: e.image_url ?? ''
    }));

    tr.innerHTML = `
      <td><strong>${escHtml(e.titre)}</strong></td>
      <td style="color:#9ca3af;font-size:.8rem">${formatDate(e.date_event)}</td>
      <td style="font-size:.8rem">0/${escHtml(String(e.capacite))}</td>
      <td>
        <button class="btn-edit"
          onclick="EventCRUD.openModalFromJson(this.dataset.event)"
          data-event="${eJsonAttr}">✏️</button>
        <button class="btn-delete"
          onclick="EventCRUD.del(${e.id}, ${JSON.stringify(e.titre)})">🗑️</button>
      </td>`;
    document.getElementById('eventBody').prepend(tr);

    ['e_titre','e_description','e_date','e_capacite','e_image']
      .forEach(id => document.getElementById(id).value = '');
  }

  async function update() {
    const id       = document.getElementById('m_eid').value;
    const titre    = document.getElementById('m_titre').value.trim();
    const date     = document.getElementById('m_date').value;
    const capacite = document.getElementById('m_capacite').value;
    if (!titre || !date || !capacite) {
      alert('Veuillez remplir le titre, la date et la capacité.');
      return;
    }

    let data;
    try {
      data = await post('/admin/events/save', {
        id,
        titre,
        description: document.getElementById('m_description').value.trim(),
        date_event:  date,
        capacite,
        image_url:   document.getElementById('m_image').value.trim(),
      });
    } catch (err) {
      alert('Erreur réseau : ' + err.message);
      return;
    }

    if (data.status !== 'success') { alert(data.message); return; }
    alert(data.message);
    closeModal();

    const row = document.getElementById('erow' + id);
    if (row) {
      row.cells[0].innerHTML  = '<strong>' + escHtml(titre) + '</strong>';
      row.cells[1].textContent = formatDate(date);

    
      const prevLeft = row.cells[2].textContent.split('/')[0].trim();
      row.cells[2].textContent = prevLeft + '/' + capacite;
    }
  }

  async function del(id, titre) {
    if (!confirm('Supprimer « ' + titre + ' » ?')) return;

    let data;
    try {
      data = await post('/admin/events/delete', { id });
    } catch (err) {
      alert('Erreur réseau : ' + err.message);
      return;
    }

    if (data.status !== 'success') { alert(data.message); return; }
    document.getElementById('erow' + id)?.remove();
  }

  return { save, openModal, openModalFromJson, closeModal, update, del };
})();
</script>