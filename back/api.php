<?php

//1. Configuración inicial y CORS

//1.1. Cabeceras JSON:
//Configurar las cabeceras HTTP para indicar que la respuesta 
//será en formato JSON y permitir solicitudes desde cualquier origen.
header("Content-Type: application/json; charset=UTF-8");

//1.2. CORS (Cross-Origin Resource Sharing):
//Permitir peticiones desde cualquier origen (necesario cuando 
//el frontend y el backend están en diferentes puertos o 
//dominios, o simplemente para simplificar la prueba).
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

//1.3. Manejo de opciones:
//Responder a las solicitudes OPTIONS (preflight requests)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

//2. Simulación de la base de datos y credenciales

//2.1. Lista de usuarios:
//Definir el array $usuarios con las creedenciales predefinidas.
//("admin" / "password123", "user" / "userpass") Por ejemplo.
$usuarios = [
    ["username" => "admin", "password" => "1234"],
    ["username" => "user", "password" => "abcd"],
    ["username" => "caros", "password" => "diezparapablo"]
];

//Función para enviar respuestas JSON
function enviarRespuesta($status, $data = null){
    http_response_code($status);
    echo json_encode($data);
    exit();
}

//Función para generar un token simulado
function generarToken($username){
    // Crear un payload simple con el nombre de usuario
    $payload = [
        'user' => $username,
        'iat' => time(),
        'exp' => time() + (60 * 60) // Expira en 1 hora
    ];
    // Codificamos el payload.
    return base64_encode(json_encode($payload));
}

//3. Enrutamiento de la API (Routing)

// Obtener la ruta solicitada (ej. /api.php/login o /api.php/welcome)
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Eliminar el nombre del script de la URI y limpiar barras para obtener la ruta lógica
$route = str_replace($script_name, '', $request_uri);
$route = trim($route, '/');

// El método HTTP de la petición
$method = $_SERVER['REQUEST_METHOD'];

//3.1. Detección de Ruta:
//Analizar la URL o el parámetro de la petición para determinar
//si la ruta es /login o /welcome.

//3.2. Detección de Método:
//Identificar si el método es POST (para login) o GET (para 
//welcome).

//Logica de enrutamiento:
if ($method === 'POST' && $route === 'login') {
    handleLogin();
} elseif ($method === 'GET' && $route === 'welcome') {
    handleWelcome();
} else {
    // Ruta o método no soportado
    sendResponse(404, ['error' => 'Ruta no encontrada o método no permitido.']);
}

//4. Endpoint POST /login
function handleLogin() {
    global $usuarios;

//4.1. Lectura de Entrada:
//Leer y decodificar el cuerpo de la petición (JSON) para obtener username y password.
$json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

//4.2. Validación de Credenciales:
//Buscar las credenciales en el array $usuarios.
$authenticatedUser = null;
    foreach ($usuarios as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            $authenticatedUser = $user;
            break;
        }
    }

    if ($authenticatedUser) {
    //4.3 Respuesta exitosa (200 OK):
    //Si las credenciales son válidas, generar un "token JWT 
    //simulado" (usando base64_encode) y devolverlo en formato JSON.
        $token = generateToken($username);
        sendResponse(200, [
            'message' => 'Autenticación exitosa',
            'token' => $token,
            'username' => $username
        ]);
    } else {
    //4.4. Respuesta Fallida (401 Unauthorized):
    //Si las credenciales son incorrectas, responder con el código 
    //HTTP 401 y un mensaje de error JSON.
        sendResponse(401, ['error' => 'Credenciales inválidas']);
    }

//5. Endpoint GET /welcome (Ruta Protegida)

//5.1. Extracción del Token:
//Intentar obtener el token del encabezado Authorization 
//(Bearer <token>).

//5.2. Validación del Token:
//Validar que el token exista y sea un token válido y no 
//expirado (simularemos que cualquier token devuelto en el 
//login es válido por ahora).

//5.3. Respuesta Exitosa (200 OK):
//Si el token es válido, devolver los datos del usuario 
//(ej. nombre) y la hora actual en JSON.

//5.4. Respuesta Fallida (403 Forbidden):
//Si el token está ausente o es inválido, responder con el 
//código HTTP 403 y un mensaje de error JSON.

?>