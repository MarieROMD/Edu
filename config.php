<?php


$host="localhost";
$username="root";
$pass="";
$dbname="eduevents";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
try {
    $pdo=new PDO("mysql:host=$host;dbname=$dbname;port=3307", $username, $pass);
    
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>