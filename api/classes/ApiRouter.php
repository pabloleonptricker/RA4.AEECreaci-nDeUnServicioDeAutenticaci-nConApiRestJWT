<?php
// ApiRouter: Maneja las rutas y actúa como controlador de la API REST.
class ApiRouter
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Envía una respuesta en formato JSON.
     * @param array $data Datos a devolver.
     * @param int $httpCode Código de estado HTTP.
     */
    private function sendResponse($data, $httpCode)
    {
        header('Content-Type: application/json');
        http_response_code($httpCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Maneja la solicitud de login (POST /api/login).
     */
    private function handleLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(["message" => "Método no permitido"], 405);
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if ($this->authService->validateCredentials($username, $password)) {
            $token = $this->authService->generateToken($username);
            $this->sendResponse([
                "message" => "Autenticación exitosa",
                "token" => $token
            ], 200);
        } else {
            $this->sendResponse([
                "message" => "Credenciales incorrectas. Acceso no autorizado."
            ], 401);
        }
    }

    /**
     * Maneja la solicitud de bienvenida (GET /api/welcome).
     */
    private function handleWelcome()
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

        // Se espera el formato: Authorization: Bearer <token>
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            $token = '';
        }

        $username = $this->authService->validateToken($token);

        if ($username) {
            $this->sendResponse([
                "message" => "Bienvenido al área protegida, {$username}!",
                "username" => $username,
                "currentTime" => date("H:i:s"),
                "additionalMessage" => "¡Estamos encantados de tenerte aquí!"
            ], 200);
        } else {
            $this->sendResponse([
                "message" => "Acceso prohibido. Token inválido o ausente."
            ], 403);
        }
    }

    /**
     * Método principal para enrutar la solicitud.
     */
    public function handleRequest()
    {
        // 1. Obtener la URI completa
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // 2. Obtener la ruta del script para calcular la base de la API
        // Esto elimina automáticamente la carpeta raíz del proyecto.
        $script_path = $_SERVER['SCRIPT_NAME'];
        $api_base_path = dirname($script_path);

        // 3. Limpiar la URI para obtener solo el endpoint deseado (ej: /login)
        $endpoint = str_replace($api_base_path, '', $uri);

        // Aseguramos que el endpoint empiece con '/'
        if (substr($endpoint, 0, 1) !== '/') {
            $endpoint = '/' . $endpoint;
        }

        // Si el endpoint es solo "//", lo ajustamos a "/"
        if ($endpoint === '//') {
            $endpoint = '/';
        }

        // El enrutamiento debe usar el endpoint limpio
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $endpoint === '/login') {
            $this->handleLogin();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $endpoint === '/welcome') {
            $this->handleWelcome();
        } else {
            // Ruta no encontrada
            $this->sendResponse([
                "message" => "Ruta no encontrada.",
                "debug_endpoint" => $endpoint // Muestra qué ruta intentó acceder el cliente
            ], 404);
        }
    }
}
