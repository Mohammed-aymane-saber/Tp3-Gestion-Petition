<?php
session_start();
require '../../db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Login_view.php');
    exit;
}

// Gérer la suppression de signature
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_signature') {
    $signature_id = $_POST['signature_id'];
    $user_id = $_SESSION['user_id'];

    // Supprimer la signature en s'assurant qu'elle appartient bien à l'utilisateur connecté
    $stmt = $conn->prepare("DELETE FROM signature WHERE idS = ? AND idUser = ?");
    $stmt->execute([$signature_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = 'Votre signature a été supprimée avec succès.';
        $_SESSION['status'] = 'success';
    } else {
        $_SESSION['message'] = 'Impossible de supprimer cette signature.';
        $_SESSION['status'] = 'error';
    }
    header("Location: ./User_view_Signatures.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$update_fields = [];
$params = [];

// --- Traitement des champs texte ---
if (isset($_POST['nom']) && !empty(trim($_POST['nom']))) {
    $update_fields[] = "nom = ?";
    $params[] = trim($_POST['nom']);
}

if (isset($_POST['prenom']) && !empty(trim($_POST['prenom']))) {
    $update_fields[] = "prenom = ?";
    $params[] = trim($_POST['prenom']);
}

if (isset($_POST['email']) && filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
    $update_fields[] = "email = ?";
    $params[] = trim($_POST['email']);
}

// --- Traitement de la date de naissance ---
if (isset($_POST['date_naissance']) && !empty($_POST['date_naissance'])) {
    // Validation simple pour s'assurer que c'est un format de date plausible
    $d = DateTime::createFromFormat('Y-m-d', $_POST['date_naissance']);
    if ($d && $d->format('Y-m-d') === $_POST['date_naissance']) {
        $update_fields[] = "date_naissance = ?";
        $params[] = $_POST['date_naissance'];
    }
}

// --- Traitement de l'image ---
if (isset($_FILES['image_signature']) && $_FILES['image_signature']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../../Assets/Uploads/'; // Correction du chemin
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filename = uniqid() . '_' . basename($_FILES['image_signature']['name']);
    $target_file = $upload_dir . $filename;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Validation du fichier
    if (!in_array($file_type, $allowed)) {
        $_SESSION['message'] = 'Format non supporté. Formats autorisés : jpg, jpeg, png.';
        $_SESSION['status'] = 'error';
    } elseif ($_FILES['image_signature']['size'] > $max_size) {
        $_SESSION['message'] = 'Le fichier est trop volumineux (max 5MB).';
        $_SESSION['status'] = 'error';
    } elseif (move_uploaded_file($_FILES['image_signature']['tmp_name'], $target_file)) {
        $update_fields[] = "Image_Signature = ?";
        $params[] = $filename;
    } else {
        $_SESSION['message'] = "Erreur lors du téléversement du fichier.";
        $_SESSION['status'] = 'error';
    }
}

// --- Mise à jour de la base de données ---
if (!empty($update_fields)) {
    $sql = "UPDATE user SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $params[] = $user_id;

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $_SESSION['message'] = 'Profil mis à jour avec succès.';
        $_SESSION['status'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Erreur lors de la mise à jour du profil.';
        $_SESSION['status'] = 'error';
        // error_log($e->getMessage()); // Optionnel: pour le débogage
    } 
} elseif (!isset($_SESSION['message'])) { // Si aucun champ n'a été modifié et qu'il n'y a pas d'erreur de fichier
    $_SESSION['message'] = 'Aucune modification détectée.';
    $_SESSION['status'] = 'info'; // ou 'warning'
}

header("Location: ./User_view_Signatures.php");
exit;
?>