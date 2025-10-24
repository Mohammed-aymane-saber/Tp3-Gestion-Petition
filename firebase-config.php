<?php
/**
 * ==========================================================================
 * ==      FICHIER DE CONFIGURATION FIREBASE POUR LE CLIENT-SIDE           ==
 * ==========================================================================
 *
 * Ce script injecte la configuration Firebase dans le JavaScript en utilisant
 * les variables d'environnement définies dans Elastic Beanstalk.
 *
 * Incluez ce fichier dans les pages qui utilisent Firebase (Login, Register, etc.).
 */

// Récupérer les variables d'environnement. Utiliser 'null' si non défini.
$firebase_api_key = $_SERVER['FIREBASE_API_KEY'] ?? null;
$firebase_auth_domain = $_SERVER['FIREBASE_AUTH_DOMAIN'] ?? null;
$firebase_project_id = $_SERVER['FIREBASE_PROJECT_ID'] ?? null;
$firebase_storage_bucket = $_SERVER['FIREBASE_STORAGE_BUCKET'] ?? null;
$firebase_messaging_sender_id = $_SERVER['FIREBASE_MESSAGING_SENDER_ID'] ?? null;
$firebase_app_id = $_SERVER['FIREBASE_APP_ID'] ?? null;

?>
<!-- Firebase SDK -->
<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js";

  // Your web app's Firebase configuration
  const firebaseConfig = {
    apiKey: "<?php echo $firebase_api_key; ?>",
    authDomain: "<?php echo $firebase_auth_domain; ?>",
    projectId: "<?php echo $firebase_project_id; ?>",
    storageBucket: "<?php echo $firebase_storage_bucket; ?>",
    messagingSenderId: "<?php echo $firebase_messaging_sender_id; ?>",
    appId: "<?php echo $firebase_app_id; ?>"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  // Export 'app' or other Firebase services if needed by other scripts
  export { app };
</script>