<?php
session_start();
require '../../db.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../Admin/Admin_Login_view.php');
    exit;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['petition_id'])) {
        $action = $_POST['action'];
        $petition_id = intval($_POST['petition_id']);

        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE petition SET statut_petition = 'Approve', dateApproved = NOW() WHERE idP = ?");
            $stmt->execute([$petition_id]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = "La pétition #{$petition_id} a été approuvée avec succès.";
                $_SESSION['status'] = 'success';
            } else {
                $_SESSION['message'] = "Erreur lors de l'approbation de la pétition #{$petition_id}.";
                $_SESSION['status'] = 'error';
            }
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE petition SET statut_petition = 'Refuse' WHERE idP = ?");
            $stmt->execute([$petition_id]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = "La pétition #{$petition_id} a été rejetée.";
                $_SESSION['status'] = 'success';
            } else {
                $_SESSION['message'] = "Erreur lors du rejet de la pétition #{$petition_id}.";
                $_SESSION['status'] = 'error';
            }
        }
    }
    header('Location: ../Admin/Admin_view.php');
    exit;
}
header('Location: ../Admin/Admin_view.php'); // Redirection si la méthode n'est pas POST
exit;
