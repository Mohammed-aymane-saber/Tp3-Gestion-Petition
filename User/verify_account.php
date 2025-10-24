<?php
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vérification du Compte</title>
  <link rel="icon" type="image/png" href="../Assets/Images/favicon.png"> <!-- Ajout du Favicon -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col justify-center items-center text-gray-800 font-sans">

  <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-md border border-gray-100 text-center">
    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
    </div>
    <h2 class="text-2xl font-bold text-indigo-600 mb-2">Vérifiez votre email</h2>
    <p class="text-gray-600 mb-6">Un code de confirmation à 6 chiffres a été envoyé à <strong class="text-gray-800"><?php echo $email; ?></strong>.</p>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="Actions/verify_account_action.php" method="POST" class="space-y-4">
      <input type="hidden" name="email" value="<?php echo $email; ?>">
      <input type="text" name="code" placeholder="_ _ _ _ _ _" required maxlength="6"
             class="w-full p-3 text-center text-2xl tracking-[1em] border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      
      <button type="submit"
              class="w-full bg-indigo-600 text-white py-3 rounded-xl hover:bg-indigo-700 transition duration-300 shadow">
        Vérifier le compte
      </button>
    </form>

    <p class="text-center text-gray-600 mt-6 text-sm">
      Vous n'avez pas reçu de code ? 
      <a href="#" class="text-indigo-600 hover:underline font-medium">Renvoyer le code</a>
    </p>
  </div>

  <footer class="mt-6 text-sm text-gray-500">© 2025 Gestion des Pétitions — Tous droits réservés.</footer>
</body>
</html>