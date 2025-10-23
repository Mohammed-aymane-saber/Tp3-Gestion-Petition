<?php
session_start();
require '../../db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../Login_view.php');
        exit;
    }
    $user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté

    // Données communes
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $objectif = filter_var($_POST['objectif'], FILTER_VALIDATE_INT);
    $date_fin = !empty($_POST['date_fin']) ? $_POST['date_fin'] : null;

    // Vérifier si c'est une mise à jour ou une création
    if (isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['petition_id'])) {
        // --- C'est une MISE À JOUR ---
        $petition_id = $_POST['petition_id'];

        // Sécurité : Vérifier que l'utilisateur est bien le créateur de la pétition
        $check_stmt = $conn->prepare("SELECT CreatorUser FROM petition WHERE idP = ?");
        $check_stmt->execute([$petition_id]);
        $owner = $check_stmt->fetchColumn();

        if ($owner == $user_id) {
            // L'utilisateur est autorisé, on met à jour
            $stmt = $conn->prepare("UPDATE petition SET titreP = ?, descriptionP = ?, Objectif_signature = ?, dateFinP = ?, statut_petition = 'en_attente' WHERE idP = ?");
            $stmt->execute([$titre, $description, $objectif, $date_fin, $petition_id]);
            $_SESSION['message'] = "Votre pétition a été modifiée avec succès et est de nouveau en attente d'approbation.";
            $_SESSION['status'] = 'success';
        } else {
            // Tentative de modification non autorisée
            $_SESSION['message'] = "Action non autorisée.";
            $_SESSION['status'] = 'error';
        }
        header('Location: User_view_Signatures.php'); // Rediriger vers la page de profil

    } else {
        // --- C'est une CRÉATION ---
        $stmt = $conn->prepare("INSERT INTO petition (CreatorUser, titreP, descriptionP, Objectif_signature, dateAjoutP, dateFinP, statut_petition) VALUES (?, ?, ?, ?, NOW(), ?, 'en_attente')");
        $stmt->execute([$user_id, $titre, $description, $objectif, $date_fin]);
        $_SESSION['message'] = "Votre pétition a été créée avec succès et est en attente d'approbation.";
        $_SESSION['status'] = 'success';
        header('Location: User_view_petitions.php');
    }
} else {
    header('Location: Petition_Gestion.php');
    exit;
}