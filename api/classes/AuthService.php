<?php
// Clase AuthService: Lógica de autenticación y manejo de tokens simples
class AuthService {
    
    // Array predefinido de usuarios para simular la base de datos
    private $users = [
        ["username" => "admin", "password" => "1234"],
        ["username" => "user", "password" => "abcd"]
    ];

    /**
     * Valida las credenciales contra el array predefinido.
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function validateCredentials($username, $password) {
        foreach ($this->users as $user) {
            if ($user["username"] === $username && $user["password"] === $password) {
                return true;
            }
        }
        return false;
    }

    /**
     * Genera un token simple codificando el nombre de usuario en Base64. 
     * @param string $username
     * @return string Token en Base64
     */
    public function generateToken($username) {
        $payload = json_encode(["user" => $username, "iat" => time()]);
        return base64_encode($payload);
    }

    /**
     * Valida si un token es "válido" (no está vacío y contiene un nombre de usuario decodificable).
     * @param string $token
     * @return string|null Nombre de usuario si es válido, o null si no lo es.
     */
    public function validateToken($token) {
        if (empty($token)) {
            return null;
        }
        
        // El token se decodifica para extraer el nombre de usuario
        $payload = base64_decode($token);
        $data = json_decode($payload, true);

        // Se valida que la estructura decodificada tenga el campo 'user'
        if (isset($data['user']) && !empty($data['user'])) {
            // Se puede agregar lógica de expiración (exp) aquí si se incluyera en el payload
            return $data['user'];
        }
        
        return null;
    }
}
?>