<?php
// User/Actions/Login_action.php
session_start();
require '../../db.php';
require '../../firebase_init.php';

// 1) Gérer requête JSON (Google Sign-In depuis le client)
$input = json_decode(file_get_contents('php://input'), true);
if (!empty($input) && !empty($input['googleLogin'])) {

    $email = trim($input['email'] ?? '');
    $name = trim($input['name'] ?? '');
    $uid = trim($input['uid'] ?? '');

    if (empty($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email requis']);
        exit();
    }

    // Vérifier si l'utilisateur existe localement
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Créer un utilisateur minimal localement
        // On tente de séparer prenom / nom depuis $name
        $parts = explode(' ', $name, 2);
        $prenom = $parts[0] ?? '';
        $nom = $parts[1] ?? '';

        $insert = $conn->prepare("INSERT INTO user (nom, prenom, email, role, password,statut_compte, firebase_uid) VALUES (:nom, :prenom, :email, :role, :password,:statut_compte, :uid)");
        $role_default = 'user';
        $null_password = null;
        $statut_compte = 'active';
        $insert->bindParam(':nom', $nom);
        $insert->bindParam(':prenom', $prenom);
        $insert->bindParam(':email', $email);
        $insert->bindParam(':role', $role_default);
        $insert->bindParam(':password', $null_password);
        $insert->bindParam(':statut_compte', $statut_compte);
        $insert->bindParam(':uid', $uid);
        $insert->execute();

        // Récupérer l'utilisateur inséré
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Si user existe mais firebase_uid vide, on peut mettre à jour
        if (empty($user['firebase_uid']) && !empty($uid)) {
            $upd = $conn->prepare("UPDATE user SET firebase_uid = :uid WHERE id = :id");
            $upd->bindParam(':uid', $uid);
            $upd->bindParam(':id', $user['id']);
            $upd->execute();
            $user['firebase_uid'] = $uid;
        }
    }

    // Créer session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nom'];
    if ($user['role'] === 'admin') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $_SESSION['user_logged_in'] = true;
    }

    echo json_encode(['success' => true]);
    exit();
}

// 2) Gérer formulaire de connexion classique (POST form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si le POST vient de formulaire normal (application/x-www-form-urlencoded)
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        die("Email et mot de passe requis.");
    }

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Si mot de passe stocké (hash), vérifier
        if (!empty($user['password']) && password_verify($password, $user['password'])) {
            // Synchroniser avec Firebase : si user absent dans Firebase, le créer
            try {
                $firebaseUser = $auth->getUserByEmail($email);
            } catch (Exception $e) {
                try {
                    $firebaseUser = $auth->createUser([
                        'email' => $email,
                        'password' => $password,
                        'displayName' => $user['nom'] . ' ' . $user['prenom'],
                    ]);
                    // mettre à jour firebase_uid localement
                    $uid = $firebaseUser->uid;
                    $upd = $conn->prepare("UPDATE user SET firebase_uid = :uid, statut_compte = :statut_compte WHERE id = :id");
                    $statut = 'active';
                    $upd->bindParam(':statut_compte', $statut);
                    $upd->bindParam(':uid', $uid);
                    $upd->bindParam(':id', $user['id']);
                    $upd->execute();
                } catch (Exception $e2) {
                    error_log("Erreur création Firebase lors login: " . $e2->getMessage());
                }
            }

            // Sessions et cookies
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nom'];

            // Gérer "se souvenir" - tu as déjà une fonction dans ta version initiale, on la reproduit ici
            if (isset($_POST['se_souvenir']) && $_POST['se_souvenir'] === 'oui') {
                setcookie("email_" . $user['role'], $email, time() + (86400 * 30), "/");
                setcookie("password_" . $user['role'], $password, time() + (86400 * 30), "/");
            } else {
                setcookie("email_" . $user['role'], '', time() - 3600, '/');
                setcookie("password_" . $user['role'], '', time() - 3600, '/');
            }

            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
                header('Location: ../Admin/Admin_view.php');
            } else {
                $_SESSION['user_logged_in'] = true;
                header('Location: ../Clients/User_view_petitions.php');
            }
            exit();
        } else {
            die("Mot de passe incorrect.");
        }
    } else {
        die("Aucun utilisateur trouvé avec cet email.");
    }
}

die("Méthode non autorisée.");
