<?php
// firebase_init.php (à la racine)
require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount(__DIR__ . '/firebase_credentials.json'); // <-- place ton fichier JSON ici

$auth = $factory->createAuth();
