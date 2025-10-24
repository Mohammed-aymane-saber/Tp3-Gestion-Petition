<?php
// c:/xampp/htdocs/Tp3/db.php

/**
 * Ce fichier gère la connexion à la base de données.
 * Il détecte s'il est sur un environnement AWS Elastic Beanstalk (avec RDS)
 * ou en développement local (XAMPP).
 */

// Vérifier si les variables d'environnement RDS d'AWS sont présentes
if (isset($_SERVER['RDS_HOSTNAME'])) {
    // Environnement de production (AWS)
    $db_host = $_SERVER['RDS_HOSTNAME'];
    $db_port = $_SERVER['RDS_PORT'];
    $db_name = $_SERVER['RDS_DB_NAME'];
    $db_user = $_SERVER['RDS_USERNAME'];
    $db_pass = $_SERVER['RDS_PASSWORD'];
} else {
    // Environnement de développement local (XAMPP)
    $db_host = 'localhost';
    $db_port = '3306';
    $db_name = 'tp3'; // Le nom de votre base de données locale
    $db_user = 'root';
    $db_pass = '';
}

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
    error_log('Database Connection Error: ' . $e->getMessage());
    http_response_code(503);
    die("Service temporairement indisponible en raison d'un problème de connexion à la base de données.");
}