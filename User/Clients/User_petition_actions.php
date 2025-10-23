<?php 
session_start();
require '../../db.php'; // Le chemin correct est ../../db.php

// Pour les requêtes AJAX, on s'assure de retourner du JSON
header('Content-Type: application/json');

// Utilisons l'ID utilisateur stocké lors de la connexion
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Utilisateur non connecté.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['petition_id'])) {
        $action = $_POST['action'];
        $petition_id = intval($_POST['petition_id']);

        // Préparer la réponse par défaut
        $response = [
            'status' => 'error',
            'message' => 'Une erreur inconnue est survenue.'
        ];

        if ($action === 'sign') {
            // Vérifier si l'utilisateur a déjà signé
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM signature WHERE idUser = ? AND idP = ?");
            $check_stmt->execute([$user_id, $petition_id]);

            if ($check_stmt->fetchColumn() == 0) {
                // L'utilisateur n'a pas encore signé, on procède
                // La récupération des infos utilisateur n'est plus nécessaire ici
                // car on ne les insère plus dans la table signature (nom, prenom)
                if (empty($user_id)) {
                    $response = ['status' => 'error', 'message' => 'ID utilisateur invalide.'];
                }

                try {
                    $current_date = date('Y-m-d');
                    $current_time = date('H:i:s');

                    $insert_stmt = $conn->prepare("INSERT INTO signature (idUser, idP, dateS, heureS, paysS) VALUES (?, ?, ?, ?, ?)");
                    $insert_stmt->execute([$user_id, $petition_id, $current_date, $current_time, 'Maroc']);

                    $response = ['status' => 'success', 'message' => 'Merci ! Votre signature a bien été enregistrée.'];

                } catch (Exception $e) {
                    $response = ['status' => 'error', 'message' => 'Une erreur technique est survenue lors de la signature.'];
                    error_log("Erreur lors de la signature: " . $e->getMessage());
                }
            } else {
                $response = ['status' => 'info', 'message' => 'Vous avez déjà signé cette pétition.'];
            }
            echo json_encode($response);
            exit();
        }
    }
}


echo json_encode(['status' => 'error', 'message' => 'Action non valide.']);
exit();