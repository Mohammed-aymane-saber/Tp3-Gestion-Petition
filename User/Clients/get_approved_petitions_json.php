<?php
session_start();
require '../../db.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

header('Content-Type: application/json');


$sql = "SELECT 
            p.idP, p.titreP, p.descriptionP, p.dateAjoutP, p.dateFinP, p.dateApproved, p.Objectif_signature,
            (SELECT COUNT(*) FROM signature s WHERE s.idP = p.idP) as nombre_signatures_actuel 
        FROM petition p 
        WHERE p.Statut_petition = 'Approve' 
          AND (p.dateFinP IS NULL OR p.dateFinP > NOW())
          AND (SELECT COUNT(*) FROM signature s WHERE s.idP = p.idP) < p.Objectif_signature
        ORDER BY p.dateApproved DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$petitions = $stmt->fetchAll(PDO::FETCH_ASSOC);


$user_id = $_SESSION['user_id'];
$signed_petitions_stmt = $conn->prepare("SELECT idP FROM signature WHERE idUser = ?");
$signed_petitions_stmt->execute([$user_id]);
$signed_petitions_ids = $signed_petitions_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

// On combine les deux informations dans la réponse JSON
echo json_encode([
    'petitions' => $petitions,
    'signed_ids' => $signed_petitions_ids
]);
?>