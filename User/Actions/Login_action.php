<?php
// User/Actions/Login_action.php
declare(strict_types=1); // Pour une meilleure gestion des types

session_start();
require '../../db.php';
require '../../firebase_init.php';
require '../../config.php'; // Inclure la configuration

/**
 * Crée une session pour un utilisateur.
 * @param array $user
 */
function createUserSession(array $user): void {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom']; // Utiliser le nom complet
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true; // Session unifiée
}

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
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $parts = explode(' ', $name, 2);
        $prenom = $parts[0] ?? '';
        $nom = $parts[1] ?? '';

        $insert = $conn->prepare("INSERT INTO user (nom, prenom, email, role, statut_compte, firebase_uid) VALUES (?, ?, ?, 'user', 'active', ?)");
        $insert->execute([$nom, $prenom, $email, $uid]);
        $userId = $conn->lastInsertId();

        // Récupérer l'utilisateur inséré
        $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Si user existe mais firebase_uid vide, on peut mettre à jour
        if (empty($user['firebase_uid']) && !empty($uid)) {
            $upd = $conn->prepare("UPDATE user SET firebase_uid = ? WHERE id = ?");
            $upd->execute([$uid, $user['id']]);
            $user['firebase_uid'] = $uid;
        }
    }

    if ($user['statut_compte'] !== 'active') {
        http_response_code(403);
        echo json_encode(['error' => 'Votre compte n\'est pas actif. Veuillez vérifier vos emails.']);
        exit();
    }

    // Créer session
    createUserSession($user);

    // Renvoyer le rôle pour une redirection côté client si nécessaire
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'role' => $user['role']]);
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

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);

    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($user['statut_compte'] !== 'active') {
            // Rediriger vers la page de vérification si le compte est en attente
            if ($user['statut_compte'] === 'pending') {
                header('Location: ' . BASE_URL . '/User/verify_account.php?email=' . urlencode($email) . '&error=' . urlencode('Votre compte doit être vérifié.'));
                exit();
            }
            die("Votre compte est inactif. Veuillez contacter le support.");
        }
        // Si mot de passe stocké (hash), vérifier
        if (!empty($user['password']) && password_verify($password, $user['password'])) {
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
                    $upd = $conn->prepare("UPDATE user SET firebase_uid = ? WHERE id = ?");
                    $upd->execute([$firebaseUser->uid, $user['id']]);
                } catch (Exception $e2) {
                    error_log("Erreur création Firebase lors login: " . $e2->getMessage());
                }
            }

            // Sessions et cookies
            createUserSession($user);

            // Gérer "se souvenir"
            if (isset($_POST['se_souvenir']) && $_POST['se_souvenir'] === 'oui') {
                setcookie("email_user", $email, ['expires' => time() + (86400 * 30), 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
                // Ne jamais stocker le mot de passe en clair dans un cookie !
            } else {
                setcookie("email_user", '', ['expires' => time() - 3600, 'path' => '/']);
            }

            if ($user['role'] === 'admin') {
                header('Location: ' . BASE_URL . '/User/Admin/Admin_view.php');
            } else {
                header('Location: ' . BASE_URL . '/User/Clients/User_view_petitions.php');
            }
            exit();
        } else {
            die("Mot de passe incorrect.");
        }
    } else {
        die("Aucun utilisateur trouvé avec cet email.");
    }
}

http_response_code(405); // Method Not Allowed
die("Méthode non autorisée.");
