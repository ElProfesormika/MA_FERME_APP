<?php
// Configuration pour Railway
// Les variables d'environnement sont automatiquement injectées par Railway

return [
    'host' => $_ENV['MYSQL_HOST'] ?? 'localhost',
    'database' => $_ENV['MYSQL_DATABASE'] ?? 'ferme_db',
    'username' => $_ENV['MYSQL_USERNAME'] ?? 'root',
    'password' => $_ENV['MYSQL_PASSWORD'] ?? '',
    'charset' => 'utf8mb4'
];
?>
