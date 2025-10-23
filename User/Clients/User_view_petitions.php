<?php

session_start();
require '../../db.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ../Login_view.php'); // Correction du chemin
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Récupérer les pétitions déjà signées par l'utilisateur
$signed_petitions_stmt = $conn->prepare("SELECT idP FROM signature WHERE idUser = ?");
$signed_petitions_stmt->execute([$user_id]);
$signed_petitions_ids = $signed_petitions_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

// La logique de récupération des pétitions est maintenant entièrement gérée par AJAX.
// Le PHP se contente de préparer la page.
$petitions_a_afficher = []; // Initialisé à vide.


?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toutes les Pétitions</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

  <!-- Barre de navigation -->
  <nav class="bg-white shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-16">
              <a href="../index.php" class="text-xl font-bold text-indigo-600">PétitionsEnLigne</a>
              <div class="flex items-center">
                  <a href="User_view_Signatures.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Mon Profil</a>
                  <a href="../Actions/Logout.php" class="ml-4 text-red-500 hover:text-red-700 px-3 py-2 rounded-md text-sm font-medium">Déconnexion</a>
              </div>
          </div>
      </div>
  </nav>

  <div class="container mx-auto p-4 md:p-8">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <h1 class="text-4xl font-bold text-gray-800">Pétitions Actives</h1>
            <button id="refresh-petitions-btn" class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600 transition duration-300 flex items-center gap-2 disabled:opacity-50">
                <svg id="refresh-petitions-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path></svg>
                <span id="refresh-petitions-text">Actualiser</span>
            </button>
        </div>
        <a href="./Petition_Gestion.php" class="bg-indigo-600 text-white px-5 py-2 rounded-lg shadow hover:bg-indigo-700 transition duration-300">
            + Créer une pétition
        </a>
    </div>

    <!-- Section pour la pétition la plus signée (AJAX) -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">🔥 La plus populaire</h2>
        <div id="most-signed-petition" class="bg-white p-6 rounded-xl shadow-lg border-2 border-indigo-500">
            <!-- Le contenu sera chargé ici par AJAX -->
        </div>
    </div>
     <div class="mt-8 border-t pt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📝 5 dernières signatures</h2>
                <div id="dernieres-signatures" class="bg-gray-50 p-4 rounded-lg">
                    <!-- Les signatures vont apparaître ici dynamiquement -->
                    <p class="text-gray-500 text-center">Chargement des dernières signatures...</p>
                </div>
               
            </div>

    <!-- Message de confirmation ou d'erreur -->
    <?php if (isset($_SESSION['message'])): ?>
      <div class="mb-6 px-4 py-3 rounded-lg text-center <?php echo ($_SESSION['status'] == 'error') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
        <?php 
          echo htmlspecialchars($_SESSION['message']); 
          // Nettoyer les messages de session après affichage
          unset($_SESSION['message'], $_SESSION['status']);
        ?>
      </div>
      <script>
        setTimeout(function() {
          document.querySelector('.mb-6').style.display = 'none';
        }, 4000);
      </script>
    <?php endif; ?>

    <div id="petitions-container">
      <!-- Ce conteneur sera rempli par AJAX. On affiche un message de chargement initial. -->
      <div id="petitions-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <p class="text-gray-500 col-span-full text-center py-10">Chargement des pétitions...</p>
      </div>
      <div id="no-petitions-message" class="hidden text-center bg-white p-12 rounded-lg shadow-md"></div>
    </div>
  </div>

  <!-- Notification de nouvelle signature -->
  <div id="signatureNotification" class="fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-3 rounded-lg shadow-lg hidden transition-opacity duration-500 ease-in-out opacity-0">
      🔔 Nouvelle signature !
  </div>
  <!-- Notification de nouvelle pétition -->
  <div id="petitionNotification" class="fixed top-20 right-4 bg-indigo-600 text-white px-4 py-3 rounded-lg shadow-lg hidden transition-opacity duration-500 ease-in-out opacity-0">
      ✨ Nouvelle pétition disponible !
  </div>
  <!-- Notification pour la signature -->
  <div id="actionNotification" class="fixed bottom-4 left-4 bg-gray-800 text-white px-4 py-3 rounded-lg shadow-lg hidden transition-opacity duration-500 ease-in-out opacity-0">
      <!-- Le message sera inséré ici -->
  </div>
  <!-- SCRIPT AJAX -->
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const grid = document.getElementById('petitions-grid');
        const noPetitionsMessage = document.getElementById('no-petitions-message');
        const refreshBtn = document.getElementById('refresh-petitions-btn');
        const refreshIcon = document.getElementById('refresh-petitions-icon');
        const refreshText = document.getElementById('refresh-petitions-text');
        const petitionNotification = document.getElementById('petitionNotification');
        const actionNotification = document.getElementById('actionNotification');
        const signatureNotification = document.getElementById('signatureNotification');

        let displayedPetitionIds = new Set();
        let lastSignatureId = 0;

        function escapeHtml(text) {
            if (text === null || typeof text === 'undefined') return '';
            return text.toString().replace(/[&<>"']/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m]));
        }

        function createPetitionCard(petition, signed_ids) {
            const isSigned = signed_ids.includes(petition.idP);
            const percentage = petition.Objectif_signature > 0 ? Math.min(100, (petition.nombre_signatures_actuel / petition.Objectif_signature) * 100) : 0;
            const endDate = petition.dateFinP ? `Termine le ${new Date(petition.dateFinP).toLocaleDateString('fr-FR')}` : 'Pas de date de fin';

            let actionButton;
            if (isSigned) {
                actionButton = `<button disabled class="w-full mt-4 bg-gray-400 text-white font-bold py-2 px-4 rounded-lg cursor-not-allowed">Déjà signé</button>`;
            } else {
                // On remplace le formulaire par un bouton avec un data-attribute
                actionButton = `<button data-petition-id="${petition.idP}" class="sign-btn w-full mt-4 bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-300">Signer</button>`;
            }

            return `
                <div id="petition-card-${petition.idP}" class="bg-white rounded-xl shadow-lg p-6 flex flex-col transition-all duration-500" style="opacity: 0; transform: translateY(20px);">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">${escapeHtml(petition.titreP)}</h3>
                    <p class="text-gray-600 text-sm mb-4 flex-grow">${escapeHtml(petition.descriptionP)}</p>
                    <div class="mt-auto space-y-3">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: ${percentage}%"></div>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-semibold text-gray-700"><span id="sig-count-${petition.idP}">${petition.nombre_signatures_actuel}</span> / ${petition.Objectif_signature} signatures</span>
                            <span class="text-gray-500">${Math.round(percentage)}%</span>
                        </div>
                        <div class="text-xs text-gray-500 border-t pt-2">${endDate}</div>
                    </div>
                    ${actionButton}
                </div>`;
        }
        
        function showActionNotification(message, status) {
            actionNotification.textContent = message;
            actionNotification.className = `fixed bottom-4 left-4 text-white px-4 py-3 rounded-lg shadow-lg transition-opacity duration-500 ease-in-out opacity-0 ${
                status === 'success' ? 'bg-green-600' : (status === 'error' ? 'bg-red-600' : 'bg-blue-600')
            }`;

            actionNotification.classList.remove('hidden');
            setTimeout(() => actionNotification.style.opacity = '1', 10);
            setTimeout(() => {
                actionNotification.style.opacity = '0';
                setTimeout(() => actionNotification.classList.add('hidden'), 500);
            }, 4000);
        }

        function fetchNewPetitions(isManualRefresh = false) {
            if (isManualRefresh) {
                refreshBtn.disabled = true;
                refreshIcon.classList.add('animate-spin');
                refreshText.textContent = 'Chargement...';
            }

            fetch('get_approved_petitions_json.php')
                .then(response => response.json())
                .then(data => {
                    if (displayedPetitionIds.size === 0) { // Premier chargement
                        if (data.petitions.length > 0) {
                            grid.innerHTML = '';
                            data.petitions.forEach(petition => {
                                const cardHtml = createPetitionCard(petition, data.signed_ids);
                                grid.insertAdjacentHTML('beforeend', cardHtml);
                            });
                            data.petitions.forEach(p => displayedPetitionIds.add(p.idP));
                        } else {
                            noPetitionsMessage.innerHTML = '<h3 class="text-xl font-semibold text-gray-700">Aucune pétition active</h3><p class="text-gray-500 mt-2">Soyez le premier à en créer une !</p>';
                            noPetitionsMessage.classList.remove('hidden');
                        }
                        // Animer les cartes après les avoir ajoutées
                        Array.from(grid.children).forEach((card, index) => {
                            setTimeout(() => {
                                card.style.opacity = '1';
                                card.style.transform = 'translateY(0)';
                            }, index * 100);
                        });
                    } else { // Rechargement (manuel ou automatique)
                        const newPetitions = data.petitions.filter(p => !displayedPetitionIds.has(p.idP));
                        if (newPetitions.length > 0) {
                            if (!isManualRefresh) {
                                petitionNotification.classList.remove('hidden');
                                setTimeout(() => petitionNotification.style.opacity = '1', 10);
                                setTimeout(() => {
                                    petitionNotification.style.opacity = '0';
                                    setTimeout(() => petitionNotification.classList.add('hidden'), 500);
                                }, 4000);
                            }
                            
                            newPetitions.sort((a, b) => new Date(b.dateApproved) - new Date(a.dateApproved));
                            newPetitions.forEach(petition => { // Boucle pour insérer les nouvelles pétitions
                                const cardHtml = createPetitionCard(petition, data.signed_ids);
                                grid.insertAdjacentHTML('afterbegin', cardHtml);
                                const newCard = grid.firstElementChild;
                                requestAnimationFrame(() => {
                                    newCard.style.opacity = '1';
                                    newCard.style.transform = 'translateY(0)';
                                }); // Animation fluide
                                displayedPetitionIds.add(petition.idP);
                            });

                            if (noPetitionsMessage.classList.contains('hidden') === false) {
                                noPetitionsMessage.classList.add('hidden');
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error("Erreur de chargement des pétitions:", error);
                    grid.innerHTML = `<p class="text-red-500 col-span-full text-center py-10">Impossible de charger les pétitions.</p>`;
                })
                .finally(() => {
                    if (isManualRefresh) {
                        setTimeout(() => {
                            refreshBtn.disabled = false;
                            refreshIcon.classList.remove('animate-spin');
                            refreshText.textContent = 'Actualiser';
                        }, 500);
                    }
                });
        }

        function fetchMostSignedPetition() {
            fetch('get_most_signed_petition_json.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('most-signed-petition');
                    if (data) {
                        const percentage = data.Objectif_signature > 0 ? Math.min(100, (data.signature_count / data.Objectif_signature) * 100) : 0;
                        container.innerHTML = `
                            <h3 class="text-xl font-bold text-indigo-700 mb-2">${escapeHtml(data.titreP)}</h3>
                            <p class="text-gray-600 text-sm mb-4">${escapeHtml(data.descriptionP)}</p>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-yellow-500 h-2.5 rounded-full" style="width: ${percentage}%"></div>
                            </div>
                            <div class="flex justify-between items-center text-sm mt-2">
                                <span class="font-semibold text-gray-700">${data.signature_count} signatures</span>
                                <span class="text-gray-500">${Math.round(percentage)}%</span>
                            </div>`;
                    } else {
                        container.innerHTML = `<p class="text-gray-500 text-center">Aucune pétition n'a encore été signée.</p>`;
                    }
                });
        }

        function fetchLastSignatures() {
            fetch('get_last_signatures_json.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('dernieres-signatures');
                    if (data.length > 0) {
                        if (lastSignatureId !== 0 && data[0].idS > lastSignatureId) {
                            signatureNotification.classList.remove('hidden');
                            setTimeout(() => signatureNotification.style.opacity = '1', 10);
                            setTimeout(() => {
                                signatureNotification.style.opacity = '0';
                                setTimeout(() => signatureNotification.classList.add('hidden'), 500);
                            }, 3000);
                        }
                        lastSignatureId = data[0].idS;

                        container.innerHTML = data.map(sig => `
                            <div class="text-sm text-gray-600 py-1">
                                <strong>${escapeHtml(sig.prenom)} ${escapeHtml(sig.nom)}</strong> a signé la pétition <em>"${escapeHtml(sig.titreP)}"</em>.
                            </div>`).join('');
                    } else {
                        container.innerHTML = `<p class="text-gray-500 text-center">Aucune signature récente.</p>`;
                    }
                });
        }

        // Gestion de la signature en AJAX
        grid.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('sign-btn')) {
                e.preventDefault();
                const button = e.target;
                const petitionId = button.dataset.petitionId;

                button.disabled = true;
                button.textContent = 'Signature en cours...';

                const formData = new FormData();
                formData.append('action', 'sign');
                formData.append('petition_id', petitionId);

                fetch('User_petition_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showActionNotification(data.message, data.status);

                    if (data.status === 'success') {
                        // Mettre à jour l'UI
                        button.textContent = 'Déjà signé';
                        button.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                        button.classList.add('bg-gray-400', 'cursor-not-allowed');
                        // Mettre à jour le compteur de signatures
                        const countSpan = document.getElementById(`sig-count-${petitionId}`);
                        if(countSpan) {
                            countSpan.textContent = parseInt(countSpan.textContent) + 1;
                        }
                        fetchLastSignatures(); // Rafraîchir immédiatement la liste des dernières signatures
                    } else {
                        // Réactiver le bouton en cas d'erreur (sauf si déjà signé)
                        button.disabled = (data.status === 'info');
                        button.textContent = (data.status === 'info') ? 'Déjà signé' : 'Signer';
                    }
                }).catch(err => console.error('Erreur AJAX:', err));
            }
        });

        // Chargements initiaux
        fetchNewPetitions(true);
        fetchMostSignedPetition();
        fetchLastSignatures();

        // Intervalles de rafraîchissement
        setInterval(() => fetchNewPetitions(false), 15000); // Vérifie les nouvelles pétitions
        setInterval(fetchLastSignatures, 8000); // Vérifie les dernières signatures
        setInterval(fetchMostSignedPetition, 60000); // Met à jour la pétition populaire

        // Écouteur pour le bouton d'actualisation manuelle
        refreshBtn.addEventListener('click', () => fetchNewPetitions(true));
    });
  </script>
</body>
</html>
