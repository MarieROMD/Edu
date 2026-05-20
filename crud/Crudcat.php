<?php
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    try {

        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');

            if (!$name) {
                echo json_encode(["status"=>"error","message"=>"Nom requis"]);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO categories (nom) VALUES (?)");
            $stmt->execute([$name]);

            echo json_encode(["status"=>"success","message"=>"Catégorie ajoutée"]);
            exit;
        }

        if ($action === 'update') {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name'] ?? '');

            if (!$id || !$name) {
                echo json_encode(["status"=>"error","message"=>"Données invalides"]);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE categories SET nom=? WHERE id=?");
            $stmt->execute([$name, $id]);

            echo json_encode(["status"=>"success","message"=>"Catégorie modifiée"]);
            exit;
        }

        if ($action === 'delete') {
            $id = (int)$_POST['id'];

            $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
            $stmt->execute([$id]);

            echo json_encode(["status"=>"success","message"=>"Catégorie supprimée"]);
            exit;
        }

    } catch (Exception $e) {
        echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
        exit;
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Categories</title>

<style>
body{
    margin:0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg,#fdfbfb,#ebedee);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    color:#333;
}

.container{
    width:92%;
    max-width:850px;
    background:#ffffff;
    border-radius:18px;
    padding:25px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    border:1px solid #f2f2f2;
}

h2{
    text-align:center;
    margin-bottom:20px;
    font-weight:600;
    color:#4a4a4a;
}

.top-bar{
    display:flex;
    gap:10px;
    margin-bottom:15px;
}

input{
    flex:1;
    padding:12px 14px;
    border-radius:12px;
    border:1px solid #e6e6e6;
    outline:none;
    background:#fafafa;
    transition:0.3s;
}

input:focus{
    border-color:#b8d8ff;
    background:#fff;
    box-shadow:0 0 0 3px rgba(184,216,255,0.4);
}

button{
    padding:10px 14px;
    border:none;
    border-radius:12px;
    cursor:pointer;
    font-weight:600;
    transition:0.3s;
}

.add-btn{
    background: #a7d8ff;
    color:#1b3a57;
}

.add-btn:hover{
    background:#89c9ff;
    transform:translateY(-2px);
}

table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 10px;
}

th{
    text-align:left;
    padding:10px;
    color:#777;
    font-size:14px;
}

td{
    background:#f9fafb;
    padding:12px;
    border-radius:12px;
}

tr td{
    transition:0.3s;
}

tr:hover td{
    background:#eef6ff;
}

td input{
    width:90%;
    padding:8px;
    border-radius:10px;
    border:1px solid #eee;
    background:#fff;
}

.edit{
    background:#b8f2c2;
    color:#1e5a2a;
    margin-right:5px;
}

.delete{
    background:#ffd1d1;  
    color:#7a1f1f;
}

.edit:hover{
    background:#9fe9ae;
}

.delete:hover{
    background:#ffb8b8;
}

#msg{
    margin-top:10px;
    text-align:center;
    font-weight:600;
    color:#555;
}
</style>
</head>

<body>

<div class="container">

<h2>📂 Gestion des Catégories</h2>

<div class="top-bar">
    <input type="text" id="name" placeholder="Nouvelle catégorie...">
    <button class="add-btn" onclick="create()">Ajouter</button>
</div>

<div id="msg"></div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach($categories as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td>
                <input type="text" id="name-<?= $c['id'] ?>" value="<?= htmlspecialchars($c['nom']) ?>">
            </td>
            <td>
                <button class="edit" onclick="update(<?= $c['id'] ?>)">Edit</button>
                <button class="delete" onclick="removeCat(<?= $c['id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>

<script>

function create(){
    let name = document.getElementById("name").value;

    let fd = new FormData();
    fd.append("action","create");
    fd.append("name",name);

    fetch("",{method:"POST",body:fd})
    .then(r=>r.json())
    .then(showMsg);
}

function update(id){
    let name = document.getElementById("name-"+id).value;

    let fd = new FormData();
    fd.append("action","update");
    fd.append("id",id);
    fd.append("name",name);

    fetch("",{method:"POST",body:fd})
    .then(r=>r.json())
    .then(showMsg);
}

function removeCat(id){
    if(!confirm("Supprimer ?")) return;

    let fd = new FormData();
    fd.append("action","delete");
    fd.append("id",id);

    fetch("",{method:"POST",body:fd})
    .then(r=>r.json())
    .then(showMsg);
}

function showMsg(data){
    let msg = document.getElementById("msg");
    msg.innerText = data.message;

    msg.style.color = data.status === "success" ? "#00ff99" : "#ff4d4d";

    if(data.status==="success"){
        setTimeout(()=>location.reload(),600);
    }
}

</script>

</body>
</html>