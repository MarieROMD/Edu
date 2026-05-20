<?php
require '../config.php';




if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])){

header('Content-Type: application/json');

try{

if($_POST['action']==='save'){

$id = $_POST['id'] ?? null;
$username = $_POST['username'];
$email = $_POST['email'];
$role = $_POST['role'];
$password = $_POST['password'];

if($id){

if(!empty($password)){
$pdo->prepare("UPDATE users SET username=?,email=?,password=?,role=? WHERE id=?")
->execute([
$username,
$email,
password_hash($password,PASSWORD_DEFAULT),
$role,
$id
]);
}else{
$pdo->prepare("UPDATE users SET username=?,email=?,role=? WHERE id=?")
->execute([$username,$email,$role,$id]);
}

}else{

$pdo->prepare("INSERT INTO users(username,email,password,role) VALUES (?,?,?,?)")
->execute([
$username,
$email,
password_hash($password,PASSWORD_DEFAULT),
$role
]);

}

echo json_encode(["status"=>"success","message"=>"Saved"]);
exit;
}

if($_POST['action']==='delete'){

$id=$_POST['id'];
$pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);

echo json_encode(["status"=>"success"]);
exit;
}

}catch(Exception $e){
echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
exit;
}
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Users CRUD</title>

<style>
body{
  margin:0;
  font-family:'DM Sans',sans-serif;
  background:transparent;
}

.page{
  width:100%;
  display:grid;
  grid-template-columns:320px 1fr;
  gap:20px;
}

.card{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:16px;
  padding:20px;
  box-shadow:0 10px 25px rgba(0,0,0,0.25);
}

h2{
  font-family:'Syne',sans-serif;
  font-size:1.1rem;
  margin-bottom:12px;
}

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

.edit{
  background:rgba(59,130,246,0.15);
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

.modal{
  position:fixed;
  top:0;left:0;
  width:100%;height:100%;
  background:rgba(0,0,0,0.6);
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

<h2>Créer / Modifier user</h2>

<input type="hidden" id="id">

<input id="username" placeholder="Username">
<input id="email" placeholder="Email">
<input id="password" placeholder="Password">

<select id="role">
<option value="membre">Membre</option>
<option value="admin">Admin</option>
</select>

<button class="btn" onclick="save()">Enregistrer</button>

</div>

<div class="card">

<h2>Users</h2>

<table>
<?php foreach($users as $u): ?>
<tr id="row<?= $u['id'] ?>">

<td><?= htmlspecialchars($u['username']) ?></td>

<td>
<button class="edit"
onclick="editUser(<?= $u['id'] ?>,'<?= $u['username'] ?>','<?= $u['email'] ?>','<?= $u['role'] ?>')">
✏️
</button>

<button class="delete" onclick="deleteUser(<?= $u['id'] ?>)">
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

<h3>Modifier user</h3>

<input type="hidden" id="mid">

<input id="musername">
<input id="memail">
<input id="mpassword">
<select id="mrole">
<option value="membre">Membre</option>
<option value="admin">Admin</option>
</select>

<button class="btn" onclick="update()">Save</button>
<button onclick="closeModal()">Close</button>

</div>
</div>

<script>

function save(){

let fd=new FormData();
fd.append("action","save");
fd.append("id",id.value);
fd.append("username",username.value);
fd.append("email",email.value);
fd.append("password",password.value);
fd.append("role",role.value);

fetch("",{method:"POST",body:fd})
.then(r=>r.json())
.then(d=>{
alert(d.message);
location.reload();
});

}

function editUser(i,u,e,r){
modal.classList.add("show");
mid.value=i;
musername.value=u;
memail.value=e;
mrole.value=r;
}

function closeModal(){
modal.classList.remove("show");
}

function update(){

let fd=new FormData();
fd.append("action","save");
fd.append("id",mid.value);
fd.append("username",musername.value);
fd.append("email",memail.value);
fd.append("password",mpassword.value);
fd.append("role",mrole.value);

fetch("",{method:"POST",body:fd})
.then(r=>r.json())
.then(d=>{
alert(d.message);
location.reload();
});

}

function deleteUser(id){

if(confirm("Delete ?")){

let fd=new FormData();
fd.append("action","delete");
fd.append("id",id);

fetch("",{method:"POST",body:fd})
.then(()=>location.reload());

}

}

</script>

</body>
</html>