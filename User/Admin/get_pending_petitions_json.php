<?php
session_start();
require '../../db.php';

// Sécurité : Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

header('Content-Type: application/json');

// 1. Récupérer le nombre de pétitions en attente
$pending_count = $conn->query("SELECT COUNT(*) FROM petition WHERE Statut_petition = 'en_attente'")->fetchColumn();

// 2. Récupérer les détails des pétitions en attente
$stmt_pending = $conn->prepare("SELECT * FROM petition WHERE Statut_petition = 'en_attente' ORDER BY dateAjoutP DESC");
$stmt_pending->execute();
$petitions = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);

// 3. Renvoyer les données au format JSON
echo json_encode([
    'count' => (int)$pending_count,
    'petitions' => $petitions
]);
?>