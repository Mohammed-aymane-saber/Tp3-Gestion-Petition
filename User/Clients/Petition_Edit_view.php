<?php
session_start();
require '../../db.php';

// 1. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ../Login_view.php');
    exit;
}

// 2. Vérifier si un ID de pétition est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: User_view_Signatures.php');
    exit;
}

$petition_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 3. Récupérer les données de la pétition et vérifier que l'utilisateur en est le créateur
$stmt = $conn->prepare("SELECT * FROM petition WHERE idP = ? AND CreatorUser = ?");
$stmt->execute([$petition_id, $user_id]);
$petition = $stmt->fetch(PDO::FETCH_ASSOC);

// 4. Si la pétition n'existe pas ou n'appartient pas à l'utilisateur, le rediriger
if (!$petition) {
    $_SESSION['message'] = "Vous n'êtes pas autorisé à modifier cette pétition ou elle n'existe pas.";
    $_SESSION['status'] = 'error';
    header('Location: User_view_Signatures.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Pétition</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <!-- Barre de navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="../index.php" class="text-xl font-bold text-indigo-600">PétitionsEnLigne</a>
                <div class="flex items-center">
                    <a href="User_view_petitions.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Voir les pétitions</a>
                    <a href="User_view_Signatures.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Mon Profil</a>
                    <a href="../Actions/Logout.php" class="ml-4 text-red-500 hover:text-red-700 px-3 py-2 rounded-md text-sm font-medium">Déconnexion</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4 md:p-8">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Modifier la pétition</h1>
            
            <form action="Petition_Gestion_action.php" method="POST">
                <!-- Champ caché pour l'ID de la pétition et l'action -->
                <input type="hidden" name="petition_id" value="<?php echo htmlspecialchars($petition['idP']); ?>">
                <input type="hidden" name="action" value="update">

                <div class="mb-4">
                    <label for="titre" class="block text-gray-700 text-sm font-bold mb-2">Titre de la pétition</label>
                    <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($petition['titreP']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea id="description" name="description" rows="6" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?php echo htmlspecialchars($petition['descriptionP']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="objectif" class="block text-gray-700 text-sm font-bold mb-2">Objectif de signatures</label>
                    <input type="number" id="objectif" name="objectif" min="1" value="<?php echo htmlspecialchars($petition['Objectif_signature']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>

                <div class="mb-6">
                    <label for="date_fin" class="block text-gray-700 text-sm font-bold mb-2">Date de fin (Optionnel)</label>
                    <input type="date" id="date_fin" name="date_fin" value="<?php echo htmlspecialchars($petition['dateFinP']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-300">Mettre à jour la pétition</button>
            </form>           
        </div>
    </div>
</body>
</html>