<?php
session_start();
require '../../db.php';
require '../EmailService.php';
require '../../config.php'; // Inclure la configuration

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/User/Register_view.php');
    exit();
}

$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$email = $_POST['email'];
$date_naissance = $_POST['date_naissance'];
$password = $_POST['password'];
$password_confirmation = $_POST['password_confirmation'];

if ($password !== $password_confirmation) {
    die("Les mots de passe ne correspondent pas.");
}

// Vérifier si l'email existe déjà
$stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die("Cet email est déjà utilisé.");
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Insérer l'utilisateur avec un statut 'pending'
    $stmt = $conn->prepare("INSERT INTO user (nom, prenom, email, date_naissance, password, statut_compte) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$nom, $prenom, $email, $date_naissance, $hashed_password]);
    $user_id = $conn->lastInsertId();

    // Générer un code de confirmation
    $code = sprintf('%06d', mt_rand(0, 999999));
    $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));


    $stmt_code = $conn->prepare("INSERT INTO account_confirmations (user_id, code, expires_at) VALUES (?, ?, ?)");
    $stmt_code->execute([$user_id, $code, $expires_at]);

    // Envoyer l'email
    $emailService = new EmailService();
    $emailSent = $emailService->sendAccountConfirmationCode($email, $prenom, $code);

    if ($emailSent) {
        // Rediriger vers la page de vérification
        header('Location: ' . BASE_URL . '/User/verify_account.php?email=' . urlencode($email));
        exit();
    } else {
        die("Erreur lors de l'envoi de l'email de confirmation. Veuillez contacter le support.");
    }

} catch (PDOException $e) {
    // En cas d'erreur, vous pouvez logger le message pour le débogage
    error_log($e->getMessage());
    die("Une erreur est survenue lors de l'inscription. Veuillez réessayer.");
}
?>