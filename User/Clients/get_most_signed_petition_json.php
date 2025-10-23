<?php
require '../../db.php';

header('Content-Type: application/json');

// Cette requête trouve la pétition approuvée qui a le plus grand nombre de signatures.
$sql = "SELECT 
            p.titreP,
            p.descriptionP,
            p.Objectif_signature,
            COUNT(s.idP) AS signature_count
        FROM signature s
        JOIN petition p ON s.idP = p.idP
        WHERE p.statut_petition = 'Approve'
        GROUP BY p.idP, p.titreP, p.descriptionP, p.Objectif_signature
        ORDER BY signature_count DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute();
$petition = $stmt->fetch(PDO::FETCH_ASSOC);

// Si aucune pétition n'a encore de signature, $petition sera `false`.
// On renvoie un objet vide ou le résultat trouvé.
if ($petition) {
    echo json_encode($petition);
} else {
    echo json_encode(null); // Renvoyer null si aucune pétition n'a de signature
}
?>