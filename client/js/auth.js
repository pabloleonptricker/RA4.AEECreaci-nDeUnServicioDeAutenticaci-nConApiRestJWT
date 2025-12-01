
const API_BASE_URL = 'http://localhost/RA4.AEECreaci-nDeUnServicioDeAutenticaci-nConApiRestJWT/api';
const TOKEN_KEY = 'authToken'; // Clave para localStorage

/**
 * Almacena el token en localStorage.
 * @param {string} token - El token simple devuelto por la API.
 */
function setToken(token) {
    localStorage.setItem(TOKEN_KEY, token);
}

/**
 * Obtiene el token de localStorage.
 * @returns {string | null}
 */
function getToken() {
    return localStorage.getItem(TOKEN_KEY);
}

/**
 * Elimina el token de localStorage.
 */
function removeToken() {
    localStorage.removeItem(TOKEN_KEY);
}

/**
 * Realiza una solicitud fetch a un endpoint.
 * @param {string} endpoint - La ruta a la que llamar (ej: '/welcome').
 * @param {string} method - Método HTTP (GET, POST).
 * @param {object} body - Datos a enviar en el cuerpo (para POST).
 * @returns {Promise<Response>}
 */
async function callApi(endpoint, method = 'GET', body = null) {
    const token = getToken();
    const headers = {
        'Content-Type': 'application/json'
    };

    // Agregar la cabecera de autorización para solicitudes protegidas
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const config = {
        method: method,
        headers: headers,
        // Solo incluir body para métodos que lo requieran (POST, PUT, etc.)
        body: body ? JSON.stringify(body) : null
    };

    return fetch(`${API_BASE_URL}${endpoint}`, config);
}

/**
 * Lógica para la pantalla de login.
 * Esta función es llamada cuando se envía el formulario de login.
 */
async function handleLogin(event) {
    // Prevenir el envío tradicional del formulario
    event.preventDefault(); 
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const messageElement = document.getElementById('message');
    messageElement.textContent = ''; // Limpiar mensajes anteriores

    try {
        const response = await callApi('/login', 'POST', { username, password });
        const data = await response.json();

        if (response.ok) { // Código 200
            // Almacenar el token y redirigir
            setToken(data.token);
            window.location.href = 'welcome.html';
        } else if (response.status === 401) {
            // Error de credenciales (Unauthorized)
            messageElement.textContent = data.message || 'Error: Credenciales no válidas.';
        } else {
            // Otros errores del servidor
            messageElement.textContent = `Error ${response.status}: ${data.message || 'Fallo en el servidor.'}`;
        }
    } catch (error) {
        // Error de red (API no disponible)
        messageElement.textContent = 'Error de conexión con la API. Revise la URL o si el servidor PHP está corriendo.';
        console.error('Error en fetch:', error);
    }
}

/**
 * Lógica para cerrar la sesión (llamada desde welcome.html).
 */
function handleLogout() {
    removeToken(); // Eliminar el token
    window.location.href = 'login.html'; // Redirigir al login
}

/**
 * Lógica de inicialización para la página de bienvenida.
 * Llama a la API protegida '/welcome'.
 */
async function initWelcomePage() {
    const token = getToken();

    if (!token) {
        // No hay token: Redirigir a "No Tienes Permisos"
        window.location.href = 'forbidden.html';
        return;
    }

    try {
        const response = await callApi('/welcome');

        if (response.ok) { // Código 200
            const data = await response.json();
            // Mostrar datos del usuario
            document.getElementById('usernameDisplay').textContent = data.username;
            document.getElementById('welcomeMessage').textContent = data.message;
            document.getElementById('currentTime').textContent = data.currentTime;
            document.getElementById('additionalMessage').textContent = data.additionalMessage;
        } else if (response.status === 403) {
            // Token inválido o expirado (Forbidden)
            removeToken(); // Limpiar token inválido
            window.location.href = 'forbidden.html';
        } else {
            // Manejar otros errores
            console.error(`Error al cargar datos: ${response.status}`);
            window.location.href = 'forbidden.html';
        }
    } catch (error) {
        console.error('Error de red:', error);
        window.location.href = 'forbidden.html';
    }
}