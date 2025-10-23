<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription - Gestion des Pétitions</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col justify-center items-center text-gray-800 font-sans">

  <!-- Carte d'inscription -->
  <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-md border border-gray-100">
    <h2 class="text-2xl font-bold text-center text-indigo-600 mb-6">Créer un compte</h2>
    
    <!-- Formulaire original stylisé -->
    <form action="Actions/Register_action.php" method="POST" class="space-y-4">

      <input type="text" name="nom" placeholder="Nom" required
             class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">

      <input type="text" name="prenom" placeholder="Prénom" required
             class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">

      <input type="email" name="email" placeholder="Email" required
             class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">

      <input type="date" name="date_naissance" placeholder="Date de naissance" required
             class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">

      <input type="password" name="password" placeholder="Mot de passe" required
             class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">

      <input type="password" name="password_confirmation" placeholder="Confirmation du mot de passe" required
             class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">

      <button type="submit"
              class="w-full bg-indigo-600 text-white py-3 rounded-xl hover:bg-indigo-700 transition duration-300 shadow">
        S'inscrire
      </button>

      <p class="text-center text-gray-600 mt-4">
        Déjà inscrit ? 
        <a href="login_view.php" class="text-indigo-600 hover:underline font-medium">
          Connectez-vous
        </a>
        <br>
        <a href="../index.php">Retour a la page d'accueil</a>
      </p>


    </form>
  </div>

  <!-- Pied de page -->
  <footer class="mt-6 text-sm text-gray-500">
    © 2025 Gestion des Pétitions — Tous droits réservés.
  </footer>

</body>
</html>
