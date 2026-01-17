<?php
/**
 * Front Controller - Point d'entrée unique de l'application
 */

// Démarrer la session
if (session_id() === '') {
    session_start();
}

// Inclure la configuration
require_once __DIR__ . '/../App/Config/Settings.php';

// Inclure l'autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\VirtualHostController;
use App\Controllers\ApacheLogController;
use App\Controllers\ServiceController;
use App\Controllers\Api\ServerStatusController;
use App\Controllers\Api\MonitoringController;

// Récupérer l'URI et la nettoyer
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestUri = strtok($requestUri, '?'); // Retirer les paramètres GET

// Détecter et retirer le base path (ex: /dashboard/Public)
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && strpos($requestUri, $scriptName) === 0) {
    $requestUri = substr($requestUri, strlen($scriptName));
}

// Nettoyer l'URI
$requestUri = '/' . trim($requestUri, '/');
if ($requestUri === '/') {
    // URI racine, OK
} else {
    // Retirer le slash final pour les autres routes
    $requestUri = rtrim($requestUri, '/');
}

// Définir les routes
$routes = [
    '/' => [HomeController::class, 'index'],
    '/vhosts' => [VirtualHostController::class, 'index'],
    '/vhosts/store' => [VirtualHostController::class, 'store'],
    '/services' => [ServiceController::class, 'index'],
    '/api/server-status' => [ServerStatusController::class, 'index'],
    '/api/monitoring/current' => [MonitoringController::class, 'current'],
    '/api/monitoring/history' => [MonitoringController::class, 'history'],
    '/logs' => [ApacheLogController::class, 'index'],
];

// Router la requête
if (isset($routes[$requestUri])) {
    $route = $routes[$requestUri];
    $controllerClass = $route[0];
    $method = $route[1];

    $controller = new $controllerClass();
    $controller->$method();
} else {
    // Page 404
    http_response_code(404);
    echo '<h1>404 - Page non trouvée</h1>';
    echo '<p><a href="/">Retour à l\'accueil</a></p>';
}
