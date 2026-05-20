<?php
require '../config.php';



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    header('Content-Type: application/json');
    $action = $_POST['action'];

    try {

        if ($action === 'create') {

            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $date_event = $_POST['date_event'] ?? '';
            $capacite = (int)($_POST['capacite'] ?? 0);
            $category_id = (int)($_POST['category_id'] ?? 0);

            if (!$titre || !$description || !$date_event || !$capacite || !$category_id) {
                echo json_encode(["status"=>"error","message"=>"Champs obligatoires"]);
                exit;
            }

            $image_path = null;

            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];

                if (!in_array($ext,$allowed)) {
                    throw new Exception("Format image invalide");
                }

                if (!is_dir("uploads")) mkdir("uploads",0777,true);

                $name = uniqid().".".$ext;
                move_uploaded_file($_FILES['image']['tmp_name'],"uploads/".$name);

                $image_path = "uploads/".$name;
            }

            $stmt = $pdo->prepare("
                INSERT INTO evenements
                (titre, description, date_event, image_url, capacite, category_id, created_at)
                VALUES (?,?,?,?,?,?,NOW())
            ");

            $stmt->execute([$titre,$description,$date_event,$image_path,$capacite,$category_id]);

            echo json_encode(["status"=>"success","message"=>"Événement ajouté"]);
            exit;
        }

        if ($action === 'update_event') {

            $id = (int)$_POST['id'];
            $titre = $_POST['titre'];
            $description = $_POST['description'];

            $pdo->prepare("
                UPDATE evenements
                SET titre=?, description=?
                WHERE id=?
            ")->execute([$titre,$description,$id]);

            echo json_encode(["status"=>"success","message"=>"Modifié"]);
            exit;
        }

        if ($action === 'delete_event') {

            $id = (int)$_POST['id'];

            $pdo->prepare("DELETE FROM evenements WHERE id=?")->execute([$id]);

            echo json_encode(["status"=>"success","message"=>"Supprimé"]);
            exit;
        }

    } catch(Exception $e){
        echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
        exit;
    }
}

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
$events = $pdo->query("SELECT * FROM evenements ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Événements</title>

<style>
body{
  margin:0;
  font-family:'DM Sans',sans-serif;
  background:transparent;
}

/* ===== LAYOUT ===== */
.page{
  width:100%;
  display:grid;
  grid-template-columns:320px 1fr;
  gap:20px;
}

/* ===== CARD ===== */
.card{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:16px;
  padding:20px;
  box-shadow:0 10px 25px rgba(0,0,0,0.25);
}

/* ===== TITLES ===== */
h2{
  font-family:'Syne',sans-serif;
  font-size:1.1rem;
  margin-bottom:12px;
}

/* ===== INPUTS ===== */
input,select{
  width:100%;
  padding:10px;
  margin:8px 0;
  border-radius:10px;
  border:1px solid var(--border);
  background:var(--surface);
  color:var(--text);
  font-size:0.9rem;
}

input:focus,select:focus{
  border-color:var(--accent);
  outline:none;
}

/* ===== BUTTON ===== */
.btn{
  width:100%;
  padding:10px;
  border:none;
  border-radius:10px;
  background:var(--accent);
  color:white;
  font-weight:600;
  cursor:pointer;
  transition:0.2s;
}

.btn:hover{
  background:#2563eb;
}

/* ===== TABLE ===== */
table{
  width:100%;
  border-collapse:collapse;
  margin-top:10px;
}

td{
  padding:12px;
  border-bottom:1px solid var(--border);
  font-size:0.85rem;
}

tr:hover td{
  background:rgba(255,255,255,0.03);
}

/* ===== ACTION BUTTONS ===== */
.edit{
  background:rgba(246, 143, 59, 0.15);
  color:#60a5fa;
  border:none;
  padding:6px 10px;
  border-radius:8px;
  cursor:pointer;
  margin-right:6px;
}

.delete{
  background:rgba(239,68,68,0.15);
  color:#f87171;
  border:none;
  padding:6px 10px;
  border-radius:8px;
  cursor:pointer;
}

/* ===== MODAL ===== */
.modal{
  position:fixed;
  top:0;left:0;
  width:100%;height:100%;
  background:rgba(218, 10, 10, 0.6);
  display:flex;
  justify-content:center;
  align-items:center;
  opacity:0;
  pointer-events:none;
  transition:0.25s;
  z-index:200;
}

.modal.show{
  opacity:1;
  pointer-events:auto;
}

.modal-content{
  background:var(--card);
  border:1px solid var(--border);
  padding:20px;
  border-radius:16px;
  width:320px;
}

/* ===== RESPONSIVE ===== */
@media(max-width:900px){
  .page{
    grid-template-columns:1fr;
  }
}
</style>
</head>

<body>

<div class="page">

<div class="card">

<h2>Créer événement</h2>

<form id="form" enctype="multipart/form-data">

<input name="titre" placeholder="Titre">
<textarea name="description" placeholder="Description"></textarea>
<input type="datetime-local" name="date_event">
<input type="number" name="capacite" placeholder="Capacité">

<select name="category_id">
<option value="">Catégorie</option>
<?php foreach($cats as $c): ?>
<option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
<?php endforeach; ?>
</select>

<input type="file" name="image">

<button class="btn">Créer</button>

</form>

</div>

<div class="card">

<h2>Événements</h2>

<table>
<?php foreach($events as $e): ?>
<tr>
<td><?= htmlspecialchars($e['titre']) ?></td>

<td>
<button class="edit"
onclick="openModal(<?= $e['id'] ?>,'<?= addslashes($e['titre']) ?>','<?= addslashes($e['description']) ?>')">
✏️
</button>

<button class="delete"
onclick="removeEvent(<?= $e['id'] ?>)">
🗑️
</button>
</td>
</tr>
<?php endforeach; ?>
</table>

</div>

</div>

<div class="modal" id="modal">
<div class="modal-content">

<input type="hidden" id="edit_id">
<input id="edit_title">
<textarea id="edit_desc"></textarea>

<button class="btn" onclick="saveUpdate()">Enregistrer</button>
<button onclick="closeModal()">Annuler</button>

</div>
</div>

<script>

document.getElementById("form").addEventListener("submit",function(e){
e.preventDefault();

let fd = new FormData(this);
fd.append("action","create");

fetch("",{method:"POST",body:fd})
.then(r=>r.json())
.then(data=>{
alert(data.message);
if(data.status==="success") location.reload();
});
});

function openModal(id,title,desc){
document.getElementById("edit_id").value=id;
document.getElementById("edit_title").value=title;
document.getElementById("edit_desc").value=desc;
document.getElementById("modal").classList.add("show");
}

function closeModal(){
document.getElementById("modal").classList.remove("show");
}

function saveUpdate(){
let fd=new FormData();
fd.append("action","update_event");
fd.append("id",document.getElementById("edit_id").value);
fd.append("titre",document.getElementById("edit_title").value);
fd.append("description",document.getElementById("edit_desc").value);

fetch("",{method:"POST",body:fd})
.then(r=>r.json())
.then(data=>{
alert(data.message);
location.reload();
});
}

function removeEvent(id){
if(!confirm("Supprimer ?")) return;

let fd=new FormData();
fd.append("action","delete_event");
fd.append("id",id);

fetch("",{method:"POST",body:fd})
.then(r=>r.json())
.then(data=>{
alert(data.message);
location.reload();
});
}

</script>

</body>
</html>