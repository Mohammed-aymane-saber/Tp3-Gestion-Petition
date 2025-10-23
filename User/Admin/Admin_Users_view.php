<?php
session_start();
require '../../db.php';

// Sécurité : Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../Login_view.php'); // Rediriger vers la page de connexion si non admin
    exit;
}


$stmt_users = $conn->prepare("SELECT id, nom, prenom, email, date_naissance FROM user where role = 'user' ORDER BY date_naissance DESC");
$stmt_users->execute();
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <a href="../index.php" class="text-xl font-bold text-indigo-600">PétitionsEnLigne</a>
        <div>
            <a href="Admin_view.php" class="text-gray-600 hover:text-indigo-600 mr-4">Tableau de bord</a>
            <a href="Admin_Users_view.php" class="text-indigo-600 hover:text-indigo-800 mr-4">Gérer les utilisateurs</a>
            <a href="../Actions/Logout.php" class="text-red-500 hover:text-red-700">Déconnexion</a>
        </div>
    </nav>

    <div class="container mx-auto mt-8">
        <h1 class="text-2xl font-bold mb-6">Gestion des Utilisateurs</h1>
        <div class="bg-white shadow rounded-lg p-6">
            <table class="min-w-full table-auto">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b text-left">ID</th>
                        <th class="px-4 py-2 border-b text-left">Nom</th>
                        <th class="px-4 py-2 border-b text-left">Prénom</th>
                        <th class="px-4 py-2 border-b text-left">Email</th>
                        <th class="px-4 py-2 border-b text-left">Date de naissance</th>
                        <!-- <th class="px-4 py-2 border-b">Actions</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500">Aucun utilisateur enregistré.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($user['nom']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($user['prenom']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-4 py-2 border-b">
                                <?php
                                $dob = $user['date_naissance'];
                                $dateFormatted = '—';
                                $ageText = '';
                                if (!empty($dob)) {
                                    try {
                                        $dobObj = DateTime::createFromFormat('Y-m-d', $dob) ?: new DateTime($dob);
                                        $dateFormatted = $dobObj->format('d/m/Y');
                                        $age = $dobObj->diff(new DateTime())->y;
                                        $ageText = ' (' . $age . ' ans)';
                                    } catch (Exception $e) {
                                        $dateFormatted = htmlspecialchars($dob);
                                    }
                                }
                                echo htmlspecialchars($dateFormatted . $ageText);
                                ?>
                            </td>
                            <!-- <td class="px-4 py-2 border-b">
                                <button class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">Modifier</button>
                                <button class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 ml-2">Supprimer</button>
                            </td> -->
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>