<?php
session_start();
require '../../db.php';

// 1. Sécurité : Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../Login_view.php'); // Rediriger vers la page de connexion si non admin
    exit;
}

// 2. Récupérer les statistiques
$total_users = $conn->query("SELECT COUNT(*) FROM user")->fetchColumn();
$total_petitions = $conn->query("SELECT COUNT(*) FROM petition")->fetchColumn();
$total_signatures = $conn->query("SELECT COUNT(*) FROM signature")->fetchColumn();
$pending_petitions_count = $conn->query("SELECT COUNT(*) FROM petition WHERE Statut_petition = 'en_attente'")->fetchColumn();

// 3. Récupérer les pétitions en attente pour le tableau
$stmt_pending = $conn->prepare("SELECT * FROM petition WHERE Statut_petition = 'en_attente' ORDER BY dateAjoutP DESC");
$stmt_pending->execute();
$result = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des pétitions</title>
    <link rel="icon" type="image/png" href="../../Assets/Images/favicon.png"> <!-- Ajout du Favicon -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <a href="../index.php" class="text-xl font-bold text-indigo-600">PétitionsEnLigne</a>
        <div>
            <a href="Admin_view.php" class="text-indigo-600 hover:text-indigo-800 mr-4">Tableau de bord</a>
            <a href="Admin_Users_view.php" class="text-gray-600 hover:text-indigo-600 mr-4">Gérer les utilisateurs</a>
            <a href="../Actions/Logout.php" class="text-red-500 hover:text-red-700">Déconnexion</a>
        </div>
    </nav>

    <div class="container mx-auto mt-8">
        <!-- Message de confirmation ou d'erreur -->
        <?php if (isset($_SESSION['message'])): ?>
        <div class="mb-6 mx-4 px-4 py-3 rounded-lg text-center <?php echo ($_SESSION['status'] == 'error') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>" role="alert">
            <?php 
            echo htmlspecialchars($_SESSION['message']); 
            // Nettoyer les messages de session après affichage
            unset($_SESSION['message'], $_SESSION['status']);
            ?>
        </div>
        <script>
            setTimeout(function() {
                const alert = document.querySelector('[role="alert"]');
                if (alert) alert.style.display = 'none';
            }, 5000); // Le message disparaît après 5 secondes
        </script>
        <?php endif; ?>
        <!-- Section des statistiques -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold mb-4">Statistiques Générales</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-gray-600">Utilisateurs</h2>
                    <p class="text-3xl font-bold text-indigo-600"><?php echo $total_users; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-gray-600">Pétitions Totales</h2>
                    <p class="text-3xl font-bold text-indigo-600"><?php echo $total_petitions; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-gray-600">Signatures Totales</h2>
                    <p class="text-3xl font-bold text-indigo-600"><?php echo $total_signatures; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-semibold text-gray-600">Pétitions en Attente</h2>
                    <p id="pending-petitions-stat" class="text-3xl font-bold text-yellow-500"><?php echo $pending_petitions_count; ?></p>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Gestion des pétitions en attente</h1>
            <button id="refresh-pending-btn" class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600 transition duration-300 flex items-center gap-2 disabled:opacity-50">
                <svg id="refresh-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" /></svg>
                <span id="refresh-text">Actualiser</span>
            </button>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <table class="min-w-full table-auto">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b">Titre</th>
                        <th class="px-4 py-2 border-b">Description</th>
                        <th class="px-4 py-2 border-b">Date d'ajout</th>
                        <th class="px-4 py-2 border-b">Objectif des Signatures</th>
                        <th class="px-4 py-2 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody id="pending-petitions-tbody">
                    <?php if (empty($result)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">Aucune pétition en attente de validation.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($result as $petition): ?>
                        <tr>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($petition['titreP']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($petition['descriptionP']); ?></td>
                            <td class="px-4 py-2 border-b"><?php echo date('d/m/Y', strtotime($petition['dateAjoutP'])); ?></td>
                            <td class="px-4 py-2 border-b">
                                <?php echo htmlspecialchars($petition['Objectif_signature']); ?>   
                            </td>

                            <td class="px-4 py-2 border-b">
                                <form method="POST" action="Admin_Actions.php" class="inline">
                                    <input type="hidden" name="petition_id" value="<?php echo $petition['idP']; ?>">
                                    <button type="submit" name="action" value="approve" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Approuver</button>
                                </form>
                                <form method="POST" action="Admin_Actions.php" class="inline ml-2">
                                    <input type="hidden" name="petition_id" value="<?php echo $petition['idP']; ?>">
                                    <button type="submit" name="action" value="reject" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Rejeter</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Garder en mémoire le nombre initial de pétitions
        let currentPendingCount = <?php echo $pending_petitions_count; ?>;
        const refreshBtn = document.getElementById('refresh-pending-btn');
        const refreshIcon = document.getElementById('refresh-icon');
        const refreshText = document.getElementById('refresh-text');

        // Fonction pour formater la date
        function formatDate(dateString) {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        // Fonction pour échapper le HTML et éviter les failles XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Fonction AJAX pour récupérer les nouvelles pétitions
        function fetchPendingPetitions(isManualRefresh = false) {
            // Afficher l'état de chargement sur le bouton si c'est un refresh manuel
            if (isManualRefresh) {
                refreshBtn.disabled = true;
                refreshIcon.classList.add('animate-spin');
                refreshText.textContent = 'Chargement...';
            }
            fetch('get_pending_petitions_json.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau ou accès non autorisé.');
                    }
                    return response.json();
                })
                .then(data => {
                    // Mettre à jour le compteur dans la carte de stat
                    const statElement = document.getElementById('pending-petitions-stat');
                    if (statElement) {
                        statElement.textContent = data.count;
                    }

                    // Mettre à jour le tableau si le nombre a changé OU si c'est un refresh manuel
                    if (data.count !== currentPendingCount || isManualRefresh) {
                        currentPendingCount = data.count;
                        const tbody = document.getElementById('pending-petitions-tbody');
                        let newHtml = '';

                        if (data.petitions.length > 0) {
                            data.petitions.forEach(petition => {
                                newHtml += `
                                    <tr>
                                        <td class="px-4 py-2 border-b">${escapeHtml(petition.titreP)}</td>
                                        <td class="px-4 py-2 border-b">${escapeHtml(petition.descriptionP)}</td>
                                        <td class="px-4 py-2 border-b">${formatDate(petition.dateAjoutP)}</td>
                                        <td class="px-4 py-2 border-b">${escapeHtml(String(petition.Objectif_signature))}</td>
                                        <td class="px-4 py-2 border-b">
                                            <form method="POST" action="Admin_Actions.php" class="inline">
                                                <input type="hidden" name="petition_id" value="${petition.idP}">
                                                <button type="submit" name="action" value="approve" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Approuver</button>
                                            </form>
                                            <form method="POST" action="Admin_Actions.php" class="inline ml-2">
                                                <input type="hidden" name="petition_id" value="${petition.idP}">
                                                <button type="submit" name="action" value="reject" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Rejeter</button>
                                            </form>
                                        </td>
                                    </tr>`;
                            });
                        } else {
                            newHtml = '<tr><td colspan="5" class="text-center py-4 text-gray-500">Aucune pétition en attente de validation.</td></tr>';
                        }
                        tbody.innerHTML = newHtml;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des pétitions:', error);
                    if (isManualRefresh) {
                        refreshText.textContent = 'Erreur';
                    }
                })
                .finally(() => {
                    // Rétablir l'état normal du bouton après le chargement
                    if (isManualRefresh) {
                        setTimeout(() => { // Petite pause pour que l'utilisateur voie le changement
                            refreshBtn.disabled = false;
                            refreshIcon.classList.remove('animate-spin');
                            refreshText.textContent = 'Actualiser';
                        }, 500);
                    }
                });
        }

        // Vérifier les nouvelles pétitions toutes les 15 secondes
        setInterval(() => fetchPendingPetitions(false), 15000);

        // Gérer le clic sur le bouton d'actualisation
        refreshBtn.addEventListener('click', () => fetchPendingPetitions(true));
    });
    </script>
</body>
</html> 
