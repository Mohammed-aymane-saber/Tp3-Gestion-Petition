<?php
session_start();
require '../../db.php';

require '../../config.php'; // Inclure la configuration

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/User/Login_view.php');
    exit();
}

$email = $_POST['email'] ?? '';
$code = $_POST['code'] ?? '';

if (empty($email) || empty($code)) {
    header('Location: ' . BASE_URL . '/User/verify_account.php?email=' . urlencode($email) . '&error=' . urlencode('Veuillez entrer le code.'));
    exit();
}

// 1. Trouver l'utilisateur et le code de confirmation
$stmt = $conn->prepare("
    SELECT u.id, ac.id as code_id
    FROM user u
    JOIN account_confirmations ac ON u.id = ac.user_id
    WHERE u.email = ? AND ac.code = ? AND ac.expires_at > NOW()
");
$stmt->execute([$email, $code]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $user_id = $result['id'];
    $code_id = $result['code_id'];

    // 2. Mettre à jour le statut du compte utilisateur
    $update_stmt = $conn->prepare("UPDATE user SET statut_compte = 'active' WHERE id = ?");
    $update_stmt->execute([$user_id]);

    // 3. Supprimer le code utilisé
    $delete_stmt = $conn->prepare("DELETE FROM account_confirmations WHERE id = ?");
    $delete_stmt->execute([$code_id]);

    // 4. Rediriger vers la page de connexion avec un message de succès
    header('Location: ' . BASE_URL . '/User/Login_view.php?status=verified');
    exit();
} else {
    // Code invalide ou expiré
    header('Location: ' . BASE_URL . '/User/verify_account.php?email=' . urlencode($email) . '&error=' . urlencode('Code invalide ou expiré.'));
    exit();
}
?>