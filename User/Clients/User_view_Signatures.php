<?php
session_start();
require '../../db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Login_view.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Récupérer les informations de l'utilisateur
$stmt_user = $conn->prepare("SELECT nom, prenom, email, date_naissance, Image_Signature FROM user WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: ../Login_view.php');
    exit;
}

// 2. Récupérer les pétitions signées
$stmt_petitions = $conn->prepare("
    SELECT p.titreP, s.dateS, s.idS
    FROM signature s 
    JOIN petition p ON s.idP = p.idP
    WHERE s.idUser = ? 
    ORDER BY s.dateS DESC
");
$stmt_petitions->execute([$user_id]);
$signed_petitions = $stmt_petitions->fetchAll(PDO::FETCH_ASSOC);

// 3. Récupérer les pétitions créées par l'utilisateur
$stmt_created = $conn->prepare("
    SELECT p.idP, p.titreP, p.statut_petition, p.dateAjoutP, 
           (SELECT COUNT(*) FROM signature WHERE idP = p.idP) as signature_count
    FROM petition p
    WHERE p.CreatorUser = ?
    ORDER BY p.dateAjoutP DESC
");
$stmt_created->execute([$user_id]);
$created_petitions = $stmt_created->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Gestion des Pétitions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <!-- Barre de navigation -->
    <nav class="bg-white shadow-md mb-8">
        <div class="max-w-5xl mx-auto px-4 py-3 flex justify-between items-center">
            <a href="../index.php" class="text-xl font-bold text-indigo-600">PétitionsEnLigne</a>
            <div>
                <a href="User_view_petitions.php" class="text-gray-600 hover:text-indigo-600 mr-4">Voir les pétitions</a>
                <a href="../Actions/Logout.php" class="text-red-500 hover:text-red-700">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto p-4">

        <!-- Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-6 px-4 py-3 rounded-lg text-center <?php echo ($_SESSION['status'] == 'error') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                <?php 
                    echo htmlspecialchars($_SESSION['message']); 
                    unset($_SESSION['message'], $_SESSION['status']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Profil -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <form action="User_Signature_actions.php" method="POST" enctype="multipart/form-data">
                <div class="md:flex items-start space-y-8 md:space-y-0 md:space-x-8">
                    <!-- Informations utilisateur -->
                    <div class="flex-grow space-y-4">
                        <h1 class="text-3xl font-bold text-gray-800 mb-4">Mon Profil</h1>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="date_naissance" class="block text-sm font-medium text-gray-700">Date de naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($user['date_naissance'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Upload signature -->
                    <div class="flex-shrink-0 w-full md:w-80">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Mon image de signature</label>
                        
                        <!-- Preview -->
                        <div id="imagePreview" class="<?php echo empty($user['Image_Signature']) ? 'hidden' : ''; ?> relative w-full h-32 border-2 border-gray-300 rounded-lg flex items-center justify-center bg-gray-50 mb-2">
                            <img id="previewImage" src="<?php echo !empty($user['Image_Signature']) ? '../../Assets/Uploads/' . htmlspecialchars($user['Image_Signature']) : ''; ?>" alt="Aperçu" class="max-h-full max-w-full object-contain">
                            <button type="button" id="removeImage" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs hover:bg-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <p id="fileName" class="text-xs text-gray-500 text-center mb-2 truncate"><?php echo !empty($user['Image_Signature']) ? htmlspecialchars($user['Image_Signature']) : ''; ?></p>

                        <!-- Progress -->
                        <div id="uploadProgress" class="hidden w-full bg-gray-200 rounded-full h-2.5 mb-2">
                            <div id="progressBar" class="bg-indigo-600 h-2.5 rounded-full" style="width: 0%"></div>
                            <span id="progressText" class="text-xs text-gray-500 text-center block mt-1">0%</span>
                        </div>

                        <!-- Upload input -->
                        <label for="signaturePhoto" class="<?php echo !empty($user['Image_Signature']) ? 'hidden' : 'flex'; ?> items-center justify-center w-full p-4 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer hover:border-indigo-500 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-4 text-center">
                                <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="text-sm text-slate-600"><span class="font-semibold">Cliquez pour télécharger</span><br><span class="text-xs text-slate-500">PNG, JPG (MAX. 5MB)</span></p>
                            </div>
                                <input id="signaturePhoto" name="image_signature" type="file" accept="image/png,image/jpeg,image/jpg" class="hidden" />
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                     <button type="submit" class="w-full md:w-auto bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-300">
                        Mettre à jour le profil
                    </button>
                </div>
            </form>
        </div>

        <!-- Pétitions signées -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-6 border-b pb-3">Mes Pétitions Signées</h2>
            <?php if (empty($signed_petitions)): ?>
                <p class="text-gray-500 text-center py-4">Vous n'avez signé aucune pétition pour le moment.</p>
            <?php else: ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($signed_petitions as $petition): ?>
                        <li class="py-4 flex justify-between items-center">
                            <div>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($petition['titreP']); ?></span>
                                <span class="block text-sm text-gray-500">Signé le <?php echo date('d/m/Y', strtotime($petition['dateS'])); ?></span>
                            </div>
                            <form action="User_Signature_actions.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre signature ? Cette action est irréversible.');">
                                <input type="hidden" name="action" value="delete_signature">
                                <input type="hidden" name="signature_id" value="<?php echo $petition['idS']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-semibold">Supprimer</button>
                            </form>
                        </li> 
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Pétitions créées -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-6 border-b pb-3">Mes Pétitions Créées</h2>
            <?php if (empty($created_petitions)): ?>
                <p class="text-gray-500 text-center py-4">Vous n'avez créé aucune pétition. <a href="Petition_Gestion.php" class="text-indigo-600 hover:underline">Lancez-en une !</a></p>
            <?php else: ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($created_petitions as $petition): ?>
                        <li class="py-4 flex justify-between items-center">
                            <div>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($petition['titreP']); ?></span>
                                <div class="flex items-center gap-4 text-sm text-gray-500 mt-1">
                                    <span>
                                        <?php 
                                            $status_text = 'En attente'; $status_color = 'text-yellow-600';
                                            if ($petition['statut_petition'] == 'Approve') { $status_text = 'Approuvée'; $status_color = 'text-green-600'; }
                                            elseif ($petition['statut_petition'] == 'Reject') { $status_text = 'Rejetée'; $status_color = 'text-red-600'; }
                                        ?>
                                        Statut : <span class="font-semibold <?php echo $status_color; ?>"><?php echo $status_text; ?></span>
                                    </span>
                                    <span class="text-gray-400">|</span>
                                    <span><?php echo $petition['signature_count']; ?> signatures</span>
                                </div>
                            </div>
                            <a href="Petition_Edit_view.php?id=<?php echo $petition['idP']; ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition duration-300 text-sm font-semibold">
                                Modifier
                            </a>
                        </li> 
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </div>

    <!-- === Image Upload Preview with Progress === -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const signatureInput = document.getElementById('signaturePhoto');
        const imagePreview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');
        const removeImage = document.getElementById('removeImage');
        const uploadProgress = document.getElementById('uploadProgress');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const uploadLabel = document.querySelector('label[for="signaturePhoto"]');

        if (signatureInput) {
            signatureInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                if (file.size > 5 * 1024 * 1024) {
                    alert('Le fichier est trop volumineux (max 5MB)');
                    signatureInput.value = '';
                    return;
                }
                if (!file.type.match('image/(png|jpeg|jpg)')) {
                    alert('Format non supporté (PNG, JPG, JPEG)');
                    signatureInput.value = '';
                    return;
                }

                uploadLabel.classList.add('hidden');
                imagePreview.classList.add('hidden');
                uploadProgress.classList.remove('hidden');

                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 20 + 5;
                    if (progress > 100) progress = 100;
                    progressBar.style.width = progress + '%';
                    progressText.textContent = Math.round(progress) + '%';
                    if (progress >= 100) {
                        clearInterval(interval);
                        setTimeout(() => {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                previewImage.src = event.target.result;
                                fileName.textContent = file.name;
                                uploadProgress.classList.add('hidden');
                                imagePreview.classList.remove('hidden');
                            };
                            reader.readAsDataURL(file);
                        }, 300);
                    }
                }, 100);
            });
        }

        if (removeImage) {
            removeImage.addEventListener('click', function() {
                signatureInput.value = '';
                imagePreview.classList.add('hidden');
                uploadLabel.classList.remove('hidden');
                previewImage.src = '';
                fileName.textContent = '';
            });
        }
    });
    </script>
</body>
</html>
