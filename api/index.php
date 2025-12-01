<?php
header("Access-Control-Allow-Origin: *");
// Activar reportes de error 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir las clases
require_once 'classes/User.php';
require_once 'classes/AuthService.php';
require_once 'classes/ApiRouter.php';

// Configurar encabezados CORS (necesario para que el cliente JS pueda hacer peticiones)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Si el método es OPTIONS (preflight), responder con 200 OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Inicializar el servicio y el router
$authService = new AuthService();
$router = new ApiRouter($authService);

// Manejar la solicitud
$router->handleRequest();
?>