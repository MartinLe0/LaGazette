<?php
// Router script pour le serveur PHP intégré
// Sert les fichiers statiques directement, sinon passe à index.php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Si le fichier existe, le servir directement
if ($uri !== '/' && file_exists(__DIR__.$uri)) {
    return false;
}

// Sinon, passer à index.php (Symfony)
require_once __DIR__.'/index.php';
