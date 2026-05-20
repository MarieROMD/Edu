

<div class="table-card">
  <div class="table-card-header">
    <div class="card-title">Catégories</div>
    <button class="btn-primary" onclick="openCatModal()">+ Ajouter</button>
  </div>

  <table id="cat-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Nom</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categories ?? [] as $c): ?>
      <tr id="cat-row-<?= $c['id'] ?>">
        <td><?= $c['id'] ?></td>
        <td><?= htmlspecialchars($c['nom']) ?></td>
        <td>
          <button class="btn-edit"
            onclick="openCatModal(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode($c['nom'])) ?>)">
            Modifier
          </button>
          <button class="btn-delete" onclick="deleteCat(<?= $c['id'] ?>)">Supprimer</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div id="cat-modal" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-title" id="cat-modal-title">Nouvelle catégorie</div>

    <input type="hidden" id="cat-id">

    <label class="form-label">Nom</label>
    <input type="text" id="cat-nom" class="form-input" placeholder="Ex : Informatique">

    <div id="cat-error" class="form-error" style="display:none"></div>

    <div class="modal-actions">
      <button class="btn-secondary" onclick="closeCatModal()">Annuler</button>
      <button class="btn-primary" onclick="saveCat()">Enregistrer</button>
    </div>
  </div>
</div>

<script>
function openCatModal(id = null, nom = '') {
  document.getElementById('cat-id').value  = id ?? '';
  document.getElementById('cat-nom').value = nom;
  document.getElementById('cat-error').style.display = 'none';
  document.getElementById('cat-modal-title').textContent =
    id ? 'Modifier la catégorie' : 'Nouvelle catégorie';
  document.getElementById('cat-modal').style.display = 'flex';
}

function closeCatModal() {
  document.getElementById('cat-modal').style.display = 'none';
}

async function saveCat() {
  const id  = document.getElementById('cat-id').value;
  const nom = document.getElementById('cat-nom').value.trim();

  if (!nom) { showCatError('Le nom est requis.'); return; }

  const body = new FormData();
  body.append('nom', nom);
  if (id) body.append('id', id);

  const res  = await fetch('/Edu/public/admin/categories/save', { method: 'POST', body });
  const data = await res.json();

  if (data.error) { showCatError(data.error); return; }

  closeCatModal();
  location.reload();
}

async function deleteCat(id) {
  if (!confirm('Supprimer cette catégorie ?')) return;

  const body = new FormData();
  body.append('id', id);

  const res  = await fetch('/Edu/public/admin/categories/delete', { method: 'POST', body });
  const data = await res.json();

  if (data.error) { alert(data.error); return; }

  document.getElementById('cat-row-' + id)?.remove();
}

function showCatError(msg) {
  const el = document.getElementById('cat-error');
  el.textContent = msg;
  el.style.display = 'block';
}
</script>