<?php
session_start();
require './db.php';

// Récupérer toutes les pétitions approuvées avec leur nombre de signatures
$sql="SELECT p.*, 
        (SELECT COUNT(*) FROM signature s WHERE s.idP = p.idP) AS signature_count 
        FROM petition p 
        WHERE p.statut_petition = 'Approve' 
        ORDER BY p.dateAjoutP DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$petitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Pétitions</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Animation de défilement infini */
    @keyframes scroll {
      to { transform: translateX(-50%); }
    }
    .scroller {
      max-width: 100%;
      overflow: hidden;
      -webkit-mask: linear-gradient(90deg, transparent, white 15%, white 85%, transparent);
      mask: linear-gradient(90deg, transparent, white 15%, white 85%, transparent);
    }
    .scroller__inner {
      display: flex;
      gap: 1.5rem; /* gap-6 */
      width: max-content;
      animation: scroll 40s linear infinite;
    }
    .scroller:hover .scroller__inner {
      animation-play-state: paused;
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans flex flex-col min-h-screen">

  <!-- Barre de navigation -->
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="./index.php" class="text-xl font-bold text-indigo-600">PétitionsEnLigne</a>
    <div>
      <a href="./User/Register_view.php" 
         class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300 mr-4">
         S'inscrire
      </a>
      <a href="./User/Login_view.php" 
         class="text-gray-600 hover:text-indigo-600">
         Se connecter
      </a>
    </div>
  </nav>

  <!-- Section Héros (ajustée pour la navbar) -->
  <div class="py-16 flex flex-col items-center justify-center text-center px-4">
    <div class="flex flex-col items-center space-y-6">
      <img src="./Assets/Images/petition-signatures-1.png" alt="Pétition" height="700" width="700" class="rounded-2xl shadow-lg hover:scale-105 transition-transform duration-300">
      
      <h1 class="text-4xl font-bold text-indigo-600">Bienvenue sur la plateforme de gestion des pétitions</h1>
      <p class="text-gray-600 max-w-md">
        Créez, signez et soutenez des causes importantes. Rejoignez notre communauté pour faire entendre votre voix !
      </p>
    </div>
  </div>

  <!-- Section des pétitions défilantes -->
  <section id="petitions" class="py-20 lg:py-28 bg-gray-100 w-full">
    <div class="container mx-auto px-6 text-center">
      <h2 class="text-3xl lg:text-4xl font-bold mb-4">Pétitions Actives</h2>
      <p class="max-w-2xl mx-auto text-gray-600 mb-16">Découvrez les causes qui mobilisent notre communauté. Votre signature peut faire la différence.</p>
      
      <?php if (count($petitions) > 0): ?>
          <div class="scroller">
              <div class="scroller__inner">
                  <?php 
                      // Dupliquer le tableau pour un défilement continu
                      $scrolling_petitions = array_merge($petitions, $petitions);
                      foreach ($scrolling_petitions as $petition): 
                  ?>
                  <div class="bg-white rounded-2xl shadow-md overflow-hidden flex flex-col group w-80 flex-shrink-0">
                      <div class="p-6 text-left flex flex-col flex-grow">
                          <h3 class="font-bold text-xl mb-2 text-gray-900 group-hover:text-indigo-600 transition-colors"><?php echo htmlspecialchars($petition['titreP']); ?></h3>
                          <p class="text-gray-600 text-sm mb-4 flex-grow">
                            <?php echo htmlspecialchars(substr($petition['descriptionP'], 0, 120)) . (strlen($petition['descriptionP']) > 120 ? '...' : ''); ?>
                          </p>
                          <div class="mt-auto border-t border-gray-200 pt-4 space-y-3 text-sm">
                            <?php
                              // Calcul du pourcentage pour la barre de progression
                              $objectif = $petition['Objectif_signature'] ?? 0; // Assurez-vous que cette colonne existe dans votre table 'petition'
                              $actuel = $petition['signature_count'];
                              $pourcentage = ($objectif > 0) ? ($actuel / $objectif) * 100 : 0;
                              if ($pourcentage > 100) $pourcentage = 100;
                            ?>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: <?php echo $pourcentage; ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center">
                                <p class="text-gray-700 flex items-center gap-2 font-bold">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" /></svg>
                                    <?php echo htmlspecialchars($actuel); ?> signatures
                                </p>
                                <?php if ($objectif > 0): ?>
                                <p class="text-gray-500 font-semibold"><?php echo round($pourcentage); ?>%</p>
                                <?php endif; ?>
                            </div>
                          </div>
                          <a href="./User/Login_view.php" class="mt-5 inline-block w-full text-center px-4 py-2.5 rounded-lg bg-indigo-600 text-white font-semibold shadow-md hover:bg-indigo-700 transition-all">
                            Signer cette pétition
                          </a>
                      </div>
                  </div>
                  <?php endforeach; ?>
              </div>
          </div>
          
      <?php else: ?>
          <p class="text-gray-500 bg-white/80 p-8 rounded-xl">Aucune pétition active pour le moment. Revenez bientôt !</p>
      <?php endif; ?>
    </div>
    
  </section>

  <!-- Pied de page -->
  <footer class="w-full text-center p-6 bg-white text-sm text-gray-500 mt-auto">
    © 2025 Gestion des Pétitions — Tous droits réservés.
  </footer>

</body>
</html>
