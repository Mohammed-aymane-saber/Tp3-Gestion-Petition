<?php
require '../config.php'; 

$remembered_email = $_COOKIE['email_user'] ?? $_COOKIE['email_admin'] ?? '';
$remembered_password = $_COOKIE['password_user'] ?? $_COOKIE['password_admin'] ?? '';
$is_remembered = !empty($remembered_email);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Connexion - Gestion des Pétitions</title>
  <script src="https://cdn.tailwindcss.com"></script>

 
<!-- Import Firebase (modulaire) -->
  <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/12.4.0/firebase-app.js";
    import { getAuth, GoogleAuthProvider, signInWithPopup, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/12.4.0/firebase-auth.js";

   const firebaseConfig = {
    apiKey: "AIzaSyBE2xDWDT_0hn8iI_fB0tjP86P7B_QsBlI",
    authDomain: "petition-tp3.firebaseapp.com",
    projectId: "petition-tp3",
    storageBucket: "petition-tp3.firebasestorage.app",
    messagingSenderId: "755807680113",
    appId: "1:755807680113:web:773ee24bb014fa95040dd0",
    measurementId: "G-J74MJR0DPR"
  };


    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);
    const provider = new GoogleAuthProvider();

    document.addEventListener("DOMContentLoaded", () => {
      const googleBtn = document.getElementById("googleLoginBtn");
      if (googleBtn) {
        googleBtn.addEventListener("click", async () => {
          try {
            const result = await signInWithPopup(auth, provider);
            const user = result.user;

            // Envoie des infos utilisateur à ton backend PHP
            const payload = {
              googleLogin: true,
              email: user.email,
              name: user.displayName,
              uid: user.uid
            };

            const res = await fetch("Actions/Login_action.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(payload)
            });

            if (res.ok) {
              // Utiliser l'URL de base définie en PHP
              window.location.href = "<?php echo BASE_URL; ?>/User/Clients/User_view_petitions.php";
            } else {
              const text = await res.text();
              alert("Erreur serveur : " + text);
            }
          } catch (error) {
            console.error(error);
            alert(error.message);
          }
        });
      }
    });
  </script>
</head>

 
</head>
<body class="bg-gray-50 min-h-screen flex flex-col justify-center items-center text-gray-800 font-sans">

  <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-md border border-gray-100">
    <h2 class="text-2xl font-bold text-center text-indigo-600 mb-6">Se connecter</h2>
    
    <!-- Formulaire classique -->
    <form action="Actions/Login_action.php" method="POST" class="space-y-4">
      <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($remembered_email); ?>"
             class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      
      <input type="password" name="password" placeholder="Mot de passe" required value="<?php echo htmlspecialchars($remembered_password); ?>"
             class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      
      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-slate-600"> 
          <input type="checkbox" name="se_souvenir" value="oui" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" <?php if ($is_remembered) echo 'checked'; ?>> 
          Se souvenir de moi 
        </label>
      </div>
      <button type="submit"
              class="w-full bg-indigo-600 text-white py-3 rounded-xl hover:bg-indigo-700 transition duration-300 shadow">
        Se connecter
      </button>
    </form>

    <div class="my-4 text-center text-gray-500">— ou —</div>

    <!-- Bouton Google -->
    <button id="googleLoginBtn" class="w-full flex items-center justify-center gap-3 border py-2 rounded-xl hover:shadow">
      <img src="https://www.svgrepo.com/show/355037/google.svg" alt="Google" class="w-6 h-6"/>
      <span>Se connecter avec Google</span>
    </button>

    <p class="text-center text-gray-600 mt-4">
      Pas encore inscrit ? 
      <a href="Register_view.php" class="text-indigo-600 hover:underline font-medium">Inscrivez-vous</a>
      <br>
      <a href="../index.php">Retour a la page d'accueil</a>
    </p>
  </div>

  <footer class="mt-6 text-sm text-gray-500">© 2025 Gestion des Pétitions — Tous droits réservés.</footer>
</body>
</html>
