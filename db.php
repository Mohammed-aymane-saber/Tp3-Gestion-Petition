<?php
// c:/xampp/htdocs/Tp3/db.php

/**
 * Ce fichier gère la connexion à la base de données.
 * Il utilise les variables d'environnement fournies par AWS Elastic Beanstalk.
 * Pour le développement local (XAMPP), il utilise des valeurs par défaut.
 */

// Lire les variables d'environnement ou utiliser des valeurs par défaut
$db_host = $_SERVER['DB_HOST'] ?? 'localhost';
$db_name = $_SERVER['DB_NAME'] ?? 'votre_bdd_locale'; // Remplacez par le nom de votre BDD sur XAMPP
$db_user = $_SERVER['DB_USER'] ?? 'root'; // Votre utilisateur XAMPP
$db_pass = $_SERVER['DB_PASS'] ?? ''; // Votre mot de passe XAMPP
$db_port = $_SERVER['DB_PORT'] ?? '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // En production, ne jamais afficher les détails de l'erreur à l'utilisateur.
    // Loggez l'erreur dans un fichier pour le débogage.
    error_log('Database Connection Error: ' . $e->getMessage());
    // Affichez un message générique.
    http_response_code(503); // Service Unavailable
    die("Erreur de connexion au service. Veuillez réessayer plus tard.");
}