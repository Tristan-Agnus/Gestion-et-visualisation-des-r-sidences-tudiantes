<?php
$host = 'mysql_serv';
$dbname = 'fsoftic_05';
$user = 'fsoftic'; 
$pass = 'Faris2603!'; 

//try = essaye 
//catch = sinon fais ça
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
