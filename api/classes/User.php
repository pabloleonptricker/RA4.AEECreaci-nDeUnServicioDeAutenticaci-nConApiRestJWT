<?php
// Clase User: Modelo de datos simple (no se usa mucho, pero mantiene la estructura)
class User {
    public $username;
    public $password;

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
}
?>