<?php
session_start();
require '../../db.php';

header('Content-Type: application/json');

$sql = "SELECT 
            COALESCE(u.prenom, 'Utilisateur') as prenom, 
            COALESCE(u.nom, 'Anonyme') as nom, 
            p.titreP, s.dateS, s.heureS, s.idS
        FROM signature s 
        JOIN petition p ON s.idP = p.idP 
        JOIN user u ON s.idUser = u.id
        ORDER BY s.dateS DESC, s.heureS DESC
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->execute();
$signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($signatures);
?>