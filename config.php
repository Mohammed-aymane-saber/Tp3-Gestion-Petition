<?php
/**
 * Fichier de configuration global de l'application.
 */

// Détecter si le serveur utilise HTTPS
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// Définir l'URL de base. Remplacez par votre domaine Elastic Beanstalk.
define('BASE_URL', $protocol . 'Saber-Gestion-Peition.eu-west-3.elasticbeanstalk.com');

// Vous pouvez ajouter d'autres configurations ici (DB, etc.)
?>