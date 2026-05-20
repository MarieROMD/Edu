<style>
.page {
  width: 100%;
  display: grid;
  grid-template-columns: 320px 1fr;
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

.card input, .card select {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
  font-size: 0.9rem;
  box-sizing: border-box;
}

.card input:focus, .card select:focus {
  border-color: var(--accent);
  outline: none;
}

.btn {
  width: 100%;
  padding: 10px;
  border: none;
  border-radius: 10px;
  background: var(--accent);
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
  margin-top: 8px;
}

.btn:hover { background: #2563eb; }

.crud-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.crud-table td {
  padding: 12px;
  border-bottom: 1px solid var(--border);
  font-size: 0.85rem;
}

.crud-table tr:hover td {
  background: rgba(255,255,255,0.03);
}

.btn-edit {
  background: rgba(59,130,246,0.15);
  color: #60a5fa;
  border: none;
  padding: 6px 10px;
  border-radius: 8px;
  cursor: pointer;
  margin-right: 6px;
}

.btn-delete {
  background: rgba(239,68,68,0.15);
  color: #f87171;
  border: none;
  padding: 6px 10px;
  border-radius: 8px;
  cursor: pointer;
}

.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 0;
  pointer-events: none;
  transition: 0.25s;
  z-index: 200;
}

.modal-overlay.show {
  opacity: 1;
  pointer-events: auto;
}

.modal-box {
  background: var(--card);
  border: 1px solid var(--border);
  padding: 24px;
  border-radius: 16px;
  width: 340px;
}

.modal-box h3 {
  font-family: 'Syne', sans-serif;
  margin: 0 0 16px;
}

.modal-box input, .modal-box select {
  width: 100%;
  padding: 10px;
  margin: 6px 0;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
  font-size: 0.9rem;
  box-sizing: border-box;
}

.btn-close {
  width: 100%;
  padding: 9px;
  margin-top: 8px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: transparent;
  color: var(--text);
  cursor: pointer;
  font-size: 0.9rem;
}

@media(max-width: 900px) {
  .page { grid-template-columns: 1fr; }
}
</style>

<div class="page">

  <div class="card">
    <h2>Créer un user</h2>

    <input type="hidden" id="c_id">
    <input id="c_username" placeholder="Username">
    <input id="c_email"    placeholder="Email" type="email">
    <input id="c_password" placeholder="Password" type="password">
    <select id="c_role">
      <option value="membre">Membre</option>
      <option value="admin">Admin</option>
    </select>

    <button class="btn" onclick="CRUD.save()">Enregistrer</button>
  </div>

  <div class="card">
    <h2>Users</h2>

    <table class="crud-table">
      <tbody id="userBody">
        <?php foreach ($users ?? [] as $u): ?>
        <tr id="row<?= $u['id'] ?>">
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td style="color:#9ca3af;font-size:.8rem"><?= htmlspecialchars($u['email']) ?></td>
          <td>
            <button class="btn-edit"
              onclick="CRUD.openModal(<?= $u['id'] ?>,'<?= addslashes($u['username']) ?>','<?= addslashes($u['email']) ?>','<?= $u['role'] ?>')">
              ✏️
            </button>
            <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
            <button class="btn-delete" onclick="CRUD.del(<?= $u['id'] ?>)">
              🗑️
            </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <h3>Modifier user</h3>

    <input type="hidden" id="m_id">
    <input id="m_username" placeholder="Username">
    <input id="m_email"    placeholder="Email" type="email">
    <input id="m_password" placeholder="Nouveau mot de passe (optionnel)" type="password">
    <select id="m_role">
      <option value="membre">Membre</option>
      <option value="admin">Admin</option>
    </select>

    <button class="btn"      onclick="CRUD.update()">Enregistrer</button>
    <button class="btn-close" onclick="CRUD.closeModal()">Fermer</button>
  </div>
</div>

<script>
const CRUD = (() => {
  const BASE = '/Edu/public';

  async function post(url, data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    const res = await fetch(BASE + url, { method: 'POST', body: fd });
    return res.json();
  }

  async function save() {
    const data = await post('/admin/users/save', {
      id:       document.getElementById('c_id').value,
      username: document.getElementById('c_username').value,
      email:    document.getElementById('c_email').value,
      password: document.getElementById('c_password').value,
      role:     document.getElementById('c_role').value,
    });

    if (data.status !== 'success') { alert(data.message); return; }

    alert(data.message);

    const u  = data.user;
    const tr = document.createElement('tr');
    tr.id = 'row' + u.id;
    tr.innerHTML = `
      <td>${u.username}</td>
      <td style="color:#9ca3af;font-size:.8rem">${u.email}</td>
      <td>
        <button class="btn-edit"
          onclick="CRUD.openModal(${u.id},'${u.username}','${u.email}','${u.role}')">✏️</button>
        <button class="btn-delete" onclick="CRUD.del(${u.id})">🗑️</button>
      </td>`;
    document.getElementById('userBody').prepend(tr);

    document.getElementById('c_username').value = '';
    document.getElementById('c_email').value    = '';
    document.getElementById('c_password').value = '';
    document.getElementById('c_role').value     = 'membre';
  }

  function openModal(id, username, email, role) {
    document.getElementById('m_id').value       = id;
    document.getElementById('m_username').value = username;
    document.getElementById('m_email').value    = email;
    document.getElementById('m_password').value = '';
    document.getElementById('m_role').value     = role;
    document.getElementById('editModal').classList.add('show');
  }

  function closeModal() {
    document.getElementById('editModal').classList.remove('show');
  }

  async function update() {
    const id = document.getElementById('m_id').value;
    const data = await post('/admin/users/save', {
      id:       id,
      username: document.getElementById('m_username').value,
      email:    document.getElementById('m_email').value,
      password: document.getElementById('m_password').value,
      role:     document.getElementById('m_role').value,
    });

    if (data.status !== 'success') { alert(data.message); return; }

    alert(data.message);
    closeModal();

    const row = document.getElementById('row' + id);
    if (row) {
      row.cells[0].textContent = document.getElementById('m_username').value;
      row.cells[1].textContent = document.getElementById('m_email').value;
    }
  }

  async function del(id) {
    if (!confirm('Supprimer ?')) return;

    const data = await post('/admin/users/delete', { id });

    if (data.status !== 'success') { alert(data.message); return; }

    document.getElementById('row' + id)?.remove();
  }

  return { save, openModal, closeModal, update, del };
})();
</script>