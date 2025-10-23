<?php
$servername = 'localhost';
$user = 'root';
$pass = '';

try {

  $conn = new PDO("mysql:host=$servername;dbname=tp3;charset=utf8mb4", $user, $pass);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
} catch(PDOException $e) {
 
  error_log("Erreur de connexion à la base de données: " . $e->getMessage());

  die("Impossible de se connecter à la base de données. Veuillez réessayer plus tard.");
}
